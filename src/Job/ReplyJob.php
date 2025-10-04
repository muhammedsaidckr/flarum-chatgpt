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
        $log = resolve('log');

        try {
            $log->info('[ChatGPT Job] ReplyJob started', [
                'discussion_id' => $this->discussion->id,
                'title' => $this->discussion->title,
                'created_at' => $this->discussion->created_at->toDateTimeString()
            ]);

            $settings = resolve(SettingsRepositoryInterface::class);
            $duration = $settings->get('muhammedsaidckr-chatgpt.answer_duration');

            // check if the discussion is greater or equal to the duration
            if ($this->discussion->created_at->diffInMinutes() < $duration) {
                $log->info('[ChatGPT Job] Discussion too recent, releasing job', [
                    'discussion_id' => $this->discussion->id,
                    'age_minutes' => $this->discussion->created_at->diffInMinutes(),
                    'required_duration' => $duration
                ]);
                // if the discussion is less than the duration, dont do anything but do not delete from the queue
                $this->release(60);
                return;
            }

            // check reply_to_post setting in settings
            $replyToPost = $settings->get('muhammedsaidckr-chatgpt.reply_to_post');

            // check if any user replied to the post and replyToPost setting is true
            $postCount = $this->discussion->posts()->where('type', 'comment')->count();
            if ($replyToPost && $postCount > 1) {
                $log->info('[ChatGPT Job] Skipping - users already replied', [
                    'discussion_id' => $this->discussion->id,
                    'post_count' => $postCount,
                    'reply_to_post_setting' => $replyToPost
                ]);
                return;
            }

            $log->info('[ChatGPT Job] Calling agent->repliesTo', [
                'discussion_id' => $this->discussion->id
            ]);

            $agent->repliesTo($this->discussion);

            $log->info('[ChatGPT Job] ReplyJob completed successfully', [
                'discussion_id' => $this->discussion->id
            ]);
        } catch (\Exception $e) {
            $log->error('[ChatGPT Job] Exception in ReplyJob', [
                'discussion_id' => $this->discussion->id,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
