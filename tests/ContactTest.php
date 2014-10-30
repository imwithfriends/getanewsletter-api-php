<?php

class ContactTests extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->apiMock = $this->getMock('Gan\Api', ['call'], ['TOKEN']);
    }

    public function testSubscribe()
    {
        $contactResponse = (object) ['body' => (object) ['email' => 'test@example.com']];

        $this->apiMock->expects($this->at(0))
                ->method('call')
                ->willReturn($contactResponse);

        $contactManager = new \Gan\ContactManager($this->apiMock);
        $contact = $contactManager->get('test@example.com');

        $listManager = new \Gan\NewsletterManager($this->apiMock);
        $list = $listManager->constructEntity((object) ['hash' => 'hash']);

        $returnedList = $listManager->normalizeEntity($list);
        $returnedList->hash = 'hash';

        $contact->subscribeTo($list);

        $expected = (object) (object) [
            'attributes' => (object) [],
            'first_name' => null,
            'last_name' => null,
            'lists' => [
                clone $returnedList
            ]
        ];

        $this->apiMock->expects($this->any())
                ->method('call')
                ->with($this->equalTo(Httpful\Http::PATCH),
                       $this->equalTo('contacts/test@example.com/'),
                       $this->equalTo($expected))
                ->willReturn($contactResponse);

        $contactManager->save($contact);
    }
}
