<?php

namespace Gerardojbaez\Messenger\Traits;

use App\Models\Message;

trait Messageable
{
    /**
     * Get all threads.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function threads()
    {
        return $this->belongsToMany(
            config('messenger.models.thread'),
            'message_thread_participants',
            'user_id',
            'thread_id'
        )->withPivot('last_read');
    }

    /**
     * Scope user existing thread.
     *
     * @param $query
     * @param int $thread_id
     *
     * @return \Illuminate\Database\Elquent\Builder
     */
    public function scopeFindThread($query, $thread_id)
    {
        return $this->threads()->where('thread_id', $thread_id);
    }

    /**
     * Get all messages sent.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messagesSent()
    {
        return $this->hasMany(config('messenger.models.message'), 'sender_id');
    }

    /**
     * Get count of all unread messages.
     *
     * @return int
     */
    public function getUnreadMessagesCountAttribute()
    {
        $count = 0;

        foreach ($this->threads as $thread) {
            $count += $thread->messages->filter(function ($msg, $key) use ($thread) {

                // Exclude messages that were sent
                // by this user.
                if ($this->id == $msg->sender_id) {
                    return false;
                }

                // If last_read is null this means
                // all messages are unread since
                // the user hasn't opened the
                // thread yet.
                if (is_null($thread->pivot->last_read)) {
                    return true;
                }

                // Return new messages only
                return $msg->created_at > $thread->pivot->last_read;
            })->count();
        }

        return $count;
    }

    /**
     * Mark user thread as read.
     */
    public function markThreadAsRead($thread_id)
    {
        $this->threads()->updateExistingPivot($thread_id, [
            'last_read' => $this->freshTimestamp(),
        ]);
    }
}
