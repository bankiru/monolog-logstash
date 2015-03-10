Monolog Logstash - logging to [Logstash](http://logstash.net/) [![Build Status](https://travis-ci.org/bankiru/monolog-logstash.svg)](https://travis-ci.org/bankiru/monolog-logstash) [![Coverage Status](https://coveralls.io/repos/bankiru/monolog-logstash/badge.png)](https://coveralls.io/r/bankiru/monolog-logstash) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bankiru/monolog-logstash/badges/quality-score.png)](https://scrutinizer-ci.com/g/bankiru/monolog-logstash/) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/3cd492d5-7086-415c-aea9-ce8458c85f70/mini.png)](https://insight.sensiolabs.com/projects/3cd492d5-7086-415c-aea9-ce8458c85f70)
========

[![Latest Stable Version](https://poser.pugx.org/bankiru/monolog-logstash/v/stable.svg)](https://packagist.org/packages/bankiru/monolog-logstash)
[![Total Downloads](https://poser.pugx.org/bankiru/monolog-logstash/downloads.svg)](https://packagist.org/packages/bankiru/monolog-logstash)
[![Latest Unstable Version](https://poser.pugx.org/bankiru/monolog-logstash/v/unstable.svg)](https://packagist.org/packages/bankiru/monolog-logstash)
[![License](https://poser.pugx.org/bankiru/monolog-logstash/license.svg)](https://packagist.org/packages/bankiru/monolog-logstash)

## Installing

### Composer

```
"require": {
  "bankiru/monolog-logstash": "~0.1.0"
}
```

### Github

Releases available on [Github](https://github.com/bankiru/monolog-logstash).


## Documentation

Currently implemented:
* [ZMQ transport](http://logstash.net/docs/1.4.2/inputs/zeromq)
* [Lumberjack transport](http://logstash.net/docs/1.4.2/inputs/lumberjack)

### ZMQ transport
```
<?php

use Bankiru\MonologLogstash\ZMQHandler;
use Monolog\Formatter\JsonFormatter;

$zmqHandler = new ZMQHandler(
    'tcp://127.0.0.1:2120', // dsn
     true,                  // persistent
     [],                    // ZMQContext options (http://php.net/manual/en/zmqcontext.setopt.php)
     \ZMQ::SOCKET_PUSH,     // ZMQSocket type
     [],                    // ZMQSocket options (http://php.net/manual/en/zmqsocket.setopt.php)
     Logger::INFO,          // log level
     true                   // bubble
);

$zmqHandler->setFormatter(new JsonFormatter(JsonFormatter::BATCH_MODE_NEWLINES)); // optional but recommended

$log = new Logger('name');
$log->pushHandler($zmqHandler);
```

### Lumberjack transport
```
<?php

use Bankiru\MonologLumberjack\LumberjackHandler;
use Bankiru\MonologLumberjack\LumberjackFormatter;
use Ekho\Logstash\Lumberjack;

$lumberjackHandler = new LumberjackHandler(Logger::INFO, true);
$lumberjackHandler->init(
    '127.0.0.1',
    2323,
    'path/to/certificate.crt',
    [
        'window_size' => 5000,
    ]
);
$lumberjackHandler->setFormatter(new LumberjackFormatter('my_app_name'));

$log = new Logger('name');
$log->pushHandler($lumberjackHandler);
```