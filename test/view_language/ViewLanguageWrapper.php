<?php
require_once(dirname(dirname(__DIR__))."/vendor/lucinda/view-language/loader.php");
require_once(str_replace("/test/","/src/",__FILE__));

$xml = '
<xml>
    <application>
        <paths>
            <compilations>
                <local>compilations</local>
            </compilations>
            <tags>tags</tags>
            <views>views</views>
        </paths>
        <templates_extension>html</templates_extension>
    </application>
</xml>
';
$compiler = new ViewLanguageWrapper(simplexml_load_string($xml), "index", "local");
echo __LINE__.": ".(file_get_contents("compilations/index.html")=='My name is <strong>Lucian Popescu</strong>!'?"Y":"N");