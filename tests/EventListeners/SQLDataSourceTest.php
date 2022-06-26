<?php

namespace Test\Lucinda\Project\EventListeners;

use Lucinda\Project\EventListeners\SQLDataSource;
use Lucinda\Project\Attributes;
use Lucinda\STDOUT\Application;
use Lucinda\UnitTest\Result;

class SQLDataSourceTest
{
    public function run()
    {
        $attributes = new Attributes();
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");

        $event = new SQLDataSource($attributes, $application);
        $event->run();

        try {
            return new Result(\SQL("SELECT CURDATE()")->toValue() == date("Y-m-d"));
        } catch (\Throwable $e) {
            return new Result(false, $e->getMessage());
        }
    }
}
