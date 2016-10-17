<?php

namespace Gerardojbaez\Messenger\Contracts;

interface MessageableInterface
{
    public function threads();
    public function scopeFindThread($query, $thread_id);
    public function messagesSent();
    public function getUnreadMessagesCountAttribute();
    public function markThreadAsRead($thread_id);
}
