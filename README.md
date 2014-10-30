API-PHP
=======

The API-PHP library presents a simple and easy to use interface to the Get a Newsletter's REST API.
**Warning: It is still in developments and unstable!**

Requirements
------------
* PHP 5.5.* or greater
* [Httpful 0.2.*](http://phphttpclient.com/)

Installation
------------

If your project is supporting [Composer](https://getcomposer.org/) you may use the following in your composer.json file:
```json
"minimum-stability": "dev",
"repositories": [
    {
        "type": "git",
        "url": "https://github.com/getanewsletter/api-php"
    }
],
"require": {
    "getanewsletter/api-php": "master"
}
```

For manual installation you have to obtain the [api-php-0.1.0.phar]() and drop it somewhere in your project. Then use ```require "api-php-1.0.1.phar"``` to make it's functionality available.

Usage
-----
Start by creating an instance of the ```\Gan\Api``` object:
```php
<?php
$token = '...';
$gan = new \Gan\Api($token);
```
Here ```$token``` variable must contain a valid [API token](http://help.getanewsletter.com/en/support/api-token-2/) string.

#### The contact object
The instances of the \Gan\Contact class represent the contact entities in the API.
They have the following fields:

*Required fields*
* ```email``` - contact's email. It's also a ***_lookup field_*** - required when updating or deleting the contact.

*Optional fields*
* ```attributes``` - list of the contact's [attributes](http://help.getanewsletter.com/en/support/attribute-overview/).
* ```first_name```
* ```last_name```
* ```lists``` - list of the newsletters for which this contact is subscribed to.

*Read-only fields*
* ```url``` - the contact's resource URL.
* ```active``` - true if the contact is active and can receive mails, false otherwise.
* ```updated``` - the date of the last change.
* ```created``` - the date of creation.

#### Retreiving a contact
You have to create an instance of the ```\Gan\ContactManager\``` class and then use it's ```get()``` method to retrieve the contact you need.
```php
<?php
$contactManager = new \Gan\ContactManager($gan);
$contact = $contactManager->get('john.doe@example.com');
```
The manager methods will throw an ```\Gan\ApiException``` in case of HTTP error from the API, so it's a good idea to catch it.
```php
<?php
try {
    $contact = $contactManager->get('john.doe@example.com');
} catch (\Gan\ApiException $e) {
    if ($e->response->code === 404) {
        echo 'Contact not found!';
    } else {
        echo 'API error: ' . $e;
    }
}
```

#### Creating a contact
```php
<?php
$contact = new \Gan\Contact();
$contact->email = 'jane.doe@example.com';
$contact->first_name = 'Jane';

$contactManager->save($contact);
```
This will create a new contact and save it. Again, it'll be a good idea to catch exceptions when calling the ```save()``` method. The API will respond with an error if the contact already exists.
One way to avoid it is to force the creation of the contact, overwriting the existing one:
```php
<?php
$contactManager->overwrite($contact);
```

Both ```save()``` and ```overwrite()``` will return the same contact object with it's read-only fields updated (e.g. ```created```, ```updated```).

```php
<?php
$contact = $contactManager->save($contact);
echo $contact->created;
```

#### Updating an existing contact
```php
<?php
// Get the contact.
$contact = $contactManager->get('john.doe@example.com');
// Change some fields.
$contact->first_name = 'John';
// Save it.
$contactManager->save($contact);
```
You can avoid making two calls to the API by forcing a *partial update*.
```php
<?php
$contact = new \Gan\Contact();
$contact->setPersisted();
$contact->email = 'john.doe@example.com';
$contact->first_name = 'John';
$contactManager->save($contact);
```
Calling ```setPersisted()``` on the contact object marks it like it's already existing and coming from the API. The calls to the ```save()``` method when a contact is maked as existing will do only a *partial update*, i.e. update only the supplied fields and skipping all the ```null``` fields.
Do not forget that ```email``` is a ***_lookup field_*** and required when updating or deleting the contact.

#### Deleting a contact
```php
$contactManager->delete($contact);
```

#### The newsletter object
The instances of the \Gan\Newsletter class represent the [lists](http://help.getanewsletter.com/en/support/lists-overview/) in the API. They have the following structure:

*Required fields*
* ```email``` - sender's email.
* ```name``` - name of the list.
* ```sender``` - sender's name.
*
*Optional fields*
* ```description```

*Lookup field*
* ```hash``` - the list's unique hash.

*Read-only fields*
* ```responders_count```
* ```subcribers```
* ```created```
* ```url```
* ```subscribers_count```
* ```active_subscribers_count```
* ```responders```

#### Retreiving, creating, updating and deleting a list
The CRUD operations on lists are no different from the operations on contacts:
```php
<?php
$listManager = new \Gan\NewsletterManager($gan);

// Retrieve a list.
$list = $listManager->get('hash');

// Update the list.
$list->name = 'my list';
$list = $listManager->save($list);
echo $list->updated;

// Create new list.
$new_list = new \Gan\Newsletter();
$new_list->email = 'john.doe@example.com'; // requred fields
$new_list->name = 'my new list';
$new_list->sender = 'John Doe';
$listManager->save($new_list);

// Partial update.
$list = new \Gan\Newsletter();
$list->hash = 'hash'; // lookup field
$list->name = 'updated list';
$listManager->save($list);

// Delete the list.
$listManager->delete($list);

```

#### Subscribing a contact to a list
```php
<?php
$contact->subscribeTo($list);
$contactManager->save($contact);
```
You can also create a new contact automatically subscribed.
```php
<?php
$contact = new \Gan\Contact();
$contact->email = 'john.doe@example.com';
$contact->subscribeTo($list);
$contactManager->save($contact);
```
