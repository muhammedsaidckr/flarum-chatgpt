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
        $log = resolve('log');

        try {
            $log->info('[ChatGPT Job] ReplyPostJob started', [
                'post_id' => $this->post->id,
                'discussion_id' => $this->post->discussion_id,
                'post_number' => $this->post->number,
                'created_at' => $this->post->created_at->toDateTimeString()
            ]);

            $settings = resolve(SettingsRepositoryInterface::class);
            $duration = $settings->get('muhammedsaidckr-chatgpt.answer_duration');

            // check if the discussion is greater or equal to the duration
            if ($this->post->created_at->diffInMinutes() < $duration) {
                $log->info('[ChatGPT Job] Post too recent, releasing job', [
                    'post_id' => $this->post->id,
                    'age_minutes' => $this->post->created_at->diffInMinutes(),
                    'required_duration' => $duration
                ]);
                // if the discussion is less than the duration, dont do anything but do not delete from the queue
                $this->release(60);
                return;
            }

            $continueToReply = $settings->get('muhammedsaidckr-chatgpt.continue_to_reply');
            if (!$continueToReply) {
                $log->info('[ChatGPT Job] Skipping - continue_to_reply disabled', [
                    'post_id' => $this->post->id,
                    'continue_to_reply' => $continueToReply
                ]);
                return;
            }

            $log->info('[ChatGPT Job] Calling agent->repliesToCommentPost', [
                'post_id' => $this->post->id
            ]);

            $agent->repliesToCommentPost($this->post);

            $log->info('[ChatGPT Job] ReplyPostJob completed successfully', [
                'post_id' => $this->post->id
            ]);
        } catch (\Exception $e) {
            $log->error('[ChatGPT Job] Exception in ReplyPostJob', [
                'post_id' => $this->post->id ?? null,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
