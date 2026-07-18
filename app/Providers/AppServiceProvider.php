<?php

namespace App\Providers;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(CommandStarting::class, function (CommandStarting $event) {

            if (in_array($event->command, [
                'migrate:fresh',
                'migrate:refresh',
                'migrate:reset',
                'db:wipe',
                'db:seed',
            ])) {

                throw new RuntimeException(
                    'Command ini telah dinonaktifkan.'
                );
            }
        });
    }
}