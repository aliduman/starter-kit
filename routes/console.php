<?php

use App\Support\ResponseCache\ScheduledCacheBoundaries;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Scheduler
|--------------------------------------------------------------------------
| cPanel cron her dakika `php artisan schedule:run` çalıştırır. Paylaşımlı hostlarda exec/proc_open genellikle
| kapalı olduğundan görevler Schedule::call / Schedule::job ile yazılır; Schedule::command alt süreç açar.
*/

// Yayın tarihi gelen içerikler (modüllerin kaydettiği kolonlar) için response cache temizlenir.
Schedule::call(fn () => app(ScheduledCacheBoundaries::class)->purgeIfCrossed())
    ->name('responsecache:scheduled-boundaries')
    ->everyMinute()
    ->withoutOverlapping();
