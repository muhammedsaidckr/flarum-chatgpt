<?php

use Flarum\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateChatGptCotTable extends Migration
{
    public function up()
    {
        Schema::create('chatgpt_cot', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('discussion_id');
            $table->text('cot');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('discussion_id')->references('id')->on('discussions')->onDelete('cascade');
            $table->index('discussion_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chatgpt_cot');
    }
}
