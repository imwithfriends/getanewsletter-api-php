<?php

namespace Gan;

class Entity
{
    private $_persisted = false;

    public function isPersisted()
    {
        return $this->_persisted;
    }

    public function setPersisted($persisted = true)
    {
        $this->_persisted = $persisted;
    }
}
