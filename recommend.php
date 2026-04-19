<?php
// Core Content-Based Filtering Algorithm
function textToVector($text) {
    $stop_words = ['the', 'and', 'is', 'of', 'to', 'in', 'it', 'with', 'for', 'this', 'that', 'a', 'an'];
    $words = str_word_count(strtolower($text), 1);
    $filtered = array_diff($words, $stop_words);
    return array_count_values($filtered);
}

function cosineSimilarity($vecA, $vecB) {
    $dot = 0; $normA = 0; $normB = 0;
    $allKeys = array_unique(array_merge(array_keys($vecA), array_keys($vecB)));
    foreach ($allKeys as $k) {
        $a = $vecA[$k] ?? 0;
        $b = $vecB[$k] ?? 0;
        $dot += $a * $b;
        $normA += $a * $a;
        $normB += $b * $b;
    }
    return ($normA && $normB) ? ($dot / (sqrt($normA) * sqrt($normB))) : 0;
}

function displayRecommendations($productId, $conn) {
    $productId = mysqli_real_escape_string($conn, $productId);
    $target_res = mysqli_query($conn, "SELECT * FROM products WHERE id = '$productId'");
    $target = mysqli_fetch_assoc($target_res);
    if(!$target) return;

    $targetVec = textToVector($target['name'] . ' ' . $target['details'] . ' ' . $target['category']);
    $others = mysqli_query($conn, "SELECT * FROM products WHERE id != '$productId' LIMIT 50");
    $scores = [];
    while($row = mysqli_fetch_assoc($others)){
        $vec = textToVector($row['name'] . ' ' . $row['details'] . ' ' . $row['category']);
        $scores[] = ['product' => $row, 'score' => cosineSimilarity($targetVec, $vec)];
    }

    usort($scores, fn($a, $b) => $b['score'] <=> $a['score']);
    $top4 = array_slice($scores, 0, 4);

    echo '<div class="box-container">';
    foreach($top4 as $item){
        $p = $item['product'];
        ?>
        <form action="" method="post" class="box">
            <!-- Eye Icon Link (CSS will move this to the right) -->
            <a href="view_page.php?id=<?php echo $p['id']; ?>" class="fas fa-eye"></a>
            
            <img class="image" src="uploaded_img/<?php echo $p['image']; ?>" alt="">
            <div class="name"><?php echo $p['name']; ?></div>
            <div class="price">Rs.<?php echo $p['price']; ?>/-</div>
            
            <input type="hidden" name="product_id" value="<?php echo (int) $p['id']; ?>">
            <input type="hidden" name="product_name" value="<?php echo $p['name']; ?>">
            <input type="hidden" name="product_price" value="<?php echo $p['price']; ?>">
            <input type="hidden" name="product_image" value="<?php echo $p['image']; ?>">
            <input type="hidden" name="product_quantity" value="1">
            <input type="submit" name="add_to_cart" value="add to cart" class="btn">
        </form>
        <?php
    }
    echo '</div>';
}
?>