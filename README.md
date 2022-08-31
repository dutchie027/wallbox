# wallbox
Wallbox API

## Overview
This API wrapper was written to allow me to get better metrics and usage out of my wallbox EVSE.

## Usage
To start, simply download the package using composer:

```php
composer require dutchie027/wallbox
```

After downloading it with composer, rename `.env.sample` to `.env` and add your specific variables and credentials.

Once you have all of that, depending on how you want to use it, create a simple PHP file that calls the library:

```php
#!/usr/bin/php
<?php

include_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$wallbox = new dutchie027\Wallbox\Wallbox($_ENV['API_USERNAME'], $_ENV['API_PASSWORD']);
$data = json_decode($wallbox->getFullPayload(), true);
...
```
### Functions

#### checkLock

```php
print $wallbox->checkLock($id);
```

#### getStats

```php
print $wallbox->getStats($id);
```

#### getChargerStatus

```php
print $wallbox->getChargerStatus($id);
```

#### getFullPayload

```php
print $wallbox->getFullPayload($id);
```

#### getLastChargeDuration

```php
print $wallbox->getLastChargeDuration($id);
```

#### checkFirmwareStatus

```php
print $wallbox->checkFirmwareStatus($id);
```

#### unlockCharger

```php
print $wallbox->unlockCharger($id);
```

#### lockCharger

```php
print $wallbox->lockCharger($id);
```

#### getChargerData

```php
print $wallbox->getChargerData($id);
```

#### getTotalChargeTime

```php
print $wallbox->getTotalChargeTime($id);
```

#### getTotalSessions

```php
print $wallbox->getTotalSessions($id);
```

## Dependencies
The code uses a few external libraries, but they're all bundled in the composer.json file.

* monolog/monolog
* guzzlehttp/guzzle
* vlucas/phpdotenv
* serhiy/pushover

## Acknowledgements
Shout out to the [Python work](https://github.com/cliviu74/wallbox) that [cliviu74](https://github.com/cliviu74) did. This was the foundation that gave me a lot of the URLs.
