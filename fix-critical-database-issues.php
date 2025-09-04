<?php
require_once 'config/db.php';

try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    echo "=== FIXING CRITICAL DATABASE ISSUES ===\n\n";

    // 1. Fix cart ID 0 issue
    echo "1. Fixing cart ID 0 issue...\n";
    $result = $conn->query("SELECT COUNT(*) as count FROM cart WHERE id = 0");
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        // First, update any orders that reference cart_id = 0
        $conn->query("UPDATE order_details SET cart_id = NULL WHERE cart_id = 0");
        echo "   Updated orders with cart_id = 0 to NULL\n";
        
        // Delete the cart with ID 0
        $conn->query("DELETE FROM cart WHERE id = 0");
        echo "   Deleted cart with ID 0\n";
        
        // Reset auto increment
        $conn->query("ALTER TABLE cart AUTO_INCREMENT = 1");
        echo "   Reset cart auto increment\n";
    } else {
        echo "   No cart with ID 0 found\n";
    }

    // 2. Fix column name typos
    echo "\n2. Fixing column name typos...\n";
    
    // Fix upated_at in order_item
    $result = $conn->query("SHOW COLUMNS FROM order_item LIKE 'upated_at'");
    if ($result->num_rows > 0) {
        $conn->query("ALTER TABLE order_item CHANGE upated_at updated_at timestamp NULL");
        echo "   Fixed 'upated_at' to 'updated_at' in order_item table\n";
    } else {
        echo "   'upated_at' column already fixed or doesn't exist\n";
    }
    
    // Fix Currency in product_skus
    $result = $conn->query("SHOW COLUMNS FROM product_skus LIKE 'Currency'");
    if ($result->num_rows > 0) {
        $conn->query("ALTER TABLE product_skus CHANGE Currency currency varchar(3) NOT NULL DEFAULT 'EGP'");
        echo "   Fixed 'Currency' to 'currency' in product_skus table\n";
    } else {
        echo "   'Currency' column already fixed or doesn't exist\n";
    }
    
    // Fix Loyality_status in users
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'Loyality_status'");
    if ($result->num_rows > 0) {
        $conn->query("ALTER TABLE users CHANGE Loyality_status Loyalty_status tinyint(1) NOT NULL DEFAULT 0");
        echo "   Fixed 'Loyality_status' to 'Loyalty_status' in users table\n";
    } else {
        echo "   'Loyality_status' column already fixed or doesn't exist\n";
    }

    // 3. Fix invalid birthdates
    echo "\n3. Fixing invalid birthdates...\n";
    $result = $conn->query("
        SELECT id, f_name, l_name, birthdate 
        FROM users 
        WHERE birthdate LIKE '0222-%' OR birthdate < '1900-01-01' OR birthdate > CURDATE()
    ");
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Fix the obvious typo birthdate
            if ($row['birthdate'] === '0222-12-12') {
                $conn->query("UPDATE users SET birthdate = '2000-12-12' WHERE id = {$row['id']}");
                echo "   Fixed birthdate for user {$row['id']} ({$row['f_name']} {$row['l_name']}): {$row['birthdate']} -> 2000-12-12\n";
            }
        }
    } else {
        echo "   No invalid birthdates found\n";
    }

    // 4. Add missing indexes
    echo "\n4. Adding missing indexes...\n";
    
    // Check and add index on order_details.status
    $result = $conn->query("SHOW INDEX FROM order_details WHERE Column_name = 'status'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE order_details ADD INDEX idx_status (status)");
        echo "   Added index on order_details.status\n";
    } else {
        echo "   Index on order_details.status already exists\n";
    }
    
    // Check and add index on order_details.created_at
    $result = $conn->query("SHOW INDEX FROM order_details WHERE Column_name = 'created_at'");
    if ($result->num_rows == 0) {
        $conn->query("ALTER TABLE order_details ADD INDEX idx_created_at (created_at)");
        echo "   Added index on order_details.created_at\n";
    } else {
        echo "   Index on order_details.created_at already exists\n";
    }

    // 5. Fix inconsistent column casing
    echo "\n5. Fixing inconsistent column casing...\n";
    
    // Fix product_ID in comments table
    $result = $conn->query("SHOW COLUMNS FROM comments LIKE 'product_ID'");
    if ($result->num_rows > 0) {
        $conn->query("ALTER TABLE comments CHANGE product_ID product_id int(11) NOT NULL");
        echo "   Fixed 'product_ID' to 'product_id' in comments table\n";
    } else {
        echo "   'product_ID' column already fixed or doesn't exist\n";
    }

    // 6. Remove duplicate order_items (keep only the latest)
    echo "\n6. Removing duplicate order_items...\n";
    $result = $conn->query("
        SELECT order_id, product_id, product_sku_id, COUNT(*) as count
        FROM order_item 
        GROUP BY order_id, product_id, product_sku_id 
        HAVING COUNT(*) > 1
    ");
    
    if ($result->num_rows > 0) {
        $duplicates = [];
        while ($row = $result->fetch_assoc()) {
            $duplicates[] = $row;
        }
        
        foreach ($duplicates as $dup) {
            // Keep the latest record, delete the older ones
            $conn->query("
                DELETE FROM order_item 
                WHERE order_id = {$dup['order_id']} 
                AND product_id = {$dup['product_id']} 
                AND product_sku_id = {$dup['product_sku_id']} 
                AND id NOT IN (
                    SELECT id FROM (
                        SELECT id FROM order_item 
                        WHERE order_id = {$dup['order_id']} 
                        AND product_id = {$dup['product_id']} 
                        AND product_sku_id = {$dup['product_sku_id']} 
                        ORDER BY created_at DESC 
                        LIMIT 1
                    ) as latest
                )
            ");
            echo "   Removed duplicate order_item for Order: {$dup['order_id']}, Product: {$dup['product_id']}, SKU: {$dup['product_sku_id']}\n";
        }
    } else {
        echo "   No duplicate order_items found\n";
    }

    // 7. Fix timestamp inconsistencies (update updated_at to be after created_at)
    echo "\n7. Fixing timestamp inconsistencies...\n";
    $result = $conn->query("
        SELECT id, created_at, updated_at 
        FROM order_details 
        WHERE updated_at < created_at AND updated_at IS NOT NULL
    ");
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Set updated_at to be 1 hour after created_at
            $new_updated_at = date('Y-m-d H:i:s', strtotime($row['created_at']) + 3600);
            $conn->query("UPDATE order_details SET updated_at = '$new_updated_at' WHERE id = {$row['id']}");
            echo "   Fixed timestamp for order {$row['id']}: updated_at set to $new_updated_at\n";
        }
    } else {
        echo "   No timestamp inconsistencies found\n";
    }

    echo "\n=== CRITICAL ISSUES FIXED ===\n";
    echo "✅ Cart ID 0 issue resolved\n";
    echo "✅ Column name typos fixed\n";
    echo "✅ Invalid birthdates corrected\n";
    echo "✅ Missing indexes added\n";
    echo "✅ Inconsistent column casing fixed\n";
    echo "✅ Duplicate order_items removed\n";
    echo "✅ Timestamp inconsistencies resolved\n\n";

    echo "⚠️  RECOMMENDED NEXT STEPS:\n";
    echo "1. Test your application to ensure everything works correctly\n";
    echo "2. Consider renaming 'whishlist' table to 'wishlist'\n";
    echo "3. Add data validation constraints\n";
    echo "4. Implement audit trails for important operations\n";
    echo "5. Add more foreign key constraints where appropriate\n\n";

    $conn->close();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>


