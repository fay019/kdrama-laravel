<?php

namespace Tests\Feature;

use App\Models\Actor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActorModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_contains_actor_modal(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            'id="actorModal"',
            'window.openActorModal',
            'window.filterByActor',
        ]);
    }

    public function test_actor_carousel_renders_on_homepage(): void
    {
        // Create test actors
        Actor::factory()->count(5)->create([
            'profile_path' => '/test-path.jpg',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('actor-carousel', false);
    }

    public function test_actor_details_endpoint_returns_html(): void
    {
        $actor = Actor::factory()->create([
            'name' => 'Test Actor',
            'biography' => 'Test biography content',
            'profile_path' => '/test.jpg',
            'birthday' => '1990-01-01',
            'birthplace' => 'Test City',
        ]);

        $response = $this->get("/kdrams/actor/{$actor->tmdb_id}");

        $response->assertStatus(200);
        $response->assertSee('flex-col');
        $response->assertSee('Test Actor');
    }

    public function test_actor_modal_js_functions_exist(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            'window.openActorModal = function(actorId)',
            'window.closeActorModal = function()',
            'window.filterByActor = function(actorId, actorName)',
        ], false);
    }

    public function test_modal_closes_on_escape_key(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee("e.key === 'Escape'", false);
    }

    public function test_modal_closes_on_backdrop_click(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('if (e.target === actorModal)', false);
    }

    public function test_translation_keys_exist(): void
    {
        $keys = [
            'common.loading',
            'show.actor_error_loading',
        ];

        foreach (['en', 'fr', 'de'] as $lang) {
            foreach ($keys as $key) {
                $translation = __($key, [], $lang);
                $this->assertNotEquals($key, $translation, "Translation key '$key' missing for language '$lang'");
            }
        }
    }
}
