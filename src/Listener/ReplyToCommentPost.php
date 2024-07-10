<?php

namespace Msc\ChatGPT\Listener;

use Flarum\Post\Event\Posted;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Support\Arr;
use Msc\ChatGPT\Agent;
use Msc\ChatGPT\Job\ReplyPostJob;

class ReplyToCommentPost
{
    public function __construct(
        protected Agent $agent,
        protected Queue $queue
    )
    {
    }

    /**
     * @param \Flarum\Post\Event\Posted $event
     * @return void
     */
    public function handle(Posted $event): void
    {
        $settings = resolve(SettingsRepositoryInterface::class);
        $enabledTagIds = $settings->get('muhammedsaidckr-chatgpt.enabled-tags', []);
        $enabled = $settings->get('muhammedsaidckr-chatgpt.queue_active');
        $actor = $event->actor;

        if ($enabledTagIds = json_decode($enabledTagIds, true)) {
            $discussion = $event->post->discussion;
            $tagIds = Arr::pluck($discussion->tags, 'id');

            if (!array_intersect($enabledTagIds, $tagIds)) {
                return;
            }
        }

        if($actor->can('discussion.useChatGPTAssistant', $discussion) === false) {
            return;
        }

        if (!$enabled) {
            $this->agent->repliesToCommentPost($event->post);
            return;
        }
        $this->queue->push(new ReplyPostJob($event->post));
    }
}
