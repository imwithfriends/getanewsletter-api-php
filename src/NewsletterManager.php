<?php

namespace Gan;

/**
 * Entity manager for the Newsletter entity.
 */
class NewsletterManager extends EntityManager
{
    protected $basePath = 'lists';
    protected $entityClass = '\Gan\Newsletter';
    protected $writableFields = [
        'email',
        'name',
        'sender',
        'description'
    ];
    protected $lookupField = 'hash';
}
