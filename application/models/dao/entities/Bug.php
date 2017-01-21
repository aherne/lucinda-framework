<?php
class BugEnvironment {
    public $files;
    public $get;
    public $post;
    public $server;
}

class ExceptionInformation {
    public $type;
    public $file;
    public $line;
    public $message;
    public $trace;
}

class Bug {
    public $id;
    public $date;
    public $count;
    /**
     * @var Exception|ExceptionInformation
     */
    public $exception;
    /**
     * @var BugEnvironment
     */
    public $environment;
}