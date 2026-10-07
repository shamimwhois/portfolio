<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application.
     *
     * `php artisan test` boots the framework (and putenvs APP_ENV=local)
     * before PHPUnit applies phpunit.xml's <env> blocks. PHPUnit only
     * updates $_ENV and putenv, but Laravel's Env repository reads
     * $_SERVER first — so the app would boot as 'local' and CSRF would
     * never be skipped during tests. Normalize all three sources here.
     */
    public function createApplication()
    {
        $_SERVER['APP_ENV'] = 'testing';
        $_ENV['APP_ENV'] = 'testing';
        putenv('APP_ENV=testing');

        return parent::createApplication();
    }
}
