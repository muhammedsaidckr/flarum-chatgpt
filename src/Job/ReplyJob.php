<?php

namespace Msc\ChatGPT\Job;

use Flarum\Discussion\Discussion;
use Flarum\Queue\AbstractJob;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Msc\ChatGPT\Agent;

class ReplyJob extends AbstractJob
{
    use Queueable;
    use SerializesModels;

    public function __construct(protected Discussion $discussion)
    {
    }

    public function handle(Agent $agent): void
    {
        $agent->repliesTo($this->discussion);
    }
}
