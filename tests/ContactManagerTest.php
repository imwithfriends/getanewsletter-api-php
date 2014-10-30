<?php

class ContactManagerTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->apiMock = $this->getMock('Gan\Api', ['call'], ['TOKEN']);

        $this->response = (object) [
            'code' => 200,
            'body' => (object) [
                'email' => 'test@example.com',
                'first_name' => 'John'
            ]
        ];

        $this->manager = new Gan\ContactManager($this->apiMock);
    }

    public function testGet()
    {
        $this->apiMock->expects($this->once())
                ->method('call')
                ->with($this->equalTo(Httpful\Http::GET),
                       $this->equalTo('contacts/test@example.com/'))
                ->willReturn($this->response);

        $contact = $this->manager->get('test@example.com');

        $this->assertEquals('Gan\Contact', get_class($contact));
        $this->assertEquals('John', $contact->first_name);
        $this->assertTrue($contact->isPersisted());
    }

    public function testNew()
    {
        $contact = new Gan\Contact();
        $contact->email = 'test@example.com';
        $contact->first_name = 'John';

        $payload = (object) [
            'attributes' => (object) [],
            'first_name' => 'John',
            'last_name' => null,
            'lists' => []
        ];

        $this->apiMock->expects($this->once())
                ->method('call')
                ->with($this->equalTo(Httpful\Http::POST),
                       $this->equalTo('contacts/'),
                       $this->equalTo($payload))
                ->willReturn($this->response);

        $result = $this->manager->save($contact);
        $this->assertEquals('Gan\Contact', get_class($result));
    }

    public function testSave()
    {
        $contact = new Gan\Contact();
        $contact->email = 'test@example.com';
        $contact->first_name = 'John';
        $contact->setPersisted();

        $payload = (object) [
            'attributes' => (object) [],
            'first_name' => 'John',
            'last_name' => null,
            'lists' => []
        ];

        $this->apiMock->expects($this->once())
                ->method('call')
                ->with($this->equalTo(Httpful\Http::PATCH),
                       $this->equalTo('contacts/test@example.com/'),
                       $this->equalTo($payload))
                ->willReturn($this->response);

        $this->manager->save($contact);
    }

    /**
     * @expectedException Exception
     */
    public function testUpdateWithoutEmail()
    {
        $contact = new Gan\Contact();
        $contact->first_name = 'John';
        $contact->setPersisted();

        $this->manager->save($contact);
    }

    public function testOverwrite()
    {
        $contact = new Gan\Contact();
        $contact->email = 'test@example.com';
        $contact->first_name = 'John';

        $payload = (object) [
            'attributes' => (object) [],
            'first_name' => 'John',
            'last_name' => null,
            'lists' => []
        ];

        $this->apiMock->expects($this->once())
                ->method('call')
                ->with($this->equalTo(Httpful\Http::PUT),
                       $this->equalTo('contacts/test@example.com/'),
                       $this->equalTo($payload))
                ->willReturn($this->response);

        $this->manager->overwrite($contact);
    }

    public function testFind()
    {
        $response = (object) [
            'code' => 200,
            'body' => (object) [
                'results' => [
                    (object) [
                        'email' => 'john@example.com'
                    ],
                    (object) [
                        'email' => 'jack@example.com'
                    ]
                ]
            ]
        ];

        $this->apiMock->expects($this->once())
                ->method('call')
                ->with($this->equalTo(Httpful\Http::GET),
                       $this->equalTo('contacts/?search_email=test%40&page=2'))
                ->willReturn($response);

        $result = $this->manager->query(['search_email' => 'test@', 'page' => 2]);
        $this->assertCount(2, $result);
        $this->assertEquals('john@example.com', $result[0]->email);
        $this->assertEquals('Gan\Contact', get_class($result[0]));
        $this->assertEquals('jack@example.com', $result[1]->email);
    }

    public function testDelete()
    {
        $contact = new Gan\Newsletter();
        $contact->email = 'jack@example.com';

        $this->apiMock->expects($this->once())
                ->method('call')
                ->with($this->equalTo(Httpful\Http::DELETE),
                       $this->equalTo('contacts/jack@example.com/'))
                ->willReturn($this->response);

        $this->manager->delete($contact);
    }
}
