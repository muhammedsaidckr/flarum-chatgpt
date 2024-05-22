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

//    public function operational(): bool
//    {
//        return $this->client !== null;
//    }
//
//    public
//    function is(User $someone): bool
//    {
//        return $this->user->is($someone);
//    }

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

        // check prompt has [language] tag
        if (strpos($prompt, '[language]') !== false) {
            // replace the [language] tag with the language of the discussion
            $prompt = str_replace('[language]', 'turkish', $prompt);
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

        if (empty($response->choices)) return;

        $choice = Arr::first($response->choices);
        $respond = $choice->message->content;

        if (empty($respond)) return;

        $userPrompt = $this->user->id;

//        if (Str::startsWith($respond, 'FLAG: ')) {
//            $flag = new Flag(
//                Str::after($respond, 'FLAG: '),
//                $discussion
//            );
//
//            $flag();
//        } else {
//        $reply = new Reply(
//            reply: $respond,
//            shouldMention: $this->canMention,
//            inReplyTo: $discussion
//        );

        CommentPost::reply(
            discussionId: $discussion->id,
            content: $respond,
            userId: $userPrompt,
            ipAddress: '127.0.0.1'
        )->save();
//        }
    }

}
