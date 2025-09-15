<?php

namespace Tests\Feature;

use App\Models\Actor;
use App\Services\ActorExtractionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ActorSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_shows_form_page()
    {
        $response = $this->get(route('actors.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Actors/Create'));
    }

    public function test_it_requires_email_and_description()
    {
        $response = $this->post(route('actors.store'), []);

        $response->assertSessionHasErrors(['email', 'description']);
    }

    public function test_it_requires_valid_email()
    {
        $response = $this->post(route('actors.store'), [
            'email' => 'invalid-email',
            'description' => 'John Doe lives at 123 Main St'
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_it_requires_unique_email()
    {
        Actor::factory()->create(['email' => 'test@example.com']);

        $response = $this->post(route('actors.store'), [
            'email' => 'test@example.com',
            'description' => 'John Doe lives at 123 Main St'
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_it_requires_unique_description()
    {
        Actor::factory()->create(['description' => 'John Doe lives at 123 Main St']);

        $response = $this->post(route('actors.store'), [
            'email' => 'test@example.com',
            'description' => 'John Doe lives at 123 Main St'
        ]);

        $response->assertSessionHasErrors(['description']);
    }

    public function test_it_requires_minimum_description_length()
    {
        $response = $this->post(route('actors.store'), [
            'email' => 'test@example.com',
            'description' => 'short'
        ]);

        $response->assertSessionHasErrors(['description']);
    }

    public function test_it_errors_when_ai_missing_required_fields()
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'first_name' => 'John',
                                'last_name' => null, // Missing last name
                                'address' => null, // Missing address
                                'height' => '6ft',
                                'weight' => '180lbs',
                                'gender' => 'male',
                                'age' => 30
                            ])
                        ]
                    ]
                ]
            ])
        ]);

        $response = $this->post(route('actors.store'), [
            'email' => 'test@example.com',
            'description' => 'John is a tall guy who weighs 180lbs'
        ]);

        $response->assertSessionHasErrors(['description']);
        $response->assertSessionHasErrorsIn('default', [
            'description' => 'Data not found'
        ]);
        $this->assertDatabaseCount('actors', 0);
    }

    public function test_it_saves_and_redirects_on_success()
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

        $response = $this->post(route('actors.store'), [
            'email' => 'test@example.com',
            'description' => 'Tom Cruise, 123 Hollywood Blvd, Los Angeles, CA'
        ]);

        $response->assertRedirect(route('actors.index'));
        $response->assertSessionHas('success', 'Actor information submitted successfully!');
        
        $this->assertDatabaseHas('actors', [
            'email' => 'test@example.com',
            'first_name' => 'Tom',
            'last_name' => 'Cruise',
            'address' => '123 Hollywood Blvd, Los Angeles, CA',
            'height' => '5ft 7in',
            'weight' => '170lbs',
            'gender' => 'male',
            'age' => 62
        ]);
    }

    public function test_it_extracts_famous_actor_with_knowledge_based_details()
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

        $response = $this->post(route('actors.store'), [
            'email' => 'angelina@example.com',
            'description' => 'Angelina Jolie, Cedars-Sinai Medical Center, Los Angeles, CA'
        ]);

        $response->assertRedirect(route('actors.index'));
        $response->assertSessionHas('success', 'Actor information submitted successfully!');
        
        // Verify that AI knowledge was used to fill in physical details
        $this->assertDatabaseHas('actors', [
            'email' => 'angelina@example.com',
            'first_name' => 'Angelina',
            'last_name' => 'Jolie',
            'address' => 'Cedars-Sinai Medical Center, Los Angeles, CA',
            'height' => '5ft 7in',
            'weight' => '130lbs',
            'gender' => 'female',
            'age' => 49
        ]);
    }

    public function test_it_handles_openai_api_failure()
    {
        Http::fake([
            'api.openai.com/*' => Http::response([], 500)
        ]);

        $response = $this->post(route('actors.store'), [
            'email' => 'test@example.com',
            'description' => 'John Doe lives at 123 Main St'
        ]);

        $response->assertSessionHasErrors(['description']);
        $this->assertDatabaseCount('actors', 0);
    }

    public function test_it_handles_invalid_json_response()
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

        $response = $this->post(route('actors.store'), [
            'email' => 'test@example.com',
            'description' => 'John Doe lives at 123 Main St'
        ]);

        $response->assertSessionHasErrors(['description']);
        $this->assertDatabaseCount('actors', 0);
    }
}
