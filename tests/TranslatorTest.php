<?php

namespace Test\Lucinda\Project;

use Lucinda\Internationalization\Reader;
use Lucinda\Internationalization\Wrapper;
use Lucinda\Project\Translator;
use Lucinda\UnitTest\Result;

class TranslatorTest
{
    public function set()
    {
        $wrapper = new Wrapper(simplexml_load_file(__DIR__."/mocks/stdout.xml"), [], []);
        Translator::set($wrapper->getReader());
        return new Result(true, "tested via get() method");
    }


    public function get()
    {
        return new Result(Translator::get() instanceof Reader);
    }
}
