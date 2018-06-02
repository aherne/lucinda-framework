<?php
require_once(str_replace("/test/","/src/",__FILE__));

// create test environment
$xml = '
<xml>
    <application>
        <environments>
            <local>localhost</local>
            <dev>development</dev>
            <live>live</live>
        </environments>
    </application>
</xml>
';
putenv("ENVIRONMENT=live");

// instance class
$test = new VariableEnvironmentDetection(simplexml_load_string($xml));

// run tests
echo ($test->getEnvironment()=="live"?"OK":"NOK");