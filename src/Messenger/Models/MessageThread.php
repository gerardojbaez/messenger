<?php

namespace Gerardojbaez\Messenger\Models;

use Illuminate\Database\Eloquent\Model;
use Gerardojbaez\Messenger\Contracts\MessageThreadInterface;

class MessageThread extends Model implements MessageThreadInterface
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'last_read',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var array
     */
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    public $dates = ['last_read'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['messages'];

    /**
     * Get thread messages.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages()
    {
        return $this->hasMany(config('messenger.models.message'), 'thread_id');
    }

    /**
     * Get thread participants.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function participants()
    {
        return $this->hasMany(config('messenger.models.participant'), 'thread_id');
    }

    /**
     * Get thread title.
     *
     * IMPORTANT: participants.user relation
     * must be loaded when working with
     * multiple results!
     *
     * @return string
     */
    public function getTitleAttribute()
    {
        if (!$this->relationLoaded('participants')) {
            $this->load('participants.user');
        }

        $excludeUser = ($this->relationLoaded('pivot') ? $this->pivot->user_id : null);
        $participants = [];
        $numberOfParticipants = 0;

        foreach ($this->participants as $participant) {
            // Exclude creator...
            if ($excludeUser and $participant->user_id == $excludeUser) {
                continue;
            }

            $participants[] = $participant->user->name;
            ++$numberOfParticipants;
        }

        return trans_choice('messenger::messenger.threads.title', $numberOfParticipants, [
            'name' => $participants[0],
            'count' => ($numberOfParticipants - 1), // substract one since we have shown the name.
        ]);
    }

    /**
     * Get thread last message.
     *
     * IMPORTANT: messages relation must be
     * loaded when working with multiple results!
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getLastMessageAttribute()
    {
        return $this->messages->sortBy('created_at')->last();
    }

    /**
     * Get count of all unread messages in thread.
     *
     * For this to work you need to load the threads
     * through the user relation. For example:
     * $user->threads or
     * User::with('threads') etc.
     *
     * @return int|null
     */
    public function getUnreadMessagesCountAttribute()
    {
        // We need the pivot relation
        if (!$this->relationLoaded('pivot')) {
            return null;
        }

        $last_read = $this->pivot->last_read;
        $user_id = $this->pivot->user_id;

        // If message date is greater than the
        // last_read, the message is unread.
        return $this->messages->filter(function ($msg, $key) use ($last_read,$user_id) {
            // Exclude messages that were sent
            // by this user.
            if ($user_id == $msg->sender_id) {
                return false;
            }

            // If last_read is null this means
            // all messages are unread since
            // the user hasn't opened the
            // thread yet.
            if (is_null($last_read)) {
                return true;
            }

            // Return new messages only
            return $msg->created_at > $last_read;
        })->count();
    }

    /**
     * Get thread creator.
     *
     * IMPORTANT: messages and messages.sender
     * relatios must be loaded when working
     * with multiple results!
     *
     * @return \App\Models\User|null
     */
    public function getCreatorAttribute()
    {
        return $this->messages->sortBy('created_at')->first()->sender;
    }

    /**
     * Scope threads between given users.
     *
     * @param $query
     * @param array $participants User Ids
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetween($query, $participants)
    {
        if (!is_array($participants)) {
            $participants = func_get_args();
            array_shift($participants);
        }

        return $query->whereHas('participants', function ($query) use ($participants) {
            $query->select('thread_id')
                ->whereIn('user_id', $participants)
                ->groupBy('thread_id')
                ->havingRaw('COUNT(thread_id) = '.count($participants));
        });
    }
}
