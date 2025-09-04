<?php
require_once 'config/db.php';

try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    echo "=== QUICK PROMO CODE USAGE CHECK ===\n\n";

    // Quick summary
    $query = "
        SELECT 
            pc.code,
            pc.discount_type,
            pc.discount_value,
            COUNT(cpc.id) as used,
            pc.max_uses,
            ROUND((COUNT(cpc.id) / pc.max_uses) * 100, 1) as usage_pct,
            pc.is_active
        FROM promocodes pc
        LEFT JOIN cart_promocodes cpc ON pc.id = cpc.promocode_id
        GROUP BY pc.id
        ORDER BY used DESC, pc.code
    ";
    
    $result = $conn->query($query);
    
    printf("%-12s %-8s %-8s %-6s %-8s %-8s %-6s\n", 
           "CODE", "TYPE", "VALUE", "USED", "MAX", "USAGE%", "ACTIVE");
    echo str_repeat("-", 70) . "\n";
    
    while ($row = $result->fetch_assoc()) {
        $type = $row['discount_type'] == 'percentage' ? '%' : 'EGP';
        $value = $row['discount_value'] . $type;
        $active = $row['is_active'] ? 'Yes' : 'No';
        
        printf("%-12s %-8s %-8s %-6d %-8d %-8s %-6s\n", 
               $row['code'], 
               $row['discount_type'], 
               $value, 
               $row['used'], 
               $row['max_uses'], 
               $row['usage_pct'] . '%', 
               $active);
    }

    echo "\n=== RECENT USAGE (Last 7 Days) ===\n";
    $query = "
        SELECT 
            pc.code,
            DATE(cpc.applied_at) as date,
            COUNT(*) as count
        FROM promocodes pc
        INNER JOIN cart_promocodes cpc ON pc.id = cpc.promocode_id
        WHERE cpc.applied_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY pc.code, DATE(cpc.applied_at)
        ORDER BY date DESC, count DESC
    ";
    
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "{$row['code']} - {$row['date']}: {$row['count']} uses\n";
        }
    } else {
        echo "No usage in the last 7 days.\n";
    }

    $conn->close();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
