[![Build Status](https://img.shields.io/travis/gerardojbaez/messenger.svg?style=flat-square)](https://travis-ci.org/gerardojbaez/messenger)
[![Latest Version](https://img.shields.io/github/release/gerardojbaez/messenger.svg?style=flat-square)](https://github.com/gerardojbaez/messenger/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

# Messenger

Chat/Message system for Laravel 5.x.

With Messenger:

- Users can send and receive messages.
- Users can participate in multiple conversations.
- Users can send messages to one or multiple users.

## TL;DR

```php
use Messenger;

// Sending a message to one user:
Messenger::from($user)->to($user)->message('Hey!');

// Sending a message to multiple users: (an array of user ids)
Messenger::from($user)->to([1,2,3,4])->message('Who want to chat?!');

// Sending a message to one thread: (perfect for replying to a specific thread!)
Messenger::from($user)->to($thread)->message('I\'ll be there');
```

## Content
- [Installation](#installation)
	- [Requirements](#requirements)
	- [Composer](#composer)
	- [Service Provider and Facade](#service-provider-and-facade)
	- [Config file and Migrations](#config-file-and-migrations)
	- [Traits and Contracts](#traits-and-contracts)
- [Usage](#usage)
	- [Sending to One User](#sending-to-one-user)
	- [Sending to Multiple Users](#sending-to-multiple-users)
	- [Sending to thread](#sending-to-thread)
	- [Get count of new messages](#get-count-of-new-messages)
	- [Mark thread as read](#mark-thread-as-read)
	- [Thread Dynamic Attributes](#thread-dynamic-attributes)
	- [Displaying user threads](#displaying-user-threads)
	- [Using Models](#usgin-models)
- [Config File](#config-file)
- [License](#license)

## Installation

### Requirements

- Laravel 5.x
- PHP >=5.5

### Composer

	$ composer require gerardojbaez/messenger

### Service Provider and Facade

> If you are using laravel 5.5 and later, you can skip the following two steps since this package supports package auto-discovery feature.

Add the package to your application service providers in `config/app.php` file.

```php
'providers' => [
	/**
	 * Third Party Service Providers...
	 */
	Gerardojbaez\Messenger\MessengerServiceProvider::class,
]
```

Add the Facade to your aliases array:
```php
'aliases' => [

	[...]

	'Messenger' => Gerardojbaez\Messenger\Facades\Messenger::class,
]
```

### Config file and Migrations

Publish package config file and migrations with the command:

	$ php artisan vendor:publish --provider="Gerardojbaez\Messenger\MessengerServiceProvider"

Then run migrations:

	$ php artisan migrate

### Traits and Contracts

Add `Gerardojbaez/Messenger/Traits/Messageable` trait and `Gerardojbaez/Messenger/Contracts/MessageableInterface` contract to your `Users` model.

See the following example:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Gerardojbaez\Messenger\Contracts\MessageableInterface;
use Gerardojbaez\Messenger\Traits\Messageable;

class User extends Authenticatable implements MessageableInterface
{
    use Messageable;
```

## Usage

### Sending to One User

```php
<?php

// Import the facade
use Messenger;

Messenger::from($user)->to($user2)->message('Hey!')->send();
```

### Sending to Multiple Users

```php
<?php

// Import the facade
use Messenger;

Messenger::from($user)->to([1,2,3,4,5])->message('Hey!')->send();
```

### Sending to Thread

```php
<?php

// Import the facade
use Messenger;

Messenger::from($user)->to($thread)->message('That\'s awesome!')->send();
```

### Get count of new messages

Get global count - new messages in all user threads:
```php
<?php

echo $user->unreadMessagesCount;
```

Get count for a particular user thread:
```php
<?php

echo $user->threads->first()->unreadMessagesCount;
```

### Mark thread as read

To mark a thread as read:

```php
<?php

$user->markThreadAsRead($thread_id);
```

### Thread Dynamic Attributes

Threads dynamic attributes are attributes that doesn't come from the database, instead we generate them based on the data.

For example, threads doesn't have a title by itself, Messenger will create it based on the participants list.

Attributes:

- `$thread->title`
- `$thread->creator` to get the thread creator.
- `$thread->lastMessage` to get the latest message in the thread.

### Displaying user threads

*The controller:*
```php
public function index()
{
    // Eager Loading - this helps prevent hitting the
    // database more than the necessary.
    $this->user->load('threads.messages.sender');

    return view('messages.index', [
        'threads' => $this->user->threads
    ]);
}
```

*The view:*
```html
<div class="panel panel-default">
    <div class="list-group">
        @if($threads->count() > 0)
            @foreach($threads as $thread)
                @if($thread->lastMessage)
                    <a href="#" class="list-group-item">
                        <div class="clearfix">
                            <div class="pull-left">
                                <span class="h5">{{ $thread->title }}</span>
                                @if($thread->unreadMessagesCount > 0)
                                    <span class="label label-success">{!! $thread->unreadMessagesCount !!}</span>
                                @endif
                            </div>
                            <span class="text-muted pull-right">{{ $thread->lastMessage->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-muted no-margin">{{ str_limit($thread->lastMessage->body, 35) }}</p>
                    </a>
                @endif
            @endforeach
        @endif
    </div>
</div>
```

Preview:

![Preview of all user threads](http://static.gerardobaez.com/images/messenger-threads-preview.png)

### Using Models

Messenger has 3 models:

```php
Gerardojbaez\Messenger\Models\Message;
Gerardojbaez\Messenger\Models\MessageThread;
Gerardojbaez\Messenger\Models\MessageThreadParticipant;
```
You can use these as normal. For more details take a look to each model and the `Gerardojbaez\Messenger\Traits\Messageable` trait.

## Config File

For now you can configure what models to use.

## License

This package is free software distributed under the terms of the MIT license.