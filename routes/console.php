<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('video:clean-orphans')->everyMinute();

// Auto-expirar disponibilidad cada 5 minutos
Schedule::command('availability:expire')->everyFiveMinutes();