<?php
require_once(str_replace("/test/","/src/",__FILE__));

// create test environment
$xml = '
<xml>
    <application>
        <environments>
            <local>/var/www/html/test</local>
            <dev>/home/bad/site</dev>
            <live>'.dirname(dirname(__DIR__)).'</live>
        </environments>
    </application>
</xml>
';

// instance class
$test = new PathEnvironmentDetection(simplexml_load_string($xml));

// run tests
echo ($test->getEnvironment()=="live"?"OK":"NOK");