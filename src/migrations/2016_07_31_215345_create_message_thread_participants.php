<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageThreadParticipants extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('message_thread_participants', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('thread_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->timestamp('last_read')->nullable();
            $table->softDeletes();

            $table->unique(['thread_id', 'user_id']);
            $table->foreign('thread_id')->references('id')->on('message_threads')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('message_thread_participants');
    }
}
