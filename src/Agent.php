<?php

namespace Msc\ChatGPT;

use Flarum\Discussion\Discussion;
use Flarum\Post\CommentPost;
use Flarum\Post\Post;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
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
        $content = $discussion->firstPost->content;
        $title = $discussion->title;

        ['role' => $role, 'prompt' => $prompt] = $this->prepareChatForMessage();

        $messages = $this->createMessages($title, $content, $role, $prompt);

        $response = $this->sendCompletionRequest($messages);

        if (empty($response->choices)) {
            return;
        }

        $this->saveResponse($response, $discussion->id);
    }

    public function repliesToCommentPost(CommentPost $commentPost): void
    {
        // get the discussion title
        $discussion = $commentPost->discussion;
        $title = $discussion->title;
        $content = $discussion->firstPost->content;

        if (!$this->checkIfAssistantCanReplyToPost($commentPost)) {
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
        $messages[] = $this->createMessageForUserWithPrompt($title, $commentPost->content, $prompt);

        // answer to the post
        $response = $this->sendCompletionRequest($messages);

        if (empty($response->choices)) {
            return;
        }

        $this->saveResponse($response, $discussion->id);
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
            'input' => $content
        ]);

        $results = Arr::first($response->results);


        resolve('log')->info($results->flagged);

        if (empty($results->flagged)) {
            return true;
        }

        return $results->flagged;
    }


    private function sendCompletionRequest(array $messages)
    {
        return $this->client->chat()->create(
            [
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => $this->maxTokens
            ]
        );
    }


    private function saveResponse($response, $discussionId): bool
    {
        try {
            $choice = Arr::first($response->choices);
            $respond = $choice->message->content;

            if (empty($respond)) {
                return false;
            }

            $userPrompt = $this->user->id;

            CommentPost::reply(
                discussionId: $discussionId,
                content: $respond,
                userId: $userPrompt,
                ipAddress: '127.0.0.1'
            )->save();

            return true;
        } catch (\Exception $e) {
            resolve('log')->error($e->getMessage());
            return false;
        }
    }

    private function createMessages($title, $content, $role, $prompt): array
    {
        return [
            ['role' => 'system', 'content' => $role],
            $this->createMessageForUserWithPrompt($title, $content, $prompt)
        ];
    }

    private function createMessageForUserWithPrompt($title, $content, $prompt): array
    {
        return [
            'role' => 'user',
            'content' => str_replace(
                ['[title]', '[content]'],
                [$title, $content],
                $prompt
            )
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
