<?php
namespace Test\Lucinda\Project\DAO;

use Lucinda\NoSQL\Wrapper;
use Lucinda\Project\DAO\NoSQLSessionHandler;
use Lucinda\UnitTest\Result;
use Lucinda\STDOUT\Application;

require_once(dirname(__DIR__, 2)."/helpers/getParentNode.php");

class NoSQLSessionHandlerTest extends AbstractSessionHandlerTest
{
    public function __construct()
    {
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        new Wrapper(\getParentNode($application, "nosql"), ENVIRONMENT);
        $this->object = new NoSQLSessionHandler();
    }
}
