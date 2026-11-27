<?php

namespace Test\Lucinda\Project\EventListeners;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\EventListeners as Listeners;
use Test\Lucinda\Project\Support\ProjectFixture;
use Test\Lucinda\Project\Support\HttpRequest;

/** Verifies Localization presentation behavior. */
final class LocalizationTest
{
    /**
     * Negotiates the request locale and publishes usable translation state
     *
     * @return Result[] Observed behavior assertions
     */
    public function run(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $project->write('locale/en_US/messages.json', '{"welcome":"Hello %0"}');
        $project->write('locale/ro_RO/messages.json', '{"welcome":"Salut %0"}');
        $project->installXml(
            'xml/stdout/internationalization.xml',
            'localization/headers.xml'
        );
        $request = (new HttpRequest())->create(
            headers: ['Accept-Language' => 'ro-RO']
        );
        (new Listeners\Localization($request))->run();
        $results[] = (new Validator\Strings(
            translate('welcome', '', 'Ana')
        ))->assertEquals(
            'Salut Ana',
            'Configured request locale supplies translated text'
        );
        $results[] = (new Validator\Strings(
            translate('missing')
        ))->assertEquals(
            'missing',
            'Missing translation retains its key'
        );
        (new Listeners\Localization((new HttpRequest())->create()))->run();
        $results[] = (new Validator\Strings(
            translate('welcome', '', 'Ana')
        ))->assertEquals(
            'Hello Ana',
            'Missing locale preference selects the configured default'
        );
        return $results;
    }
}
