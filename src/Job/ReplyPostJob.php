<?php

namespace Msc\ChatGPT\Job;

use Flarum\Discussion\Discussion;
use Flarum\Post\CommentPost;
use Flarum\Queue\AbstractJob;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Msc\ChatGPT\Agent;

class ReplyPostJob extends AbstractJob
{
    use Queueable;
    use SerializesModels;

    public function __construct(protected CommentPost $post)
    {
    }

    public function handle(Agent $agent): void
    {
        $settings = resolve(SettingsRepositoryInterface::class);
        $duration = $settings->get('muhammedsaidckr-chatgpt.answer_duration');
        // check if the discussion is greater or equal to the duration
        if ($this->post->created_at->diffInMinutes() < $duration) {
            // if the discussion is less than the duration, dont do anything but do not delete from the queue
            $this->release(60);
            return;
        }

        $continueToReply = $settings->get('muhammedsaidckr-chatgpt.continue_to_reply');
        if (!$continueToReply) {
            return;
        }

        $agent->repliesToCommentPost($this->post);
    }
}
