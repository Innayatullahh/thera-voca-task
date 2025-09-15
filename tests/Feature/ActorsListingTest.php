<?php

namespace Tests\Feature;

use App\Models\Actor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActorsListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_submissions_in_table()
    {
        $actors = Actor::factory()->count(3)->create([
            'first_name' => 'John',
            'address' => '123 Main St',
            'gender' => 'male',
            'height' => '6ft'
        ]);

        $response = $this->get(route('actors.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Actors/Index')
                ->has('actors.data', 3)
                ->where('actors.data.0.first_name', 'John')
                ->where('actors.data.0.address', '123 Main St')
                ->where('actors.data.0.gender', 'male')
                ->where('actors.data.0.height', '6ft')
        );
    }

    public function test_it_shows_empty_state_when_no_submissions()
    {
        $response = $this->get(route('actors.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Actors/Index')
                ->has('actors.data', 0)
        );
    }

    public function test_it_paginates_results()
    {
        Actor::factory()->count(20)->create();

        $response = $this->get(route('actors.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Actors/Index')
                ->has('actors.data', 15) // Default pagination is 15
                ->where('actors.current_page', 1)
                ->where('actors.total', 20)
        );
    }

    public function test_it_orders_by_created_at_desc()
    {
        $oldActor = Actor::factory()->create([
            'first_name' => 'Old',
            'created_at' => now()->subDays(2)
        ]);
        
        $newActor = Actor::factory()->create([
            'first_name' => 'New',
            'created_at' => now()
        ]);

        $response = $this->get(route('actors.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Actors/Index')
                ->where('actors.data.0.first_name', 'New')
                ->where('actors.data.1.first_name', 'Old')
        );
    }

    public function test_it_only_shows_required_columns()
    {
        Actor::factory()->create([
            'email' => 'test@example.com',
            'description' => 'Test description',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address' => '123 Main St',
            'gender' => 'male',
            'height' => '6ft',
            'weight' => '180lbs',
            'age' => 30
        ]);

        $response = $this->get(route('actors.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->component('Actors/Index')
                ->has('actors.data.0', fn ($actor) => 
                    $actor->has('first_name')
                        ->has('address')
                        ->has('gender')
                        ->has('height')
                        ->has('created_at')
                        ->missing('email')
                        ->missing('description')
                        ->missing('last_name')
                        ->missing('weight')
                        ->missing('age')
                )
        );
    }
}
