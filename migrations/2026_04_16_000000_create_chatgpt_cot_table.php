<?php

use Illuminate\Database\Schema\Blueprint;

return [
    'up' => function ($schema) {
        $schema->create('chatgpt_cot', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('discussion_id');
            $table->text('cot');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('discussion_id')->references('id')->on('discussions')->onDelete('cascade');
            $table->index('discussion_id');
        });
    },
    'down' => function ($schema) {
        $schema->dropIfExists('chatgpt_cot');
    },
];
