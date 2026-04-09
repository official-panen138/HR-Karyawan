<?php

use Illuminate\Support\Facades\Schedule;

// Document expiry reminders — daily at 08:00
Schedule::command('hr:remind-document-expiry')
    ->dailyAt('08:00')
    ->withoutOverlapping();

// Leave carry-over — annually on Jan 1st at 00:30
Schedule::command('hr:leave-carry-over')
    ->yearlyOn(1, 1, '00:30')
    ->withoutOverlapping();
