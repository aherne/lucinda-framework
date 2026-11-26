<?php

namespace Test\Lucinda\Project\Cacheables;

use Lucinda\Framework\Persistence\NoSqlExecutor;
use Lucinda\Project\Cacheables\Date;
use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Test\Lucinda\Project\Support\HttpRequest;
use Test\Lucinda\Project\Support\RedisFixture;

/** Exercises Date's existing static-driver injection against live Redis. */
final class DateTest
{
    /**
     * Injects real Redis and retains or renews representation timestamps
     *
     * @return Result[] Live cache representation assertions
     */
    public function setDriver(): array
    {
        $redis = new RedisFixture();
        Date::setDriver($redis->getDriver());
        $request = (new HttpRequest())->create();
        $first = new Date($request, 'body');
        $same = new Date($request, 'body');
        $results = [
            (new Validator\Booleans($first->getTime() > 0))
                ->assertTrue('Real Redis supplies the observation timestamp'),
            (new Validator\Integers($same->getTime()))
                ->assertEquals(
                    $first->getTime(),
                    'Unchanged representations retain their timestamp'
                ),
            (new Validator\Strings($first->getEtag()))->assertEmpty(),
        ];
        $executor = new NoSqlExecutor($redis->getDriver());
        $key = $executor->getKeyspace()->getKey(
            'representation',
            'example.test#body'
        );
        $executor->replace($executor->read($key), [
            'time' => 1, 'expires' => time() - 1,
        ], 60);
        $renewed = new Date($request, 'body');
        $results[] = (new Validator\Booleans($renewed->getTime() > 1))
            ->assertTrue('An expired Redis timestamp is renewed');
        return $results;
    }

    /**
     * Keeps different rendered bodies and hosts in independent Redis records
     *
     * @return Result[] Representation identity assertions
     */
    public function representationIsolation(): array
    {
        $redis = new RedisFixture();
        Date::setDriver($redis->getDriver());
        $executor = new NoSqlExecutor($redis->getDriver());
        $key = $executor->getKeyspace()->getKey(
            'representation',
            'example.test#body'
        );
        $executor->replace($executor->read($key), [
            'time' => 123, 'expires' => time() + 60,
        ], 60);
        $requests = new HttpRequest();
        $same = new Date($requests->create(), 'body');
        $body = new Date($requests->create(), 'different body');
        $host = new Date($requests->create(host: 'other.test'), 'body');
        return [
            (new Validator\Integers($same->getTime()))->assertEquals(123),
            (new Validator\Booleans($body->getTime() > 123))->assertTrue(),
            (new Validator\Booleans($host->getTime() > 123))->assertTrue(),
        ];
    }
}
