<?php
/**
 * API endpoint for tax calculation after discount
 * Usage: GET /api/calculate-tax.php?subtotal=1000&discount=100&tax_rate=14
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'tax-calculator.php';

try {
    // Get parameters from query string or POST data
    $subtotal = isset($_REQUEST['subtotal']) ? (float)$_REQUEST['subtotal'] : 0.0;
    $discount = isset($_REQUEST['discount']) ? (float)$_REQUEST['discount'] : 0.0;
    $taxRate = isset($_REQUEST['tax_rate']) ? (float)$_REQUEST['tax_rate'] : 0.0;
    
    // Validate input
    if ($subtotal < 0) {
        throw new Exception('Subtotal cannot be negative');
    }
    
    if ($discount < 0) {
        throw new Exception('Discount cannot be negative');
    }
    
    if ($taxRate < 0 || $taxRate > 100) {
        throw new Exception('Tax rate must be between 0 and 100');
    }
    
    // Calculate tax after discount
    $result = calculateTaxAfterDiscount($subtotal, $discount, $taxRate);
    
    // Add formatted version
    $result['formatted'] = TaxCalculator::formatCurrency($result);
    
    echo json_encode([
        'status' => 'success',
        'data' => $result
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
