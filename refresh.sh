#!/bin/bash
php bin/console cache:clear --env=prod --no-debug
php bin/console cache:clear --env=dev --no-debug
rm -rf var/cache/dev/*
rm -rf var/cache/prod/*
php bin/console assetic:dump web