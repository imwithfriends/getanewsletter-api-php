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
     * Fixes the attributes and lists properties - they should not be null.
     * Normalizes the lists.
     *
     * @param \Gan\Entity $entity
     * @return object
     */
    public function normalizeEntity($entity) {
        $data = parent::normalizeEntity($entity);

        if (!is_object($data->attributes)) {
            $data->attributes = (object) [];
        }

        $listManager = new NewsletterManager($this->api);
        $data->lists = [];

        if (is_array($entity->lists)) {
            foreach ($entity->lists as $list) {
                $norm = $listManager->normalizeEntity($list);
                $norm->hash = $list->hash;
                $data->lists[] = $norm;
            }
        }

        return $data;
    }
}
