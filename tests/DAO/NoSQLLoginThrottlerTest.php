<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\NoSQL\ConnectionSingleton;

class NoSQLLoginThrottlerTest extends AbstractLoginThrottlerTest
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getThrottlerClass()
    {
        return "Lucinda\\Project\\DAO\\NoSQLLoginThrottler";
    }

    protected function cleanup()
    {
        $key = "logins__".sha1(json_encode(array("ip"=>self::USER_IP, "username"=>self::USER_NAME)));
        $driver = ConnectionSingleton::getInstance();
        if ($driver->contains($key)) {
            $driver->delete($key);
        }
    }
}
