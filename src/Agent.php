<?php

namespace Msc\ChatGPT;

use Flarum\Discussion\Discussion;
use Flarum\Post\CommentPost;
use Flarum\Post\Post;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Illuminate\Support\Arr;
use OpenAI;
use OpenAI\Client;

class Agent
{
    protected int $maxTokens = 100;
    protected string $model;

    public function __construct(
        public readonly User $user,
        protected ?Client    $client = null,
        string               $model = null,
    )
    {
        $this->model = $model ?? 'gpt-3.5-turbo-instruct';
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

        $response = $this->client->completions()->create([
            'model' => $this->model,
            'prompt' => $content,
            'max_tokens' => $this->maxTokens,
        ]);

        if (empty($response->choices)) return;

        $choice = Arr::first($response->choices);
        $respond = $choice->text;

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
