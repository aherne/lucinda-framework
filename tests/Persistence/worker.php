<?php

require __DIR__ . '/bootstrap.php';

use Lucinda\Migration\Status;
use Lucinda\Project\DAO;

try {
    $sql = persistenceTestSql();
    $mode = $argv[1];
    $id = $argv[2];
    switch ($mode) {
        case 'state':
            $states = new DAO\OAuth2States($sql);
            $value = (int) $states->consume(
                $id,
                'Google',
                str_repeat('a', 64)
            );
            break;
        case 'counter':
            $mfa = new DAO\MultiFactorAuthentication($sql);
            $value = (int) $mfa->consumeTotpCounter($id, 123456);
            break;
        case 'oauth':
            $oauth = new DAO\OAuth2AutomaticProvisioning($sql);
            $value = $oauth->create(persistenceTestUser($id), 'Google');
            break;
        case 'throttle':
            $throttler = new DAO\SQLLoginThrottler($sql);
            $throttler->penalize($id, '127.0.0.1');
            $value = 1;
            break;
        case 'session':
            $handler = new DAO\LockingSQLSessionHandler($sql);
            $previous = (int) $handler->read($id);
            usleep(15000);
            $handler->write($id, (string) ($previous + 1));
            $handler->close();
            $value = 1;
            break;
        default:
            throw new RuntimeException('Unknown worker mode');
    }
    echo $value;
} catch (Throwable $error) {
    fwrite(STDERR, get_class($error) . ': ' . $error->getMessage());
    exit(1);
}
