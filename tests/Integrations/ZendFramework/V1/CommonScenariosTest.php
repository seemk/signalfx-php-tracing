<?php

namespace DDTrace\Tests\Integrations\ZendFramework\V1;

use DDTrace\Tests\Common\SpanAssertion;
use DDTrace\Tests\Common\WebFrameworkTestCase;
use DDTrace\Tests\Frameworks\Util\Request\RequestSpec;

class CommonScenariosTest extends WebFrameworkTestCase
{
    protected static function getAppIndexScript()
    {
        return __DIR__ . '/../../../Frameworks/ZendFramework/Version_1_12/public/index.php';
    }

    /**
     * @dataProvider provideSpecs
     * @param RequestSpec $spec
     * @param array $spanExpectations
     * @throws \Exception
     */
    public function testScenario(RequestSpec $spec, array $spanExpectations)
    {
        $traces = $this->tracesFromWebRequest(function () use ($spec) {
            $this->call($spec);
        });

        $this->assertFlameGraph($traces, $spanExpectations);
    }

    public function provideSpecs()
    {
        return $this->buildDataProvider(
            [
                'A simple GET request returning a string' => [
                    SpanAssertion::build(
                        'simple@index default',
                        'unnamed-php-service',
                        SpanAssertion::NOT_TESTED,
                        'GET /simple'
                    )
                    ->withExactTags([
                        'zf1.controller' => 'simple',
                        'zf1.action' => 'index',
                        'zf1.route_name' => 'default',
                        'http.method' => 'GET',
                        'http.url' => 'http://localhost:9999/simple',
                        'http.status_code' => '200',
                        'component' => 'zendframework',
                    ]),
                ],
                'A simple GET request with a view' => [
                    SpanAssertion::build(
                        'simple@view my_simple_view_route',
                        'unnamed-php-service',
                        SpanAssertion::NOT_TESTED,
                        'GET /simple_view'
                    )
                    ->withExactTags([
                        'zf1.controller' => 'simple',
                        'zf1.action' => 'view',
                        'zf1.route_name' => 'my_simple_view_route',
                        'http.method' => 'GET',
                        'http.url' => 'http://localhost:9999/simple_view',
                        'http.status_code' => '200',
                        'component' => 'zendframework',
                    ]),
                ],
                'A GET request with an exception' => [
                    SpanAssertion::build(
                        'error@error default',
                        'unnamed-php-service',
                        SpanAssertion::NOT_TESTED,
                        'GET /error'
                    )
                    ->withExactTags([
                        'zf1.controller' => 'error',
                        'zf1.action' => 'error',
                        'zf1.route_name' => 'default',
                        'http.method' => 'GET',
                        'http.url' => 'http://localhost:9999/error',
                        'http.status_code' => '500',
                        'component' => 'zendframework',
                    ])->setError(),
                ],
            ]
        );
    }
}
