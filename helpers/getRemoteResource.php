<?php
use Lucinda\Framework\SingletonRepository;

/**
 * Gets remote resource from current OAuth2 driver
 *
 * @param string $url
 * @param array $fields
 * @return array
 */
function getRemoteResource(string $url, array $fields=[]): array
{
    $wrapper = SingletonRepository::get("oauth2");
    return ($wrapper->getDriver() ? $wrapper->getDriver()->getResource($wrapper->getAccessToken(), $url, $fields) : []);
}
