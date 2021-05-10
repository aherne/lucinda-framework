<?php
namespace Test\Lucinda\Project\EventListeners;

use Lucinda\Project\EventListeners\NoSQLDataSource;
use Lucinda\Project\Attributes;
use Lucinda\STDOUT\Application;
use Lucinda\UnitTest\Result;
use Lucinda\NoSQL\ConnectionSingleton;

class NoSQLDataSourceTest
{
    public function run()
    {
        $attributes = new Attributes();
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        
        $event = new NoSQLDataSource($attributes, $application);
        $event->run();
        
        try {
            $driver = ConnectionSingleton::getInstance();
            $driver->set("test", "me");
            $val = $driver->get("test");
            $driver->delete("test");
            return new Result($val == "me");
        } catch (\Throwable $e) {
            return new Result(false, $e->getMessage());
        }
    }
}
