Monolog Logstash - logging to [Logstash](http://logstash.net/) [![Build Status](https://travis-ci.org/bankiru/monolog-logstash.svg)](https://travis-ci.org/bankiru/monolog-logstash) [![Coverage Status](https://coveralls.io/repos/bankiru/monolog-logstash/badge.png)](https://coveralls.io/r/bankiru/monolog-logstash) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bankiru/monolog-logstash/badges/quality-score.png)](https://scrutinizer-ci.com/g/bankiru/monolog-logstash/) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/ad63c000-52ae-4e4d-af3c-e3eaf207e5df/mini.png)](https://insight.sensiolabs.com/projects/ad63c000-52ae-4e4d-af3c-e3eaf207e5df)
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

Releases of IP Tools are available on [Github](https://github.com/bankiru/monolog-logstash).


## Documentation

Currently implemented only [ZMQ transport](http://logstash.net/docs/1.4.2/inputs/zeromq)


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