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

use Flarum\Discussion\Event\Started;
use Flarum\Extend;
use Msc\ChatGPT\Listener\ReplyToPost;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/less/admin.less'),
    (new Extend\Locales(__DIR__.'/locale')),

    (new Extend\ServiceProvider())
        ->register(BindingsProvider::class)
        ->register(ClientProvider::class),

    (new Extend\Event())
        ->listen(Started::class, ReplyToPost::class),

    (new Extend\Settings())
        ->default('muhammedsaidckr-chatgpt.model', 'gpt-3.5-turbo-instruct')
        ->default('muhammedsaidckr-chatgpt.enable_on_discussion_started', true)
        ->default('muhammedsaidckr-chatgpt.max_tokens', 100)
        ->default('muhammedsaidckr-chatgpt.user_prompt_badge_text', 'Assistant')
        // new setting for answer duration in minutes (default 5)
        ->default('muhammedsaidckr-chatgpt.answer_duration', 5)
        // If any user replied to post, the AI will not reply to that post setting
        ->default('muhammedsaidckr-chatgpt.reply_to_post', true)
        ->default('muhammedsaidckr-chatgpt.role', 'You are a forum user')
        ->default('muhammedsaidckr-chatgpt.prompt', 'Write a arguable or thankfully opinion asking or arguing something about an answer that has talked about "[title]" and who talked about [content]. Don\'t talk about what you would like or don\'t like. Speak in a close tone, like you are writing in a Tech Forum. Be random and unpredictable. Answer in [language].')
        ->serializeToForum('chatGptUserPromptId', 'muhammedsaidckr-chatgpt.user_prompt')
        ->serializeToForum('chatGptBadgeText', 'muhammedsaidckr-chatgpt.user_prompt_badge_text'),
];
