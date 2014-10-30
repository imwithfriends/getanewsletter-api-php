<?php

namespace Gan;

/**
 * Represents a contact object.
 */
class Contact extends Entity
{
    public $email;
    public $attributes;
    public $first_name;
    public $last_name;
    public $lists;
    public $url;
    public $active;
    public $updated;
    public $created;

    /**
     * Subscribes the contact to a list.
     *
     * Do not forget to call ContactManager->save()!
     *
     * @param \Gan\Newsletter $list The list to subscribe on.
     */
    public function subscribeTo(\Gan\Newsletter $list) {
        if (!is_array($this->lists)) {
            $this->lists = [];
        }
        $this->lists[] = $list;
    }
}
