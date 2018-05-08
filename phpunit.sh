#!/usr/bin/env bash
cd -- "$(dirname "$BASH_SOURCE")"
php7.1 ./vendor/phpunit/phpunit/phpunit -c ./phpunit.xml ${@:2:99}
