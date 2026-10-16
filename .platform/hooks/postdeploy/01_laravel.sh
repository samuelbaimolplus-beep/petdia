#!/bin/bash
chmod -R 775 /var/app/current/storage /var/app/current/bootstrap/cache || true
chown -R webapp:webapp /var/app/current/storage /var/app/current/bootstrap/cache || true
php /var/app/current/artisan storage:link --relative || true
php /var/app/current/artisan config:cache || true
php /var/app/current/artisan route:cache || true
php /var/app/current/artisan view:cache || true
