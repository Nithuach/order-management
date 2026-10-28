
<?php

use App\Jobs\SendOrderConfirmation;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();
});

test('it creates an order and deducts product stock', function () {
    $customer = Customer::factory()->create();

    $product = Product::factory()->create([
        'price' => 100.00,
        'tax_percentage' => 18,
        'stock_on_hand' => 10,
    ]);

    $response = $this->postJson('/api/orders', [
        'customer' => [
            'name' => $customer->name,
            'email' => $customer->email,
        ],
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
            ],
        ],
    ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Order created successfully.')
        ->assertJsonPath('order.subtotal', '200.00')
        ->assertJsonPath('order.tax_total', '36.00')
        ->assertJsonPath('order.grand_total', '236.00');

    expect($product->fresh()->stock_on_hand)->toBe(8);

    $this->assertDatabaseHas('orders', [
        'customer_id' => $customer->id,
        'subtotal' => 200.00,
        'tax_total' => 36.00,
        'grand_total' => 236.00,
    ]);

    $this->assertDatabaseHas('order_items', [
        'order_id' => $response->json('order.id'),
        'product_id' => $product->id,
        'quantity' => 2,
        'unit_price' => 100.00,
        'line_subtotal' => 200.00,
        'line_tax' => 36.00,
    ]);

    Queue::assertPushed(
        SendOrderConfirmation::class,
        fn ($job) => $job->orderId === $response->json('order.id')
    );
});

test('it rejects an order when product stock is insufficient', function () {
    $customer = Customer::factory()->create();

    $product = Product::factory()->create([
        'price' => 100.00,
        'tax_percentage' => 18,
        'stock_on_hand' => 2,
    ]);

    $response = $this->postJson('/api/orders', [
        'customer' => [
            'name' => $customer->name,
            'email' => $customer->email,
        ],
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 3,
            ],
        ],
    ]);

    $response->assertUnprocessable();

    expect($product->fresh()->stock_on_hand)->toBe(2);
    expect(Order::count())->toBe(0);

    $this->assertDatabaseCount('order_items', 0);

    Queue::assertNothingPushed();
});

test('it rejects an order with invalid input', function () {
    $response = $this->postJson('/api/orders', [
        'customer' => [
            'name' => '',
            'email' => 'not-an-email',
        ],
        'items' => [],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'customer.name',
            'customer.email',
            'items',
        ]);

    expect(Order::count())->toBe(0);

    Queue::assertNothingPushed();
});

test('it rejects a quantity of zero', function () {
    $customer = Customer::factory()->create();

    $product = Product::factory()->create([
        'stock_on_hand' => 10,
    ]);

    $response = $this->postJson('/api/orders', [
        'customer' => [
            'name' => $customer->name,
            'email' => $customer->email,
        ],
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 0,
            ],
        ],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('items.0.quantity');

    expect($product->fresh()->stock_on_hand)->toBe(10);
    expect(Order::count())->toBe(0);

    Queue::assertNothingPushed();
});
