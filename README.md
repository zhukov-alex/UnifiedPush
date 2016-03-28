Unified Push
========================
[![Build Status](https://travis-ci.org/zbox/UnifiedPush.svg?branch=master)](https://travis-ci.org/zbox/UnifiedPush)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/zbox/UnifiedPush/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/zbox/UnifiedPush/?branch=master)

Unified Push supports push notifications for iOS, Android and Windows Phone devices via APNs, GCM and MPNS

## Install

The recommended way to install UnifiedPush is [through composer](http://getcomposer.org).

```JSON
{
    "require": {
	    "zbox/unified-push": "^0.9"
    }
}
```

## Features
 - Unified interface that supports sending push notifications for platforms:
   - Apple (APNS)
   - Android (GCM)
   - Windows Phone (MPNS)

## Requirements
* PHP 5.3.2 or later
* HTTP client (kriswallsmith/buzz)
* PHPUnit to run tests

## Usage

### Configure Notification Services Client Factory

Create service client factory configured with credentials.

```php
<?php

use Zbox\UnifiedPush\NotificationService\ServiceClientFactory;
use Zbox\UnifiedPush\NotificationService\ServiceCredentialsFactory;
use Zbox\UnifiedPush\Utils\ClientCredentials\CredentialsMapper;

$credentialsFactory = 
    new ServiceCredentialsFactory(
        new CredentialsMapper()
    );

$credentialsFactory->setCredentialsPath('pathToCredentialsConfig');

$clientFactory = new ServiceClientFactory($credentialsFactory);
$clientFactory->setDefaultConfigPath();
```

### Initialize Message Dispatcher

Initialize class with client factory, notification builder and response handler.

```php
<?php

use Zbox\UnifiedPush\Dispatcher;
use Zbox\UnifiedPush\Notification\NotificationBuilder;
use Zbox\UnifiedPush\NotificationService\ResponseHandler;

$dispatcher =
    new Dispatcher(
        $clientFactory,
        new NotificationBuilder(),
        new ResponseHandler()
    );

$dispatcher->setDevelopmentMode(true);
```

### Create messages

Create messages of type APNS, GCM, MPNS (Raw, Tile or Toast).

```php
<?php

use Zbox\UnifiedPush\Message\Type\APNS as APNSMessage;
use Zbox\UnifiedPush\Message\Type\GCM as GCMMessage;

$message1 = new APNSMessage();
$message1
	->setSound('alert')
	->getBadge('2');

$message1->addRecipient('deviceToken1');

$message2 = new GCMMessage();
$message2
	->setCollapseKey('key')
	->addRecipientIdentifiers(
       new \ArrayIterator([
			'deviceToken1', 
			'deviceToken2'
		])
	)
    ->setPayloadData([
		'keyA' => 'value1',
		'keyB' => 'value2',
    ]);
```

### Dispatch messages

Send messages and load feedback.

```php
<?php

$dispatcher
    ->dispatch($message1)
    ->dispatch($message2)
    ->loadFeedback();
```

### Status

Handle responses to see a report on dispatch errors.

```php
<?php

$responseHandler = $dispatcher->getResponseHandler();
$responseHandler->handleResponseCollection();

$invalidRecipients  = $responseHandler->getInvalidRecipients();
$messageErrors      = $responseHandler->getMessageErrors();
```

## License

MIT, see LICENSE.
