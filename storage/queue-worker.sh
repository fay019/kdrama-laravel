#!/bin/bash
cd '/Volumes/Dev/Projets/Web/kdrama-laravel'
'/usr/bin/php' artisan queue:work --timeout=600 >> '/Volumes/Dev/Projets/Web/kdrama-laravel/storage/logs/queue-worker.log' 2>&1
