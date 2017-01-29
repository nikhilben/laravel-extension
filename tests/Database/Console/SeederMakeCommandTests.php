<?php

use LaravelPlus\Extension\Database\Commands\SeederMakeCommand as Command;

class SeederMakeCommandTests extends TestCase
{
    use ConsoleCommandTrait;

    public function test_withNoParameter()
    {
        // 1. setup
        $app = $this->createApplication();

        // 2. condition

        // 3. test
        $command = $app->make(Command::class);

        try {
            $this->runCommand($app, $command);

            Assert::failure();
        } catch (RuntimeException $ex) {
            Assert::stringStartsWith('Not enough arguments', $ex->getMessage());
        }
    }

    public function test_withNameParameter()
    {
        // 1. setup
        $app = $this->createApplication();

        // 2. condition

        // 3. test
        $command = $app->make(Command::class);

        $result = $this->runCommand($app, $command, [
            'name' => 'foo',
        ]);

        Assert::same(0, $result);
        Assert::fileExists($app['path'].'/Database/Seeds/Foo.php');
    }

    public function test_withNameAndAddonParameter_addonNotFound()
    {
        // 1. setup
        $app = $this->createApplication();

        // 2. condition

        // 3. test
        $command = $app->make(Command::class);

        try {
            $result = $this->runCommand($app, $command, [
                'name' => 'foo',
                '--addon' => 'bar',
            ]);

            Assert::failure();
        }
        // RuntimeException: Addon 'bar' is not found.
        catch (RuntimeException $ex) {
            Assert::equals("Addon 'bar' is not found.", $ex->getMessage());
        }
    }

    public function test_withNameAndAddonParameter_addonFound()
    {
        // 1. setup
        $app = $this->createApplication();
        $this->createAddon('bar', 'minimum', [
            'namespace' => 'Bar',
        ]);

        // 2. condition

        // 3. test
        $command = $app->make(Command::class);

        $result = $this->runCommand($app, $command, [
            'name' => 'foo',
            '--addon' => 'bar',
        ]);

        Assert::same(0, $result);
        Assert::fileExists($app['path.base'].'/addons/bar/classes/Seeds/Foo.php');
    }
}
