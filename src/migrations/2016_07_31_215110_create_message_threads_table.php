<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageThreadsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('message_threads', function (Blueprint $table) {
            $table->increments('id');
        });

        // 'message_threads' table is created after 'messages' table,
        // we need to add this relation after 'message_threads' is created.
        Schema::table('messages', function (Blueprint $table) {
            $table->foreign('thread_id')->references('id')->on('message_threads')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // To prevent 'Integrity constraint violation' when refreshing or
        // resetting migrations we need to remove this relation before
        // deleting the 'message_threads' table.
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign('messages_thread_id_foreign');
        });

        Schema::drop('message_threads');
    }
}
