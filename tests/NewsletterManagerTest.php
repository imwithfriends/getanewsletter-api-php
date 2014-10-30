<?php

class NewsletterManagerTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->apiMock = $this->getMock('Gan\Api', ['call'], ['TOKEN']);

        $this->response = (object) [
            'code' => 200,
            'body' => (object) [
                'hash' => 'hash',
                'name' => 'list',
                'sender' => 'John Doe',
                'email' => 'john@example.com'
            ]
        ];

        $this->manager = new Gan\NewsletterManager($this->apiMock);
    }

    public function testGet()
    {
        $this->apiMock->expects($this->once())
                ->method('call')
                ->with($this->equalTo(Httpful\Http::GET),
                       $this->equalTo('lists/hash/'))
                ->willReturn($this->response);

        $list = $this->manager->get('hash');

        $this->assertEquals('Gan\Newsletter', get_class($list));
        $this->assertEquals('list', $list->name);
        $this->assertEquals('hash', $list->hash);
        $this->assertTrue($list->isPersisted());
    }

    /**
     * @expectedException Exception
     */
    public function testUpdateWithoutHash()
    {
        $list = new Gan\Newsletter();
        $list->name = 'list';
        $list->sender = 'John Doe';
        $list->setPersisted();

        $this->manager->save($list);
    }

    public function testNew()
    {
        $list = new Gan\Newsletter();
        $list->name = 'list';
        $list->sender = 'John Doe';

        $payload = (object) [
            'email' => null,
            'name' => 'list',
            'sender' => 'John Doe',
            'description' => null
        ];

        $this->apiMock->expects($this->once())
                ->method('call')
                ->with($this->equalTo(Httpful\Http::POST),
                       $this->equalTo('lists/'),
                       $this->equalTo($payload))
                ->willReturn($this->response);

        $result = $this->manager->save($list);
        $this->assertEquals('Gan\Newsletter', get_class($result));
    }

    public function testSave()
    {
        $list = new Gan\Newsletter();
        $list->hash = 'hash';
        $list->name = 'list';
        $list->sender = 'sender';
        $list->setPersisted();

        $payload = (object) [
            'email' => null,
            'name' => 'list',
            'sender' => 'sender',
            'description' => null
        ];

        $this->apiMock->expects($this->once())
                ->method('call')
                ->with($this->equalTo(Httpful\Http::PATCH),
                       $this->equalTo('lists/hash/'),
                       $this->equalTo($payload))
                ->willReturn($this->response);

        $this->manager->save($list);
    }

    public function testOverwrite()
    {
        $list = new Gan\Newsletter();
        $list->hash = 'hash';
        $list->email = 'test@example.com';
        $list->sender = 'John';

        $payload = (object) [
            'email' => 'test@example.com',
            'name' => null,
            'sender' => 'John',
            'description' => null
        ];

        $this->apiMock->expects($this->once())
                ->method('call')
                ->with($this->equalTo(Httpful\Http::PUT),
                       $this->equalTo('lists/hash/'),
                       $this->equalTo($payload))
                ->willReturn($this->response);

        $this->manager->overwrite($list);
    }

    public function testDelete()
    {
        $list = new Gan\Newsletter();
        $list->hash = 'hash';

        $this->apiMock->expects($this->once())
                ->method('call')
                ->with($this->equalTo(Httpful\Http::DELETE),
                       $this->equalTo('lists/hash/'))
                ->willReturn($this->response);

        $this->manager->delete($list);
    }
}
