<?php

namespace Gerardojbaez\Messenger\Models;

use Illuminate\Database\Eloquent\Model;
use Gerardojbaez\Messenger\Contracts\MessageInterface;

class Message extends Model implements MessageInterface
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'thread_id',
        'sender_id',
        'body',
        'related_listing_id',
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
    public $dates = ['created_at'];

    /**
     * The "booting" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }

    /**
     * Get sender.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sender()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'sender_id');
    }

    /**
     * Get thread.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function thread()
    {
        return $this->belongsTo(config('messenger.models.thread'), 'thread_id');
    }

    /**
     * Scope by sender.
     *
     * @param $query
     * @param int $sender User ID
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFromSender($query, $sender)
    {
        return $query->where('sender_id', $sender);
    }
}
