<?php

/*
 * This file is part of muhammedsaidckr/flarum-chatgpt.
 *
 * Copyright (c) 2023 Muhammed Said Cakir.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Msc\ChatGPT;

use Flarum\Discussion\Event\Saving;
use Flarum\Discussion\Event\Started;
use Flarum\Extend;
use Flarum\Http\Middleware\HandleErrors;
use Flarum\Post\Event\Posted;
use Msc\ChatGPT\Listener\ReplyToCommentPost;
use Msc\ChatGPT\Listener\ReplyToPost;
use Msc\ChatGPT\Middleware\ModerationMiddleware;
use Tobscure\JsonApi\ErrorHandler;

return [
    (new Extend\Middleware('api'))
        ->add(ModerationMiddleware::class),
    (new Extend\Middleware('forum'))
        ->add(ModerationMiddleware::class),
    (new Extend\Middleware('admin'))
        ->add(ModerationMiddleware::class),

    (new Extend\Middleware('frontend'))
        ->insertBefore(HandleErrors::class, ModerationMiddleware::class),

    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js')
        ->css(__DIR__ . '/less/forum.less'),
    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js')
        ->css(__DIR__ . '/less/admin.less'),
    (new Extend\Locales(__DIR__ . '/locale')),

    (new Extend\ServiceProvider())
        ->register(BindingsProvider::class)
        ->register(ClientProvider::class),

    (new Extend\Event())
        ->listen(Started::class, ReplyToPost::class),
    (new Extend\Event())
        ->listen(Posted::class, ReplyToCommentPost::class),
//    (new Extend\Event())
//        ->listen(Saving::class, SavingPost::class),

    (new Extend\Settings())
        ->default('muhammedsaidckr-chatgpt.model', 'gpt-3.5-turbo-instruct')
        ->default('muhammedsaidckr-chatgpt.enable_on_discussion_started', true)
        ->default('muhammedsaidckr-chatgpt.max_tokens', 100)
        ->default('muhammedsaidckr-chatgpt.user_prompt_badge_text', 'Assistant')
        ->default('muhammedsaidckr-chatgpt.queue_active', true)
        // new setting for answer duration in minutes (default 5)
        ->default('muhammedsaidckr-chatgpt.answer_duration', 0)
        // If any user replied to post, the AI will not reply to that post setting
        ->default('muhammedsaidckr-chatgpt.reply_to_post', true)
        ->default('muhammedsaidckr-chatgpt.role', 'You are a forum user')
        ->default('muhammedsaidckr-chatgpt.prompt',
            'Write a arguable or thankfully opinion asking or arguing something about an answer that has talked about "[title]" and who talked about [content]. Don\'t talk about what you would like or don\'t like. Speak in a close tone, like you are writing in a Tech Forum. Be random and unpredictable. Answer in [language].')
        ->default('muhammedsaidckr-chatgpt.continue_to_reply', true)
        ->default('muhammedsaidckr-chatgpt.continue_to_reply_count', 5)
        ->default('muhammedsaidckr-chatgpt.moderation', false)
        ->default('muhammedsaidckr-chatgpt.base_uri', 'https://api.openai.com/v1/')
        ->serializeToForum('chatGptUserPromptId', 'muhammedsaidckr-chatgpt.user_prompt')
        ->serializeToForum('chatGptBadgeText', 'muhammedsaidckr-chatgpt.user_prompt_badge_text'),
];
