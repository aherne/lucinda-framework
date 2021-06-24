<?php
namespace Test\Lucinda\Project\DAO;

use Lucinda\SQL\Wrapper;
use Lucinda\STDOUT\Application;

require_once("helpers/SQL.php");

class SQLLoginThrottlerTest extends AbstractLoginThrottlerTest
{
    private $request;
    private $throttler;

    public function __construct()
    {
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        new Wrapper(\getParentNode($application, "sql"), ENVIRONMENT);
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
