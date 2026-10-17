<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace aiprovider_xai;

use aiprovider_xai\test\testcase_helper_trait;
use core_ai\aiactions\base;
use core_ai\provider;
use GuzzleHttp\Psr7\Response;

/**
 * Test response_base xAI provider methods.
 *
 * @package    aiprovider_xai
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\aiprovider_xai\provider::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\aiprovider_xai\process_generate_image::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\aiprovider_xai\abstract_processor::class)]
final class process_generate_image_test extends \advanced_testcase {
    use testcase_helper_trait;

    /** @var string A successful response in JSON format. */
    protected string $responsebodyjson;

    /** @var \core_ai\manager */
    private $manager;

    /** @var provider The provider that will process the action. */
    protected provider $provider;

    /** @var base The action to process. */
    protected base $action;

    /**
     * Set up the test.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        // Load a response body from a file.
        $this->responsebodyjson = file_get_contents(self::get_fixture_path('aiprovider_xai', 'image_request_success.json'));
        $this->manager = \core\di::get(\core_ai\manager::class);
        $this->provider = $this->create_provider(
            actionclass: \core_ai\aiactions\generate_image::class,
            actionconfig: [
                'model' => 'grok-imagine-image-2.0',
            ],
        );
        $this->create_action();
    }

    /**
     * Create the action object.
     * @param int $userid The user id to use in the action.
     */
    private function create_action(int $userid = 1): void {
        $this->action = new \core_ai\aiactions\generate_image(
            contextid: 1,
            userid: $userid,
            prompttext: 'This is a test prompt',
            quality: 'hd',
            aspectratio: 'square',
            numimages: 1,
            style: 'vivid',
        );
    }

    /**
     * Test calculate_aspect_ratio.
     */
    public function test_calculate_aspect_ratio(): void {
        $processor = new process_generate_image($this->provider, $this->action);

        // We're working with a private method here, so we need to use reflection.
        $method = new \ReflectionMethod($processor, 'calculate_aspect_ratio');

        $this->assertEquals('1:1', $method->invoke($processor, 'square'));
        $this->assertEquals('9:16', $method->invoke($processor, 'portrait'));
        $this->assertEquals('16:9', $method->invoke($processor, 'landscape'));
    }

    /**
     * Test calculate_quality.
     */
    public function test_calculate_quality(): void {
        $processor = new process_generate_image($this->provider, $this->action);
        $method = new \ReflectionMethod($processor, 'calculate_quality');

        // Grok Imagine Image 2.0 maps standard to low and hd to medium.
        $this->assertEquals('low', $method->invoke($processor, 'standard'));
        $this->assertEquals('medium', $method->invoke($processor, 'hd'));

        // Grok Imagine Image does not accept quality.
        $this->provider = $this->create_provider(
            actionclass: \core_ai\aiactions\generate_image::class,
            actionconfig: [
                'model' => 'grok-imagine-image',
            ],
        );
        $processor = new process_generate_image($this->provider, $this->action);
        $method = new \ReflectionMethod($processor, 'calculate_quality');
        $this->assertNull($method->invoke($processor, 'hd'));
    }

    /**
     * Test create_request_object
     */
    public function test_create_request_object(): void {
        $processor = new process_generate_image($this->provider, $this->action);

        // We're working with a private method here, so we need to use reflection.
        $method = new \ReflectionMethod($processor, 'create_request_object');
        $request = $method->invoke($processor, 1);

        $requestdata = (object) json_decode($request->getBody()->getContents());

        $this->assertEquals('This is a test prompt', $requestdata->prompt);
        $this->assertEquals('grok-imagine-image-2.0', $requestdata->model);
        $this->assertEquals(1, $requestdata->n);
        $this->assertEquals('medium', $requestdata->quality);
        $this->assertEquals('b64_json', $requestdata->response_format);
        $this->assertEquals('1:1', $requestdata->aspect_ratio);
        $this->assertObjectNotHasProperty('size', $requestdata);
        $this->assertObjectNotHasProperty('style', $requestdata);
    }

    /**
     * Test image count, landscape ratio, resolution and the model that omits quality.
     */
    public function test_create_request_object_image_mapping(): void {
        $this->action = new \core_ai\aiactions\generate_image(
            contextid: 1,
            userid: 1,
            prompttext: 'This is a test prompt',
            quality: 'standard',
            aspectratio: 'landscape',
            numimages: 12,
            style: 'natural',
        );
        $this->provider = $this->create_provider(
            actionclass: \core_ai\aiactions\generate_image::class,
            actionconfig: [
                'model' => 'grok-imagine-image-2.0',
                'resolution' => '2k',
            ],
        );
        $processor = new process_generate_image($this->provider, $this->action);
        $method = new \ReflectionMethod($processor, 'create_request_object');
        $request = $method->invoke($processor, 1);
        $requestdata = (object) json_decode($request->getBody()->getContents());

        $this->assertEquals(10, $requestdata->n);
        $this->assertEquals('low', $requestdata->quality);
        $this->assertEquals('16:9', $requestdata->aspect_ratio);
        $this->assertEquals('2k', $requestdata->resolution);
        $this->assertEquals('b64_json', $requestdata->response_format);
        $this->assertObjectNotHasProperty('style', $requestdata);

        $this->provider = $this->create_provider(
            actionclass: \core_ai\aiactions\generate_image::class,
            actionconfig: [
                'model' => 'grok-imagine-image',
            ],
        );
        $processor = new process_generate_image($this->provider, $this->action);
        $request = $method->invoke($processor, 1);
        $requestdata = (object) json_decode($request->getBody()->getContents());
        $this->assertEquals('grok-imagine-image', $requestdata->model);
        $this->assertObjectNotHasProperty('quality', $requestdata);
        $this->assertEquals('b64_json', $requestdata->response_format);
    }

    /**
     * Test create_request_object with extra model settings.
     */
    public function test_create_request_object_with_model_settings(): void {
        $this->provider = $this->create_provider(
            actionclass: \core_ai\aiactions\generate_image::class,
            actionconfig: [
                'model' => 'grok-imagine-image-2.0',
                'temperature' => '0.5',
                'max_completion_tokens' => '100',
            ],
        );
        $processor = new process_generate_image($this->provider, $this->action);

        // We're working with a private method here, so we need to use reflection.
        $method = new \ReflectionMethod($processor, 'create_request_object');
        $request = $method->invoke($processor, 1);

        $body = (object) json_decode($request->getBody()->getContents());

        $this->assertEquals('grok-imagine-image-2.0', $body->model);
        $this->assertEquals('0.5', $body->temperature);
        $this->assertEquals('100', $body->max_completion_tokens);

        $this->provider = $this->create_provider(
            actionclass: \core_ai\aiactions\generate_image::class,
            actionconfig: [
                'model' => 'my/custom-grok',
                'modelextraparams' => '{"temperature": 0.5,"max_completion_tokens": 100}',
            ],
        );
        $processor = new process_generate_image($this->provider, $this->action);

        // We're working with a private method here, so we need to use reflection.
        $method = new \ReflectionMethod($processor, 'create_request_object');
        $request = $method->invoke($processor, 1);

        $rawbody = $request->getBody()->getContents();
        $body = (object) json_decode($rawbody);

        $this->assertEquals('my/custom-grok', $body->model);
        $this->assertEquals('0.5', $body->temperature);
        $this->assertEquals('100', $body->max_completion_tokens);
        $this->assertStringNotContainsString('\/', $rawbody); // Slashes must not be escaped.
    }

    /**
     * Test the API error response handler method.
     */
    public function test_handle_api_error(): void {
        $responses = [
            500 => new Response(500, ['Content-Type' => 'application/json']),
            503 => new Response(503, ['Content-Type' => 'application/json']),
            401 => new Response(
                401,
                ['Content-Type' => 'application/json'],
                '{"error": {"message": "Invalid Authentication"}}'
            ),
            404 => new Response(
                404,
                ['Content-Type' => 'application/json'],
                '{"error": {"message": "You must be a member of an organization to use the API"}}'
            ),
            429 => new Response(
                429,
                ['Content-Type' => 'application/json'],
                '{"error": {"message": "Rate limit reached for requests"}}'
            ),
        ];

        $processor = new process_generate_image($this->provider, $this->action);
        $method = new \ReflectionMethod($processor, 'handle_api_error');

        foreach ($responses as $status => $response) {
            $result = $method->invoke($processor, $response);
            $this->assertEquals($status, $result['errorcode']);
            if ($status == 500) {
                $this->assertEquals('Internal Server Error', $result['errormessage']);
            } else if ($status == 503) {
                $this->assertEquals('Service Unavailable', $result['errormessage']);
            } else {
                $this->assertStringContainsString($response->getBody()->getContents(), $result['errormessage']);
            }
        }
    }

    /**
     * Test the API success response handler method.
     */
    public function test_handle_api_success(): void {
        $response = new Response(
            200,
            ['Content-Type' => 'application/json'],
            $this->responsebodyjson,
        );

        // We're testing a private method, so we need to setup reflector magic.
        $processor = new process_generate_image($this->provider, $this->action);
        $method = new \ReflectionMethod($processor, 'handle_api_success');

        $result = $method->invoke($processor, $response);

        $this->assertStringContainsString('An image that represents the concept of a \'test\'.', $result['revisedprompt']);
        $this->assertNotEmpty($result['b64json']);
        $this->assertEquals('grok-imagine-image-2.0', $result['model']);
    }

    /**
     * Test query_ai_api for a successful call.
     */
    public function test_query_ai_api_success(): void {
        // Mock the http client to return a successful response.
        ['mock' => $mock] = $this->get_mocked_http_client();

        // The response from xAI.
        $mock->append(new Response(
            200,
            ['Content-Type' => 'application/json'],
            $this->responsebodyjson,
        ));

        $this->setAdminUser();

        $processor = new process_generate_image($this->provider, $this->action);
        $method = new \ReflectionMethod($processor, 'query_ai_api');
        $result = $method->invoke($processor);

        $this->assertStringContainsString('An image that represents the concept of a \'test\'.', $result['revisedprompt']);
        $this->assertInstanceOf(\stored_file::class, $result['draftfile']);
        $this->assertEquals('grok-imagine-image-2.0', $result['model']);
    }

    /**
     * Test prepare_response success.
     */
    public function test_prepare_response_success(): void {
        $processor = new process_generate_image($this->provider, $this->action);

        // We're working with a private method here, so we need to use reflection.
        $method = new \ReflectionMethod($processor, 'prepare_response');

        $response = [
            'success' => true,
            'revisedprompt' => 'An image that represents the concept of a \'test\'.',
            'imageurl' => 'oaidalleapiprodscus.blob.core.windows.net',
            'model' => 'grok-imagine-image-2.0',
        ];

        $result = $method->invoke($processor, $response);

        $this->assertInstanceOf(\core_ai\aiactions\responses\response_base::class, $result);
        $this->assertTrue($result->get_success());
        $this->assertEquals('generate_image', $result->get_actionname());
        $this->assertEquals($response['success'], $result->get_success());
        $this->assertEquals($response['revisedprompt'], $result->get_response_data()['revisedprompt']);
        $this->assertEquals($response['model'], $result->get_response_data()['model']);
    }

    /**
     * Test prepare_response error.
     */
    public function test_prepare_response_error(): void {
        $processor = new process_generate_image($this->provider, $this->action);

        // We're working with a private method here, so we need to use reflection.
        $method = new \ReflectionMethod($processor, 'prepare_response');

        $response = [
            'success' => false,
            'errorcode' => 500,
            'error' => 'Internal server error',
            'errormessage' => 'Try again later',
        ];

        $result = $method->invoke($processor, $response);

        $this->assertInstanceOf(\core_ai\aiactions\responses\response_base::class, $result);
        $this->assertFalse($result->get_success());
        $this->assertEquals('generate_image', $result->get_actionname());
        $this->assertEquals($response['errorcode'], $result->get_errorcode());
        $this->assertEquals($response['errormessage'], $result->get_errormessage());
    }

    /**
     * Test create_file_from_response.
     */
    public function test_create_file_from_response(): void {
        // Log in user.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $processor = new process_generate_image($this->provider, $this->action);
        // We're working with a private method here, so we need to use reflection.
        $method = new \ReflectionMethod($processor, 'create_file_from_response');

        $userid = $user->id;
        $response = [
            'b64json' => base64_encode(file_get_contents(self::get_fixture_path('aiprovider_xai', 'test.jpg'))),
            'output_format' => 'jpg',
        ];
        $fileobj = $method->invoke($processor, $userid, $response);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{16}\.jpg$/', $fileobj->get_filename());
    }

    /**
     * Test process.
     */
    public function test_process(): void {
        // Log in user.
        $this->setUser($this->getDataGenerator()->create_user());

        // Mock the http client to return a successful response.
        ['mock' => $mock] = $this->get_mocked_http_client();

        // The response from xAI.
        $mock->append(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode([
                'created' => 1719140500,
                'data' => [
                    (object) [
                        'revised_prompt' => 'An image that represents the concept of a \'test\'.',
                        'b64_json' => base64_encode(file_get_contents(self::get_fixture_path('aiprovider_xai', 'test.jpg'))),
                    ],
                ],
            ]),
        ));

        // Create a request object.
        $contextid = 1;
        $userid = 1;
        $prompttext = 'This is a test prompt';
        $aspectratio = 'square';
        $quality = 'hd';
        $numimages = 1;
        $style = 'vivid';
        $this->action = new \core_ai\aiactions\generate_image(
            contextid: $contextid,
            userid: $userid,
            prompttext: $prompttext,
            quality: $quality,
            aspectratio: $aspectratio,
            numimages: $numimages,
            style: $style,
        );

        $processor = new process_generate_image($this->provider, $this->action);
        $result = $processor->process();

        $this->assertInstanceOf(\core_ai\aiactions\responses\response_base::class, $result);
        $this->assertTrue($result->get_success());
        $this->assertEquals('generate_image', $result->get_actionname());
        $this->assertEquals('An image that represents the concept of a \'test\'.', $result->get_response_data()['revisedprompt']);
        $this->assertInstanceOf(\stored_file::class, $result->get_response_data()['draftfile']);
    }

    /**
     * Test process method with error.
     */
    public function test_process_error(): void {
        // Log in user.
        $this->setUser($this->getDataGenerator()->create_user());

        // Mock the http client to return a successful response.
        ['mock' => $mock] = $this->get_mocked_http_client();

        // The response from xAI.
        $mock->append(new Response(
            401,
            ['Content-Type' => 'application/json'],
            json_encode(['error' => ['message' => 'Invalid Authentication']]),
        ));

        $processor = new process_generate_image($this->provider, $this->action);
        $result = $processor->process();

        $this->assertInstanceOf(\core_ai\aiactions\responses\response_base::class, $result);
        $this->assertFalse($result->get_success());
        $this->assertEquals('generate_image', $result->get_actionname());
        $this->assertEquals(401, $result->get_errorcode());
        $this->assertEquals('Invalid Authentication', $result->get_errormessage());
    }

    /**
     * Test process method with user rate limiter.
     */
    public function test_process_with_user_rate_limiter(): void {
        // Create users.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        // Log in user1.
        $this->setUser($user1);
        // Mock clock.
        $clock = $this->mock_clock_with_frozen();

        // Set the user rate limiter.
        $config = [
            'apikey' => '123',
            'enableuserratelimit' => true,
            'userratelimit' => 1,
        ];
        $provider = $this->manager->create_provider_instance(
            classname: '\aiprovider_xai\provider',
            name: 'dummy',
            config: $config,
            actionconfig: [
                \core_ai\aiactions\generate_image::class => [
                    'settings' => [
                        'model' => 'grok-imagine-image-2.0',
                        'endpoint' => "https://api.x.ai/v1/chat/completions",
                    ],
                ],
            ],
        );

        // Mock the http client to return a successful response.
        ['mock' => $mock] = $this->get_mocked_http_client();

        // Case 1: User rate limit has not been reached.
        $this->create_action($user1->id);
        // The response from xAI.
        $mock->append(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode([
                'created' => 1719140500,
                'data' => [
                    (object) [
                        'revised_prompt' => 'An image that represents the concept of a \'test\'.',
                        'b64_json' => base64_encode(file_get_contents(self::get_fixture_path('aiprovider_xai', 'test.jpg'))),
                    ],
                ],
            ]),
        ));
        $processor = new process_generate_image($provider, $this->action);
        $result = $processor->process();
        $this->assertTrue($result->get_success());

        // Case 2: User rate limit has been reached.
        $clock->bump(HOURSECS - 10);
        // The response from xAI.
        $mock->append(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode([
                'created' => 1719140500,
                'data' => [
                    (object) [
                        'revised_prompt' => 'An image that represents the concept of a \'test\'.',
                        'b64_json' => base64_encode(file_get_contents(self::get_fixture_path('aiprovider_xai', 'test.jpg'))),
                    ],
                ],
            ]),
        ));
        $this->create_action($user1->id);
        $processor = new process_generate_image($provider, $this->action);
        $result = $processor->process();
        $this->assertEquals(429, $result->get_errorcode());
        $this->assertEquals(
            expected: 'You have reached the maximum number of AI requests you can make in an hour. Try again later.',
            actual: $result->get_errormessage(),
        );
        $this->assertFalse($result->get_success());

        // Case 3: User rate limit has not been reached for a different user.
        // Log in user2.
        $this->setUser($user2);
        $this->create_action($user2->id);
        // The response from xAI.
        $mock->append(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode([
                'created' => 1719140500,
                'data' => [
                    (object) [
                        'revised_prompt' => 'An image that represents the concept of a \'test\'.',
                        'b64_json' => base64_encode(file_get_contents(self::get_fixture_path('aiprovider_xai', 'test.jpg'))),
                    ],
                ],
            ]),
        ));
        $processor = new process_generate_image($this->provider, $this->action);
        $result = $processor->process();
        $this->assertTrue($result->get_success());

        // Case 4: Time window has passed, user rate limit should be reset.
        $clock->bump(11);
        // Log in user1.
        $this->setUser($user1);
        // The response from xAI.
        $mock->append(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode([
                'created' => 1719140500,
                'data' => [
                    (object) [
                        'revised_prompt' => 'An image that represents the concept of a \'test\'.',
                        'b64_json' => base64_encode(file_get_contents(self::get_fixture_path('aiprovider_xai', 'test.jpg'))),
                    ],
                ],
            ]),
        ));
        $this->create_action($user1->id);
        $processor = new process_generate_image($provider, $this->action);
        $result = $processor->process();
        $this->assertTrue($result->get_success());
    }

    /**
     * Test process method with global rate limiter.
     */
    public function test_process_with_global_rate_limiter(): void {
        // Create users.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        // Log in user1.
        $this->setUser($user1);
        // Mock clock.
        $clock = $this->mock_clock_with_frozen();

        // Set the global rate limiter.
        $config = [
            'apikey' => '123',
            'enableglobalratelimit' => true,
            'globalratelimit' => 1,
        ];
        $provider = $this->manager->create_provider_instance(
            classname: '\aiprovider_xai\provider',
            name: 'dummy',
            config: $config,
            actionconfig: [
                \core_ai\aiactions\generate_image::class => [
                    'settings' => [
                        'model' => 'grok-imagine-image-2.0',
                        'endpoint' => "https://api.x.ai/v1/chat/completions",
                    ],
                ],
            ],
        );

        // Mock the http client to return a successful response.
        ['mock' => $mock] = $this->get_mocked_http_client();

        // Case 1: Global rate limit has not been reached.
        $this->create_action($user1->id);
        // The response from xAI.
        $mock->append(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode([
                'created' => 1719140500,
                'data' => [
                    (object) [
                        'revised_prompt' => 'An image that represents the concept of a \'test\'.',
                        'b64_json' => base64_encode(file_get_contents(self::get_fixture_path('aiprovider_xai', 'test.jpg'))),
                    ],
                ],
            ]),
        ));
        $processor = new process_generate_image($provider, $this->action);
        $result = $processor->process();
        $this->assertTrue($result->get_success());

        // Case 2: Global rate limit has been reached.
        $clock->bump(HOURSECS - 10);
        // The response from xAI.
        $mock->append(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode([
                'created' => 1719140500,
                'data' => [
                    (object) [
                        'revised_prompt' => 'An image that represents the concept of a \'test\'.',
                        'b64_json' => base64_encode(file_get_contents(self::get_fixture_path('aiprovider_xai', 'test.jpg'))),
                    ],
                ],
            ]),
        ));
        $this->provider = $this->create_provider(\core_ai\aiactions\generate_image::class);
        $this->create_action($user1->id);
        $processor = new process_generate_image($provider, $this->action);
        $result = $processor->process();
        $this->assertEquals(429, $result->get_errorcode());
        $this->assertEquals(
            expected: 'The AI service has reached the maximum number of site-wide requests per hour. Try again later.',
            actual: $result->get_errormessage(),
        );
        $this->assertFalse($result->get_success());

        // Case 3: Global rate limit has been reached for a different user too.
        // Log in user2.
        $this->setUser($user2);
        $this->provider = $this->create_provider(\core_ai\aiactions\generate_image::class);
        $this->create_action($user2->id);
        // The response from xAI.
        $mock->append(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode([
                'created' => 1719140500,
                'data' => [
                    (object) [
                        'revised_prompt' => 'An image that represents the concept of a \'test\'.',
                        'b64_json' => base64_encode(file_get_contents(self::get_fixture_path('aiprovider_xai', 'test.jpg'))),
                    ],
                ],
            ]),
        ));
        $processor = new process_generate_image($provider, $this->action);
        $result = $processor->process();
        $this->assertFalse($result->get_success());

        // Case 4: Time window has passed, global rate limit should be reset.
        $clock->bump(11);
        // Log in user1.
        $this->setUser($user1);
        // The response from xAI.
        $mock->append(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode([
                'created' => 1719140500,
                'data' => [
                    (object) [
                        'revised_prompt' => 'An image that represents the concept of a \'test\'.',
                        'b64_json' => base64_encode(file_get_contents(self::get_fixture_path('aiprovider_xai', 'test.jpg'))),
                    ],
                ],
            ]),
        ));
        $this->provider = $this->create_provider(\core_ai\aiactions\generate_image::class);
        $this->create_action($user1->id);
        $processor = new process_generate_image($provider, $this->action);
        $result = $processor->process();
        $this->assertTrue($result->get_success());
    }
}
