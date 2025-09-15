<?php

namespace Tests\Unit;

use App\Services\ActorExtractionService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ActorExtractionServiceTest extends TestCase
{
    private ActorExtractionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ActorExtractionService();
    }

    public function test_parses_valid_json_correctly()
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'first_name' => 'Tom',
                                'last_name' => 'Cruise',
                                'address' => '123 Hollywood Blvd, Los Angeles, CA',
                                'height' => '5ft 7in',
                                'weight' => '170lbs',
                                'gender' => 'male',
                                'age' => 62
                            ])
                        ]
                    ]
                ]
            ])
        ]);

        $result = $this->service->extract('Tom Cruise, 123 Hollywood Blvd, Los Angeles, CA');

        $this->assertEquals('Tom', $result['first_name']);
        $this->assertEquals('Cruise', $result['last_name']);
        $this->assertEquals('123 Hollywood Blvd, Los Angeles, CA', $result['address']);
        $this->assertEquals('5ft 7in', $result['height']);
        $this->assertEquals('170lbs', $result['weight']);
        $this->assertEquals('male', $result['gender']);
        $this->assertEquals(62, $result['age']);
        $this->assertIsArray($result['raw_ai_response']);
    }

    public function test_handles_malformed_json()
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'invalid json response'
                        ]
                    ]
                ]
            ])
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to process actor information. Please try again.');

        $this->service->extract('John Doe lives at 123 Main St');
    }

    public function test_handles_api_failure()
    {
        Http::fake([
            'api.openai.com/*' => Http::response([], 500)
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to extract actor information. Please try again.');

        $this->service->extract('John Doe lives at 123 Main St');
    }

    public function test_handles_missing_fields_gracefully()
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'first_name' => 'John',
                                // Missing other fields
                            ])
                        ]
                    ]
                ]
            ])
        ]);

        $result = $this->service->extract('John');

        $this->assertEquals('John', $result['first_name']);
        $this->assertNull($result['last_name']);
        $this->assertNull($result['address']);
        $this->assertNull($result['height']);
        $this->assertNull($result['weight']);
        $this->assertNull($result['gender']);
        $this->assertNull($result['age']);
    }

    public function test_converts_age_to_integer()
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'first_name' => 'John',
                                'last_name' => 'Doe',
                                'address' => '123 Main St',
                                'age' => '30' // String age
                            ])
                        ]
                    ]
                ]
            ])
        ]);

        $result = $this->service->extract('John Doe, 30 years old');

        $this->assertIsInt($result['age']);
        $this->assertEquals(30, $result['age']);
    }

    public function test_extracts_famous_actor_with_knowledge_based_details()
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'first_name' => 'Angelina',
                                'last_name' => 'Jolie',
                                'address' => 'Cedars-Sinai Medical Center, Los Angeles, CA',
                                'height' => '5ft 7in',
                                'weight' => '130lbs',
                                'gender' => 'female',
                                'age' => 49
                            ])
                        ]
                    ]
                ]
            ])
        ]);

        $result = $this->service->extract('Angelina Jolie, Cedars-Sinai Medical Center, Los Angeles, CA');

        $this->assertEquals('Angelina', $result['first_name']);
        $this->assertEquals('Jolie', $result['last_name']);
        $this->assertEquals('Cedars-Sinai Medical Center, Los Angeles, CA', $result['address']);
        $this->assertEquals('5ft 7in', $result['height']);
        $this->assertEquals('130lbs', $result['weight']);
        $this->assertEquals('female', $result['gender']);
        $this->assertEquals(49, $result['age']);
        $this->assertIsArray($result['raw_ai_response']);
    }

    public function test_ensures_all_seven_fields_are_always_present()
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'first_name' => 'Brad',
                                'last_name' => 'Pitt',
                                'address' => '456 Sunset Blvd, Hollywood, CA',
                                'height' => '5ft 11in',
                                'weight' => '172lbs',
                                'gender' => 'male',
                                'age' => 61
                            ])
                        ]
                    ]
                ]
            ])
        ]);

        $result = $this->service->extract('Brad Pitt, 456 Sunset Blvd, Hollywood, CA');

        // Ensure all 7 required fields are present
        $this->assertArrayHasKey('first_name', $result);
        $this->assertArrayHasKey('last_name', $result);
        $this->assertArrayHasKey('address', $result);
        $this->assertArrayHasKey('height', $result);
        $this->assertArrayHasKey('weight', $result);
        $this->assertArrayHasKey('gender', $result);
        $this->assertArrayHasKey('age', $result);
        $this->assertArrayHasKey('raw_ai_response', $result);

        // Verify none of the main fields are null for a famous actor
        $this->assertNotNull($result['first_name']);
        $this->assertNotNull($result['last_name']);
        $this->assertNotNull($result['address']);
        $this->assertNotNull($result['height']);
        $this->assertNotNull($result['weight']);
        $this->assertNotNull($result['gender']);
        $this->assertNotNull($result['age']);
    }

    public function test_validate_required_fields_returns_true_when_all_present()
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => '123 Main St'
        ];

        $result = $this->service->validateRequiredFields($data);

        $this->assertTrue($result);
    }

    public function test_validate_required_fields_returns_false_when_missing_first_name()
    {
        $data = [
            'first_name' => null,
            'last_name' => 'Doe',
            'address' => '123 Main St'
        ];

        $result = $this->service->validateRequiredFields($data);

        $this->assertFalse($result);
    }

    public function test_validate_required_fields_returns_false_when_missing_last_name()
    {
        $data = [
            'first_name' => 'John',
            'last_name' => '',
            'address' => '123 Main St'
        ];

        $result = $this->service->validateRequiredFields($data);

        $this->assertFalse($result);
    }

    public function test_validate_required_fields_returns_false_when_missing_address()
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => null
        ];

        $result = $this->service->validateRequiredFields($data);

        $this->assertFalse($result);
    }

    public function test_get_prompt_text_returns_expected_string()
    {
        $result = ActorExtractionService::getPromptText();

        $this->assertEquals('Please enter first name and last name, and also provide address.', $result);
    }
}
