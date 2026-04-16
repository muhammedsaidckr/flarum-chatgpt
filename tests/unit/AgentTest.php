<?php

namespace Msc\ChatGPT\Tests\Unit;

use Msc\ChatGPT\Agent;
use PHPUnit\Framework\TestCase;
use Flarum\User\User;
use Msc\ChatGPT\OpenAIClientInterface;
use Msc\ChatGPT\ChatInterface;
use Msc\ChatGPT\ResponsesInterface;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Mockery;

class AgentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $logMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        \Illuminate\Container\Container::getInstance()->instance('log', $logMock);

        $settingsMock = $this->createMock(SettingsRepositoryInterface::class);
        \Illuminate\Container\Container::getInstance()->instance(SettingsRepositoryInterface::class, $settingsMock);
    }

    protected function tearDown(): void
    {
        \Illuminate\Support\Facades\Facade::clearResolvedInstances();
        Mockery::close();
        parent::tearDown();
    }

    public function testIsGpt5Model()
    {
        $user = $this->createMock(User::class);
        $client = $this->createMock(OpenAIClientInterface::class);

        $agentGpt5 = new Agent($user, $client, 'gpt-5-preview');
        $this->assertTrue($this->invokeMethod($agentGpt5, 'isGpt5Model'));

        $agentGpt4 = new Agent($user, $client, 'gpt-4');
        $this->assertFalse($this->invokeMethod($agentGpt4, 'isGpt5Model'));
    }

    public function testIsReasoningModel()
    {
        $user = $this->createMock(User::class);
        $client = $this->createMock(OpenAIClientInterface::class);

        $agentO1 = new Agent($user, $client, 'o1-preview');
        $this->assertTrue($this->invokeMethod($agentO1, 'isReasoningModel'));

        $agentGpt4 = new Agent($user, $client, 'gpt-4');
        $this->assertFalse($this->invokeMethod($agentGpt4, 'isReasoningModel'));
    }

    public function testSendRequestRouting()
    {
        $user = $this->createMock(User::class);
        $client = $this->createMock(OpenAIClientInterface::class);
        $messages = [['role' => 'user', 'content' => 'hello']];
        $discussionId = 123;

        // Test GPT-5 routing
        $agentGpt5 = new Agent($user, $client, 'gpt-5-preview');
        
        $responsesMock = $this->createMock(ResponsesInterface::class);
        $client->method('responses')->willReturn($responsesMock);
        $responsesMock->expects($this->once())
            ->method('create')
            ->willReturn((object)[
                'content' => 'hi',
                'cot' => 'thought process',
                'finish_reason' => 'stop'
            ]);

        DB::shouldReceive('table')->with('chatgpt_cot')->andReturnSelf();
        DB::shouldReceive('where')->with('discussion_id', $discussionId)->andReturnSelf();
        DB::shouldReceive('first')->andReturn(null);
        DB::shouldReceive('updateOrInsert')->once()->andReturn(true);

        $this->invokeMethod($agentGpt5, 'sendRequest', [$messages, $discussionId]);

        // Test Non-GPT-5 routing
        $agentGpt4 = new Agent($user, $client, 'gpt-4');
        $chatMock = $this->createMock(ChatInterface::class);
        $client->method('chat')->willReturn($chatMock);
        $chatMock->expects($this->once())->method('create')->willReturn((object)['choices' => []]);

        $this->invokeMethod($agentGpt4, 'sendRequest', [$messages, $discussionId]);
    }

    public function testSendCompletionRequestParams()
    {
        $user = $this->createMock(User::class);
        $messages = [['role' => 'user', 'content' => 'hello']];

        // Test reasoning model (o1) uses max_completion_tokens
        $clientO1 = $this->createMock(OpenAIClientInterface::class);
        $chatMockO1 = $this->createMock(ChatInterface::class);
        $clientO1->method('chat')->willReturn($chatMockO1);
        
        $agentO1 = new Agent($user, $clientO1, 'o1-preview', 500);
        $chatMockO1->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($params) {
                return isset($params['max_completion_tokens']) && $params['max_completion_tokens'] === 500 && !isset($params['max_tokens']);
            }))
            ->willReturn((object)['choices' => []]);

        $this->invokeMethod($agentO1, 'sendCompletionRequest', [$messages]);

        // Test legacy model (gpt-4) uses max_tokens
        $clientGpt4 = $this->createMock(OpenAIClientInterface::class);
        $chatMockGpt4 = $this->createMock(ChatInterface::class);
        $clientGpt4->method('chat')->willReturn($chatMockGpt4);

        $agentGpt4 = new Agent($user, $clientGpt4, 'gpt-4', 200);
        $chatMockGpt4->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($params) {
                return isset($params['max_tokens']) && $params['max_tokens'] === 200 && !isset($params['max_completion_tokens']);
            }))
            ->willReturn((object)['choices' => []]);

        $this->invokeMethod($agentGpt4, 'sendCompletionRequest', [$messages]);
    }


    public function testSendResponsesApiRequestCoTFlow()
    {
        $user = $this->createMock(User::class);
        $client = $this->createMock(OpenAIClientInterface::class);
        $messages = [['role' => 'user', 'content' => 'hello']];
        $discussionId = 123;
        $storedCoT = 'some previous thought';
        $newCoT = 'new thought process';

        $agent = new Agent($user, $client, 'gpt-5-preview');

        $responsesMock = $this->createMock(ResponsesInterface::class);
        $client->method('responses')->willReturn($responsesMock);

        // 1. Mock retrieving existing CoT
        DB::shouldReceive('table')->with('chatgpt_cot')->andReturnSelf();
        DB::shouldReceive('where')->with('discussion_id', $discussionId)->andReturnSelf();
        DB::shouldReceive('first')->once()->andReturn((object)['cot' => $storedCoT]);

        // 2. Verify that the API request includes the previous_cot
        $responsesMock->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($params) use ($storedCoT) {
                return isset($params['previous_cot']) && $params['previous_cot'] === $storedCoT;
            }))
            ->willReturn((object)[
                'content' => 'hi',
                'cot' => $newCoT,
                'finish_reason' => 'stop'
            ]);

        // 3. Verify that the new CoT is stored
        DB::shouldReceive('updateOrInsert')->once()->with(
            ['discussion_id' => $discussionId],
            Mockery::on(function ($data) use ($newCoT) {
                return $data['cot'] === $newCoT && isset($data['created_at']);
            })
        )->andReturn(true);

        $this->invokeMethod($agent, 'sendRequest', [$messages, $discussionId]);
    }

    protected function invokeMethod(&$object, $methodName, array $parameters = [])

    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}

