<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/db.php';

try {
    // Get order ID from query parameter
    $orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if (!$orderId) {
        throw new Exception('Order ID is required');
    }
    
    // Fetch order details with user information
    $orderQuery = "SELECT 
                    o.id,
                    o.user_id,
                    o.cart_id,
                    o.total,
                    o.status,
                    o.tax_amount,
                    o.tax_rate,
                    o.created_at,
                    o.updated_at,
                    u.f_name,
                    u.l_name,
                    u.email,
                    u.phone_no
                  FROM order_details o
                  LEFT JOIN users u ON o.user_id = u.id
                  WHERE o.id = ?";
    
    $stmt = $conn->prepare($orderQuery);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Order not found');
    }
    
    $order = $result->fetch_assoc();
    
    // Fetch customer's latest address (if any)
    $addressQuery = "SELECT 
                        a.id,
                        a.title,
                        a.address1,
                        a.address2,
                        a.country,
                        a.city,
                        a.postal_code,
                        a.latitude,
                        a.longitude,
                        a.location_accuracy,
                        a.created_at
                     FROM addresses a
                     WHERE a.user_id = ? AND a.deleted_at IS NULL
                     ORDER BY a.created_at DESC
                     LIMIT 1";

    $stmt = $conn->prepare($addressQuery);
    $stmt->bind_param("i", $order['user_id']);
    $stmt->execute();
    $addrResult = $stmt->get_result();
    $address = $addrResult->num_rows > 0 ? $addrResult->fetch_assoc() : null;

    // Fetch order items with product information
    $itemsQuery = "SELECT 
                    oi.id,
                    oi.product_id,
                    oi.product_sku_id,
                    oi.quantity,
                    oi.created_at,
                    p.name as product_name,
                    ps.price,
                    ps.sku as sku_code
                  FROM order_item oi
                  LEFT JOIN products p ON oi.product_id = p.id
                  LEFT JOIN product_skus ps ON oi.product_sku_id = ps.id
                  WHERE oi.order_id = ?
                  ORDER BY oi.created_at ASC";
    
    $stmt = $conn->prepare($itemsQuery);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $itemsResult = $stmt->get_result();
    
    $items = [];
    $discountTotalRaw = 0.0;
    $itemsSubtotalRaw = 0.0;
    while ($item = $itemsResult->fetch_assoc()) {
        // Format the item data
        $item['created_at'] = date('Y-m-d H:i:s', strtotime($item['created_at']));

        $price = isset($item['price']) ? (float)$item['price'] : 0.0;
        $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 0;
        $lineSubtotal = $price * $quantity;
        $itemsSubtotalRaw += $lineSubtotal;

        // Resolve applicable discount for the product at the time of order creation
        $discountType = null;
        $discountValue = 0.0;
        $discountAmount = 0.0;
        if (!empty($item['product_id'])) {
            $discountSql = "SELECT discount_type, discount_value FROM discounts 
                            WHERE product_id = ? AND is_active = 1
                              AND (start_date IS NULL OR start_date <= DATE(?))
                              AND (end_date IS NULL OR end_date >= DATE(?))
                            ORDER BY created_at DESC LIMIT 1";
            $ds = $conn->prepare($discountSql);
            // Use order created_at as the reference date
            $orderCreatedAt = $order['created_at'];
            $ds->bind_param("iss", $item['product_id'], $orderCreatedAt, $orderCreatedAt);
            $ds->execute();
            $dres = $ds->get_result();
            if ($dres && $dres->num_rows > 0) {
                $drow = $dres->fetch_assoc();
                $discountType = $drow['discount_type'];
                $discountValue = (float)$drow['discount_value'];
                if ($discountType === 'percentage') {
                    $discountAmount = round($lineSubtotal * ($discountValue / 100.0), 2);
                } elseif ($discountType === 'fixed') {
                    $discountAmount = round($discountValue * $quantity, 2);
                }
            }
        }

        $discountTotalRaw += $discountAmount;
        $finalLineTotal = max(0, $lineSubtotal - $discountAmount);

        // Attach computed fields
        $item['line_subtotal'] = number_format($lineSubtotal, 2);
        $item['discount_type'] = $discountType;
        $item['discount_value'] = $discountType ? number_format($discountValue, 2) : null;
        $item['discount_amount'] = number_format($discountAmount, 2);
        $item['final_total'] = number_format($finalLineTotal, 2);

        $items[] = $item;
    }
    
    // Fetch payment information
    $paymentQuery = "SELECT 
                      p.id as payment_id,
                      p.amount,
                      p.provider,
                      p.status as payment_status,
                      p.created_at as payment_date
                    FROM payment_details p
                    WHERE p.order_id = ?
                    ORDER BY p.created_at DESC
                    LIMIT 1";
    
    $stmt = $conn->prepare($paymentQuery);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $paymentResult = $stmt->get_result();
    
    $payment = null;
    if ($paymentResult->num_rows > 0) {
        $payment = $paymentResult->fetch_assoc();
        // Format payment date
        $payment['payment_date'] = date('Y-m-d H:i:s', strtotime($payment['payment_date']));
        // Format payment amount
        $payment['amount'] = number_format($payment['amount'], 2);
    }
    
    // Calculate order discount based on promocode
    $orderDiscountAmount = 0.0;
    $orderTotalAfterDiscount = (float)$order['total'];
    
    // Check if there's a promocode discount on this order
    if (!empty($order['cart_id'])) {
        $promoQuery = "SELECT pc.discount_type, pc.discount_value
                       FROM cart_promocodes cpc
                       JOIN promocodes pc ON pc.id = cpc.promocode_id
                       WHERE cpc.cart_id = ?
                       ORDER BY cpc.applied_at DESC
                       LIMIT 1";
        $promoStmt = $conn->prepare($promoQuery);
        $promoStmt->bind_param("i", $order['cart_id']);
        $promoStmt->execute();
        $promoResult = $promoStmt->get_result();
        
        if ($promoResult->num_rows > 0) {
            $promo = $promoResult->fetch_assoc();
            $discountType = $promo['discount_type'];
            $discountValue = (float)$promo['discount_value'];
            
            if ($discountType === 'fixed') {
                // Fixed discount amount
                $orderDiscountAmount = $discountValue;
            } elseif ($discountType === 'percentage') {
                // Percentage discount - apply on the total after product discounts
                $totalAfterProductDiscounts = $itemsSubtotalRaw - $discountTotalRaw;
                $orderDiscountAmount = $totalAfterProductDiscounts * ($discountValue / 100);
            }

            // Calculate total after discount: original total - product discounts - promo discount
            $orderTotalAfterDiscount = $itemsSubtotalRaw - $discountTotalRaw - $orderDiscountAmount;
        }
    }
    
    // Add tax to the final amount (whether promo code exists or not)
    $taxRate = isset($order['tax_rate']) ? (float)$order['tax_rate'] : 0.0;
    $calculatedTaxAmount = 0.0;
    if ($taxRate > 0) {
        $subtotalBeforeTax = $orderTotalAfterDiscount;
        $calculatedTaxAmount = $subtotalBeforeTax * ($taxRate / 100);
        $orderTotalAfterDiscount = $subtotalBeforeTax + $calculatedTaxAmount;
    }
    
    // Add items, payment, and address to order data
    $order['items'] = $items;
    $order['payment'] = $payment;
    $order['address'] = $address;
    
    // Format dates
    $order['created_at'] = date('Y-m-d H:i:s', strtotime($order['created_at']));
    $order['updated_at'] = $order['updated_at'] ? date('Y-m-d H:i:s', strtotime($order['updated_at'])) : null;
    
    // Format currency values
    $order['total'] = number_format($order['total'], 2);
    $order['tax_amount'] = number_format($calculatedTaxAmount, 2);

    // Apply promo code discount if cart had a promocode
    $promoDiscountRaw = 0.0;
    $promocodeInfo = null;
    if (!empty($order['cart_id'])) {
        // Get cart subtotal to base promo discount on
        $cartSubtotal = null;
        $cartStmt = $conn->prepare("SELECT total FROM cart WHERE id = ?");
        $cartStmt->bind_param("i", $order['cart_id']);
        if ($cartStmt->execute()) {
            $cartRes = $cartStmt->get_result();
            if ($cartRes && $cartRes->num_rows > 0) {
                $rowCart = $cartRes->fetch_assoc();
                $cartSubtotal = isset($rowCart['total']) ? (float)$rowCart['total'] : null;
            }
        }
        $cartStmt->close();

        // Fallback to computed items subtotal if cart total missing
        if ($cartSubtotal === null) {
            $cartSubtotal = $itemsSubtotalRaw;
        }

        // Fetch any applied promo for this cart
        $promoSql = "SELECT pc.id, pc.code, pc.description, pc.discount_type, pc.discount_value, pc.min_cart_total, cpc.applied_at
                     FROM cart_promocodes cpc
                     LEFT JOIN promocodes pc ON pc.id = cpc.promocode_id
                     WHERE cpc.cart_id = ?
                     ORDER BY cpc.applied_at DESC
                     LIMIT 1";
        $ps = $conn->prepare($promoSql);
        $ps->bind_param("i", $order['cart_id']);
        if ($ps->execute()) {
            $pr = $ps->get_result();
            if ($pr && $pr->num_rows > 0) {
                $prow = $pr->fetch_assoc();
                $pType = $prow['discount_type'];
                $pValue = (float)$prow['discount_value'];
                
                // Store promocode information
                $promocodeInfo = [
                    'id' => $prow['id'],
                    'code' => $prow['code'],
                    'description' => $prow['description'],
                    'discount_type' => $prow['discount_type'],
                    'discount_value' => $prow['discount_value'],
                    'min_cart_total' => $prow['min_cart_total'],
                    'applied_at' => $prow['applied_at']
                ];
                
                if ($pType === 'percentage') {
                    $promoDiscountRaw = round($cartSubtotal * ($pValue / 100.0), 2);
                } elseif ($pType === 'fixed') {
                    $promoDiscountRaw = round(min($cartSubtotal, $pValue), 2);
                }
            }
        }
        $ps->close();
    }

    // Provide discount summary and derived totals
    $totalDiscountAmount = $discountTotalRaw + $orderDiscountAmount;
    $subtotalAfterDiscounts = $itemsSubtotalRaw - $totalDiscountAmount;
    $order['discount_amount'] = number_format($totalDiscountAmount, 2);
    $order['total_after_discount'] = number_format($subtotalAfterDiscounts, 2);
    $order['final_total'] = number_format($orderTotalAfterDiscount, 2);
    $order['items_subtotal'] = number_format($itemsSubtotalRaw, 2);
    $order['product_discounts'] = number_format($discountTotalRaw, 2);
    $order['promocode_discounts'] = number_format($orderDiscountAmount, 2);
    $order['promocode_info'] = $promocodeInfo;
    
    echo json_encode([
        'status' => 'success',
        'data' => $order
    ]);
    
} catch (Exception $e) {
    error_log("Order Details API Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch order details: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
