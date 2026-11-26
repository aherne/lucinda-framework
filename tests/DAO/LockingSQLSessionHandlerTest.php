<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\DAO;
use Test\Lucinda\Project\Support\MySqlFixture;
use Lucinda\Framework\Persistence\Locking\SqlLock;

/** Verifies LockingSQLSessionHandler application behavior. */
final class LockingSQLSessionHandlerTest
{
    /**
     * Retains the default MySQL lock until close across separate connections
     *
     * @return Result[] Observed live lock and payload lifecycle assertions
     */
    public function testMySqlLockOwnership(): array
    {
        $db = new MySqlFixture();
        $otherExecutor = $db->createExecutor();
        $first = new DAO\LockingSQLSessionHandler($db->getExecutor());
        $second = new DAO\LockingSQLSessionHandler($otherExecutor);
        $contender = new SqlLock($otherExecutor, 'framework-session:', 0);
        $results = [];
        try {
            $first->write('locked-session', 'retained payload');
            $rejected = false;
            try {
                $contender->acquire('locked-session');
            } catch (\RuntimeException) {
                $rejected = true;
            }
            $results[] = (new Validator\Booleans($rejected))->assertTrue(
                'A separate MySQL connection cannot enter an owned session'
            );
            $first->close();
            $contender->acquire('locked-session');
            $contender->release();
            $results[] = (new Validator\Strings(
                $second->read('locked-session')
            ))->assertEquals(
                'retained payload',
                'A successor connection reads the committed session payload'
            );
            $second->destroy('locked-session');
            $second->close();
            $results[] = (new Validator\Strings(
                $first->read('locked-session')
            ))->assertEquals(
                '',
                'The original connection observes the successor deletion'
            );
        } finally {
            $contender->release();
            $second->close();
            $first->close();
        }
        return $results;
    }

    /**
     * Retains the supplied lock through storage operations until close
     *
     * @return Result[] Observed behavior assertions
     */
    private function testDelegatedLocking(): array
    {
        $results = [];
        $db = new MySqlFixture();
        $lock = new \Test\Lucinda\Project\Support\RecordingLock();
        $dao = new DAO\LockingSQLSessionHandler($db->getExecutor(), $lock);
        $dao->read('session');
        $dao->write('session', 'payload');
        $dao->destroy('session');
        $results[] = (new Validator\Arrays(
            $lock->getEvents()
        ))->assertIdentical(
            ['acquire:session', 'acquire:session', 'acquire:session'],
            'Every storage operation requires ownership'
        );
        $dao->close();
        $results[] = (new Validator\Strings(
            $lock->getEvents()[3]
        ))->assertEquals(
            'release',
            'Close delegates ownership release'
        );
        $dao->acquire('explicit');
        $dao->release();
        $results[] = (new Validator\Arrays(
            array_slice(
                $lock->getEvents(),
                4
            )
        ))->assertIdentical(
            ['acquire:explicit', 'release'],
            'Explicit lock contract delegates to the supplied strategy'
        );
        return $results;
    }
    /**
     * Verifies acquire through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function acquire(): array
    {
        return $this->testDelegatedLocking();
    }

    /**
     * Verifies release through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function release(): array
    {
        return $this->testDelegatedLocking();
    }

    /**
     * Verifies read through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function read(): array
    {
        return $this->testDelegatedLocking();
    }

    /**
     * Verifies write through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function write(): array
    {
        return $this->testDelegatedLocking();
    }

    /**
     * Verifies destroy through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function destroy(): array
    {
        return $this->testDelegatedLocking();
    }

    /**
     * Verifies close through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function close(): array
    {
        return $this->testDelegatedLocking();
    }

    /**
     * Verifies open through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function open(): array
    {
        return $this->testDelegatedLocking();
    }

    /**
     * Verifies gc through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function gc(): array
    {
        return $this->testDelegatedLocking();
    }
}
