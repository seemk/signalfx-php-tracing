<?php

namespace DDTrace\Tests\Integrations\Custom\NotAutoloaded;

use DDTrace\Tests\Common\SpanAssertion;
use DDTrace\Tests\Common\WebFrameworkTestCase;
use DDTrace\Tests\Frameworks\Util\Request\GetSpec;

final class HttpHeadersNotConfiguredTest extends WebFrameworkTestCase
{
    protected static function getAppIndexScript()
    {
        return __DIR__ . '/../../../Frameworks/Custom/Version_Not_Autoloaded/Headers/index.php';
    }

    protected static function getEnvs()
    {
        return array_merge(parent::getEnvs(), [
            'SIGNALFX_SERVICE_NAME' => 'my-service',
        ]);
    }

    public function testSelectedHeadersAreIncluded()
    {
        $traces = $this->tracesFromWebRequest(function () {
            $spec = GetSpec::create(
                'First request: Startup logs test',
                '/',
                [
                    'first-Header: some value: with colon',
                    'FORTH-header: 123',
                ]
            );
            $this->call($spec);
        });

        $this->assertFlameGraph(
            $traces,
            [
                SpanAssertion::build(
                    'web.request',
                    'my-service',
                    SpanAssertion::NOT_TESTED,
                    'GET /'
                )->withExactTags([
                    'http.method' => 'GET',
                    'http.url' => '/',
                    'http.status_code' => 200,
                    'component' => 'web.request',
                ]),
            ]
        );
    }
}
