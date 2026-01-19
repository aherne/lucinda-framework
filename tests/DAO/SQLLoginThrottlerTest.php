<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\Framework\ServiceRegistry;
use Lucinda\Framework\SqlConnectionProvider;
use Lucinda\STDOUT\Application;

require_once dirname(__DIR__, 2)."/helpers/SQL.php";

class SQLLoginThrottlerTest extends AbstractLoginThrottlerTest
{
    public function __construct()
    {
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        $provider = new SqlConnectionProvider($application->getXML(), ENVIRONMENT);
        ServiceRegistry::set(SqlConnectionProvider::class, $provider);
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
