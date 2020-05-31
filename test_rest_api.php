<?php
require __DIR__ . '/vendor/autoload.php';

$features = json_decode(file_get_contents("features.json"), true);

// DataSource, .htaccess
ini_set("display_errors",1);
use Lucinda\UnitTest\Validator\URL\DataSource;
use Lucinda\UnitTest\Validator\URL\Request;

// execute LOGIN to get csrf token
$loginPage = new DataSource("http://www.test.local/login");
$loginPage->setRequestMethod("GET");
$request = new Request($loginPage);
$response = json_decode($request->getResponse()->getBody(), true);

// use csrf token to LOGIN and get access token
$loginPage->setRequestMethod("POST");
$loginPage->addRequestParameter("username", "john");
$loginPage->addRequestParameter("password", "doe");
$loginPage->addRequestParameter("csrf", $response["body"]["csrf"]);
$request = new Request($loginPage);
$response = json_decode($request->getResponse()->getBody(), true);

// use access token to get to members page
$membersPage = new DataSource("http://www.test.local/".($features["security"]["isCMS"]?"index":"members"));
$membersPage->setRequestMethod("GET");
$membersPage->addRequestHeader("Authorization", "Bearer ".$response["body"]["token"]);
$request = new Request($membersPage);
$response = json_decode($request->getResponse()->getBody(), true);
echo ($response["status"]=="ok"?"OK":"NOK");
