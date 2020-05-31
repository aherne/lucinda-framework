<?php
function scan($folder, $processor) {
    $files = scandir($folder);
    foreach($files as $file) {
        if(in_array($file, [".", "..", "tests", "unit-testing", "tests_drivers"])) continue;
        $filename = $folder."/".$file;
        if(strpos($filename, ".php")) {
            $processor($filename);
        } else if(is_dir($filename)) {
            scan($filename, $processor);
        }
    }
}

scan(__DIR__."/vendor/lucinda", function ($filename) {
    $result = file_get_contents($filename);
    preg_match("/namespace ([a-zA-Z0-9\\\]+)/", $result, $matches2);
    preg_match("/class ([a-zA-Z0-9]+)\sextends\s\\\Exception/", $result, $matches1);
    if (!empty($matches1[1])) {
        echo $matches2[1]."\\".$matches1[1]."\n";
    } else if (preg_match("/class ([a-zA-Z0-9]+)Exception/", $result, $matches3)) {
        echo $matches2[1]."\\".$matches3[1]."Exception\n";
    }
});
    
scan(__DIR__."/vendor/lucinda", function ($filename) {
    $result = file_get_contents($filename);
    preg_match_all("/function\s([a-zA-Z0-9_]+)\s*\(([^\)]*)\)([^\\n]*)/", $result, $matches);
    if(!empty($matches[1])) {
        foreach ($matches[1] as $i=>$methodName) {
            if(!trim($matches[3][$i]) && !in_array($methodName, ["__construct", "__destruct"])) {
                echo $filename."\t".$methodName."\n";
            }
        }
    }
});