<?php

namespace Msc\ChatGPT\Listener;

use Flarum\Discussion\Event\Started;
use Flarum\Post\Event\Posted;
use Illuminate\Contracts\Queue\Queue;
use Msc\ChatGPT\Agent;
use Msc\ChatGPT\Job\ReplyJob;
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
     * @param  \Flarum\Post\Event\Posted  $event
     * @return void
     */
    public function handle(Posted $event): void
    {
        $this->queue->push(new ReplyPostJob($event->post));
    }
}
