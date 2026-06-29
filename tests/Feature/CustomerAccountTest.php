<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\SystemSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SystemSettingSeeder::class, LanguageSeeder::class, CurrencySeeder::class]);
    }

    public function test_account_requires_customer_authentication(): void
    {
        $this->get(route('account.index'))->assertRedirect(route('login'));

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('account.index'))->assertForbidden();

        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)->get(route('account.index'))->assertOk();
    }

    public function test_customer_can_update_profile_and_password(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->patch(route('account.profile.update'), [
            'name' => 'Updated Customer',
            'email' => 'updated@example.com',
            'phone' => '0901234567',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->actingAs($customer)->patch(route('account.password.update'), [
            'current_password' => 'password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $customer->refresh();
        $this->assertSame('Updated Customer', $customer->name);
        $this->assertSame('0901234567', $customer->phone);
        $this->assertTrue(Hash::check('new-secure-password', $customer->password));
    }

    public function test_customer_address_crud_is_scoped_and_defaults_are_unique(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $other = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->post(route('account.addresses.store'), $this->addressPayload([
            'label' => 'Home',
        ]))->assertRedirect(route('account.addresses.index'));
        $first = $customer->customerAddresses()->firstOrFail();
        $this->assertTrue($first->is_default_shipping);
        $this->assertTrue($first->is_default_billing);

        $this->actingAs($customer)->post(route('account.addresses.store'), $this->addressPayload([
            'label' => 'Office',
            'is_default_shipping' => '1',
            'is_default_billing' => '1',
        ]))->assertRedirect(route('account.addresses.index'));
        $second = $customer->customerAddresses()->where('label', 'Office')->firstOrFail();

        $this->assertFalse($first->fresh()->is_default_shipping);
        $this->assertFalse($first->fresh()->is_default_billing);
        $this->assertTrue($second->is_default_shipping);
        $this->assertTrue($second->is_default_billing);

        $foreign = $other->customerAddresses()->create($this->addressPayload());
        $this->actingAs($customer)->get(route('account.addresses.edit', $foreign))->assertNotFound();
        $this->actingAs($customer)->patch(route('account.addresses.update', $foreign), $this->addressPayload())->assertNotFound();
        $this->actingAs($customer)->delete(route('account.addresses.destroy', $foreign))->assertNotFound();

        $this->actingAs($customer)->delete(route('account.addresses.destroy', $first))
            ->assertRedirect(route('account.addresses.index'));
        $this->assertDatabaseMissing('customer_addresses', ['id' => $first->id]);
    }

    public function test_order_history_and_detail_only_show_customer_snapshot_orders(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $other = User::factory()->create(['role' => 'customer']);
        $own = $this->order($customer, ['order_code' => 'ORD-OWN-31']);
        $foreign = $this->order($other, ['order_code' => 'ORD-FOREIGN-31']);
        $own->orderItems()->create([
            'product_name' => 'Historical Snapshot Product',
            'sku' => 'SNAP-31',
            'price' => 125000,
            'quantity' => 2,
            'subtotal' => 250000,
            'taxable_amount' => 250000,
            'tax_rate' => 10,
            'tax_amount' => 25000,
            'total' => 275000,
        ]);
        $own->internalNotes()->create(['type' => 'internal', 'note' => 'Secret admin note']);

        $this->actingAs($customer)->get(route('account.orders.index'))
            ->assertOk()
            ->assertSee('ORD-OWN-31')
            ->assertDontSee('ORD-FOREIGN-31');

        $this->actingAs($customer)->get(route('account.orders.show', $own))
            ->assertOk()
            ->assertSee('Historical Snapshot Product')
            ->assertSee('275.000 ₫')
            ->assertDontSee('Secret admin note');

        $this->actingAs($customer)->get(route('account.orders.show', $foreign))->assertNotFound();
    }

    public function test_guest_order_requires_correct_random_token_and_cannot_open_customer_order(): void
    {
        $guestOrder = $this->order(null, ['success_token' => str_repeat('g', 80)]);
        $customerOrder = $this->order(User::factory()->create(), ['success_token' => str_repeat('c', 80)]);

        $this->get(route('guest.orders.show', $guestOrder->success_token))
            ->assertOk()
            ->assertSee($guestOrder->order_code);
        $this->get(route('guest.orders.show', 'wrong-token'))->assertNotFound();
        $this->get(route('guest.orders.show', $customerOrder->success_token))->assertNotFound();
    }

    private function addressPayload(array $overrides = []): array
    {
        return array_merge([
            'label' => 'Address',
            'recipient_name' => 'Customer',
            'phone' => '0900000000',
            'address_line_1' => '1 Nguyen Hue',
            'address_line_2' => '',
            'city' => 'Ho Chi Minh City',
            'district' => 'District 1',
            'ward' => 'Ben Nghe',
            'postal_code' => '700000',
            'country' => 'VN',
            'is_default_shipping' => '0',
            'is_default_billing' => '0',
        ], $overrides);
    }

    private function order(?User $user, array $overrides = []): Order
    {
        $order = Order::query()->create(array_merge([
            'user_id' => $user?->id,
            'order_code' => 'ORD-'.fake()->unique()->numerify('######'),
            'success_token' => fake()->unique()->sha1().fake()->sha1(),
            'customer_name' => 'Snapshot Customer',
            'customer_phone' => '0900000000',
            'customer_email' => 'snapshot@example.com',
            'language_code' => 'vi',
            'shipping_address' => '1 Nguyen Hue',
            'currency_code' => 'VND',
            'currency_symbol' => '₫',
            'currency_symbol_position' => 'after',
            'currency_decimal_places' => 0,
            'currency_snapshot' => [
                'symbol' => '₫',
                'symbol_position' => 'after',
                'decimal_places' => 0,
                'thousand_separator' => '.',
                'decimal_separator' => ',',
            ],
            'exchange_rate' => 1,
            'subtotal' => 250000,
            'discount_amount' => 0,
            'tax_amount' => 25000,
            'shipping_fee' => 0,
            'total_amount' => 275000,
            'payment_method' => 'cod',
            'payment_method_name' => 'Cash on Delivery',
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
            'fulfillment_status' => 'unfulfilled',
            'placed_at' => now(),
        ], $overrides));

        foreach (['shipping', 'billing'] as $type) {
            $order->orderAddresses()->create([
                'type' => $type,
                'full_name' => 'Snapshot Customer',
                'phone' => '0900000000',
                'country_code' => 'VN',
                'province' => 'Ho Chi Minh City',
                'district' => 'District 1',
                'ward' => 'Ben Nghe',
                'address_line' => '1 Nguyen Hue',
            ]);
        }
        $order->orderPayments()->create([
            'payment_method_code' => 'cod',
            'payment_method_name' => 'Cash on Delivery',
            'payment_status' => 'unpaid',
            'amount' => 275000,
            'currency_code' => 'VND',
        ]);

        return $order;
    }
}
