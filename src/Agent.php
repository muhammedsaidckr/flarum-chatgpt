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
        protected ?Client $client = null,
        string $model = null,
        int $maxTokens = null
    ) {
        $this->model = $model ?? 'gpt-3.5-turbo-instruct';
        $this->maxTokens = $maxTokens ?? 100;
    }

    public function repliesTo(Discussion $discussion): void
    {
        $content = $discussion->firstPost->content;
        $title = $discussion->title;

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


        // use chat method to reply to the discussion
        $response = $this->client->chat()->create(
            [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $role
                    ],
                    [
                        'role' => 'user',
                        'content' => str_replace(
                            ['[title]', '[content]'],
                            [$title, $content],
                            $prompt
                        )
                    ]
                ],
                'max_tokens' => $this->maxTokens
            ]
        );

        if (empty($response->choices)) {
            return;
        }

        $choice = Arr::first($response->choices);
        $respond = $choice->message->content;

        if (empty($respond)) {
            return;
        }

        $userPrompt = $this->user->id;

        CommentPost::reply(
            discussionId: $discussion->id,
            content: $respond,
            userId: $userPrompt,
            ipAddress: '127.0.0.1'
        )->save();
    }

    public function repliesToCommentPost(CommentPost $commentPost)
    {
        // get the discussion title
        $discussion = $commentPost->discussion;
        $title = $discussion->title;
        $content = $discussion->firstPost->content;


        $settings = resolve(SettingsRepositoryInterface::class);
        $role = $settings->get('muhammedsaidckr-chatgpt.role');
        if (empty($role)) {
            // if the role is empty, set the role to default
            $role = 'You are a helpful assistant.';
        }

        $userPromptId = $settings->get('muhammedsaidckr-chatgpt.user_prompt');

        if ($commentPost->user_id == $userPromptId) {
            return;
        }

        // is it the first post?
        if ($commentPost->number == 1) {
            return;
        }
        $messages = [];
        $messages[] = ['role' => 'system', 'content' => $role];
        $messages[] = [
            'role' => 'user',
            'content' => str_replace(
                ['[title]', '[content]'],
                [$title, $content],
                $settings->get('muhammedsaidckr-chatgpt.prompt')
            )
        ];

        foreach ($discussion->posts()->where('number', '>', 1)->get() as $post) {
            if ($post->type == 'comment') {
                $messages[] = [
                    'role' => $post->user_id == $userPromptId ? 'assistant' : 'user',
                    'content' => $post->content
                ];
            }
        }

        // log the messages
        resolve('log')->info('Messages', $messages);

        // answer to the post
        $response = $this->client->chat()->create(
            [
                'model' => $this->model,
                'messages' => $messages,
                'max_tokens' => $this->maxTokens
            ]
        );

        if (empty($response->choices)) {
            return;
        }

        $choice = Arr::first($response->choices);
        $respond = $choice->message->content;

        if (empty($respond)) {
            return;
        }

        CommentPost::reply(
            discussionId: $discussion->id,
            content: $respond,
            userId: $userPromptId,
            ipAddress: '127.0.0.1'
        )->save();
    }

}
