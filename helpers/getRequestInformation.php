<?php
use \Lucinda\Logging\RequestInformation;

function getRequestInformation(): RequestInformation
{
    $requestInformation = new RequestInformation();
    $requestInformation->setUri($_SERVER["REQUEST_URI"]??"");
    $requestInformation->setIpAddress($_SERVER["REMOTE_ADDR"]??"");
    $requestInformation->setUserAgent($_SERVER["HTTP_USER_AGENT"]??"");
    return $requestInformation;
}