<?php
namespace Test\Lucinda\Project\EventListeners\Console;

use Lucinda\Project\EventListeners\Console\SQLDataSource;
use Lucinda\Project\ConsoleAttributes;
use Lucinda\ConsoleSTDOUT\Application;
use Lucinda\UnitTest\Result;

class SQLDataSourceTest
{
    public function run()
    {
        $attributes = new ConsoleAttributes();
        $application = new Application(dirname(__DIR__, 2)."/mocks/stdout.xml");

        $event = new SQLDataSource($attributes, $application);
        $event->run();

        try {
            return new Result(\SQL("SELECT CURDATE()")->toValue() == date("Y-m-d"));
        } catch (\Throwable $e) {
            return new Result(false, $e->getMessage());
        }
    }
}
