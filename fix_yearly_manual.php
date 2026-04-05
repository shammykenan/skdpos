<?php
$file = 'app/views/cashier/view_reports.php';
$content = file_get_contents($file);

// Find the yearly section and replace it with net revenue included
$yearlyStart = strpos($content, '<!-- YEARLY (admin) -->');
if ($yearlyStart === false) {
    $yearlyStart = strpos($content, '// =========================================================================');
    while ($yearlyStart !== false && strpos($content, 'YEARLY', $yearlyStart, 50) === false) {
        $yearlyStart = strpos($content, '// =========================================================================', $yearlyStart + 1);
    }
}

if ($yearlyStart !== false) {
    // Find the end of yearly section
    $yearlyEnd = strpos($content, '<?php', $yearlyStart + 100);
    if ($yearlyEnd === false) {
        $yearlyEnd = strpos($content, '// =========================================================================', $yearlyStart + 100);
    }
    
    if ($yearlyEnd !== false) {
        // Extract the yearly section
        $yearlySection = substr($content, $yearlyStart, $yearlyEnd - $yearlyStart);
        
        // Add net revenue display to the yearly section
        $yearlyWithNetRevenue = str_replace(
            "            </div>\n        </div>\n        <div class=\"stat-card c-coral\">",
            "            </div>
            <?php if ((\$s['total_refunds']??0) > 0): ?>
            <div class=\"refund-breakdown\" style=\"margin-top:8px;padding-top:8px;border-top:1px solid var(--border);\">
                <div style=\"font-size:0.75rem;color:var(--warm-gray);margin-bottom:2px;\">Less: Refunds</div>
                <div style=\"font-weight:600;color:var(--danger);\"><?=peso((float)(\$s['total_refunds']??0))?></div>
            </div>
            <?php endif; ?>
            <?php if ((\$s['net_revenue_after_refunds']??0) != (\$s['net_revenue']??0)): ?>
            <div class=\"net-revenue-breakdown\" style=\"margin-top:8px;padding-top:8px;border-top:1px solid var(--border);\">
                <div style=\"font-size:0.8rem;font-weight:600;color:var(--primary-dark);\">Net Revenue</div>
                <div style=\"font-weight:700;color:var(--success);font-size:1.1rem;\"><?=peso((float)(\$s['net_revenue_after_refunds']??0))?></div>
            </div>
            <?php endif; ?>
        </div>
        <div class=\"stat-card c-coral\">",
            $yearlySection
        );
        
        // Replace the yearly section in the content
        $newContent = substr_replace($content, $yearlyWithNetRevenue, $yearlyStart, $yearlyEnd - $yearlyStart);
        
        file_put_contents($file, $newContent);
        echo "Successfully added net revenue display to yearly report!\n";
    } else {
        echo "Could not find end of yearly section.\n";
    }
} else {
    echo "Could not find yearly section.\n";
}
?>
