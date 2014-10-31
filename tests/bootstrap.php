<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$loader = require __DIR__ . "/../vendor/autoload.php";
$loader->addPsr4('Bankiru\\MonologLogstash\\', __DIR__);
$loader->addPsr4('Monolog\\', __DIR__ . "/../vendor/monolog/monolog/tests/Monolog");

date_default_timezone_set('UTC');
