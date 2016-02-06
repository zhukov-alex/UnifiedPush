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
	    "zbox/unified-push": "dev-master"
    }
}
```

## Features
 - Unified interface that suports sending push notifications for platforms:
   - Apple (APNS)
   - Android (GCM)
   - Windows Phone (MPNS)

## Requirements
* PHP 5.3.2 or later
* HTTP client (kriswallsmith/buzz)
* PHPUnit to run tests

## Usage

### Load application credentials

Load available notification services and authentication credentials for selected application.

```php
<?php

$application = new Zbox\UnifiedPush\Application('myApplication');
```

### Create message
Create a message of one of supported types, then add messages to collection.

```php
<?php

use Zbox\UnifiedPush\Message\Type\APNS as APNSMessage;
use Zbox\UnifiedPush\Message\Type\GCM as GCMMessage;

$message1 = new APNSMessage();
$message1
	->setSound('alert')
	->getBadge('2');

$message1->addRecipients(array('deviceToken1', 'deviceToken2'));

$message2 = new GCMMessage();
$message2
	->setCollapseKey('key')
	->addRecipients(array('deviceToken1', 'deviceToken2'))
	->setPayloadData(array(
		'keyA' => 'value1',
		'keyB' => 'value2',
		)
	);

$application->addMessage($message1);
$application->addMessage($message2);
```

### Initialize dispatcher

Initialize dispatcher, then set environment and try to send message queue, then try to load feedback (for services, where available). Then get report of any invalid recipient device identifiers.

```php
<?php

$dispatcher = new Zbox\UnifiedPush\Dispatcher($application);

$dispatcher->setDevelopmentMode(true);

$dispatcher->dispatch(); // sending all messages
$dispatcher->loadFeedback();
```

### Get report

Get report about failed devices.


```php
<?php

$application->getInvalidRecipients();
```

## License

MIT, see LICENSE.
