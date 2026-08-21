<?php

namespace Tests\Feature;

use App\Models\Entry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntriesTest extends TestCase
{
    use RefreshDatabase;

    private function actingUser(): User
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum');

        return $user;
    }

    public function test_entries_require_authentication(): void
    {
        $this->getJson('/api/entries')->assertStatus(401);
        $this->postJson('/api/entries', ['name' => 'سیب'])->assertStatus(401);
        $this->deleteJson('/api/entries/some-id')->assertStatus(401);
        $this->deleteJson('/api/entries')->assertStatus(401);
    }

    public function test_creates_an_entry(): void
    {
        $this->actingUser();

        $this->postJson('/api/entries', [
            'name' => 'برنج پخته',
            'calories' => 130,
            'protein' => 2.7,
            'carbs' => 28,
            'fat' => 0.3,
        ])->assertStatus(201)
            ->assertJsonStructure(['id', 'name', 'calories', 'protein', 'carbs', 'fat', 'date'])
            ->assertJsonPath('name', 'برنج پخته')
            ->assertJsonPath('date', date('Y-m-d'))
            ->assertJsonMissing(['user_id']);
    }

    public function test_create_with_client_id_is_idempotent(): void
    {
        $this->actingUser();

        $payload = [
            'id' => 'client-generated-uuid-123',
            'name' => 'موز',
            'calories' => 89,
            'protein' => 1.1,
            'carbs' => 23,
            'fat' => 0.3,
            'date' => '2026-01-15',
        ];

        $this->postJson('/api/entries', $payload)->assertStatus(201);
        $this->postJson('/api/entries', $payload)->assertStatus(201);

        $this->assertSame(1, Entry::where('id', 'client-generated-uuid-123')->count());
    }

    public function test_lists_entries_for_a_date(): void
    {
        $user = $this->actingUser();

        Entry::create([
            'id' => 'a1',
            'user_id' => $user->id,
            'name' => 'سیب',
            'calories' => 52,
            'protein' => 0.3,
            'carbs' => 14,
            'fat' => 0.2,
            'date' => '2026-01-15',
        ]);
        Entry::create([
            'id' => 'a2',
            'user_id' => $user->id,
            'name' => 'موز',
            'calories' => 89,
            'protein' => 1.1,
            'carbs' => 23,
            'fat' => 0.3,
            'date' => '2026-01-16',
        ]);

        $this->getJson('/api/entries?date=2026-01-15')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'سیب');
    }

    public function test_lists_entries_for_today_by_default(): void
    {
        $user = $this->actingUser();

        Entry::create([
            'id' => 'a3',
            'user_id' => $user->id,
            'name' => 'امروزی',
            'calories' => 100,
            'protein' => 1,
            'carbs' => 1,
            'fat' => 1,
            'date' => date('Y-m-d'),
        ]);
        Entry::create([
            'id' => 'a4',
            'user_id' => $user->id,
            'name' => 'دیروزی',
            'calories' => 100,
            'protein' => 1,
            'carbs' => 1,
            'fat' => 1,
            'date' => date('Y-m-d', strtotime('-1 day')),
        ]);

        $this->getJson('/api/entries')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'امروزی');
    }

    public function test_entries_are_scoped_to_the_owner(): void
    {
        $other = User::factory()->create();
        Entry::create([
            'id' => 'other-1',
            'user_id' => $other->id,
            'name' => 'مال دیگری',
            'calories' => 10,
            'protein' => 1,
            'carbs' => 1,
            'fat' => 1,
            'date' => date('Y-m-d'),
        ]);

        $this->actingUser();

        $this->getJson('/api/entries')
            ->assertOk()
            ->assertJsonCount(0);
    }

    public function test_deletes_an_entry(): void
    {
        $user = $this->actingUser();

        Entry::create([
            'id' => 'del-1',
            'user_id' => $user->id,
            'name' => 'حذف‌شونده',
            'calories' => 10,
            'protein' => 1,
            'carbs' => 1,
            'fat' => 1,
            'date' => date('Y-m-d'),
        ]);

        $this->deleteJson('/api/entries/del-1')
            ->assertOk()
            ->assertJson(['deleted' => true]);

        $this->assertDatabaseMissing('entries', ['id' => 'del-1']);
    }

    public function test_cannot_delete_others_entry(): void
    {
        $other = User::factory()->create();
        Entry::create([
            'id' => 'other-2',
            'user_id' => $other->id,
            'name' => 'مال دیگری',
            'calories' => 10,
            'protein' => 1,
            'carbs' => 1,
            'fat' => 1,
            'date' => date('Y-m-d'),
        ]);

        $this->actingUser();

        $this->deleteJson('/api/entries/other-2')->assertStatus(404);
        $this->assertDatabaseHas('entries', ['id' => 'other-2']);
    }

    public function test_delete_missing_entry_returns_404(): void
    {
        $this->actingUser();

        $this->deleteJson('/api/entries/does-not-exist')
            ->assertStatus(404)
            ->assertJsonPath('message', 'وعده یافت نشد');
    }

    public function test_clears_entries_by_date(): void
    {
        $user = $this->actingUser();

        foreach (['c1', 'c2'] as $id) {
            Entry::create([
                'id' => $id,
                'user_id' => $user->id,
                'name' => 'امروزی',
                'calories' => 10,
                'protein' => 1,
                'carbs' => 1,
                'fat' => 1,
                'date' => '2026-01-15',
            ]);
        }
        Entry::create([
            'id' => 'c3',
            'user_id' => $user->id,
            'name' => 'روز دیگر',
            'calories' => 10,
            'protein' => 1,
            'carbs' => 1,
            'fat' => 1,
            'date' => '2026-01-16',
        ]);

        $this->deleteJson('/api/entries?date=2026-01-15')
            ->assertOk()
            ->assertJson(['deleted' => 2]);

        $this->assertDatabaseMissing('entries', ['id' => 'c1']);
        $this->assertDatabaseHas('entries', ['id' => 'c3']);
    }

    public function test_validates_entry_payload(): void
    {
        $this->actingUser();

        $this->postJson('/api/entries', [
            'name' => '',
            'calories' => -5,
            'protein' => 'abc',
            'date' => '15/01/2026',
        ])->assertStatus(422);
    }
}
