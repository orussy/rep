<?php
require_once 'config/db.php';

try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    echo "=== DATABASE ISSUES ANALYSIS REPORT ===\n\n";

    // 1. CRITICAL ISSUES
    echo "🚨 CRITICAL ISSUES:\n";
    echo "==================\n";
    
    // Cart with ID 0
    $result = $conn->query("SELECT COUNT(*) as count FROM cart WHERE id = 0");
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        echo "❌ CRITICAL: Cart table has a record with ID 0\n";
        echo "   Impact: This breaks auto-increment and can cause duplicate key errors\n";
        echo "   Fix: DELETE FROM cart WHERE id = 0; ALTER TABLE cart AUTO_INCREMENT = 1;\n\n";
    }
    
    // Orders with cart_id = 0
    $result = $conn->query("SELECT COUNT(*) as count FROM order_details WHERE cart_id = 0");
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        echo "❌ CRITICAL: Multiple orders have cart_id = 0\n";
        echo "   Impact: These orders don't reference valid carts, breaking relationships\n";
        echo "   Count: {$row['count']} orders affected\n";
        echo "   Fix: Update these orders to reference valid cart IDs or create dummy carts\n\n";
    }

    // 2. TYPO ISSUES
    echo "📝 TYPO ISSUES:\n";
    echo "===============\n";
    
    echo "❌ Column 'upated_at' in order_item table\n";
    echo "   Should be: 'updated_at'\n";
    echo "   Fix: ALTER TABLE order_item CHANGE upated_at updated_at timestamp NULL;\n\n";
    
    echo "❌ Column 'Currency' in product_skus table\n";
    echo "   Should be: 'currency' (lowercase)\n";
    echo "   Fix: ALTER TABLE product_skus CHANGE Currency currency varchar(3) NOT NULL DEFAULT 'EGP';\n\n";
    
    echo "❌ Column 'Loyality_status' in users table\n";
    echo "   Should be: 'Loyalty_status' (correct spelling)\n";
    echo "   Fix: ALTER TABLE users CHANGE Loyality_status Loyalty_status tinyint(1) NOT NULL DEFAULT 0;\n\n";

    // 3. DATA INCONSISTENCIES
    echo "📊 DATA INCONSISTENCIES:\n";
    echo "=======================\n";
    
    // Orders with updated_at before created_at
    $result = $conn->query("
        SELECT id, created_at, updated_at 
        FROM order_details 
        WHERE updated_at < created_at 
        LIMIT 5
    ");
    
    if ($result->num_rows > 0) {
        echo "❌ Orders with updated_at before created_at:\n";
        while ($row = $result->fetch_assoc()) {
            echo "   Order ID: {$row['id']} | Created: {$row['created_at']} | Updated: {$row['updated_at']}\n";
        }
        echo "   Fix: Update these timestamps to be logical\n\n";
    }
    
    // Invalid birthdates
    $result = $conn->query("
        SELECT id, f_name, l_name, birthdate 
        FROM users 
        WHERE birthdate LIKE '0222-%' OR birthdate < '1900-01-01' OR birthdate > CURDATE()
    ");
    
    if ($result->num_rows > 0) {
        echo "❌ Users with invalid birthdates:\n";
        while ($row = $result->fetch_assoc()) {
            echo "   User ID: {$row['id']} | Name: {$row['f_name']} {$row['l_name']} | Birthdate: {$row['birthdate']}\n";
        }
        echo "   Fix: Update birthdates to valid dates\n\n";
    }

    // 4. DUPLICATE DATA
    echo "🔄 DUPLICATE DATA:\n";
    echo "==================\n";
    
    $result = $conn->query("
        SELECT order_id, product_id, product_sku_id, COUNT(*) as count
        FROM order_item 
        GROUP BY order_id, product_id, product_sku_id 
        HAVING COUNT(*) > 1
        LIMIT 5
    ");
    
    if ($result->num_rows > 0) {
        echo "❌ Duplicate order_items found:\n";
        while ($row = $result->fetch_assoc()) {
            echo "   Order: {$row['order_id']} | Product: {$row['product_id']} | SKU: {$row['product_sku_id']} | Count: {$row['count']}\n";
        }
        echo "   Fix: Remove duplicate entries or add unique constraint\n\n";
    }

    // 5. MISSING RELATIONSHIPS
    echo "🔗 RELATIONSHIP ISSUES:\n";
    echo "=======================\n";
    
    // Check for orders without valid cart references
    $result = $conn->query("
        SELECT od.id, od.cart_id, od.user_id
        FROM order_details od 
        LEFT JOIN cart c ON od.cart_id = c.id 
        WHERE c.id IS NULL AND od.cart_id != 0
        LIMIT 5
    ");
    
    if ($result->num_rows > 0) {
        echo "❌ Orders referencing non-existent carts:\n";
        while ($row = $result->fetch_assoc()) {
            echo "   Order ID: {$row['id']} | cart_id: {$row['cart_id']} | user_id: {$row['user_id']}\n";
        }
        echo "   Fix: Create missing carts or update cart_id references\n\n";
    }

    // 6. SCHEMA DESIGN ISSUES
    echo "🏗️ SCHEMA DESIGN ISSUES:\n";
    echo "========================\n";
    
    echo "❌ Table 'whishlist' should be 'wishlist' (typo in table name)\n";
    echo "   Fix: RENAME TABLE whishlist TO wishlist;\n\n";
    
    echo "❌ Column 'product_ID' in comments table should be 'product_id' (inconsistent casing)\n";
    echo "   Fix: ALTER TABLE comments CHANGE product_ID product_id int(11) NOT NULL;\n\n";

    // 7. PERFORMANCE ISSUES
    echo "⚡ PERFORMANCE ISSUES:\n";
    echo "=====================\n";
    
    // Check for missing indexes on frequently queried columns
    $result = $conn->query("SHOW INDEX FROM order_details");
    $has_status_index = false;
    while ($row = $result->fetch_assoc()) {
        if ($row['Column_name'] === 'status') $has_status_index = true;
    }
    
    if (!$has_status_index) {
        echo "❌ Missing index on order_details.status\n";
        echo "   Impact: Slow queries when filtering by order status\n";
        echo "   Fix: ALTER TABLE order_details ADD INDEX idx_status (status);\n\n";
    }
    
    $result = $conn->query("SHOW INDEX FROM order_details");
    $has_created_index = false;
    while ($row = $result->fetch_assoc()) {
        if ($row['Column_name'] === 'created_at') $has_created_index = true;
    }
    
    if (!$has_created_index) {
        echo "❌ Missing index on order_details.created_at\n";
        echo "   Impact: Slow queries when filtering by date ranges\n";
        echo "   Fix: ALTER TABLE order_details ADD INDEX idx_created_at (created_at);\n\n";
    }

    // 8. DATA VALIDATION ISSUES
    echo "✅ DATA VALIDATION ISSUES:\n";
    echo "=========================\n";
    
    // Check for negative quantities
    $result = $conn->query("SELECT COUNT(*) as count FROM product_skus WHERE quantity < 0");
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        echo "❌ Products with negative quantities: {$row['count']}\n";
        echo "   Fix: Update quantities to be >= 0\n\n";
    }
    
    // Check for negative prices
    $result = $conn->query("SELECT COUNT(*) as count FROM product_skus WHERE price < 0");
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        echo "❌ Products with negative prices: {$row['count']}\n";
        echo "   Fix: Update prices to be >= 0\n\n";
    }

    // 9. SECURITY ISSUES
    echo "🔒 SECURITY ISSUES:\n";
    echo "===================\n";
    
    // Check for weak passwords (this is just an example - don't actually check password hashes)
    echo "⚠️  Consider implementing password strength requirements\n";
    echo "   Current: Basic password hashing with bcrypt\n";
    echo "   Recommendation: Add password complexity rules\n\n";

    // 10. RECOMMENDATIONS
    echo "💡 RECOMMENDATIONS:\n";
    echo "===================\n";
    
    echo "1. Fix all typos in column names for consistency\n";
    echo "2. Remove the cart record with ID 0\n";
    echo "3. Update orders with cart_id = 0 to reference valid carts\n";
    echo "4. Fix invalid birthdates in users table\n";
    echo "5. Remove duplicate order_items\n";
    echo "6. Add missing indexes for better performance\n";
    echo "7. Implement data validation constraints\n";
    echo "8. Consider adding audit trails for important tables\n";
    echo "9. Add foreign key constraints where missing\n";
    echo "10. Implement soft deletes consistently across all tables\n\n";

    // 11. PRIORITY FIXES
    echo "🎯 PRIORITY FIXES (Fix these first):\n";
    echo "====================================\n";
    
    echo "1. CRITICAL: Fix cart ID 0 issue\n";
    echo "2. CRITICAL: Fix orders with cart_id = 0\n";
    echo "3. HIGH: Fix column name typos\n";
    echo "4. HIGH: Fix invalid birthdates\n";
    echo "5. MEDIUM: Remove duplicate order_items\n";
    echo "6. MEDIUM: Add missing indexes\n";
    echo "7. LOW: Fix table name typos\n\n";

    echo "=== END OF REPORT ===\n";

    $conn->close();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>


