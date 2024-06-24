<?php

namespace Msc\ChatGPT\Listener;

use Flarum\Discussion\Event\Started;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Queue\Queue;
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

        if (!$enabled) {
            $this->agent->repliesTo($event->discussion);
            return;
        }

        // check queue redis, or database queue is installed

        $this->queue->push(new ReplyJob($event->discussion));
    }
}
