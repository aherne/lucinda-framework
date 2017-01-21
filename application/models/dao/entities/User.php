<?php
require_once("UserRight.php");

class User {
    public $id;
    public $email;
    public $password;
    public $name;
    public $rights=array();
}