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
//        if (!$this->agent->operational()
//            || $this->agent->is($event->discussion->user)
//        ) {
//            $this
//            return;
//        }
        // Add logic to handle the event here.
        // See https://docs.flarum.org/extend/backend-events.html for more information.
        $this->queue->push(new ReplyJob($event->discussion));
    }
}
