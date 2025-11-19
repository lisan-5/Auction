<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Auction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuctionFilterTest extends TestCase
{
    use RefreshDatabase;

    private $prefix = '/api/v1';

    public function test_filter_by_condition()
    {
        $auction = Auction::factory()->create(['condition' => 'excellent']);

        $response = $this->getJson($this->prefix . '/auctions?condition=excellent');

        // Use assertJsonPath for paginated response
        $response->assertStatus(200)
                    ->assertJsonPath('data.0.condition', 'excellent');
    }

    public function test_filter_by_combination()
    {
        $auction = Auction::factory()->create([
            'condition' => 'excellent',
            'province' => 'Addis',
        ]);

        $response = $this->getJson($this->prefix . '/auctions?condition=excellent&province=Addis');

        // Updated assertions for paginated data
        $response->assertStatus(200)
                    ->assertJsonPath('data.0.condition', 'excellent')
                    ->assertJsonPath('data.0.province', 'Addis');
    }
}
