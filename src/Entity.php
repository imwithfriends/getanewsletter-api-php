<?php

namespace Gan;

/**
 * Represents a generic entity.
 *
 * Extend this class to define real entities/models.
 */
class Entity
{
    /**
     * Flag showing that the entity exists or not in the storage.
     * @var boolean
     */
    private $_persisted = false;

    /**
     * Returns the persisted status of the entity, whether it exists
     * in the storage or not.
     *
     * @return boolean
     */
    public function isPersisted()
    {
        return $this->_persisted;
    }

    /**
     * Sets the persisted status of the entity.
     *
     * Except in special cases, normally you should not use this method.
     * You may call setPersisted() on an entity in order to make
     * partial update on save().
     *
     * @param type $persisted (optional) The new persisted status. True by default.
     */
    public function setPersisted($persisted = true)
    {
        $this->_persisted = $persisted;
    }
}
