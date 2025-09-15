<?php

namespace Tests\Feature;

use App\Services\ActorExtractionService;
use Tests\TestCase;

class ActorsApiTest extends TestCase
{
    public function test_it_returns_prompt_json()
    {
        $response = $this->get('/api/actors/prompt-validation');

        $response->assertStatus(200);
        $response->assertJson([
            'message' => ActorExtractionService::getPromptText()
        ]);
        $response->assertJsonStructure([
            'message'
        ]);
    }

    public function test_prompt_message_matches_expected_text()
    {
        $response = $this->get('/api/actors/prompt-validation');

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Please enter first name and last name, and also provide address.'
        ]);
    }
}
