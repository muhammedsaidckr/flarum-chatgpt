<?php

namespace Msc\ChatGPT\Listener;

use Flarum\Discussion\Event\Saving;
use Flarum\Discussion\Event\Started;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Queue\Queue;
use Msc\ChatGPT\Agent;
use Msc\ChatGPT\Exceptions\BadWordException;
use Msc\ChatGPT\Job\ReplyJob;
use Exception;
use Symfony\Component\Console\Exception\MissingInputException;

class SavingPost
{
    public function __construct(
        protected Agent $agent,
        protected Queue $queue
    )
    {
    }

    /**
     * @param \Flarum\Discussion\Event\Saving $event
     * @return void
     */
    public function handle(Saving $event): void
    {
        if ($this->agent->checkModeration($event->discussion->title, $event->discussion->firstPost)) {
            throw new Exception('Your post contains inappropriate content. Please try again.');
        }
    }
}
