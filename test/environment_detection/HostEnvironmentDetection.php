<?php
require_once(str_replace("/test/","/src/",__FILE__));

// create test environment
$xml = '
<xml>
    <application>
        <environments>
            <local>www.example.local</local>
            <dev>dev.example.com</dev>
            <live>www.example.com</live>
        </environments>
    </application>
</xml>
';
$_SERVER["SERVER_NAME"] = "www.example.com";

// instance class
$test = new HostEnvironmentDetection(simplexml_load_string($xml));

// run tests
echo ($test->getEnvironment()=="live"?"OK":"NOK");