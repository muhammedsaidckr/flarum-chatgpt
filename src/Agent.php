<?php

namespace Msc\ChatGPT;

use Flarum\Discussion\Discussion;
use Flarum\Post\CommentPost;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Illuminate\Support\Arr;
use OpenAI;
use OpenAI\Client;


class Agent
{
    protected int $maxTokens;
    protected string $model;

    public function __construct(
        public readonly User $user,
        protected ?Client    $client = null,
        string               $model = null,
        int                  $maxTokens = null
    )
    {
        $this->model = $model ?? 'gpt-3.5-turbo-instruct';
        $this->maxTokens = $maxTokens ?? 100;
    }

    public function repliesTo(Discussion $discussion): void
    {
        $log = resolve('log');

        try {
            $log->info('[ChatGPT] Starting repliesTo for discussion', [
                'discussion_id' => $discussion->id,
                'title' => $discussion->title,
                'model' => $this->model,
                'max_tokens' => $this->maxTokens,
                'is_reasoning_model' => $this->isReasoningModel()
            ]);

            $content = $discussion->firstPost->content;
            $title = $discussion->title;

            ['role' => $role, 'prompt' => $prompt] = $this->prepareChatForMessage();

            $messages = $this->createMessages($title, $content, $role, $prompt);

            $log->info('[ChatGPT] Sending request to OpenAI', [
                'model' => $this->model,
                'message_count' => count($messages),
                'token_param' => $this->isReasoningModel() ? 'max_completion_tokens' : 'max_tokens'
            ]);

            $response = $this->sendCompletionRequest($messages);

            if (empty($response->choices)) {
                $log->warning('[ChatGPT] Empty response from OpenAI', [
                    'discussion_id' => $discussion->id,
                    'model' => $this->model
                ]);
                return;
            }

            $log->info('[ChatGPT] Received response from OpenAI', [
                'discussion_id' => $discussion->id,
                'choices_count' => count($response->choices)
            ]);

            $saved = $this->saveResponse($response, $discussion->id);

            if ($saved) {
                $log->info('[ChatGPT] Successfully saved response', [
                    'discussion_id' => $discussion->id
                ]);
            } else {
                $log->error('[ChatGPT] Failed to save response', [
                    'discussion_id' => $discussion->id
                ]);
            }
        } catch (\Exception $e) {
            $log->error('[ChatGPT] Exception in repliesTo', [
                'discussion_id' => $discussion->id,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function repliesToCommentPost(CommentPost $commentPost): void
    {
        $log = resolve('log');

        try {
            // get the discussion title
            $discussion = $commentPost->discussion;
            $title = $discussion->title;
            $content = $discussion->firstPost->content;

            $log->info('[ChatGPT] Starting repliesToCommentPost', [
                'discussion_id' => $discussion->id,
                'post_id' => $commentPost->id,
                'post_number' => $commentPost->number,
                'model' => $this->model,
                'is_reasoning_model' => $this->isReasoningModel()
            ]);

            if (!$this->checkIfAssistantCanReplyToPost($commentPost)) {
                $log->info('[ChatGPT] Assistant cannot reply to this post', [
                    'post_id' => $commentPost->id,
                    'reason' => 'checkIfAssistantCanReplyToPost returned false'
                ]);
                return;
            }

            ['role' => $role, 'prompt' => $prompt] = $this->prepareChatForMessage();

            $messages = $this->createMessages($title, $content, $role, $prompt);

            $settings = resolve(SettingsRepositoryInterface::class);
            $userPromptId = $settings->get('muhammedsaidckr-chatgpt.user_prompt');

            // get the posts where the number is greater than 1 to the last message not include last message
            $posts = $discussion->posts()
                ->where('number', '>', 1)
                ->where('number', '<', $commentPost->number)
                ->get();

            foreach ($posts as $post) {
                if ($post->type == 'comment') {
                    $messages[] = [
                        'role' => $post->user_id == $userPromptId ? 'assistant' : 'user',
                        'content' => $post->content
                    ];
                }
            }

            // add the last message with the prompt
            $messages[] = $this->createMessageForUser($commentPost->content);

            $log->info('[ChatGPT] Sending request to OpenAI for comment reply', [
                'model' => $this->model,
                'message_count' => count($messages),
                'token_param' => $this->isReasoningModel() ? 'max_completion_tokens' : 'max_tokens'
            ]);

            // answer to the post
            $response = $this->sendCompletionRequest($messages);

            if (empty($response->choices)) {
                $log->warning('[ChatGPT] Empty response from OpenAI for comment', [
                    'post_id' => $commentPost->id,
                    'model' => $this->model
                ]);
                return;
            }

            $log->info('[ChatGPT] Received response from OpenAI for comment', [
                'post_id' => $commentPost->id,
                'choices_count' => count($response->choices)
            ]);

            $saved = $this->saveResponse($response, $discussion->id);

            if ($saved) {
                $log->info('[ChatGPT] Successfully saved comment response', [
                    'discussion_id' => $discussion->id,
                    'post_id' => $commentPost->id
                ]);
            } else {
                $log->error('[ChatGPT] Failed to save comment response', [
                    'discussion_id' => $discussion->id,
                    'post_id' => $commentPost->id
                ]);
            }
        } catch (\Exception $e) {
            $log->error('[ChatGPT] Exception in repliesToCommentPost', [
                'post_id' => $commentPost->id ?? null,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function prepareChatForMessage(): array
    {
        // get settings from the database
        $settings = resolve(SettingsRepositoryInterface::class);
        // get role
        $role = $settings->get('muhammedsaidckr-chatgpt.role');
        if (empty($role)) {
            // if the role is empty, set the role to default
            $role = 'You are a helpful assistant.';
        }
        // get the prompt from the settings
        $prompt = $settings->get('muhammedsaidckr-chatgpt.prompt');
        if (empty($prompt)) {
            // if the prompt is empty, set the prompt to default
            $prompt = 'Write a arguable or thankfully opinion asking or arguing something about an answer that has talked about "[title]" and who talked about [content]. Don\'t talk about what you would like or don\'t like. Speak in a close tone, like you are writing in a Tech Forum. Be random and unpredictable. Answer in [language].';
        }

        return [
            'role' => $role,
            'prompt' => $prompt
        ];
    }

    public function checkModeration(string $title, string $content): bool
    {
        // check if the title or post content includes bad words
        // if it includes bad words, do not reply and give error message
        // if it does not include bad words, continue to reply
        $response = $this->client->moderations()->create([
            'input' => $title . ' ' . $content
        ]);

        $results = Arr::first($response->results);

        // convert results to array
        $res = json_decode(json_encode($results), true);

        return !!$results->flagged;
    }


    private function sendCompletionRequest(array $messages)
    {
        $log = resolve('log');

        try {
            $params = [
                'model' => $this->model,
                'messages' => $messages,
            ];

            // Use max_completion_tokens for reasoning models (o1, o3, o4, gpt-5 series)
            // Use max_tokens for legacy models (gpt-3.5, gpt-4, etc.)
            if ($this->isReasoningModel()) {
                $params['max_completion_tokens'] = $this->maxTokens;
            } else {
                $params['max_tokens'] = $this->maxTokens;
            }

            $log->debug('[ChatGPT] API Request Parameters', [
                'model' => $params['model'],
                'token_param_used' => $this->isReasoningModel() ? 'max_completion_tokens' : 'max_tokens',
                'token_value' => $this->maxTokens,
                'message_count' => count($messages)
            ]);

            $response = $this->client->chat()->create($params);

            $log->debug('[ChatGPT] API Response Received', [
                'has_choices' => !empty($response->choices),
                'choice_count' => count($response->choices ?? [])
            ]);

            return $response;
        } catch (\OpenAI\Exceptions\ErrorException $e) {
            $log->error('[ChatGPT] OpenAI API Error', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'model' => $this->model,
                'is_reasoning_model' => $this->isReasoningModel(),
                'token_param' => $this->isReasoningModel() ? 'max_completion_tokens' : 'max_tokens',
                'token_value' => $this->maxTokens
            ]);
            throw $e;
        } catch (\Exception $e) {
            $log->error('[ChatGPT] Unexpected error in sendCompletionRequest', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'model' => $this->model
            ]);
            throw $e;
        }
    }

    /**
     * Determine if the current model is a reasoning model.
     * Reasoning models (o1, o3, o4, gpt-5 series) require max_completion_tokens
     * instead of max_tokens.
     *
     * @return bool
     */
    private function isReasoningModel(): bool
    {
        $modelLower = strtolower($this->model);

        // Check for reasoning model patterns
        $reasoningPatterns = ['o1', 'o3', 'o4', 'gpt-5'];

        foreach ($reasoningPatterns as $pattern) {
            if (str_contains($modelLower, $pattern)) {
                return true;
            }
        }

        return false;
    }


    private function saveResponse($response, $discussionId): bool
    {
        $log = resolve('log');

        try {
            $choice = Arr::first($response->choices);
            $respond = $choice->message->content;

            if (empty($respond)) {
                $log->warning('[ChatGPT] Empty content in response', [
                    'discussion_id' => $discussionId,
                    'has_choice' => !empty($choice),
                    'has_message' => !empty($choice->message ?? null)
                ]);
                return false;
            }

            $userPrompt = $this->user->id;

            $log->debug('[ChatGPT] Saving response as CommentPost', [
                'discussion_id' => $discussionId,
                'user_id' => $userPrompt,
                'content_length' => strlen($respond)
            ]);

            CommentPost::reply(
                discussionId: $discussionId,
                content: $respond,
                userId: $userPrompt,
                ipAddress: '127.0.0.1'
            )->save();

            return true;
        } catch (\Exception $e) {
            $log->error('[ChatGPT] Exception in saveResponse', [
                'discussion_id' => $discussionId,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    private function createMessages($title, $content, $role, $prompt): array
    {
        return [
            $this->createMessageForSystem($role, $prompt, $title),
            $this->createMessageForUser($content)
        ];
    }

    private function createMessageForSystem($role, $prompt, $title): array
    {
        $prompt = str_replace(
            ['[title]', '[content]'],
            [$title, ''],
            $prompt
        );
        $systemPrompt = $role . ' ' . $prompt;
        return [
            'role' => 'system',
            'content' => $systemPrompt
        ];
    }

    private function createMessageForUser($content): array
    {
        return [
            'role' => 'user',
            'content' => $content
        ];
    }

    private function checkIfAssistantCanReplyToPost($commentPost): bool
    {
        $discussion = $commentPost->discussion;

        $settings = resolve(SettingsRepositoryInterface::class);

        $userPromptId = $settings->get('muhammedsaidckr-chatgpt.user_prompt');
        if ($commentPost->user_id == $userPromptId) {
            return false;
        }

        // is it the first post?
        if ($commentPost->number == 1) {
            return false;
        }


        $maxReplyCount = $settings->get('muhammedsaidckr-chatgpt.continue_to_reply_count');
        $assistantReplyCount = $discussion->posts()->where('type', 'comment')->where('user_id', $userPromptId)->count();

        if ($assistantReplyCount >= $maxReplyCount) {
            return false;
        }

        return true;
    }

}
