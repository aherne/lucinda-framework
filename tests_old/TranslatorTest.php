<?php

namespace Test\Lucinda\Project;

use Lucinda\Internationalization\Reader;
use Lucinda\Internationalization\Wrapper;
use Lucinda\Project\ServiceRegistry;
use Lucinda\UnitTest\Result;

class TranslatorTest
{
    public function set()
    {
        $wrapper = new Wrapper(simplexml_load_file(__DIR__."/mocks/stdout.xml"), [], []);
        ServiceRegistry::set(Reader::class, $wrapper->getReader());
        return new Result(true, "tested via get() method");
    }


    public function get()
    {
        return new Result(ServiceRegistry::get(Reader::class) instanceof Reader);
    }
}
