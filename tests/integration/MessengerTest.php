<?php

use Gerardojbaez\Messenger\Tests\TestCase;
use Gerardojbaez\Messenger\Tests\Models\User;
use Gerardojbaez\Messenger\Models\MessageThread;

class MessengerTest extends TestCase
{
    public function test_it_can_send_message_to_user()
    {
        // Get users
        $sender = User::whereName('David')->first();
        $recipient = User::whereName('Abigail')->first();

        // Send message to user
        $messenger = new Messenger();
        $messenger->from($sender)->to($recipient)->message('Hey!')->send();

        $this->assertEquals(1, $recipient->threads->count());
        $this->assertEquals(1, $recipient->unreadMessagesCount);
        $this->assertEquals($sender, $recipient->threads->first()->creator);
        $this->assertTrue(is_string($recipient->threads()->first()->title));

        // Mark thread as read
        $thread = $recipient->threads->first();
        $recipient->markThreadAsRead($thread->id);

        $this->assertEquals(0, $recipient->fresh()->unreadMessagesCount);
    }

    public function test_it_can_send_message_to_multiple_users()
    {
        // Get users
        $sender = User::find(3);

        // Send message to user
        $messenger = new Messenger();
        $messenger->from($sender)->to([1, 2])->message('Hey!')->send();

        $recipientOne = User::find(1);
        $recipientTwo = User::find(2);

        $this->assertEquals(1, $recipientOne->threads->count());
        $this->assertEquals(1, $recipientTwo->threads->count());

        $this->assertEquals($sender, $recipientOne->threads->first()->creator);
        $this->assertEquals($sender, $recipientTwo->threads->first()->creator);

        $this->assertEquals(1, $recipientOne->unreadMessagesCount);
        $this->assertEquals(1, $recipientTwo->unreadMessagesCount);

        $this->assertTrue(is_string($recipientOne->threads()->first()->title));
        $this->assertTrue(is_string($recipientTwo->threads()->first()->title));
    }

    public function test_it_can_send_message_to_thread()
    {
        $thread = new MessageThread();
        $thread->id = null;
        $thread->save();

        $sender = User::find(3);

        $thread->participants()->insert([
            ['thread_id' => $thread->id, 'user_id' => 3], // Sender/Creator
            ['thread_id' => $thread->id, 'user_id' => 1],
            ['thread_id' => $thread->id, 'user_id' => 2],
        ]);

        // Send message to user
        $messenger = new Messenger();
        $messenger->from($sender)->to($thread)->message('Hey!')->send();

        $recipientOne = User::find(1);
        $recipientTwo = User::find(2);

        $this->assertEquals(1, $recipientOne->threads->count());
        $this->assertEquals(1, $recipientTwo->threads->count());

        $this->assertEquals($sender, $recipientOne->threads->first()->creator);
        $this->assertEquals($sender, $recipientTwo->threads->first()->creator);

        $this->assertEquals(1, $recipientOne->unreadMessagesCount);
        $this->assertEquals(1, $recipientTwo->unreadMessagesCount);

        $this->assertTrue(is_string($recipientOne->threads()->first()->title));
        $this->assertTrue(is_string($recipientTwo->threads()->first()->title));
    }
}
