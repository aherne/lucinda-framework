<?php
namespace Test\Lucinda\Project\mocks\Authentication;

use Lucinda\WebSecurity\Authentication\Form\LoginThrottler;

class MockLoginThrottler extends LoginThrottler
{
    private $attempts = [];
    
    protected function setCurrentStatus(): void
    {
        $this->attempts[$this->userName] = 0;
    }
    
    public function getTimePenalty(): int
    {
        return (isset($this->attempts[$this->userName])?pow($this->attempts[$this->userName], 2):0);
    }
    
    public function setFailure(): void
    {
        if (isset($this->attempts[$this->userName])) {
            $this->attempts[$this->userName]++;
        } else {
            $this->attempts[$this->userName]=1;
        }
    }
    
    public function setSuccess(): void
    {
        $this->attempts[$this->userName] = 0;
    }
};
