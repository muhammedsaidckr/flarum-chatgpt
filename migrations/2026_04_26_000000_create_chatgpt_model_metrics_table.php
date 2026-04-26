<?php

use Illuminate\Database\Schema\Blueprint;

return [
    'up' => function ($schema) {
        $schema->create('chatgpt_model_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('discussion_id')->nullable();
            $table->string('model');
            $table->string('model_family')->default('legacy');
            $table->string('api_mode')->default('chat_completions');
            $table->string('status')->default('unknown');
            $table->unsignedInteger('request_count')->default(1);
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->unsignedInteger('total_tokens')->default(0);
            $table->unsignedInteger('latency_ms')->default(0);
            $table->decimal('estimated_cost_usd', 14, 8)->default(0);
            $table->boolean('had_refusal')->default(false);
            $table->text('error_message')->nullable();
            $table->string('pricing_version')->default('openai-2026-04');
            $table->timestamp('created_at')->useCurrent();

            $table->index('discussion_id');
            $table->index('model');
            $table->index('status');
        });
    },
    'down' => function ($schema) {
        $schema->dropIfExists('chatgpt_model_metrics');
    },
];
