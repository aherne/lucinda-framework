<?php
/**
 * Gets parent node to that identified by tag name
 * 
 * @param Lucinda\STDOUT\Application|Lucinda\STDERR\Application $application
 * @param string $tagName
 * @return SimpleXMLElement
 */
function getParentNode($application, string $tagName): SimpleXMLElement
{
    $parents = $application->getTag($tagName)->xpath("..");
    return $parents[0]??new SimpleXMLElement("<xml></xml>");
}