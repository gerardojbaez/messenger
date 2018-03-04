<?php

namespace Gerardojbaez\Messenger;

use DB;
use App;
use Gerardojbaez\Messenger\Exceptions\MessengerException;
use Gerardojbaez\Messenger\Contracts\MessageableInterface;
use Gerardojbaez\Messenger\Contracts\MessageThreadInterface;

class Messenger
{
    /**
     * Message sender.
     *
     * @var \App\Models\User
     */
    protected $from;

    /**
     * Message recipients.
     *
     * Can be an instance of MessageThread, User
     * or an array with user ids.
     *
     * @var mixed
     */
    protected $to;

    /**
     * Message.
     *
     * @var string
     */
    protected $message;

    /**
     * Set Message.
     *
     * @param string
     *
     * @return $this
     */
    public function message($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Message sender.
     *
     * @param \App\Models\User
     *
     * @return $this
     */
    public function from(MessageableInterface $from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Message recipients.
     *
     * @param mixed
     *
     * @return $this
     */
    public function to($to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Send message.
     *
     * @return \Gerardojbaez\Messenger\Contracts\MessageInterface
     */
    public function send()
    {
        if (!$this->from) {
            throw new MessengerException('Sender not provided.');
        }

        if (!$this->to) {
            throw new MessengerException('Receiver not provided.');
        }

        if (!$this->message) {
            throw new MessengerException('Message not provided');
        }

        $from = $this->from;
        $thread = $this->getThread();
        $message = $this->message;

        return $thread->messages()->create([
            'body' => $message,
            'sender_id' => $from->id,
        ]);
    }

    /**
     * Try to find a thread, if no thread is
     * found, create one.
     *
     * @return \Gerardojbaez\Messenger\Models\MessageThread
     */
    protected function getThread()
    {
        $thread = null;

        // If recipient is already a thread
        // let's use it!
        if ($this->to instanceof MessageThreadInterface) {
            $thread = $this->to;
        }

        // If recipient is a user, let's find a
        // thread between him/her and the sender.
        elseif ($this->to instanceof MessageableInterface) {
            $thread = App::make(MessageThreadInterface::class)->between($this->from->id, $this->to->id)->first();
        }

        // If recipient is an array, someone is trying
        // to send the message to multiple users...
        // Let's try to find a thread between them.
        elseif (is_array($this->to)) {
            $thread = App::make(MessageThreadInterface::class)->between(array_merge([$this->from->id], $this->to))->first();
        }

        // Return thread if was found...
        if ($thread) {
            return $thread;
        }

        return $this->createThread();
    }

    /**
     * Create thread.
     *
     * @return \App\Models\MessageThread
     */
    protected function createThread()
    {
        $from = $this->from;
        $to = $this->to;

        return DB::transaction(function () use ($from,$to) {
            $thread = App::make(MessageThreadInterface::class);
            $thread->save();

            // Build participants array
            $participants = [
                ['thread_id' => $thread->id, 'user_id' => $from->id],
            ];

            if (is_numeric($to)) {
                $participants[] = ['thread_id' => $thread->id, 'user_id' => $to];
            } elseif (is_array($to)) {
                foreach ($to as $id) {
                    $participants[] = ['thread_id' => $thread->id, 'user_id' => $id];
                }
            } elseif ($to instanceof MessageableInterface) {
                $participants[] = ['thread_id' => $thread->id, 'user_id' => $to->id];
            }

            $thread->participants()->insert($participants);

            return $thread;
        });
    }
}
