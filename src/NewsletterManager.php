<?php

namespace Gan;

class NewsletterManager extends EntityManager
{
    protected $basePath = 'lists';
    protected $entityClass = 'Gan\Newsletter';
    protected $writableFields = [
        'email',
        'name',
        'sender',
        'description'
    ];
    protected $lookupField = 'hash';
}
