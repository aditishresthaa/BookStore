<?php
/**
 * Resolves payment/order status for display. Does NOT treat missing DB values as "pending".
 * Canonical column in this project: orders.payment_status (there is no orders.status).
 *
 * @return array{label:string,color:string,raw:mixed}
 */
function resolve_order_payment_status_display(array $order_row) {
    $raw = null;
    $from_key = null;

    if (array_key_exists('payment_status', $order_row)) {
        $raw = $order_row['payment_status'];
        $from_key = 'payment_status';
    } elseif (array_key_exists('status', $order_row)) {
        $raw = $order_row['status'];
        $from_key = 'status';
    }

    if ($from_key === null) {
        return [
            'label' => 'Not available',
            'color' => '#7f8c8d',
            'raw' => null,
            'source_key' => null,
        ];
    }

    if ($raw === null) {
        return [
            'label' => 'Not available',
            'color' => '#7f8c8d',
            'raw' => null,
            'source_key' => $from_key,
        ];
    }

    $trimmed = trim((string) $raw);
    if ($trimmed === '') {
        return [
            'label' => 'Not available',
            'color' => '#7f8c8d',
            'raw' => $raw,
            'source_key' => $from_key,
        ];
    }

    $norm = strtolower($trimmed);

    $colors = [
        'pending' => '#c0392b',
        'processing' => '#e67e22',
        'completed' => '#27ae60',
        'paid' => '#27ae60',
        'cancelled' => '#7f8c8d',
    ];

    if ($norm === 'pending') {
        return ['label' => 'Pending', 'color' => $colors['pending'], 'raw' => $raw, 'source_key' => $from_key];
    }
    if ($norm === 'processing') {
        return ['label' => 'Processing', 'color' => $colors['processing'], 'raw' => $raw, 'source_key' => $from_key];
    }
    if ($norm === 'completed') {
        return ['label' => 'Completed', 'color' => $colors['completed'], 'raw' => $raw, 'source_key' => $from_key];
    }
    if ($norm === 'paid') {
        return ['label' => 'Paid', 'color' => $colors['paid'], 'raw' => $raw, 'source_key' => $from_key];
    }
    if ($norm === 'cancelled') {
        return ['label' => 'Cancelled', 'color' => $colors['cancelled'], 'raw' => $raw, 'source_key' => $from_key];
    }

    return [
        'label' => $trimmed,
        'color' => '#8e44ad',
        'raw' => $raw,
        'source_key' => $from_key,
    ];
}

/**
 * Renders order line items from orders.total_products (snapshot string from checkout).
 * Lines whose product name no longer exists in products are shown as unavailable placeholders.
 */
function format_order_products_for_display($conn, $total_products) {
    $total_products = isset($total_products) ? (string) $total_products : '';
    $total_products = trim($total_products);

    if ($total_products === '') {
        return '<span class="order-line-empty">—</span>';
    }

    if (!preg_match_all('/(.+?)\s+\((\d+)\)/u', $total_products, $matches, PREG_SET_ORDER)) {
        return '<span class="order-line-raw">' . htmlspecialchars($total_products, ENT_QUOTES, 'UTF-8') . '</span>';
    }

    $items = [];
    foreach ($matches as $m) {
        $name = trim($m[1]);
        $qty = $m[2];
        if ($name === '') {
            continue;
        }

        $esc = mysqli_real_escape_string($conn, $name);
        $check = mysqli_query($conn, "SELECT id FROM `products` WHERE name = '$esc' LIMIT 1");
        $exists = $check && mysqli_num_rows($check) > 0;

        $safe_name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safe_qty = htmlspecialchars((string) $qty, ENT_QUOTES, 'UTF-8');

        if ($exists) {
            $items[] = '<li class="order-line-available"><span class="order-line-title">' . $safe_name . '</span> <span class="order-line-qty">(' . $safe_qty . ')</span></li>';
        } else {
            $items[] = '<li class="order-line-unavailable"><span class="order-line-title">Product no longer available</span> <span class="order-line-was">(ordered as: ' . $safe_name . ', qty ' . $safe_qty . ')</span></li>';
        }
    }

    if (empty($items)) {
        return '<span class="order-line-raw">' . htmlspecialchars($total_products, ENT_QUOTES, 'UTF-8') . '</span>';
    }

    return '<ul class="order-products-list">' . implode('', $items) . '</ul>';
}
