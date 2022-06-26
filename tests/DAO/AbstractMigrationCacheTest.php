<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\Migration\Cache;
use Lucinda\UnitTest\Result;
use Lucinda\Migration\Status;

class AbstractMigrationCacheTest
{
    /**
     * @var Cache
     */
    protected $object;

    public function create()
    {
        $this->object->create();
        return new Result(true);
    }

    public function exists()
    {
        return new Result($this->object->exists());
    }


    public function add()
    {
        $this->object->add("TestMe", Status::PASSED);
        return new Result(true);
    }


    public function read()
    {
        return new Result($this->object->read()==["TestMe"=>Status::PASSED]);
    }


    public function remove()
    {
        $this->object->remove("TestMe");
        return new Result($this->object->read()==[]);
    }
}
