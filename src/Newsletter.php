<?php

namespace Gan;

/**
 * Represents a list of subscribers.
 */
class Newsletter extends Entity
{
    public $email;
    public $name;
    public $sender;
    public $description;
    public $hash;
    public $responders_count;
    public $subscribers;
    public $created;
    public $url;
    public $subscribers_count;
    public $active_subscribers_count;
    public $responders;
}