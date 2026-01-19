<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\UnitTest\Result;

class AbstractSessionHandlerTest
{
    public const SESSION_ID = "asdfghj";

    protected $object;

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
        return new Result(false, "Session garbage collection cannot be unit tested!");
    }

    public function close()
    {
        return new Result($this->object->close());
    }
}
