<?php

namespace Msc\ChatGPT\Listener;

use Flarum\Discussion\Event\Started;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Support\Arr;
use Msc\ChatGPT\Agent;
use Msc\ChatGPT\Job\ReplyJob;

class ReplyToPost
{
    public function __construct(
        protected Agent $agent,
        protected Queue $queue
    )
    {
    }

    /**
     * @param \Flarum\Discussion\Event\Started $event
     * @return void
     */
    public function handle(Started $event): void
    {
        $settings = resolve(SettingsRepositoryInterface::class);
        $enabled = $settings->get('muhammedsaidckr-chatgpt.queue_active');
        $enabledTagIds = $settings->get('muhammedsaidckr-chatgpt.enabled-tags', []);
        $actor = $event->actor;

        if ($enabledTagIds = json_decode($enabledTagIds, true)) {
            $discussion = $event->discussion;
            $tagIds = Arr::pluck($discussion->tags, 'id');

            if (!array_intersect($enabledTagIds, $tagIds)) {
                return;
            }
        }

        $actor->assertCan('discussion.useChatGPTAssistant', $discussion);

        if (!$enabled) {
            $this->agent->repliesTo($event->discussion);
            return;
        }

        // check queue redis, or database queue is installed

        $this->queue->push(new ReplyJob($event->discussion));
    }
}
