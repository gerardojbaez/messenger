<?php

namespace Gerardojbaez\Messenger\tests;

use Gerardojbaez\Messenger\Tests\Models\User;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Setup the test enviroment.
     */
    public function setUp()
    {
        parent::setUp();

        // $this->beforeApplicationDestroyed(function() {
        // 	$this->artisan('migrate:rollback');
        // });

        $this->migrate();
        $this->createUsers();
    }

    /**
     * Migrate database tables.
     */
    protected function migrate()
    {
        // Run package migrations
        $this->artisan('migrate', [
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__.'/../src/migrations'),
        ]);

        // Create user's table
        \Schema::create('users', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });
    }

    /**
     * Seed database.
     */
    protected function createUsers()
    {
        User::insert([
            ['name' => 'David', 'email' => 'd@example.org'],
            ['name' => 'Jane', 'email' => 'j@example.org'],
            ['name' => 'Abigail', 'email' => 'a@example.org'],
        ]);
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        // Set models
        $app['config']->set('auth.providers.users.model', '\Gerardojbaez\Messenger\Tests\Models\User');

        // set up database configuration
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * Get Messenger package service provider.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    public function getPackageProviders($app)
    {
        return ['Gerardojbaez\Messenger\MessengerServiceProvider'];
    }

    /**
     * Get package facade.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Messenger' => 'Gerardojbaez\Messenger\Messenger',
        ];
    }

    /*
     * Register the Eloquent factory instance in the container.
     *
     * @return void
     */
    // protected function registerEloquentFactory($app)
    // {
    //     $app->singleton(FakerGenerator::class, function () {
    //         return FakerFactory::create();
    //     });

    //     $app->singleton(EloquentFactory::class, function ($app) {
    //         $faker = $app->make(FakerGenerator::class);

    //         return EloquentFactory::construct($faker, __DIR__.'/../src/database/factories');
    //     });
    // }
}
