<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\DAO;
use Test\Lucinda\Project\Support\MySqlFixture;

/** Verifies SQLSessionHandler application behavior. */
final class SQLSessionHandlerTest
{
    /**
     * Stores binary payloads and expires or deletes sessions idempotently
     *
     * @return Result[] Observed behavior assertions
     */
    private function testPayloadLifecycle(): array
    {
        $results = [];
        $db = new MySqlFixture();
        $dao = new DAO\SQLSessionHandler($db->getExecutor());
        $results[] = (new Validator\Booleans(
            $dao->open('', 'session') === true
        ))->assertTrue(
            'Session storage opens'
        );
        $results[] = (new Validator\Strings(
            $dao->read('0017')
        ))->assertEquals(
            '',
            'Missing session has empty payload'
        );
        $results[] = (new Validator\Booleans(
            $dao->write('0017', "a\0\xffb") === true
        ))->assertTrue(
            'Binary payload written'
        );
        $results[] = (new Validator\Strings(
            $dao->read('0017')
        ))->assertEquals(
            "a\0\xffb",
            'Binary payload round trips'
        );
        $dao->write('0017', 'replacement');
        $results[] = (new Validator\Strings(
            $dao->read('0017')
        ))->assertEquals(
            'replacement',
            'Repeated write replaces payload'
        );
        $results[] = (new Validator\Strings(
            $dao->read('17')
        ))->assertEquals(
            '',
            'Numeric session IDs retain leading zeroes'
        );
        $db->execute('UPDATE sessions SET expires = ?', [time() - 1]);
        $results[] = (new Validator\Strings(
            $dao->read('0017')
        ))->assertEquals(
            '',
            'Expired payload is invisible'
        );
        $results[] = (new Validator\Integers(
            $dao->gc(1)
        ))->assertEquals(
            1,
            'Garbage collection deletes expired rows'
        );
        $results[] = (new Validator\Booleans(
            $dao->destroy('0017') === true
        ))->assertTrue(
            'Absent session destruction succeeds'
        );
        $results[] = (new Validator\Booleans(
            $dao->destroy('0017') === true
        ))->assertTrue(
            'Repeated destruction succeeds'
        );
        $results[] = (new Validator\Booleans(
            $dao->close() === true
        ))->assertTrue(
            'Storage-only session closes'
        );
        return $results;
    }
    /**
     * Verifies open through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function open(): array
    {
        return $this->testPayloadLifecycle();
    }

    /**
     * Verifies close through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function close(): array
    {
        return $this->testPayloadLifecycle();
    }

    /**
     * Verifies read through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function read(): array
    {
        return $this->testPayloadLifecycle();
    }

    /**
     * Verifies write through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function write(): array
    {
        return $this->testPayloadLifecycle();
    }

    /**
     * Verifies destroy through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function destroy(): array
    {
        return $this->testPayloadLifecycle();
    }

    /**
     * Verifies gc through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function gc(): array
    {
        return $this->testPayloadLifecycle();
    }
}
