<?php

use LaravelPlus\Extension\Database\Console\DatabaseRollbackCommand as Command;

class DatabaseRollbackCommandTests extends TestCase
{
    use ConsoleCommandTrait;

    /**
     * @test
     */
    public function test_withNoParameter()
    {
        $command = new Command();

        Assert::isInstanceOf(Command::class, $command);
    }
}
