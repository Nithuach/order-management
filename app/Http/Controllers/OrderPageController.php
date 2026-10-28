<?php

namespace App\Http\Controllers;

use App\Jobs\SendOrderConfirmation;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderPageController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'threshold' => ['sometimes', 'integer', 'min:0'],
            'rows' => ['sometimes', 'integer', 'min:1', 'max:20'],
        ]);

        $threshold = $validated['threshold']
            ?? config('inventory.low_stock_threshold', 5);

        $rowCount = (int) old(
            'row_count',
            $validated['rows'] ?? 1
        );

        $rowCount = max(1, min(20, $rowCount));

        $products = Product::orderBy('name')->get();

        $lowStockProducts = Product::where(
            'stock_on_hand',
            '<=',
            $threshold
        )->orderBy('stock_on_hand')->get();

        return view('orders', compact(
            'products',
            'lowStockProducts',
            'threshold',
            'rowCount'
        ));
    }

    public function updateRows(Request $request)
    {
        $action = $request->input('form_action', 'recalculate');

        $rowCount = max(
            1,
            min(20, (int) $request->input('row_count', 1))
        );

        $items = array_values($request->input('items', []));

        if ($action === 'add_row') {
            $rowCount = min(20, $rowCount + 1);
        } elseif (str_starts_with($action, 'remove_row:')) {
            $index = (int) substr(
                $action,
                strlen('remove_row:')
            );

            if (array_key_exists($index, $items)) {
                unset($items[$index]);
                $items = array_values($items);
                $rowCount = max(1, $rowCount - 1);
            }
        }

        $input = $request->only('customer');
        $input['items'] = $items;
        $input['row_count'] = $rowCount;

        return redirect()
            ->route('orders.index', ['rows' => $rowCount])
            ->withInput($input);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer.name' => [
                'required', 'string', 'max:255',
            ],
            'customer.email' => [
                'required', 'email', 'max:255',
            ],
            'items' => ['required', 'array'],
            'items.*.product_id' => [
                'nullable', 'integer', 'exists:products,id',
            ],
            'items.*.quantity' => [
                'nullable', 'integer', 'min:1',
            ],
        ]);

        $items = collect($validated['items'])
            ->filter(fn ($item) =>
                filled($item['product_id'] ?? null) ||
                filled($item['quantity'] ?? null)
            )
            ->values();

        if ($items->isEmpty()) {
            return back()->withInput()->withErrors([
                'items' => 'Please select at least one product and quantity.',
            ]);
        }

        foreach ($items as $item) {
            if (
                empty($item['product_id']) ||
                empty($item['quantity'])
            ) {
                return back()->withInput()->withErrors([
                    'items' => 'Every selected product must have a quantity of at least 1.',
                ]);
            }
        }

        $order = DB::transaction(function () use (
            $validated,
            $items
        ) {
            $requestedItems = $items
                ->sortBy('product_id')
                ->values();

            $products = Product::whereIn(
                'id',
                $requestedItems->pluck('product_id')->all()
            )
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Validate every stock quantity before deducting stock.
            foreach ($requestedItems as $item) {
                $product = $products->get(
                    (int) $item['product_id']
                );

                if (
                    ! $product ||
                    $product->stock_on_hand < (int) $item['quantity']
                ) {
                    throw ValidationException::withMessages([
                        'items' => [
                            'Insufficient stock for product: ' .
                            ($product?->name ?? $item['product_id']),
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
            $lines = [];

            foreach ($requestedItems as $item) {
                $product = $products->get(
                    (int) $item['product_id']
                );

                $quantity = (int) $item['quantity'];

                $unitPriceCents = (int) round(
                    (float) $product->price * 100
                );

                $lineSubtotalCents = $unitPriceCents * $quantity;
                $taxRate = (float) $product->tax_percentage;

                $lineTaxCents = (int) round(
                    $lineSubtotalCents * $taxRate / 100
                );

                $subtotalCents += $lineSubtotalCents;
                $taxTotalCents += $lineTaxCents;

                $lines[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price_cents' => $unitPriceCents,
                    'tax_percentage' => $taxRate,
                    'line_subtotal_cents' => $lineSubtotalCents,
                    'line_tax_cents' => $lineTaxCents,
                ];
            }

            $order = Order::create([
                'customer_id' => $customer->id,
                'subtotal' => $subtotalCents / 100,
                'tax_total' => $taxTotalCents / 100,
                'grand_total' => (
                    $subtotalCents + $taxTotalCents
                ) / 100,
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price_cents'] / 100,
                    'tax_percentage' => $line['tax_percentage'],
                    'line_subtotal' => $line['line_subtotal_cents'] / 100,
                    'line_tax' => $line['line_tax_cents'] / 100,
                ]);

                $line['product']->decrement(
                    'stock_on_hand',
                    $line['quantity']
                );
            }

            return $order;
        }, 5);

        SendOrderConfirmation::dispatch($order->id)->afterCommit();

        return redirect()
            ->route('orders.index')
            ->with('success', "Order #{$order->id} placed successfully!")
            ->with('order_summary', [
                'subtotal' => $order->subtotal,
                'tax_total' => $order->tax_total,
                'grand_total' => $order->grand_total,
            ]);
    }
}
