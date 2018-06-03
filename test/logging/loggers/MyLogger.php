<?php
class MyLogger extends CustomLogger {    
    protected function log($info, $level)
    {
        echo __FILE__;
    }   
}