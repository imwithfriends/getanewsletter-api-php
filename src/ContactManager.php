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

    /**
     * Fixes the attributes property. It should not be null.
     *
     * @param \Gan\Entity $entity
     * @return object
     */
    protected function normalizeEntity(Entity $entity) {
        $data = parent::normalizeEntity($entity);
        if (!is_object($data->attributes)) {
            $data->attributes = (object) [];
        }
        return $data;
    }
}
