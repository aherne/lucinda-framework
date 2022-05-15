<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\SQL\Wrapper;
use Lucinda\STDOUT\Application;

require_once(dirname(__DIR__, 2)."/helpers/SQL.php");

class SQLLoginThrottlerTest extends AbstractLoginThrottlerTest
{
    public function __construct()
    {
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        new Wrapper($application->getXML(), ENVIRONMENT);
        parent::__construct();
    }

    protected function getThrottlerClass()
    {
        return "Lucinda\\Project\\DAO\\SQLLoginThrottler";
    }

    protected function cleanup()
    {
        \SQL("DELETE FROM user_logins WHERE ip=:ip AND username=:username", [":ip"=>self::USER_IP, ":username"=>self::USER_NAME]);
    }
}
