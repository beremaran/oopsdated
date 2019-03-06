#!/usr/bin/env bash

php bin/console enqueue:setup-broker && \
php bin/console enqueue:consume;