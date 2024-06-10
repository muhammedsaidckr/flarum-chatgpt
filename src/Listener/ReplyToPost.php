<?php

namespace Msc\ChatGPT\Listener;

use Flarum\Discussion\Event\Started;
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
     * @param  \Flarum\Discussion\Event\Started  $event
     * @return void
     */
    public function handle(Started $event): void
    {
        $this->queue->push(new ReplyJob($event->discussion));
    }
}
