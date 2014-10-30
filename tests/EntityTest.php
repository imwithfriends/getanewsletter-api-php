<?php

class EntityTests extends PHPUnit_Framework_TestCase
{
    public function testPersisted()
    {
        $entity = new \Gan\Entity();
        $this->assertFalse($entity->isPersisted());
        $_entity = $entity->setPersisted();
        $this->assertEquals($_entity, $entity);
        $this->assertTrue($entity->isPErsisted());
        $entity->setPersisted(false);
        $this->assertFalse($entity->isPersisted());
    }
}
