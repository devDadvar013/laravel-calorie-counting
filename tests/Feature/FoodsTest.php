<?php

namespace Tests\Feature;

use Tests\TestCase;

class FoodsTest extends TestCase
{
    public function test_lists_all_foods(): void
    {
        $this->getJson('/api/foods')
            ->assertOk()
            ->assertJsonCount(21)
            ->assertJsonPath('0.id', 'rice')
            ->assertJsonStructure(['*' => ['id', 'name', 'calories', 'protein', 'carbs', 'fat']]);
    }

    public function test_searches_foods_by_query(): void
    {
        // «مرغ» در «سینه مرغ کبابی» و «تخم‌مرغ آب‌پز» هر دو هست
        $this->getJson('/api/foods?q=مرغ')
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.id', 'chicken');
    }

    public function test_search_ignores_whitespace(): void
    {
        // «سینه  مرغ» با دو فاصله هم باید «سینه مرغ کبابی» را پیدا کند
        $this->getJson('/api/foods?q='.urlencode('سینه  مرغ'))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', 'chicken');
    }
}
