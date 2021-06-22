<?php
namespace Test\Lucinda\Project\DAO;

use Lucinda\NoSQL\Wrapper;
use Lucinda\STDOUT\Application;
use Lucinda\Project\DAO\SQLSessionHandler;

require_once(dirname(__DIR__, 2)."/helpers/getParentNode.php");

class SQLSessionHandlerTest extends AbstractSessionHandlerTest
{
    public function __construct()
    {
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        new Wrapper(\getParentNode($application, "sql"), ENVIRONMENT);
        $this->object = new SQLSessionHandler();
    }
}
