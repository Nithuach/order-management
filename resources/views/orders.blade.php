
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Retail Counter - Order Management</title>

    <style>
        /* ---------- Global styles ---------- */
        * {
            box-sizing: border-box;
        }

        :root {
            --navy: #172b4d;
            --blue: #1769e0;
            --blue-light: #eaf1fc;
            --text: #202938;
            --muted: #667085;
            --border: #e5e9f0;
            --background: #f4f6f9;
            --white: #ffffff;
            --danger: #b42318;
            --danger-light: #fff0f0;
            --success: #17663a;
            --success-light: #e8f7ed;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: var(--text);
            background: var(--background);
        }

        input,
        select,
        button,
        textarea {
            font: inherit;
        }

        /* ---------- Header ---------- */
        header {
            background: var(--navy);
            color: var(--white);
            padding: 22px 6%;
        }

        header h1 {
            margin: 0 0 6px;
            font-size: 24px;
            line-height: 1.3;
        }

        header p {
            margin: 0;
            color: #d7e0ef;
            font-size: 14px;
        }

        /* ---------- Main layout ---------- */
        main {
            width: 100%;
            max-width: 1100px;
            margin: 28px auto;
            padding: 0 18px;
        }

        .panel {
            min-width: 0;
            margin-bottom: 20px;
            padding: 24px;
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 3px 14px #172b4d0d;
        }

        h2 {
            margin: 0 0 18px;
            color: var(--navy);
            font-size: 19px;
            line-height: 1.4;
        }

        p {
            margin-top: 10px;
            margin-bottom: 10px;
        }

        /* ---------- Customer form ---------- */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            color: var(--text);
            font-size: 13px;
            font-weight: 700;
        }

        input,
        select,
        textarea {
            display: block;
            width: 100%;
            min-width: 0;
            padding: 11px 12px;
            border: 1px solid #d5dbe5;
            border-radius: 7px;
            background: var(--white);
            color: var(--text);
            font-size: 14px;
            line-height: 1.4;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--blue);
            box-shadow: 0 0 0 3px #1769e01a;
        }

        /* ---------- Buttons ---------- */
        button,
        a.button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 10px 16px;
            border: 1px solid transparent;
            border-radius: 7px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.4;
            text-align: center;
            text-decoration: none;
        }

        .primary {
            background: var(--blue);
            color: var(--white);
        }

        .primary:hover {
            background: #1257bd;
        }

        .secondary {
            background: var(--blue-light);
            color: #174b94;
        }

        .secondary:hover {
            background: #dce8fb;
        }

        .remove {
            background: var(--danger-light);
            color: var(--danger);
        }

        .remove:hover {
            background: #ffe1e1;
        }

        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* ---------- Actions ---------- */
        .actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }

        /* ---------- Tables ---------- */
        .table-wrap,
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            border-radius: 7px;
        }

        table {
            width: 100%;
            min-width: 600px;
            border-collapse: collapse;
            background: var(--white);
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid var(--border);
            text-align: left;
            vertical-align: middle;
            font-size: 14px;
        }

        th {
            background: var(--background);
            color: #5c6677;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: #fafbfc;
        }

        .quantity {
            width: 80px;
            min-width: 75px;
        }

        .price {
            white-space: nowrap;
        }

        /* ---------- Totals ---------- */
        .totals {
            width: 100%;
            max-width: 360px;
            margin: 20px 0 0 auto;
        }

        .total-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 9px 0;
        }

        .grand-total {
            margin-top: 6px;
            padding-top: 16px;
            border-top: 1px solid #dce1e9;
            font-size: 20px;
            font-weight: 700;
        }

        /* ---------- Notifications ---------- */
        .notice {
            margin-bottom: 18px;
            padding: 13px 15px;
            border-radius: 7px;
            overflow-wrap: anywhere;
        }

        .success {
            background: var(--success-light);
            color: var(--success);
        }

        .error {
            background: var(--danger-light);
            color: #a21d24;
        }

        .error ul {
            margin: 0;
            padding-left: 20px;
        }

        .field-error {
            margin-top: 6px;
            color: var(--danger);
            font-size: 12px;
        }

        /* ---------- Low-stock section ---------- */
        .threshold-form {
            display: flex;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 12px;
            margin: 18px 0;
        }

        .threshold-form > div {
            width: 160px;
            max-width: 100%;
        }

        .threshold-form input {
            width: 100%;
        }

        .threshold-form button {
            background: var(--navy);
            color: var(--white);
        }

        .threshold-form button:hover {
            background: #243d66;
        }

        .stock-summary {
            margin: 14px 0;
            color: #5c6677;
            font-size: 14px;
        }

        .stock-note {
            margin-top: 5px;
            color: var(--muted);
            font-size: 12px;
        }

        .stock-empty {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 5px;
            background: var(--danger-light);
            color: var(--danger);
            font-weight: 700;
        }

        .stock-value {
            font-weight: 600;
        }

        /* ---------- Responsive layout ---------- */
        @media (max-width: 650px) {
            header {
                padding: 20px;
            }

            header h1 {
                font-size: 21px;
            }

            main {
                margin: 18px auto;
                padding: 0 12px;
            }

            .panel {
                padding: 17px;
                border-radius: 10px;
            }

            .form-grid {
                grid-template-columns: minmax(0, 1fr);
                gap: 13px;
            }

            .actions {
                align-items: stretch;
            }

            .actions button,
            .actions a.button {
                flex: 1 1 auto;
            }

            .threshold-form {
                align-items: stretch;
            }

            .threshold-form > div {
                width: 100%;
            }

            .threshold-form button {
                width: 100%;
            }

            .totals {
                max-width: 100%;
            }
        }
    </style>
</head>

<body>

<header>
    <h1>Retail Counter</h1>
    <p>Store orders and inventory management</p>
</header>

<main>

    {{-- Success message --}}
    @if (session('success'))
        <div class="notice success" role="status">
            {{ session('success') }}
        </div>
    @endif

    {{-- Validation errors --}}
    @if ($errors->any())
        <div class="notice error" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ORDER FORM --}}
    <form method="POST" action="{{ route('orders.store') }}">
        @csrf

        <input
            type="hidden"
            name="row_count"
            value="{{ $rowCount }}"
        >

        {{-- Customer details --}}
        <section class="panel">
            <h2>Customer details</h2>

            <div class="form-grid">
                <div>
                    <label for="customerName">Customer name</label>

                    <input
                        id="customerName"
                        name="customer[name]"
                        value="{{ old('customer.name') }}"
                        maxlength="255"
                        placeholder="Enter customer name"
                        required
                    >

                    @error('customer.name')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="customerEmail">Customer email</label>

                    <input
                        id="customerEmail"
                        name="customer[email]"
                        type="email"
                        value="{{ old('customer.email') }}"
                        maxlength="255"
                        placeholder="customer@example.com"
                        required
                    >

                    @error('customer.email')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </section>

        {{-- Calculate displayed totals in Blade --}}
        @php
            $subtotalCents = 0;
            $taxTotalCents = 0;
        @endphp

        {{-- Order items --}}
        <section class="panel">
            <h2>Order items</h2>

            @error('items')
                <div class="field-error">{{ $message }}</div>
            @enderror

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 34%">Product</th>
                            <th>Unit price</th>
                            <th>Tax</th>
                            <th>Quantity</th>
                            <th>Line total</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @for ($i = 0; $i < $rowCount; $i++)
                            @php
                                $productId = old("items.$i.product_id");

                                $product = $products->firstWhere(
                                    'id',
                                    $productId
                                );

                                $quantity = filter_var(
                                    old("items.$i.quantity", 1),
                                    FILTER_VALIDATE_INT
                                );

                                $quantity = $quantity !== false
                                    ? max(0, $quantity)
                                    : 0;

                                $unitCents = $product
                                    ? (int) round(
                                        (float) $product->price * 100
                                    )
                                    : 0;

                                $lineSubtotal = $unitCents * $quantity;

                                $lineTax = $product
                                    ? (int) round(
                                        $lineSubtotal
                                        * (float) $product->tax_percentage
                                        / 100
                                    )
                                    : 0;

                                if ($product && $quantity > 0) {
                                    $subtotalCents += $lineSubtotal;
                                    $taxTotalCents += $lineTax;
                                }
                            @endphp

                            <tr>
                                <td>
                                    <select
                                        name="items[{{ $i }}][product_id]"
                                        aria-label="Product for row {{ $i + 1 }}"
                                    >
                                        <option value="">
                                            Select product
                                        </option>

                                        @foreach ($products as $option)
                                            <option
                                                value="{{ $option->id }}"
                                                @selected(
                                                    (string) $productId
                                                    === (string) $option->id
                                                )
                                            >
                                                {{ $option->name }}
                                                ({{ $option->code }})
                                                — Stock {{ $option->stock_on_hand }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td class="price">
                                    {{ $product
                                        ? number_format((float) $product->price, 2)
                                        : '—' }}
                                </td>

                                <td>
                                    {{ $product
                                        ? number_format(
                                            (float) $product->tax_percentage,
                                            2
                                        ) . '%'
                                        : '—' }}
                                </td>

                                <td>
                                    <input
                                        class="quantity"
                                        type="number"
                                        name="items[{{ $i }}][quantity]"
                                        min="1"
                                        step="1"
                                        value="{{ old("items.$i.quantity", 1) }}"
                                        aria-label="Quantity for row {{ $i + 1 }}"
                                    >
                                </td>

                                <td class="price">
                                    {{ $product && $quantity > 0
                                        ? number_format(
                                            ($lineSubtotal + $lineTax) / 100,
                                            2
                                        )
                                        : '—' }}
                                </td>

                                <td>
                                    <button
                                        class="remove"
                                        type="submit"
                                        formaction="{{ route('orders.rows') }}"
                                        formmethod="POST"
                                        name="form_action"
                                        value="remove_row:{{ $i }}"
                                        formnovalidate
                                        aria-label="Remove row {{ $i + 1 }}"
                                    >
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            {{-- Row actions --}}
            <div class="actions">
                <button
                    type="submit"
                    class="secondary"
                    formaction="{{ route('orders.rows') }}"
                    formmethod="POST"
                    name="form_action"
                    value="add_row"
                    formnovalidate
                    {{ $rowCount >= 20 ? 'disabled' : '' }}
                >
                    + Add product
                </button>

                <button
                    type="submit"
                    class="secondary"
                    formaction="{{ route('orders.rows') }}"
                    formmethod="POST"
                    name="form_action"
                    value="recalculate"
                    formnovalidate
                >
                    Recalculate totals
                </button>
            </div>

            {{-- Totals --}}
            <div class="totals">
                <div class="total-row">
                    <span>Subtotal</span>
                    <strong>
                        {{ number_format($subtotalCents / 100, 2) }}
                    </strong>
                </div>

                <div class="total-row">
                    <span>Tax</span>
                    <strong>
                        {{ number_format($taxTotalCents / 100, 2) }}
                    </strong>
                </div>

                <div class="total-row grand-total">
                    <span>Grand total</span>
                    <span>
                        {{ number_format(
                            ($subtotalCents + $taxTotalCents) / 100,
                            2
                        ) }}
                    </span>
                </div>
            </div>

            {{-- Submit or reset --}}
            <div class="actions">
                <button type="submit" class="primary">
                    Place order
                </button>

                <a
                    href="{{ route('orders.index') }}"
                    class="button secondary"
                >
                    Reset form
                </a>
            </div>
        </section>
    </form>

    {{-- LOW-STOCK SECTION: separate form, outside the order form --}}
    <section class="panel">
        <h2>Low-stock products</h2>

        <form
            method="GET"
            action="{{ route('orders.index') }}"
            class="threshold-form"
        >
            <div>
                <label for="threshold">Stock threshold</label>

                <input
                    type="number"
                    id="threshold"
                    name="threshold"
                    min="0"
                    value="{{ $threshold }}"
                    required
                >
            </div>

            <button type="submit">
                Refresh low stock
            </button>
        </form>

        <p class="stock-summary">
            {{ $lowStockProducts->count() }} product(s) at or below
            {{ $threshold }} units.
        </p>

        <div class="table-wrapper">
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Code</th>
                        <th>Stock remaining</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($lowStockProducts as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->code }}</td>
                            <td>
                                @if ($product->stock_on_hand == 0)
                                    <span class="stock-empty">
                                        0
                                    </span>
                                @else
                                    <span class="stock-value">
                                        {{ $product->stock_on_hand }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                No products are at or below this stock threshold.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</main>

</body>
</html>
