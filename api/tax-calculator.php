<?php
/**
 * Tax Calculator Utility
 * Handles tax calculations after discounts are applied
 */

class TaxCalculator {
    
    /**
     * Calculate tax after applying discounts
     * 
     * @param float $subtotal Original subtotal before any discounts
     * @param array $productDiscounts Array of product-level discounts
     * @param array $promocodeDiscounts Array of promocode discounts
     * @param float $taxRate Tax rate as percentage (e.g., 14.0 for 14%)
     * @return array Returns calculation breakdown
     */
    public static function calculateTaxAfterDiscount($subtotal, $productDiscounts = [], $promocodeDiscounts = [], $taxRate = 0.0) {
        // Initialize totals
        $totalProductDiscount = 0.0;
        $totalPromocodeDiscount = 0.0;
        
        // Calculate product-level discounts
        foreach ($productDiscounts as $discount) {
            if (isset($discount['type']) && isset($discount['value'])) {
                if ($discount['type'] === 'percentage') {
                    $totalProductDiscount += $subtotal * ($discount['value'] / 100.0);
                } elseif ($discount['type'] === 'fixed') {
                    $totalProductDiscount += $discount['value'];
                }
            }
        }
        
        // Calculate promocode discounts
        foreach ($promocodeDiscounts as $discount) {
            if (isset($discount['type']) && isset($discount['value'])) {
                $discountableAmount = $subtotal - $totalProductDiscount;
                
                if ($discount['type'] === 'percentage') {
                    $totalPromocodeDiscount += $discountableAmount * ($discount['value'] / 100.0);
                } elseif ($discount['type'] === 'fixed') {
                    $totalPromocodeDiscount += min($discountableAmount, $discount['value']);
                }
            }
        }
        
        // Calculate final amounts
        $totalDiscount = $totalProductDiscount + $totalPromocodeDiscount;
        $subtotalAfterDiscount = max(0, $subtotal - $totalDiscount);
        
        // Calculate tax on discounted amount
        $taxAmount = 0.0;
        if ($taxRate > 0) {
            $taxAmount = $subtotalAfterDiscount * ($taxRate / 100.0);
        }
        
        $finalTotal = $subtotalAfterDiscount + $taxAmount;
        
        return [
            'original_subtotal' => round($subtotal, 2),
            'product_discounts' => round($totalProductDiscount, 2),
            'promocode_discounts' => round($totalPromocodeDiscount, 2),
            'total_discounts' => round($totalDiscount, 2),
            'subtotal_after_discount' => round($subtotalAfterDiscount, 2),
            'tax_rate' => $taxRate,
            'tax_amount' => round($taxAmount, 2),
            'final_total' => round($finalTotal, 2)
        ];
    }
    
    /**
     * Calculate tax for a single order with existing discount logic
     * 
     * @param float $itemsSubtotal Subtotal of all items
     * @param float $productDiscountTotal Total product discounts
     * @param float $promocodeDiscountTotal Total promocode discounts
     * @param float $taxRate Tax rate as percentage
     * @return array Returns calculation breakdown
     */
    public static function calculateOrderTax($itemsSubtotal, $productDiscountTotal, $promocodeDiscountTotal, $taxRate) {
        $totalDiscounts = $productDiscountTotal + $promocodeDiscountTotal;
        $subtotalAfterDiscount = max(0, $itemsSubtotal - $totalDiscounts);
        
        $taxAmount = 0.0;
        if ($taxRate > 0) {
            $taxAmount = $subtotalAfterDiscount * ($taxRate / 100.0);
        }
        
        $finalTotal = $subtotalAfterDiscount + $taxAmount;
        
        return [
            'items_subtotal' => round($itemsSubtotal, 2),
            'product_discounts' => round($productDiscountTotal, 2),
            'promocode_discounts' => round($promocodeDiscountTotal, 2),
            'total_discounts' => round($totalDiscounts, 2),
            'subtotal_after_discount' => round($subtotalAfterDiscount, 2),
            'tax_rate' => $taxRate,
            'tax_amount' => round($taxAmount, 2),
            'final_total' => round($finalTotal, 2)
        ];
    }
    
    /**
     * Format currency values for display
     * 
     * @param array $calculation The calculation result array
     * @return array Formatted calculation with currency formatting
     */
    public static function formatCurrency($calculation) {
        $formatted = [];
        foreach ($calculation as $key => $value) {
            if (is_numeric($value)) {
                $formatted[$key] = number_format($value, 2);
            } else {
                $formatted[$key] = $value;
            }
        }
        return $formatted;
    }
    
    /**
     * Validate discount data
     * 
     * @param array $discounts Array of discount data
     * @return bool True if valid, false otherwise
     */
    public static function validateDiscounts($discounts) {
        if (!is_array($discounts)) {
            return false;
        }
        
        foreach ($discounts as $discount) {
            if (!isset($discount['type']) || !isset($discount['value'])) {
                return false;
            }
            
            if (!in_array($discount['type'], ['percentage', 'fixed'])) {
                return false;
            }
            
            if (!is_numeric($discount['value']) || $discount['value'] < 0) {
                return false;
            }
        }
        
        return true;
    }
}

/**
 * Standalone function for quick tax calculation
 * 
 * @param float $subtotal Original subtotal
 * @param float $totalDiscount Total discount amount
 * @param float $taxRate Tax rate as percentage
 * @return array Calculation result
 */
function calculateTaxAfterDiscount($subtotal, $totalDiscount, $taxRate) {
    $subtotalAfterDiscount = max(0, $subtotal - $totalDiscount);
    $taxAmount = 0.0;
    
    if ($taxRate > 0) {
        $taxAmount = $subtotalAfterDiscount * ($taxRate / 100.0);
    }
    
    return [
        'subtotal' => round($subtotal, 2),
        'discount' => round($totalDiscount, 2),
        'subtotal_after_discount' => round($subtotalAfterDiscount, 2),
        'tax_rate' => $taxRate,
        'tax_amount' => round($taxAmount, 2),
        'final_total' => round($subtotalAfterDiscount + $taxAmount, 2)
    ];
}
?>
