<?php

namespace Gerardojbaez\Messenger\Contracts;

interface MessageThreadInterface
{
    public function messages();
    public function participants();
    public function getTitleAttribute();
    public function getLastMessageAttribute();
    public function getUnreadMessagesCountAttribute();
    public function getCreatorAttribute();
    public function scopeBetween($query, $participants);
}
