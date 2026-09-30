<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected static bool $shutdownRegistered = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (!self::$shutdownRegistered) {
            self::$shutdownRegistered = true;
            register_shutdown_function(function () {
                exec('php artisan db:seed --class=BinaroDemoSeeder');
            });
        }
    }
}
