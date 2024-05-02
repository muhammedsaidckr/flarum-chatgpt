<?php

namespace Msc\ChatGPT\Job;

use Flarum\Discussion\Discussion;
use Flarum\Queue\AbstractJob;
use Flarum\Settings\SettingsRepositoryInterface;
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
        $settings = resolve(SettingsRepositoryInterface::class);
        $duration = $settings->get('muhammedsaidckr-chatgpt.answer_duration');
        // check if the discussion is greater or equal to the duration
        if ($this->discussion->created_at->diffInMinutes() < $duration) {
            // if the discussion is less than the duration, dont do anything but do not delete from the queue
            $this->release(60);
            return;
        }

        // check reply_to_post setting in settings
        $replyToPost = $settings->get('muhammedsaidckr-chatgpt.reply_to_post');

        // check if any user replied to the post and replyToPost setting is true
        if ($replyToPost && $this->discussion->posts()->where('type', 'comment')->count() > 1) {
            return;
        }

        $agent->repliesTo($this->discussion);
    }
}
