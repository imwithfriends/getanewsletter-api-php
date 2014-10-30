<?php

namespace Gan;

/**
 * Entity manager for contacts.
 */
class ContactManager extends EntityManager
{
    protected $basePath = 'contacts';
    protected $entityClass = '\Gan\Contact';
    protected $writableFields = [
        'attributes',
        'first_name',
        'last_name',
        'lists'
    ];
    protected $lookupField = 'email';
}
