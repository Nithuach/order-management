<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendOrderConfirmation;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.email' => ['required', 'email', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required', 'integer', 'distinct', 'exists:products,id',
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $order = DB::transaction(function () use ($validated) {
            // Lock products in a consistent order to reduce deadlocks.
            $requestedItems = collect($validated['items'])
                ->sortBy('product_id')
                ->values();

            $products = Product::query()
                ->whereIn(
                    'id',
                    $requestedItems->pluck('product_id')->all()
                )
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Validate all stock before deducting any of it.
            foreach ($requestedItems as $item) {
                $product = $products->get($item['product_id']);

                if (!$product || $product->stock_on_hand < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => [
                            "Insufficient stock for product ID {$item['product_id']}.",
                        ],
                    ]);
                }
            }

            $customer = Customer::firstOrCreate(
                ['email' => $validated['customer']['email']],
                ['name' => $validated['customer']['name']]
            );

            $subtotalCents = 0;
            $taxTotalCents = 0;
            $orderLines = [];

            foreach ($requestedItems as $item) {
                $product = $products->get($item['product_id']);

                // Convert monetary amounts to integer cents for calculations.
                $unitPriceCents = (int) round(
                    (float) $product->price * 100
                );

                $lineSubtotalCents = $unitPriceCents * $item['quantity'];

                $taxRate = (float) $product->tax_percentage;
                $lineTaxCents = (int) round(
                    $lineSubtotalCents * $taxRate / 100
                );

                $subtotalCents += $lineSubtotalCents;
                $taxTotalCents += $lineTaxCents;

                $orderLines[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPriceCents,
                    'tax_percentage' => $taxRate,
                    'line_subtotal' => $lineSubtotalCents,
                    'line_tax' => $lineTaxCents,
                ];
            }

            $order = Order::create([
                'customer_id' => $customer->id,
                'subtotal' => number_format($subtotalCents / 100, 2, '.', ''),
                'tax_total' => number_format($taxTotalCents / 100, 2, '.', ''),
                'grand_total' => number_format(
                    ($subtotalCents + $taxTotalCents) / 100,
                    2,
                    '.',
                    ''
                ),
            ]);

            foreach ($orderLines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => number_format(
                        $line['unit_price'] / 100, 2, '.', ''
                    ),
                    'tax_percentage' => $line['tax_percentage'],
                    'line_subtotal' => number_format(
                        $line['line_subtotal'] / 100, 2, '.', ''
                    ),
                    'line_tax' => number_format(
                        $line['line_tax'] / 100, 2, '.', ''
                    ),
                ]);

                $line['product']->decrement('stock_on_hand', $line['quantity']);
            }

            return $order;
        }, 5);

        // Only queue the confirmation after the order transaction commits.
        SendOrderConfirmation::dispatch($order->id)->afterCommit();

        return response()->json([
            'message' => 'Order created successfully.',
            'order' => $order->load(['customer', 'items.product']),
        ], 201);
    }
}
