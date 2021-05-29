<?php
namespace Test\Lucinda\Project\DAO;

use Lucinda\NoSQL\Wrapper;
use Lucinda\Project\DAO\NoSQLSessionHandler;
use Lucinda\UnitTest\Result;
use Lucinda\STDOUT\Application;

require_once(dirname(__DIR__, 2)."/helpers/getParentNode.php");

class NoSQLSessionHandlerTest
{
    const SESSION_ID = "asdfghj";
    
    private $object;
    
    public function __construct()
    {
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        new Wrapper(\getParentNode($application, "nosql"), ENVIRONMENT);
        $this->object = new NoSQLSessionHandler();
    }
    
    public function open()
    {
        return new Result($this->object->open("/tmp/sessions", self::SESSION_ID));
    }
    
    public function write()
    {
        return new Result($this->object->write(self::SESSION_ID, "test"));
    }

    public function read()
    {
        return new Result($this->object->read(self::SESSION_ID) == "test");
    }

    public function destroy()
    {
        return new Result($this->object->destroy(self::SESSION_ID));
    }

    public function gc()
    {
        return new Result($this->object->gc(123));
    }

    public function close()
    {
        return new Result($this->object->close());
    }
}
