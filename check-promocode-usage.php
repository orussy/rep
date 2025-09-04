<?php
require_once 'config/db.php';

try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    echo "=== PROMO CODE USAGE ANALYSIS ===\n\n";

    // 1. Basic Usage Count by Promo Code
    echo "1. BASIC USAGE COUNT BY PROMO CODE:\n";
    echo "=====================================\n";
    $query = "
        SELECT 
            pc.code,
            pc.description,
            pc.discount_type,
            pc.discount_value,
            pc.max_uses,
            COUNT(cpc.id) as times_used,
            ROUND((COUNT(cpc.id) / pc.max_uses) * 100, 2) as usage_percentage
        FROM promocodes pc
        LEFT JOIN cart_promocodes cpc ON pc.id = cpc.promocode_id
        GROUP BY pc.id, pc.code, pc.description, pc.discount_type, pc.discount_value, pc.max_uses
        ORDER BY times_used DESC
    ";
    
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        echo "Code: {$row['code']}\n";
        echo "Description: {$row['description']}\n";
        echo "Type: {$row['discount_type']} ({$row['discount_value']})\n";
        echo "Max Uses: {$row['max_uses']}\n";
        echo "Times Used: {$row['times_used']}\n";
        echo "Usage %: {$row['usage_percentage']}%\n";
        echo "---\n";
    }

    // 2. Usage by Date Range
    echo "\n2. USAGE BY DATE RANGE:\n";
    echo "=======================\n";
    $query = "
        SELECT 
            pc.code,
            DATE(cpc.applied_at) as usage_date,
            COUNT(*) as daily_usage
        FROM promocodes pc
        INNER JOIN cart_promocodes cpc ON pc.id = cpc.promocode_id
        GROUP BY pc.code, DATE(cpc.applied_at)
        ORDER BY usage_date DESC, daily_usage DESC
    ";
    
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        echo "Code: {$row['code']} | Date: {$row['usage_date']} | Usage: {$row['daily_usage']}\n";
    }

    // 3. User Usage Tracking
    echo "\n3. USER USAGE TRACKING:\n";
    echo "=======================\n";
    $query = "
        SELECT 
            u.f_name,
            u.l_name,
            u.email,
            pc.code,
            COUNT(cpc.id) as times_used,
            pc.max_uses_per_user
        FROM users u
        INNER JOIN cart c ON u.id = c.user_id
        INNER JOIN cart_promocodes cpc ON c.id = cpc.cart_id
        INNER JOIN promocodes pc ON cpc.promocode_id = pc.id
        GROUP BY u.id, u.f_name, u.l_name, u.email, pc.code, pc.max_uses_per_user
        ORDER BY times_used DESC
    ";
    
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        echo "User: {$row['f_name']} {$row['l_name']} ({$row['email']})\n";
        echo "Code: {$row['code']} | Used: {$row['times_used']}/{$row['max_uses_per_user']}\n";
        echo "---\n";
    }

    // 4. Most Popular Promo Codes
    echo "\n4. MOST POPULAR PROMO CODES:\n";
    echo "============================\n";
    $query = "
        SELECT 
            pc.code,
            pc.description,
            COUNT(cpc.id) as total_usage,
            COUNT(DISTINCT c.user_id) as unique_users,
            AVG(c.total) as avg_cart_value
        FROM promocodes pc
        INNER JOIN cart_promocodes cpc ON pc.id = cpc.promocode_id
        INNER JOIN cart c ON cpc.cart_id = c.id
        GROUP BY pc.id, pc.code, pc.description
        ORDER BY total_usage DESC
    ";
    
    $result = $conn->query($query);
    while ($row = $result->fetch_assoc()) {
        echo "Code: {$row['code']}\n";
        echo "Description: {$row['description']}\n";
        echo "Total Usage: {$row['total_usage']}\n";
        echo "Unique Users: {$row['unique_users']}\n";
        echo "Avg Cart Value: EGP " . number_format($row['avg_cart_value'], 2) . "\n";
        echo "---\n";
    }

    // 5. Unused Promo Codes
    echo "\n5. UNUSED PROMO CODES:\n";
    echo "======================\n";
    $query = "
        SELECT 
            pc.code,
            pc.description,
            pc.discount_type,
            pc.discount_value,
            pc.max_uses,
            pc.is_active
        FROM promocodes pc
        LEFT JOIN cart_promocodes cpc ON pc.id = cpc.promocode_id
        WHERE cpc.id IS NULL
        ORDER BY pc.created_at DESC
    ";
    
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "Code: {$row['code']}\n";
            echo "Description: {$row['description']}\n";
            echo "Type: {$row['discount_type']} ({$row['discount_value']})\n";
            echo "Max Uses: {$row['max_uses']}\n";
            echo "Active: " . ($row['is_active'] ? 'Yes' : 'No') . "\n";
            echo "---\n";
        }
    } else {
        echo "All promo codes have been used at least once.\n";
    }

    // 6. Usage Statistics Summary
    echo "\n6. USAGE STATISTICS SUMMARY:\n";
    echo "=============================\n";
    $query = "
        SELECT 
            COUNT(DISTINCT pc.id) as total_promo_codes,
            COUNT(DISTINCT CASE WHEN cpc.id IS NOT NULL THEN pc.id END) as used_promo_codes,
            COUNT(cpc.id) as total_usage_count,
            COUNT(DISTINCT c.user_id) as unique_users_used_promos,
            AVG(c.total) as avg_cart_value_with_promo
        FROM promocodes pc
        LEFT JOIN cart_promocodes cpc ON pc.id = cpc.promocode_id
        LEFT JOIN cart c ON cpc.cart_id = c.id
    ";
    
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    echo "Total Promo Codes: {$row['total_promo_codes']}\n";
    echo "Used Promo Codes: {$row['used_promo_codes']}\n";
    echo "Total Usage Count: {$row['total_usage_count']}\n";
    echo "Unique Users Used Promos: {$row['unique_users_used_promos']}\n";
    echo "Avg Cart Value with Promo: EGP " . number_format($row['avg_cart_value_with_promo'], 2) . "\n";

    $conn->close();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
