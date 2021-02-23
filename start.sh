#!/bin/sh

sleep 5 # to ensure redis is up fully
php artisan serve --host=0.0.0.0 --port=8000