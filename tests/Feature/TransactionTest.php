<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_card_returns_user_info()
    {
        // Setup
        $user = User::factory()->create(['rfid_uid' => 'TESTROID']);
        Wallet::create(['user_id' => $user->id, 'balance' => 50000]);

        // Act
        $response = $this->postJson('/api/check-card', ['rfid_uid' => 'TESTROID']);

        // Assert
        $response->assertStatus(200)
                 ->assertJsonPath('data.name', $user->name)
                 ->assertJsonPath('data.balance', '50000.00');
    }

    public function test_payment_deducts_balance_correctly()
    {
        // Setup
        $user = User::factory()->create(['rfid_uid' => 'PAYTEST']);
        Wallet::create(['user_id' => $user->id, 'balance' => 20000]);

        // Act
        $response = $this->postJson('/api/pay', [
            'rfid_uid' => 'PAYTEST',
            'amount' => 15000
        ]);

        // Assert
        $response->assertStatus(200)
                 ->assertJsonPath('data.transaction.amount', 15000)
                 ->assertJsonPath('data.new_balance', 5000); // 20k - 15k = 5k

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'balance' => 5000
        ]);
    }

    public function test_payment_fails_if_insufficient_balance()
    {
         // Setup
         $user = User::factory()->create(['rfid_uid' => 'POORGUY']);
         Wallet::create(['user_id' => $user->id, 'balance' => 5000]);
 
         // Act
         $response = $this->postJson('/api/pay', [
             'rfid_uid' => 'POORGUY',
             'amount' => 10000
         ]);
 
         // Assert
         $response->assertStatus(400)
                  ->assertJsonPath('message', 'Insufficient balance');
    }
}
