<?php
namespace Test\Lucinda\Project;


use Lucinda\UnitTest\Result;

class ViewCompilationExceptionTest
{

    public function setTemplateTrace()
    {
        return new Result(false, "Not testable without simulating entire response!");
    }
        

    public function getTemplateTrace()
    {
        return new Result(false, "Not testable without simulating entire response!");
    }
        

}
