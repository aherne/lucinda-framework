<?php

namespace Test\Lucinda\Project\DAO;

class NoSQLLoginThrottlerTest extends AbstractLoginThrottlerTest
{
    public const DRIVER_NAME = "";

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
        $driver = \NoSQL(self::DRIVER_NAME);
        if ($driver->contains($key)) {
            $driver->delete($key);
        }
    }
}
