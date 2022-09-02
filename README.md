# wallbox

[![Packagist Downloads](https://img.shields.io/packagist/dm/dutchie027/wallbox)](https://packagist.org/packages/dutchie027/wallbox)
[![CodeFactor](https://www.codefactor.io/repository/github/dutchie027/wallbox/badge)](https://www.codefactor.io/repository/github/dutchie027/wallbox)

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

$wallbox = new dutchie027\Wallbox\Wallbox();
...
```

### Running as a monitor

The most common use case for the script(s) are a monitoring system. To accomplish this, there is a function called `monitor` that uses a lot of defaults and will notify you of changes to the system. It will check the EVSE every 30 seconds by default and notify you of any changes using pushover with the configuration settings in `.env`. To monitor the system, first create a file called `monitor.php` using the below:

```php
#!/usr/bin/php
<?php

include_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$wallbox = new dutchie027\Wallbox\Wallbox();
$wallbox->monitor();
```

After you've created the monitoring file, ensure it's executable by running `chmod +x monitor.php`. Once you've done that, simply trigger it with a `nohup` so it runs in the background:

```bash
nohup ./monitor.php >/dev/null 2>&1 &
```

### Functions

#### checkLock

Returns true/false if the charger is locked. `true` if the charger is locked, `false` if the charger is unlocked.

```php
print $wallbox->checkLock($id);
```

#### getStats

Returns a JSON payload of stats for the charger between start and int (epoch times).

```php
print $wallbox->getStats($id, $start, $end);
```

#### getChargerStatus

Returns a JSON payload of status of the specific charger. [Sample payload](snippets/getChargerStatus.txt)

```php
print $wallbox->getChargerStatus($id);
```

#### getFullPayload

Returns a JSON payload of status of the all chargers. [Sample payload](snippets/getFullPayload.txt)

```php
print $wallbox->getFullPayload();
```

#### getLastChargeDuration

Returns time in *x*h *x*m if hours and minutes. If only minutes it returns *x*m *x*s.

```php
print $wallbox->getLastChargeDuration();
```

#### checkFirmwareStatus

Checks if the firmware is up-to-date. Returns a human string about the status

```php
print $wallbox->checkFirmwareStatus($id);
```

#### unlockCharger

Unlocks the charger.

```php
print $wallbox->unlockCharger($id);
```

#### lockCharger

Locks the charger.

```php
print $wallbox->lockCharger($id);
```

#### getChargerData

Returns JSON payload of the specific charger data [sample](snippets/getChargerData.txt)

```php
print $wallbox->getChargerData($id);
```

#### getTotalChargeTime

Returns time in *x*h *x*m if hours and minutes. If only minutes it returns *x*m *x*s.

```php
print $wallbox->getTotalChargeTime($id);
```

#### getTotalSessions

Returns an integer denoting total charge sessions

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
