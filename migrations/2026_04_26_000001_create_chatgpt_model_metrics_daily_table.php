<?php

use Illuminate\Database\Schema\Blueprint;

return [
    'up' => function ($schema) {
        $schema->create('chatgpt_model_metrics_daily', function (Blueprint $table) {
            $table->id();
            $table->date('metric_date');
            $table->string('model');
            $table->string('model_family')->default('legacy');
            $table->string('api_mode')->default('chat_completions');
            $table->unsignedInteger('request_count')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('error_count')->default(0);
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->unsignedInteger('total_tokens')->default(0);
            $table->unsignedInteger('latency_ms_total')->default(0);
            $table->decimal('estimated_cost_usd', 14, 8)->default(0);
            $table->string('pricing_version')->default('openai-2026-04');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->unique(['metric_date', 'model']);
            $table->index('metric_date');
            $table->index('model');
        });
    },
    'down' => function ($schema) {
        $schema->dropIfExists('chatgpt_model_metrics_daily');
    },
];
