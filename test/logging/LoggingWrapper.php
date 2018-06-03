<?php
require_once(str_replace("/test/","/src/",__FILE__));
require_once(dirname(dirname(__DIR__))."/vendor/lucinda/logging/loader.php");

// create test environment
$xml = '
<xml>
    <loggers>
        <local>
            <file path="test" format="%d %m"/>
            <syslog application="test" format="%d %m"/>
            <test class="MyLogger" path="loggers"/>
        </local>
    </loggers>
</xml>
';

$xml = simplexml_load_string($xml);

$lw = new LoggingWrapper($xml->loggers->local);
echo __LINE__.": ".($lw->getLoggers()[0] instanceof FileLogger?"Y":"N")."\n";
echo __LINE__.": ".($lw->getLoggers()[1] instanceof SysLogger?"Y":"N")."\n";
echo __LINE__.": ".($lw->getLoggers()[2] instanceof CustomLogger?"Y":"N")."\n";