<?php

/**
 * Session key for guest shopping cart (not logged in).
 */
if (!defined('GUEST_CART_SESSION_KEY')) {
    define('GUEST_CART_SESSION_KEY', 'guest_cart');
}

/**
 * Whether cart.product_id exists (after migration). Cached per request.
 */
function cart_has_product_id_column($conn) {
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $r = @mysqli_query($conn, "SHOW COLUMNS FROM `cart` LIKE 'product_id'");
    $cached = $r && mysqli_num_rows($r) > 0;
    return $cached;
}

function guest_cart_ensure_array() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION[GUEST_CART_SESSION_KEY]) || !is_array($_SESSION[GUEST_CART_SESSION_KEY])) {
        $_SESSION[GUEST_CART_SESSION_KEY] = [];
    }
}

/**
 * @return list<array{product_id:int,name:string,price:int,quantity:int,image:string}>
 */
function guest_cart_get_items() {
    guest_cart_ensure_array();
    return $_SESSION[GUEST_CART_SESSION_KEY];
}

function guest_cart_set_items(array $items) {
    guest_cart_ensure_array();
    $_SESSION[GUEST_CART_SESSION_KEY] = array_values($items);
}

function guest_cart_item_count() {
    return count(guest_cart_get_items());
}

/**
 * Drops guest cart lines whose product no longer exists; optionally returns removed display names.
 *
 * @return list<string>
 */
function prune_guest_cart_stale($conn, $collect_removed = false) {
    guest_cart_ensure_array();
    $items = guest_cart_get_items();
    if ($items === []) {
        return [];
    }

    $removed = [];
    $kept = [];
    foreach ($items as $item) {
        $pid = (int) ($item['product_id'] ?? 0);
        $label = trim((string) ($item['name'] ?? ''));
        if ($label === '') {
            $label = 'An item';
        }
        if ($pid < 1) {
            if ($collect_removed) {
                $removed[] = $label;
            }
            continue;
        }
        $pid_esc = mysqli_real_escape_string($conn, (string) $pid);
        $r = mysqli_query($conn, "SELECT `id` FROM `products` WHERE `id` = '$pid_esc' LIMIT 1");
        if ($r && mysqli_num_rows($r) > 0) {
            $kept[] = $item;
        } elseif ($collect_removed) {
            $removed[] = $label;
        }
    }
    guest_cart_set_items($kept);
    return $collect_removed ? $removed : [];
}

/**
 * SQL fragment: cart rows for user with no matching product (stale).
 */
function _stale_cart_select_sql($uid_esc, $has_product_id) {
    if ($has_product_id) {
        return "SELECT c.`id`, c.`name` FROM `cart` c
            LEFT JOIN `products` p ON (
                (c.`product_id` IS NOT NULL AND c.`product_id` = p.`id`)
                OR (c.`product_id` IS NULL AND c.`name` = p.`name` AND c.`price` = p.`price` AND c.`image` = p.`image`)
            )
            WHERE c.`user_id` = '$uid_esc' AND p.`id` IS NULL";
    }
    return "SELECT c.`id`, c.`name` FROM `cart` c
        LEFT JOIN `products` p ON c.`name` = p.`name` AND c.`price` = p.`price` AND c.`image` = p.`image`
        WHERE c.`user_id` = '$uid_esc' AND p.`id` IS NULL";
}

/**
 * Removes DB cart rows that no longer match a product.
 * If $collect_removed is true, returns list of cart snapshot names that were removed (for UI). Otherwise returns [].
 *
 * @return list<string>
 */
function prune_stale_cart_items($conn, $user_id, $collect_removed = false) {
    $uid = mysqli_real_escape_string($conn, (string) $user_id);
    if ($uid === '') {
        return [];
    }

    $has_pid = cart_has_product_id_column($conn);
    $sql = _stale_cart_select_sql($uid, $has_pid);
    $res = mysqli_query($conn, $sql);
    if (!$res) {
        return [];
    }

    $ids = [];
    $names = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $ids[] = (int) $row['id'];
        $names[] = (string) $row['name'];
    }

    if ($ids === []) {
        return [];
    }

    $id_list = implode(',', array_map('intval', $ids));
    mysqli_query($conn, "DELETE FROM `cart` WHERE `user_id` = '$uid' AND `id` IN ($id_list)");

    return $collect_removed ? $names : [];
}

/**
 * Human-readable bullet list for cart removal alert.
 *
 * @param list<string> $names
 */
function format_cart_removal_notice_lines(array $names) {
    $names = array_values(array_unique(array_filter(array_map('trim', $names))));
    if ($names === []) {
        return '';
    }
    if (count($names) === 1) {
        return '“' . htmlspecialchars($names[0], ENT_QUOTES, 'UTF-8') . '” is no longer available and was removed from your cart.';
    }
    $parts = array_map(
        static fn ($n) => '“' . htmlspecialchars($n, ENT_QUOTES, 'UTF-8') . '”',
        $names
    );
    return 'One or more items were removed from your cart because they are no longer available: ' . implode(', ', $parts) . '.';
}

/**
 * Add a product to cart using DB-backed fields (ignores tampered name/price from client).
 * For guests ($user_id null), stores in $_SESSION guest cart.
 *
 * @return array{ok:bool,message:string}
 */
function add_product_to_cart($conn, $user_id, $product_id, $quantity) {
    $pid = (int) $product_id;
    if ($pid < 1) {
        return ['ok' => false, 'message' => 'Invalid product.'];
    }

    $pid_esc = mysqli_real_escape_string($conn, (string) $pid);
    $p = mysqli_query($conn, "SELECT `id`, `name`, `price`, `image` FROM `products` WHERE `id` = '$pid_esc' LIMIT 1");
    if (!$p || mysqli_num_rows($p) === 0) {
        return ['ok' => false, 'message' => 'Product not found.'];
    }
    $row = mysqli_fetch_assoc($p);
    $qty = max(1, (int) $quantity);

    $n = (string) $row['name'];
    $img = (string) $row['image'];
    $price = (int) $row['price'];

    if ($user_id === null || $user_id === '') {
        guest_cart_ensure_array();
        $items = guest_cart_get_items();
        foreach ($items as $it) {
            if ((int) ($it['product_id'] ?? 0) === $pid) {
                return ['ok' => false, 'message' => 'already added to cart!'];
            }
        }
        $items[] = [
            'product_id' => $pid,
            'name' => $n,
            'price' => $price,
            'quantity' => $qty,
            'image' => $img,
        ];
        guest_cart_set_items($items);
        return ['ok' => true, 'message' => 'product added to cart!'];
    }

    $uid = mysqli_real_escape_string($conn, (string) $user_id);

    if (cart_has_product_id_column($conn)) {
        $dup = mysqli_query($conn, "SELECT `id` FROM `cart` WHERE `user_id` = '$uid' AND `product_id` = '$pid_esc' LIMIT 1");
    } else {
        $n_esc = mysqli_real_escape_string($conn, $n);
        $dup = mysqli_query($conn, "SELECT `id` FROM `cart` WHERE `user_id` = '$uid' AND `name` = '$n_esc' LIMIT 1");
    }
    if ($dup && mysqli_num_rows($dup) > 0) {
        return ['ok' => false, 'message' => 'already added to cart!'];
    }

    $n_esc = mysqli_real_escape_string($conn, $n);
    $img_esc = mysqli_real_escape_string($conn, $img);

    if (cart_has_product_id_column($conn)) {
        $ok = mysqli_query($conn, "INSERT INTO `cart` (`user_id`, `product_id`, `name`, `price`, `quantity`, `image`) VALUES ('$uid', '$pid_esc', '$n_esc', '$price', '$qty', '$img_esc')");
    } else {
        $ok = mysqli_query($conn, "INSERT INTO `cart` (`user_id`, `name`, `price`, `quantity`, `image`) VALUES ('$uid', '$n_esc', '$price', '$qty', '$img_esc')");
    }

    if (!$ok) {
        return ['ok' => false, 'message' => 'Could not add to cart.'];
    }
    return ['ok' => true, 'message' => 'product added to cart!'];
}
