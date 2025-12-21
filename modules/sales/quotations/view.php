<?php
// session_start(); // Handled in config.php
require_once '../../../config/config.php';
require_once '../../../classes/Auth.php';
require_once '../../../classes/Database.php';

$auth = new Auth();
// Auth::enforceGlobalRouteSecurity() handles permissions.
$user = $auth->getCurrentUser();
$db = Database::getInstance();

// Get quotation ID from URL
$quotationId = $_GET['id'] ?? null;

if (!$quotationId) {
    header('Location: index.php');
    exit;
}

// Fetch company settings
$companySettings = $db->fetchOne("SELECT * FROM company_settings WHERE id = ?", [$user['company_id']]);

if (!$companySettings) {
    // Default fallback if no settings found
    $companySettings = [
        'company_name' => APP_NAME,
        'address_line1' => 'Your Company Address',
        'city' => 'City',
        'state' => 'State',
        'country' => 'India',
        'gstin' => 'GSTIN Number',
        'pan' => 'PAN Number',
        'bank_name' => 'Bank Name',
        'bank_account_number' => 'Account Number',
        'bank_ifsc' => 'IFSC Code',
        'bank_branch' => 'Branch',
        'bank_account_holder' => APP_NAME,
        'terms_conditions' => 'Terms and Conditions',
        'invoice_footer' => 'Invoice Footer'
    ];
}

// Fetch quotation details
// We get customer state from customer_addresses for Place of Supply/Tax calc
$quotation = $db->fetchOne("
    SELECT q.*, 
           c.company_name, c.gstin as customer_gstin, c.pan as customer_pan, c.email, c.phone, c.contact_person,
           ca.address_line1, ca.address_line2, ca.city, ca.state, ca.country, ca.postal_code,
           u.full_name as created_by_name
    FROM quotations q
    JOIN customers c ON q.customer_id = c.id
    LEFT JOIN customer_addresses ca ON c.id = ca.customer_id AND ca.is_default = 1
    LEFT JOIN users u ON q.created_by = u.id
    WHERE q.id = ?
", [$quotationId]);

if (!$quotation) {
    header('Location: index.php');
    exit;
}

// Fetch quotation items
$items = $db->fetchAll("
    SELECT qi.*, p.name as product_name, p.hsn_code, u.symbol as uom
    FROM quotation_items qi
    JOIN products p ON qi.product_id = p.id
    LEFT JOIN units_of_measure u ON p.uom_id = u.id
    WHERE qi.quotation_id = ?
", [$quotationId]);

// Determine Tax Type (Intra-state or Inter-state)
$companyState = strtolower(trim($companySettings['state'] ?? ''));
// Quotations might not store 'state' directly, use customer address state
$customerState = strtolower(trim($quotation['state'] ?? '')); 
$isIntraState = ($companyState === $customerState);

// Calculate Totals (Pre-calculation for Header/Footer)
$totalTaxable = 0;
$totalCGST = 0;
$totalSGST = 0;
$totalIGST = 0;

$taxDetails = []; // To store tax wise breakdown

foreach ($items as $item) {
    // Quotation items might not have 'line_total' stored, calculate if needed
    $quantity = floatval($item['quantity'] ?? 0);
    $unitPrice = floatval($item['unit_price'] ?? 0);
    $taxRate = floatval($item['tax_rate'] ?? 0);
    $discountPercent = floatval($item['discount_percent'] ?? 0);
    
    // Calculate line total if not present (logic from create.php)
    // lineSubtotal = qty * price
    // discount = lineSubtotal * (disc/100)
    // afterDisc = lineSubtotal - discount
    // tax = afterDisc * (tax/100)
    // total = afterDisc + tax
    
    $lineSubtotal = $quantity * $unitPrice;
    $discountAmount = $lineSubtotal * ($discountPercent / 100);
    $lineAfterDiscount = $lineSubtotal - $discountAmount;
    $lineTax = $lineAfterDiscount * ($taxRate / 100);
    $lineTotal = $lineAfterDiscount + $lineTax; // This is the Gross Total for the line

    // Back-calculate Taxable Value from the Line Total for breakdown consistency
    // However, since we calculated forward, taxableValue is simply $lineAfterDiscount
    $taxableValue = $lineAfterDiscount;
    $taxAmount = $lineTax;
    
    $totalTaxable += $taxableValue;
    
    if ($isIntraState) {
        $cgstAmount = $taxAmount / 2;
        $sgstAmount = $taxAmount / 2;
        $totalCGST += $cgstAmount;
        $totalSGST += $sgstAmount;
    } else {
        $igstAmount = $taxAmount;
        $totalIGST += $igstAmount;
    }

    // Accumulate tax details
    $rateKey = (string)$taxRate;
    if (!isset($taxDetails[$rateKey])) {
        $taxDetails[$rateKey] = [
            'rate' => $taxRate,
            'taxable' => 0,
            'cgst' => 0,
            'sgst' => 0,
            'igst' => 0,
            'tax' => 0
        ];
    }
    $taxDetails[$rateKey]['taxable'] += $taxableValue;
    $taxDetails[$rateKey]['tax'] += $taxAmount;
    if ($isIntraState) {
        $taxDetails[$rateKey]['cgst'] += $taxAmount/2;
        $taxDetails[$rateKey]['sgst'] += $taxAmount/2;
    } else {
        $taxDetails[$rateKey]['igst'] += $taxAmount;
    }
}

// Generate Tax Summary String
$taxSummaryParts = [];
foreach ($taxDetails as $rate => $detail) {
    $part = "Sale @{$rate}% = " . number_format($detail['taxable'], 2);
    if ($isIntraState) {
        $part .= ", CGST = " . number_format($detail['cgst'], 2);
        $part .= ", SGST = " . number_format($detail['sgst'], 2);
    } else {
        $part .= ", IGST = " . number_format($detail['igst'], 2);
    }
    $taxSummaryParts[] = $part;
}
$taxSummaryString = implode(" | ", $taxSummaryParts);


// Function to convert number to words
function numberToWords($number) {
    $ones = array('', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen');
    $tens = array('', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety');
    
    if ($number < 20) {
        return $ones[$number];
    } elseif ($number < 100) {
        return $tens[intval($number / 10)] . ' ' . $ones[$number % 10];
    } elseif ($number < 1000) {
        return $ones[intval($number / 100)] . ' Hundred ' . numberToWords($number % 100);
    } elseif ($number < 100000) {
        return numberToWords(intval($number / 1000)) . ' Thousand ' . numberToWords($number % 1000);
    } elseif ($number < 10000000) {
        return numberToWords(intval($number / 100000)) . ' Lakh ' . numberToWords($number % 100000);
    } else {
        return numberToWords(intval($number / 10000000)) . ' Crore ' . numberToWords($number % 10000000);
    }
}

$amountInWords = 'Rupees ' . trim(numberToWords(intval($quotation['total_amount']))) . ' Only';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation - <?php echo $quotation['quotation_number']; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        @page {
            size: A4;
            margin: 10mm;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #000;
            background: #fff;
        }
        
        /* The main box */
        .main-container {
            border: 1px solid #000;
            width: 100%;
            max-width: 210mm; /* A4 Width */
            margin: 0 auto;
        }

        /* Generic Table reset */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        td, th {
            border: 1px solid #000;
            padding: 4px; /* Comfortable padding */
            vertical-align: top;
        }

        /* Helpers */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .no-border { border: none !important; }

        .top-header-row td {
            font-weight: bold;
            border-bottom: 1px solid #000;
        }

        /* 2. Company Section */
        .company-section {
            text-align: center;
            padding: 8px;
            border-bottom: 1px solid #000;
        }
        .company-name { font-size: 14pt; font-weight: bold; margin-bottom: 5px; }
        .company-addr { font-size: 9pt; }

        /* 3. Info Grid */
        .billing-header {
            font-weight: bold;
            padding: 2px 5px;
            border-bottom: 1px solid #000;
        }
        
        .info-cell {
            width: 50%;
            padding: 0;
            vertical-align: top;
        }
        
        /* Nesting for Info to ensure alignment */
        .info-table td { border: none; padding: 2px 5px; } 
        .info-table tr td:first-child { font-weight: bold; width: 110px; } /* Labels */
        
        /* 4. Items Table Header */
        .items-header th {
            text-align: center;
            font-weight: bold;
        }
        
        /* Items Container - Fixed Height */
        .items-container {
            height: 500px; /* Approx 13-14cm */
            border-bottom: 1px solid #000; 
            overflow: hidden; /* Ensure no scrollbars print */
        }
        
        /* Items Table Specifics */
        .items-table { height: 100%; }
        .items-table th { border-bottom: 1px solid #000; height: 30px; }
        .items-table td { 
            border-bottom: none; 
            border-top: none; 
            padding: 4px;
        }
        /* Vertical lines */
        .items-table td { border-right: 1px solid #000; }
        .items-table td:last-child { border-right: none; }
        
        /* Filler Row logic */
        .filler-row td { height: 100%; }

        /* 5. Total Bar */
        .total-bar {
            font-weight: bold;
            text-align: right;
            border-bottom: 1px solid #000;
            padding: 5px;
        }
        
        /* 6. Footer (Amount Words + Tax + Terms + Bank) */
        .amount-words-row {
            border-bottom: 1px solid #000;
            padding: 5px;
            font-weight: bold;
        }
        
        .footer-grid-td { width: 50%; padding: 0; }
        
        .footer-headers {
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding: 2px 5px;
        }
        
        .terms-content { padding: 5px; font-size: 9pt; height: 100px; }
        
        /* Signature */
        .signature-block {
            text-align: right; 
            padding: 5px; 
            height: 100%; 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between;
        }

        /* Print Stuff */
        @media print {
            .print-actions { display: none; }
            body { margin: 0; background-color: #fff; -webkit-print-color-adjust: exact; }
            .main-container { border: 1px solid #000; width: 100%; margin: 0; }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button onclick="window.print()" class="btn" style="padding:10px; background:#000; color:#fff; cursor:pointer;">Print Quotation</button>
        <a href="index.php" class="btn" style="padding:10px; background:#000; color:#fff; text-decoration:none;">Back</a>
    </div>

    <div class="main-container">
        <!-- 1. Top Strip -->
        <table class="no-border">
            <tr class="top-header-row">
                <td style="width: 20%; border-right: none;">Page 1</td>
                <td style="width: 60%; text-align: center; border-left: none; border-right: none;" class="uppercase">Quotation</td>
                <td style="width: 20%; text-align: right; border-left: none;">Original Copy</td>
            </tr>
        </table>

        <!-- 2. Company Header -->
        <div class="company-section">
            <div class="company-name"><?php echo htmlspecialchars($companySettings['company_name']); ?></div>
            <div class="company-addr">
                <?php echo htmlspecialchars($companySettings['address_line1']); ?>, <?php echo htmlspecialchars($companySettings['city']); ?>, <?php echo htmlspecialchars($companySettings['state']); ?> - <?php echo htmlspecialchars($companySettings['postal_code'] ?? ''); ?><br>
                GSTIN - <?php echo htmlspecialchars($companySettings['gstin']); ?> | PAN - <?php echo htmlspecialchars($companySettings['pan'] ?? ''); ?>
            </div>
        </div>

        <!-- 3. Billing & INFO Grid -->
        <table class="info-table">
            <tr>
                <!-- Left: Billing Details -->
                <td class="info-cell" style="border-right: 1px solid #000;">
                    <div class="billing-header">Billing Details</div>
                    <div style="padding: 5px;">
                        <div class="text-bold"><?php echo htmlspecialchars(trim($quotation['company_name']) ?: $quotation['contact_person']); ?></div>
                        <div>GSTIN: <?php echo htmlspecialchars($quotation['customer_gstin'] ?? 'N/A'); ?></div>
                        <div style="margin-top: 5px;">
                            Address: <?php 
                                $addrParts = [
                                    $quotation['address_line1'] ?? '',
                                    $quotation['address_line2'] ?? '',
                                    $quotation['city'] ?? '',
                                    $quotation['state'] ?? '',
                                    $quotation['postal_code'] ?? ''
                                ];
                                echo htmlspecialchars(implode(', ', array_filter(array_map('trim', $addrParts)))); 
                            ?>
                        </div>
                    </div>
                </td>
                
                <!-- Right: Quotation Info -->
                <td class="info-cell">
                    <table class="no-border">
                        <!-- Quotation No -->
                        <tr>
                            <td class="bg-peach" style="width: 120px; font-weight: bold; border-bottom:1px solid #000; border-right:1px solid #000;">Quotation No</td>
                            <td style="border-bottom:1px solid #000;"> <?php echo htmlspecialchars($quotation['quotation_number']); ?></td>
                        </tr>
                        <!-- Date -->
                        <tr>
                            <td class="bg-peach" style="width: 120px; font-weight: bold; border-bottom:1px solid #000; border-right:1px solid #000;">Date</td>
                            <td style="border-bottom:1px solid #000;"> <?php echo date('d-M-y', strtotime($quotation['quotation_date'])); ?></td>
                        </tr>
                        <!-- Valid Until -->
                        <tr>
                            <td class="bg-peach" style="width: 120px; font-weight: bold; border-bottom:1px solid #000; border-right:1px solid #000;">Valid Until</td>
                            <td style="border-bottom:1px solid #000;"> <?php echo date('d-M-y', strtotime($quotation['valid_until'])); ?></td>
                        </tr>
                        <!-- Place of Supply -->
                        <tr>
                            <td class="bg-peach" style="width: 120px; font-weight: bold; border-bottom:1px solid #000; border-right:1px solid #000;">Place of Supply</td>
                            <td style="border-bottom:1px solid #000;"> <?php echo htmlspecialchars($quotation['state'] ?? '-'); ?></td>
                        </tr>
                        <!-- Carrier Info (Conditional) -->
                        <?php if(!empty($quotation['courier_name'])): ?>
                        <tr>
                            <td class="bg-peach" style="width: 120px; font-weight: bold; border-bottom:1px solid #000; border-right:1px solid #000;">Carrier</td>
                            <td style="border-bottom:1px solid #000;"> <?php echo htmlspecialchars($quotation['courier_name']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <!-- Tracking ID (Conditional) -->
                        <?php if(!empty($quotation['tracking_id'])): ?>
                        <tr>
                            <td class="bg-peach" style="width: 120px; font-weight: bold; border-bottom:1px solid #000; border-right:1px solid #000;">Tracking ID</td>
                            <td style="border-bottom:1px solid #000;"> <?php echo htmlspecialchars($quotation['tracking_id']); ?></td>
                        </tr>
                        <?php endif; ?>
                        <!-- Rev Charge -->
                        <tr>
                            <td style="width: 120px; font-weight: bold; border-right:1px solid #000;">Reverse Charge</td>
                            <td> No</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- 4. Items Section -->
        <div class="items-container" style="height: 105mm;"> <!-- 105mm for detailed totals -->
            <table class="items-table">
                <tr class="items-header">
                    <th style="width: 5%;">Sr.</th>
                    <th style="width: 35%;">Item Description</th>
                    <th style="width: 10%;">HSN/SAC</th>
                    <th style="width: 8%;">Qty</th>
                    <th style="width: 7%;">Unit</th>
                    <th style="width: 10%;">Rate</th>
                    <th style="width: 8%;">Disc</th>
                    <th style="width: 7%;">GST</th>
                    <th style="width: 10%;">Amount (₹)</th>
                </tr>
                
                <?php 
                $sr = 1;
                foreach ($items as $item): 
                    // Calculate totals on the fly if needed
                    $qty = floatval($item['quantity'] ?? 0);
                    $price = floatval($item['unit_price'] ?? 0);
                    $disc = floatval($item['discount_percent'] ?? 0);
                    $tax = floatval($item['tax_rate'] ?? 0);
                    
                    $sub = $qty * $price;
                    $subAfterDisc = $sub - ($sub * $disc / 100);
                    $lineTotal = $subAfterDisc + ($subAfterDisc * $tax / 100);
                ?>
                <tr class="items-row">
                    <td class="text-center"><?php echo $sr++; ?></td>
                    <td style="text-align: left;">
                        <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                    </td>
                    <td class="text-center"><?php echo htmlspecialchars($item['hsn_code'] ?? ''); ?></td>
                    <td class="text-center"><?php echo floatval($qty); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($item['uom'] ?? 'N.A.'); ?></td>
                    <td class="text-right"><?php echo number_format($price, 2); ?></td>
                    <td class="text-center"><?php echo ($disc > 0) ? floatval($disc).'%' : ''; ?></td>
                    <td class="text-center"><?php echo floatval($tax); ?>%</td>
                    <td class="text-right"><?php echo number_format($lineTotal, 2); ?></td>
                </tr>
                <?php endforeach; ?>
                
                <!-- Filler Row -->
                <tr class="filler-row">
                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                </tr>
            </table>
        </div>
        
        <!-- 5. Detailed Totals & Bottom Info -->
        <div style="border-bottom: 1px solid #000; overflow: hidden;">
            <!-- Left Side: Amount Words & Tax Summary -->
            <div style="width: 60%; float: left; border-right: 1px solid #000; height: 100%;">
                 <div class="amount-words-row" style="border-bottom: 1px solid #000; padding: 5px; font-weight: bold;">
                    Rs. <?php echo ucwords(strtolower(trim(numberToWords(intval($quotation['total_amount']))))); ?> only
                </div>
                <!-- Tax Summary -->
                <div style="padding: 5px; font-size: 8pt; background-color: #eee;">
                     <?php echo $taxSummaryString; ?> <br> Total Tax = <?php echo number_format($totalCGST + $totalSGST + $totalIGST, 2); ?>
                </div>

                <!-- Bank Details (Moved from Footer) -->
                <div style="padding: 5px; border-top: 1px solid #000; font-size: 9pt;">
                    <div style="font-weight: bold; margin-bottom: 2px;">Account Number: <?php echo htmlspecialchars($companySettings['bank_account_number']); ?></div>
                    <strong>Bank:</strong> <?php echo htmlspecialchars($companySettings['bank_name']); ?> <br>
                    <strong>IFSC:</strong> <?php echo htmlspecialchars($companySettings['bank_ifsc']); ?> <br>
                    <strong>Branch:</strong> <?php echo htmlspecialchars($companySettings['bank_branch']); ?>
                </div>
            </div>

            <!-- Right Side: Totals -->
            <table style="width: 40%; float: right; border-collapse: collapse;">
                <tr>
                    <td style="text-align: right; padding: 2px 5px; border-bottom: 1px solid #000;">Subtotal</td>
                    <td style="text-align: right; padding: 2px 5px; border-bottom: 1px solid #000; font-weight: bold;"><?php echo number_format($quotation['subtotal'] ?? $totalTaxable, 2); ?></td>
                </tr>
                <!-- Taxes -->
                <?php if($totalCGST > 0): ?>
                <tr>
                    <td style="text-align: right; padding: 2px 5px; border-bottom: 1px solid #000;">CGST</td>
                    <td style="text-align: right; padding: 2px 5px; border-bottom: 1px solid #000; font-weight: bold;"><?php echo number_format($totalCGST, 2); ?></td>
                </tr>
                <tr>
                    <td style="text-align: right; padding: 2px 5px; border-bottom: 1px solid #000;">SGST</td>
                    <td style="text-align: right; padding: 2px 5px; border-bottom: 1px solid #000; font-weight: bold;"><?php echo number_format($totalSGST, 2); ?></td>
                </tr>
                <?php endif; ?>
                <?php if($totalIGST > 0): ?>
                <tr>
                    <td style="text-align: right; padding: 2px 5px; border-bottom: 1px solid #000;">IGST</td>
                    <td style="text-align: right; padding: 2px 5px; border-bottom: 1px solid #000; font-weight: bold;"><?php echo number_format($totalIGST, 2); ?></td>
                </tr>
                <?php endif; ?>
                
                <tr>
                    <td style="text-align: right; padding: 2px 5px; border-bottom: 1px solid #000;">Total Tax</td>
                    <td style="text-align: right; padding: 2px 5px; border-bottom: 1px solid #000; font-weight: bold;"><?php echo number_format($totalCGST + $totalSGST + $totalIGST, 2); ?></td>
                </tr>

                <?php if(!empty($quotation['shipping_charges']) && $quotation['shipping_charges'] > 0): ?>
                <tr>
                    <td style="text-align: right; padding: 2px 5px; border-bottom: 1px solid #000;">Shipping Charges</td>
                    <td style="text-align: right; padding: 2px 5px; border-bottom: 1px solid #000; font-weight: bold;"><?php echo number_format($quotation['shipping_charges'], 2); ?></td>
                </tr>
                <?php endif; ?>

                <tr class="bg-peach">
                    <td style="text-align: right; padding: 4px 5px; border-bottom: 1px solid #000; font-weight: bold;">Total</td>
                    <td style="text-align: right; padding: 4px 5px; border-bottom: 1px solid #000; font-weight: bold;"><?php echo number_format($quotation['total_amount'], 2); ?></td>
                </tr>
            </table>
            <div style="clear: both;"></div>
        </div>

        <!-- 7. Footer Grid (Terms/Bank | Signature) -->
        <table class="info-table">
            <tr>
                <td class="footer-grid-td" style="width: 75%; border-right: 1px solid #000;">
                    <table style="width: 100%;">
                        <tr>
                            <td class="footer-headers" style="width: 100%;">Terms and Conditions</td>
                        </tr>
                        <tr>
                            <td class="terms-content" style="vertical-align: top;">
                                E & O.E.<br>
                                <?php 
                                $terms = explode("\n", $companySettings['terms_conditions'] ?? '');
                                $i = 1;
                                foreach($terms as $term) { if(trim($term)) echo $i++ . ". " . htmlspecialchars(trim($term)) . "<br>"; }
                                ?>
                            </td>
                        </tr>
                    </table>
                </td>
                
                <td class="footer-grid-td" style="width: 25%;">
                     <div class="signature-block">
                        <div style="font-weight: bold;">For <?php echo htmlspecialchars($companySettings['company_name']); ?></div>
                        <div style="font-weight: bold;">Authorized Signatory</div>
                     </div>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
