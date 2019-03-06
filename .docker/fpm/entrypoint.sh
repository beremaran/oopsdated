#!/usr/bin/env bash

php bin/console doctrine:migrations:migrate --no-interaction && \
/usr/bin/supervisord -n