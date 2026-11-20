<?php

require __DIR__ . '/bootstrap.php';
use Lucinda\Project\DAO;
use Lucinda\Project\Persistence\Locking\SqlLock;
use Lucinda\Project\Persistence\Locking\Lock;
use Test\Lucinda\Project\Persistence\RecordingLock;
use Test\Lucinda\Project\Persistence\FixtureQuery;
use Lucinda\Migration\Status;
use Test\Lucinda\Project\Persistence\HiddenPayload;
use Test\Lucinda\Project\Persistence\SerializedPayload;
use Lucinda\Project\Persistence\Builders;
use Lucinda\Project\Persistence\Queries;
use Lucinda\Framework\Persistence\Builders\Row;
use Lucinda\Framework\Persistence\Queries\Query;
use Lucinda\Framework\Persistence\SecretBox;
use Lucinda\Project\Presentation\DataBoundary;

$count = 0;
function check(bool $value, string $message): void
{
    global $count;
    ++$count; if (!$value) {
        throw new RuntimeException($message);
    }
}
function workers(string $mode, string $id, int $number = 8): array
{
    $jobs = [];
    for ($i = 0; $i < $number; $i++) {
        $process = proc_open([
            PHP_BINARY,
            __DIR__ . '/worker.php',
            $mode,
            $id,
            (string) $i,
        ], [1 => ['pipe','w'], 2 => ['pipe','w']], $pipes);
        if (!is_resource($process)) {
            throw new RuntimeException('Cannot launch concurrency test');
        }
        $jobs[] = [$process,$pipes];
    }
    $values = [];
    foreach ($jobs as [$process,$pipes]) {
        $value = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        if (proc_close($process) !== 0) {
            throw new RuntimeException('Worker failed: ' . $error);
        }
        $values[] = (int) $value;
    }
    return $values;
}
function scalarQuery(
    string $table,
    array $where,
    string $expression
): Query {
    $q = new Lucinda\Query\Select($table);
    $fields = $q->fields();
    $fields->add($expression, 'value');
    $conditions = [];
    $parameters = [];
    foreach ($where as $name => $value) {
        $conditions[$name] = ':' . $name;
        $parameters[':' . $name] = $value;
    }
    $q->where($conditions);
    return new FixtureQuery($q, $parameters);
}
function change(string $table, array $where, array $data): Query
{
    $q = new Lucinda\Query\Update($table);
    $set = [];
    $conditions = [];
    $parameters = [];
    foreach ($data as $name => $value) {
        $set[$name] = ':set_' . $name;
        $parameters[':set_' . $name] = $value;
    }
    foreach ($where as $name => $value) {
        $conditions[$name] = ':where_' . $name;
        $parameters[':where_' . $name] = $value;
    }
    $q->set($set);
    $q->where($conditions);
    return new FixtureQuery($q, $parameters);
}
try {
    $rejected = false;
    try {
        $row = new Row(['user_id' => '1']);
        $builder = new Builders\CredentialsBuilder($row);
        $builder->build();
    } catch (Throwable) {
        $rejected = true;
    }
    check($rejected, 'Missing credentials must fail');
    $rejected = false;
    try {
        $row = new Row([
            'id' => '1',
            'is_public' => 'false',
        ]);
        $builder = new Builders\PageBuilder($row);
        $builder->build();
    } catch (Throwable) {
        $rejected = true;
    }
    check($rejected, 'Text boolean must fail');
    $entity = (new Builders\CredentialsBuilder(new Row([
        'user_id' => '1',
        'password_hash' => 'secret',
    ])))->build();
    check(
        $entity->getUserID() === 1 && !($entity instanceof ArrayAccess),
        'Typed non-array entity'
    );
    $rejected = false;
    try {
        (new DataBoundary(['nested' => ['entity' => $entity]]))->validate();
    } catch (Throwable) {
        $rejected = true;
    }
    check($rejected, 'Nested entity must not reach a view');
    $versioned = new Lucinda\Framework\NoSQL\VersionedValue(
        'internal',
        ['private' => true],
        'revision',
        0.0
    );
    $rejected = false;
    try {
        (new DataBoundary(['record' => $versioned]))->validate();
    } catch (LogicException) {
        $rejected = true;
    }
    check($rejected, 'Framework storage observations must not reach a view');
    $wrapper = new HiddenPayload($entity);
    $rejected = false;
    try {
        (new DataBoundary($wrapper))->validate();
    } catch (Throwable) {
        $rejected = true;
    }
    check($rejected, 'Private entity field must not escape');
    $serializer = new SerializedPayload($entity);
    $rejected = false;
    try {
        (new DataBoundary($serializer))->validate();
    } catch (Throwable) {
        $rejected = true;
    }
    check($rejected, 'Serialized entity must not escape');
    $rejected = false;
    try {
        (new DataBoundary(new ArrayObject([$entity])))->validate();
    } catch (Throwable) {
        $rejected = true;
    }
    check($rejected, 'Internal collection storage must not escape');
    (new DataBoundary(['user' => ['id' => 1,'name' => 'Allowed']]))->validate();
    check(true, 'Plain view data allowed');
    $box = new SecretBox(base64_encode(random_bytes(32)));
    $cipher = $box->encrypt('JBSWY3DPEHPK3PXP');
    check(
        $box->decrypt($cipher) === 'JBSWY3DPEHPK3PXP' && !str_contains(
            $cipher,
            'JBSWY3DPEHPK3PXP'
        ),
        'Secret round trip'
    );
    $rejected = false;
    try {
        (new SecretBox(base64_encode(random_bytes(32))))->decrypt($cipher);
    } catch (Throwable) {
        $rejected = true;
    }
    check($rejected, 'Wrong key fails authentication');
    check(
        (new Lucinda\Framework\Persistence\ThrottleBucket(
            'form',
            'u',
            '::1'
        ))->getKey() === (new Lucinda\Framework\Persistence\ThrottleBucket(
            'form',
            'u',
            '0:0:0:0:0:0:0:1'
        ))->getKey(),
        'Canonical IPv6 throttling'
    );
    $attack = "x' OR 1=1 --";
    $query = new Queries\Accounts\FindCredentials($attack);
    check(
        !str_contains(
            (string)$query->getSql(),
            $attack
        ) && in_array($attack, $query->getParameters(), true),
        'Hostile input is bound, not interpolated'
    );
    if (!getenv('PERSISTENCE_TEST_DATABASE')) {
        echo "Unit checks passed ($count). Integration checks skipped: supply "
            . "an isolated PERSISTENCE_TEST_DATABASE.\n";
        exit(0);
    }
    $sql = persistenceTestSql();
    $id = persistenceTestInsert($sql, 'users', [
        'display_name' => 'Test',
        'email' => null,
        'mfa_required' => 1,
    ]);
    persistenceTestInsert($sql, 'user_credentials', [
        'user_id' => $id,
        'username' => 'alice',
        'password_hash' => password_hash(
            'right-password',
            PASSWORD_BCRYPT,
            ['cost' => 4]
        ),
    ]);
    $form = new DAO\FormLogin($sql);
    check(
        $form->login(
            'alice',
            'wrong'
        ) === null && $form->login($attack, 'anything') === null,
        'Failed form login'
    );
    check(
        $form->login('alice', 'right-password') === $id,
        'Successful form login'
    );
    check((new DAO\Logout())->logout($id), 'Framework-owned logout');
    $sql->write(change('users', ['id' => $id], ['is_active' => 0]));
    check(
        $form->login('alice', 'right-password') === null,
        'Disabled account denied'
    );
    $sql->write(change('users', ['id' => $id], ['is_active' => 1]));
    $state = new DAO\OAuth2States($sql);
    $proof = str_repeat('a', 64);
    $state->save('caseSensitive', 'Google', time() + 60, $proof);
    $rejected = false;
    try {
        $state->save('caseSensitive', 'Google', time() + 60, $proof);
    } catch (Throwable) {
        $rejected = true;
    }
    check($rejected, 'Duplicate state rejected');
    check(
        !$state->consume('casesensitive', 'Google', $proof),
        'State case must match'
    );
    check(
        !$state->consume('caseSensitive ', 'Google', $proof),
        'State trailing bytes must match'
    );
    check(
        !$state->consume('caseSensitive', 'GitHub', $proof),
        'Wrong provider denied'
    );
    check(
        !$state->consume('caseSensitive', 'Google', str_repeat('b', 64)),
        'Wrong proof denied'
    );
    check(
        $state->consume(
            'caseSensitive',
            'Google',
            $proof
        ) && !$state->consume('caseSensitive', 'Google', $proof),
        'State accepts once'
    );
    $state->save('expired', 'Google', time() - 1, $proof);
    check(
        !$state->consume('expired', 'Google', $proof),
        'Expired state denied'
    );
    $state->save('cleanup', 'Google', time() - 1, $proof);
    $state->clearExpired();
    check(
        $sql->select(scalarQuery(
            'oauth2_states',
            ['state' => 'cleanup'],
            'COUNT(*)'
        ))->getFirstRow()->getScalar('value') === '0',
        'Expired state maintenance'
    );
    $state->save('parallel', 'Google', time() + 60, $proof);
    check(
        array_sum(workers('state', 'parallel')) === 1,
        'Exactly one concurrent state callback accepted'
    );
    $sql->begin();
    $rejected = false;
    try {
        $state->save('transaction', 'Google', time() + 60, $proof);
    } catch (Throwable) {
        $rejected = true;
    } finally {
        $sql->rollback();
    }
    check($rejected, 'State rejects application transaction');
    $public = persistenceTestInsert(
        $sql,
        'resources',
        ['url' => 'public','is_public' => 1]
    );
    $private = persistenceTestInsert($sql, 'resources', [
        'url' => 'private',
        'is_public' => 0,
    ]);
    persistenceTestInsert($sql, 'users_resources', [
        'user_id' => $id,
        'resource_id' => $private,
        'http_method' => 'GET',
    ]);
    $pages = new DAO\PageAuthorization($sql);
    $rights = new DAO\UserAuthorization($sql);
    check(
        $pages->getID('public') === $public
            && $pages->isPublic($public)
                && $pages->getID('missing') === null
                    && !$pages->isPublic(99999),
        'Page public/missing policies'
    );
    check(
        $rights->isAllowed(
            $id,
            $private,
            'GET'
        ) && !$rights->isAllowed($id, $private, 'POST'),
        'Method-aware direct permissions'
    );
    $role = persistenceTestInsert($sql, 'roles', ['name' => 'EDITORS']);
    persistenceTestInsert($sql, 'users_roles', [
        'user_id' => $id,
        'role_id' => $role,
    ]);
    persistenceTestInsert($sql, 'roles_resources', [
        'role_id' => $role,
        'resource_id' => $private,
        'http_method' => '*',
    ]);
    check(
        (new DAO\RoleAuthorization($sql))->isAllowed($id, $private, 'POST'),
        'Role wildcard method permission'
    );
    check(
        (new DAO\UserRoles($sql))->getRoles($id) === ['EDITORS']
            && (new DAO\UserRoles($sql))->getRoles(null) === ['GUESTS'],
        'Explicit database roles'
    );
    check(
        (new DAO\SimpleUserRoles())->getRoles($id) === ['MEMBERS'],
        'Simple roles alternative'
    );
    $oauth = new DAO\OAuth2AutomaticProvisioning($sql);
    check(
        (new DAO\OAuth2Login($sql))->resolve(
            persistenceTestUser('unlinked'),
            'Google'
        ) === null,
        'Lookup must not create'
    );
    $created = $oauth->create(persistenceTestUser('new'), 'Google');
    check(
        $created !== null && $created === $oauth->create(
            persistenceTestUser('new'),
            'Google'
        ),
        'Automatic creation idempotent'
    );
    $different = $oauth->create(persistenceTestUser('different'), 'Google');
    check($different !== $created, 'Same email must not merge identities');
    $beforeCount = (int) $sql->select(scalarQuery(
        'users',
        [],
        'COUNT(*)'
    ))->getFirstRow()->getScalar('value');
    $parallelIDs = workers('oauth', 'parallel-identity');
    check(
        count(array_unique($parallelIDs)) === 1 && $parallelIDs[0] > 0,
        'Concurrent provisioners return one account'
    );
    check(
        (int) $sql->select(scalarQuery(
            'users',
            [],
            'COUNT(*)'
        ))->getFirstRow()->getScalar('value') === $beforeCount + 1,
        'Losing provisioners roll back account insertion'
    );
    $approval = new DAO\OAuth2ApprovalProvisioning($sql);
    check(
        $approval->requestApproval(
            persistenceTestUser('approval'),
            'GitHub'
        ) === Lucinda\WebSecurity\OAuth2ApprovalStatus::PENDING,
        'Approval pending'
    );
    check(
        $approval->requestApproval(
            persistenceTestUser('approval'),
            'GitHub'
        ) === Lucinda\WebSecurity\OAuth2ApprovalStatus::PENDING,
        'Approval idempotent'
    );
    $sql->write(change('oauth2_approvals', [
        'vendor_name' => 'GitHub',
        'remote_id' => 'approval',
    ], ['status' => 'rejected']));
    check(
        $approval->requestApproval(
            persistenceTestUser('approval'),
            'GitHub'
        ) === Lucinda\WebSecurity\OAuth2ApprovalStatus::REJECTED,
        'Rejection remains rejected'
    );
    $throttle = new DAO\SQLLoginThrottler($sql, 3, 30);
    check(
        !$throttle->isThrottled('alice', '127.0.0.1'),
        'Initial throttle clear'
    );
    for ($i = 0; $i < 3; $i++) {
        $throttle->penalize('alice', '127.0.0.1');
    }
    check(
        $throttle->isThrottled(
            'alice',
            '127.0.0.1'
        ) && !$throttle->isThrottled('alice', '127.0.0.2'),
        'Throttle threshold and IP isolation'
    );
    check(
        !(new DAO\SQLMultiFactorThrottler($sql))->isThrottled($id, '127.0.0.1'),
        'Form/MFA scope isolation'
    );
    workers('throttle', 'parallel', 16);
    $bucket = (new Lucinda\Framework\Persistence\ThrottleBucket(
        'form',
        'parallel',
        '127.0.0.1'
    ))->getKey();
    check(
        $sql->select(scalarQuery(
            'authentication_throttles',
            ['bucket' => $bucket],
            'attempts'
        ))->getFirstRow()->getScalar('value') === '16',
        'Concurrent failures cannot lose increments'
    );
    $sql->write(change(
        'authentication_throttles',
        ['bucket' => $bucket],
        ['expires' => time() - 1]
    ));
    (new DAO\SQLLoginThrottler($sql))->penalize('parallel', '127.0.0.1');
    check(
        $sql->select(scalarQuery(
            'authentication_throttles',
            ['bucket' => $bucket],
            'attempts'
        ))->getFirstRow()->getScalar('value') === '1',
        'Expired throttle window resets'
    );
    $expiredUser = persistenceTestInsert($sql, 'users', [
        'display_name' => 'Expired setup',
        'mfa_required' => 1,
    ]);
    $expiredMfa = new DAO\MultiFactorAuthentication($sql, $box);
    $expiredMfa->saveSetupSecret($expiredUser, 'OLDSETUPSECRET');
    $sql->write(change(
        'user_mfa',
        ['user_id' => $expiredUser],
        ['setup_expires' => time() - 1]
    ));
    check(
        $expiredMfa->getSetupSecret($expiredUser) === null
            && !$expiredMfa->consumeTotpCounter(
                $expiredUser,
                1
            ),
        'Expired setup cannot consume counters'
    );
    $expiredMfa->saveSetupSecret($expiredUser, 'NEWSETUPSECRET');
    check(
        $expiredMfa->getSetupSecret($expiredUser) === 'NEWSETUPSECRET',
        'Expired setup can be replaced'
    );
    $mfa = new DAO\MultiFactorAuthentication($sql, $box);
    check(
        $mfa->isRequired($id) && $mfa->getSecret($id) === null,
        'Required but unenrolled MFA'
    );
    check(
        $mfa->getAccountName($id) === (string) $id,
        'Enrollment account label uses its generated field alias'
    );
    $mfa->saveSetupSecret($id, 'JBSWY3DPEHPK3PXP');
    check(
        $mfa->getSetupSecret($id) === 'JBSWY3DPEHPK3PXP',
        'Setup secret retrieval'
    );
    $rejected = false;
    try {
        $mfa->enable($id, 'JBSWY3DPEHPK3PXP');
    } catch (Throwable) {
        $rejected = true;
    }
    check($rejected, 'Enable without verified counter rejected');
    check($mfa->consumeTotpCounter($id, 123455), 'Setup counter accepted');
    $mfa->enable($id, 'JBSWY3DPEHPK3PXP');
    $mfa->clearSetupSecret($id);
    check(
        $mfa->getSecret($id) === 'JBSWY3DPEHPK3PXP'
            && $mfa->getSetupSecret($id) === null,
        'Enrollment retains confirmed secret'
    );
    check(
        !$mfa->consumeTotpCounter(
            $id,
            123455
        ) && !$mfa->consumeTotpCounter($id, 123454),
        'Confirmation counter cannot replay'
    );
    check(
        array_sum(workers('counter', (string)$id)) === 1,
        'Exactly one concurrent TOTP counter accepted'
    );

    $plainSession = new DAO\SQLSessionHandler($sql);
    $plainSession->write('storage-only', 'payload');
    check(
        $plainSession->read('storage-only') === 'payload',
        'Storage-only session implementation remains usable'
    );
    $plainSession->destroy('storage-only');
    $plainSession->close();

    $recordingLock = new RecordingLock();
    $customSession = new DAO\LockingSQLSessionHandler($sql, $recordingLock);
    check(
        $customSession instanceof Lock,
        'SQL session extension exposes the locking interface'
    );
    $customSession->read('strategy');
    $customSession->write('strategy', 'custom');
    check(
        $recordingLock->getEvents() === [
            'acquire:strategy',
            'acquire:strategy',
        ],
        'Locking extension delegates ownership and retains it until close'
    );
    $customSession->close();
    check(
        $recordingLock->getEvents() === [
            'acquire:strategy',
            'acquire:strategy',
            'release',
        ],
        'Locking extension releases its injected interface implementation'
    );

    $firstLock = new SqlLock($sql, 'integration-lock:', 0);
    $secondLock = new SqlLock(persistenceTestSql(), 'integration-lock:', 0);
    $firstLock->acquire('shared');
    $firstLock->acquire('shared');
    $rejected = false;
    try {
        $secondLock->acquire('shared');
    } catch (RuntimeException) {
        $rejected = true;
    }
    check($rejected, 'Independent SQL lock owners cannot acquire one identity');
    $firstLock->release();
    $secondLock->acquire('shared');
    check(true, 'Repeated acquisition by one owner requires only one release');
    $secondLock->release();
    $firstLock->acquire('old');
    $firstLock->acquire('new');
    $secondLock->acquire('old');
    check(true, 'Switching identities releases the previously owned SQL lock');
    $firstLock->release();
    $secondLock->release();

    $session = new DAO\LockingSQLSessionHandler($sql);
    check($session->read('binary-session') === '', 'Absent session is empty');
    $session->write('binary-session', "a\0b");
    $session->write('binary-session', "c\0d");
    check(
        $session->read('binary-session') === "c\0d",
        'Session repeated binary writes'
    );
    $sql->write(change(
        'sessions',
        ['id' => 'binary-session'],
        ['expires' => time() - 1]
    ));
    check(
        $session->read('binary-session') === '' && $session->gc(1) === 1,
        'Session expiration and garbage collection'
    );
    $session->destroy('binary-session');
    $session->destroy('binary-session');
    check(
        $session->read('binary-session') === '',
        'Session destruction idempotent'
    );
    $session->close();
    workers('session', 'parallel-session');
    check(
        $session->read('parallel-session') === '8',
        'Session locking prevents lost updates'
    );
    $session->close();
    $cache = new DAO\SQLMigrationCache($sql);
    check($cache->exists(), 'Migration schema present');
    $cache->create();
    $cache->add('TestMigration', Status::PASSED);
    $cache->add('TestMigration', Status::PASSED);
    check(
        $cache->read()['TestMigration'] === Status::PASSED,
        'Migration same-status upsert'
    );
    $cache->add('TestMigration', Status::FAILED);
    check(
        $cache->read()['TestMigration'] === Status::FAILED,
        'Migration status update'
    );
    $cache->remove('TestMigration');
    check(!isset($cache->read()['TestMigration']), 'Migration removal');
    $cache->add('PendingMigration', Status::PENDING);
    check(
        $cache->read()['PendingMigration'] === Status::PENDING,
        'Pending migration status retained'
    );
    foreach (['nosql.php', 'providers.php'] as $suite) {
        $process = proc_open([
            PHP_BINARY,
            '-d',
            'apc.enable_cli=1',
            __DIR__ . '/' . $suite,
        ], [1 => STDOUT, 2 => STDERR], $pipes);
        if (!is_resource($process) || proc_close($process) !== 0) {
            throw new RuntimeException('Integration checks failed: ' . $suite);
        }
    }
    $message = "Persistence checks passed ($count), including isolated MySQL";
    $message .= ' and concurrent workers';
    echo $message . ".\n";
} catch (Throwable $error) {
    fwrite(
        STDERR,
        get_class($error) . ': ' . $error->getMessage() . "\n"
        . $error->getTraceAsString() . "\n"
    );
    exit(1);
}
