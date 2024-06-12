<?php

namespace Msc\ChatGPT\Listener;

use Flarum\Post\Event\Posted;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Queue\Queue;
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
        $enabled = $settings->get('muhammedsaidckr-chatgpt.queue_active');

        if (!$enabled) {
            $this->agent->repliesToCommentPost($event->post);
            return;
        }
        $this->queue->push(new ReplyPostJob($event->post));
    }
}
