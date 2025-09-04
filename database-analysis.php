<?php
require_once 'config/db.php';

try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    echo "=== DATABASE SCHEMA ANALYSIS ===\n\n";

    // 1. Check for typos in column names
    echo "1. CHECKING FOR TYPOS IN COLUMN NAMES:\n";
    echo "=====================================\n";
    
    $tables_to_check = [
        'order_item' => ['upated_at'], // Should be 'updated_at'
        'product_skus' => ['Currency'], // Should be 'currency' (lowercase)
        'users' => ['Loyality_status'] // Should be 'Loyalty_status'
    ];
    
    foreach ($tables_to_check as $table => $columns) {
        echo "Table: $table\n";
        $result = $conn->query("DESCRIBE $table");
        while ($row = $result->fetch_assoc()) {
            foreach ($columns as $typo) {
                if ($row['Field'] === $typo) {
                    echo "  ❌ TYPO FOUND: Column '{$typo}' should be corrected\n";
                }
            }
        }
        echo "\n";
    }

    // 2. Check data type issues
    echo "2. CHECKING DATA TYPE ISSUES:\n";
    echo "=============================\n";
    
    // Check order_details.total and payment_details.amount
    $result = $conn->query("DESCRIBE order_details");
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] === 'total' && $row['Type'] === 'decimal(10,2)') {
            echo "✅ order_details.total: Correct data type (decimal)\n";
        }
    }
    
    $result = $conn->query("DESCRIBE payment_details");
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] === 'amount' && $row['Type'] === 'decimal(10,2)') {
            echo "✅ payment_details.amount: Correct data type (decimal)\n";
        }
    }

    // 3. Check for data inconsistencies
    echo "\n3. CHECKING DATA INCONSISTENCIES:\n";
    echo "=================================\n";
    
    // Check for updated_at before created_at
    $result = $conn->query("
        SELECT id, created_at, updated_at 
        FROM order_details 
        WHERE updated_at < created_at 
        LIMIT 5
    ");
    
    if ($result->num_rows > 0) {
        echo "❌ FOUND: Orders with updated_at before created_at:\n";
        while ($row = $result->fetch_assoc()) {
            echo "  Order ID: {$row['id']} | Created: {$row['created_at']} | Updated: {$row['updated_at']}\n";
        }
    } else {
        echo "✅ No orders with updated_at before created_at found\n";
    }
    
    // Check for invalid birthdates
    $result = $conn->query("
        SELECT id, f_name, l_name, birthdate 
        FROM users 
        WHERE birthdate LIKE '0222-%' OR birthdate < '1900-01-01' OR birthdate > CURDATE()
    ");
    
    if ($result->num_rows > 0) {
        echo "\n❌ FOUND: Users with invalid birthdates:\n";
        while ($row = $result->fetch_assoc()) {
            echo "  User ID: {$row['id']} | Name: {$row['f_name']} {$row['l_name']} | Birthdate: {$row['birthdate']}\n";
        }
    } else {
        echo "\n✅ No users with invalid birthdates found\n";
    }

    // 4. Check for missing relationships
    echo "\n4. CHECKING MISSING RELATIONSHIPS:\n";
    echo "==================================\n";
    
    // Check if order_details has cart_id
    $result = $conn->query("DESCRIBE order_details");
    $has_cart_id = false;
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] === 'cart_id') {
            $has_cart_id = true;
            break;
        }
    }
    
    if ($has_cart_id) {
        echo "✅ order_details has cart_id column\n";
    } else {
        echo "❌ MISSING: order_details table lacks cart_id column\n";
    }

    // 5. Check for orphaned records
    echo "\n5. CHECKING FOR ORPHANED RECORDS:\n";
    echo "=================================\n";
    
    // Check cart_promocodes with non-existent carts
    $result = $conn->query("
        SELECT cpc.id, cpc.cart_id 
        FROM cart_promocodes cpc 
        LEFT JOIN cart c ON cpc.cart_id = c.id 
        WHERE c.id IS NULL
    ");
    
    if ($result->num_rows > 0) {
        echo "❌ FOUND: Orphaned cart_promocodes records:\n";
        while ($row = $result->fetch_assoc()) {
            echo "  cart_promocodes ID: {$row['id']} | cart_id: {$row['cart_id']}\n";
        }
    } else {
        echo "✅ No orphaned cart_promocodes records found\n";
    }
    
    // Check order_details with non-existent carts
    $result = $conn->query("
        SELECT od.id, od.cart_id 
        FROM order_details od 
        LEFT JOIN cart c ON od.cart_id = c.id 
        WHERE c.id IS NULL AND od.cart_id != 0
    ");
    
    if ($result->num_rows > 0) {
        echo "\n❌ FOUND: Order details with non-existent carts:\n";
        while ($row = $result->fetch_assoc()) {
            echo "  Order ID: {$row['id']} | cart_id: {$row['cart_id']}\n";
        }
    } else {
        echo "\n✅ No order details with non-existent carts found\n";
    }

    // 6. Check for duplicate data
    echo "\n6. CHECKING FOR DUPLICATE DATA:\n";
    echo "===============================\n";
    
    // Check for duplicate order_items
    $result = $conn->query("
        SELECT order_id, product_id, product_sku_id, COUNT(*) as count
        FROM order_item 
        GROUP BY order_id, product_id, product_sku_id 
        HAVING COUNT(*) > 1
        LIMIT 5
    ");
    
    if ($result->num_rows > 0) {
        echo "❌ FOUND: Duplicate order_items:\n";
        while ($row = $result->fetch_assoc()) {
            echo "  Order: {$row['order_id']} | Product: {$row['product_id']} | SKU: {$row['product_sku_id']} | Count: {$row['count']}\n";
        }
    } else {
        echo "✅ No duplicate order_items found\n";
    }

    // 7. Check for missing indexes
    echo "\n7. CHECKING FOR MISSING INDEXES:\n";
    echo "===============================\n";
    
    // Check if cart_promocodes has proper indexes
    $result = $conn->query("SHOW INDEX FROM cart_promocodes");
    $has_cart_index = false;
    $has_promo_index = false;
    
    while ($row = $result->fetch_assoc()) {
        if ($row['Column_name'] === 'cart_id') $has_cart_index = true;
        if ($row['Column_name'] === 'promocode_id') $has_promo_index = true;
    }
    
    if ($has_cart_index && $has_promo_index) {
        echo "✅ cart_promocodes has proper indexes\n";
    } else {
        echo "❌ MISSING: cart_promocodes missing indexes on cart_id or promocode_id\n";
    }

    // 8. Check for inconsistent data types
    echo "\n8. CHECKING FOR INCONSISTENT DATA TYPES:\n";
    echo "========================================\n";
    
    // Check if all price fields are decimal
    $tables_with_prices = ['product_skus', 'order_details', 'payment_details'];
    foreach ($tables_with_prices as $table) {
        $result = $conn->query("DESCRIBE $table");
        while ($row = $result->fetch_assoc()) {
            if (strpos($row['Field'], 'price') !== false || $row['Field'] === 'total' || $row['Field'] === 'amount') {
                if (strpos($row['Type'], 'decimal') === false) {
                    echo "❌ Table $table.{$row['Field']}: Should be decimal, found {$row['Type']}\n";
                } else {
                    echo "✅ Table $table.{$row['Field']}: Correct data type ({$row['Type']})\n";
                }
            }
        }
    }

    // 9. Check for naming convention issues
    echo "\n9. CHECKING NAMING CONVENTION ISSUES:\n";
    echo "=====================================\n";
    
    // Check for inconsistent naming
    $result = $conn->query("SHOW TABLES");
    $table_names = [];
    while ($row = $result->fetch_assoc()) {
        $table_names[] = $row[0];
    }
    
    $inconsistent_tables = [];
    foreach ($table_names as $table) {
        if (strpos($table, '_') === false && $table !== 'users' && $table !== 'cart') {
            $inconsistent_tables[] = $table;
        }
    }
    
    if (!empty($inconsistent_tables)) {
        echo "❌ Tables not following snake_case convention:\n";
        foreach ($inconsistent_tables as $table) {
            echo "  - $table\n";
        }
    } else {
        echo "✅ All tables follow snake_case convention\n";
    }

    // 10. Summary of critical issues
    echo "\n10. SUMMARY OF CRITICAL ISSUES:\n";
    echo "===============================\n";
    
    $critical_issues = [];
    
    // Check for the cart with ID 0
    $result = $conn->query("SELECT COUNT(*) as count FROM cart WHERE id = 0");
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        $critical_issues[] = "Cart table has a record with ID 0 (should be auto-increment)";
    }
    
    // Check for orders with cart_id = 0
    $result = $conn->query("SELECT COUNT(*) as count FROM order_details WHERE cart_id = 0");
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        $critical_issues[] = "Multiple orders have cart_id = 0 (should reference valid carts)";
    }
    
    if (empty($critical_issues)) {
        echo "✅ No critical issues found\n";
    } else {
        foreach ($critical_issues as $issue) {
            echo "❌ CRITICAL: $issue\n";
        }
    }

    $conn->close();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>


