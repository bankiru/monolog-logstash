#!/bin/sh

set -e

echo 'Running unit tests.'
./vendor/bin/phpunit --configuration phpunit.xml.dist --verbose --coverage-clover build/logs/clover.xml

echo ''
echo ''
echo ''
echo 'Testing for Coding Styling Compliance.'
echo 'All code should follow PSR standards.'
./vendor/bin/php-cs-fixer fix ./ --level="psr2" -vv --dry-run