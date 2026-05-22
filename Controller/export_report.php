<?php
require_once(__DIR__ . "/admin_auth.php");
require_admin_role(['admin'], "../View/php/adminlogin.php");

require_once(__DIR__ . "/../config/db.php");
require_once(__DIR__ . "/../Model/AdminReport.php");

function export_h($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function export_money($value){
    return number_format((float)$value, 2) . ' EGP';
}

function export_status_class($status){
    return 'status-' . str_replace('_', '-', (string)$status);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../View/php/report.php");
    exit();
}

$action = $_POST['action'] ?? 'preview';
$filters = AdminReport::normalizeFilters($_POST);

if ($action !== 'export') {
    $query = http_build_query([
        'preview' => 1,
        'order_type' => $filters['order_type'],
        'status' => $filters['status'],
        'date_mode' => $filters['date_mode'],
        'date_field' => $filters['date_field'],
        'date_from' => $filters['date_from'],
        'date_to' => $filters['date_to'],
        'month' => $filters['month'],
        'year' => $filters['year'],
        'last_months' => $filters['last_months']
    ]);
    header("Location: ../View/php/report.php?{$query}");
    exit();
}

$orders = AdminReport::getOrders($conn, $filters);
$summary = AdminReport::summarizeOrders($orders);
$statusLabels = AdminReport::statusLabels();
$filename = 'tamam_orders_report_' . date('Ymd_His') . '.xls';

$orderTypeLabel = [
    'all' => 'All Orders',
    'personal' => 'Personal Orders',
    'company' => 'Company Orders'
][$filters['order_type']] ?? 'All Orders';

$statusFilterLabel = $filters['status'] === 'all'
    ? 'All Statuses'
    : ($filters['status'] === 'active' ? 'Active / Not Completed' : ($statusLabels[$filters['status']] ?? ucfirst($filters['status'])));

$dateFieldLabel = [
    'auto' => 'Auto',
    'created' => 'Created Date',
    'completed' => 'Completed Date'
][$filters['date_field']] ?? 'Auto';

header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

echo "\xEF\xBB\xBF";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #111; }
        h1 { background: #111; color: #f0ce3f; padding: 14px; margin: 0 0 12px; }
        .summary { border-collapse: collapse; margin-bottom: 18px; width: 100%; }
        .summary td { border: 1px solid #ccc; padding: 8px; }
        .summary .label { background: #f3f3f3; font-weight: bold; width: 220px; }
        .orders { border-collapse: collapse; width: 100%; }
        .orders th { background: #111; color: #fff; border: 1px solid #111; padding: 9px; text-align: center; }
        .orders td { border: 1px solid #cfcfcf; padding: 8px; vertical-align: top; }
        .orders tr:nth-child(even) td { background: #f7f7f7; }
        .center { text-align: center; }
        .money { text-align: right; font-weight: bold; }
        .status { text-align: center; font-weight: bold; }
        .status-pending { background: #fff4bf; color: #6f5600; }
        .status-confirmed { background: #dbeafe; color: #1d4ed8; }
        .status-in-progress { background: #ede9fe; color: #6d28d9; }
        .status-ready { background: #dcfce7; color: #166534; }
        .status-completed { background: #22c55e; color: #fff; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <h1>Tamam Marble &amp; Granite - Orders Report</h1>

    <table class="summary">
        <tr><td class="label">Generated At</td><td><?php echo export_h(date('Y-m-d H:i:s')); ?></td></tr>
        <tr><td class="label">Report Type</td><td><?php echo export_h($orderTypeLabel); ?></td></tr>
        <tr><td class="label">Status Filter</td><td><?php echo export_h($statusFilterLabel); ?></td></tr>
        <tr><td class="label">Date Filter</td><td><?php echo export_h(AdminReport::filterDescription($filters)); ?></td></tr>
        <tr><td class="label">Date Field</td><td><?php echo export_h($dateFieldLabel); ?></td></tr>
        <tr><td class="label">Total Orders</td><td><?php echo (int)$summary['totalOrders']; ?></td></tr>
        <tr><td class="label">Completed Orders</td><td><?php echo (int)$summary['completedOrders']; ?></td></tr>
        <tr><td class="label">Active Orders</td><td><?php echo (int)$summary['activeOrders']; ?></td></tr>
        <tr><td class="label">Cancelled Orders</td><td><?php echo (int)$summary['cancelledOrders']; ?></td></tr>
        <tr><td class="label">Total Revenue</td><td><?php echo export_h(export_money($summary['totalRevenue'])); ?></td></tr>
        <tr><td class="label">Estimated Profit</td><td><?php echo export_h(export_money($summary['estimatedProfit'])); ?></td></tr>
    </table>

    <table class="orders">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Type</th>
                <th>Client / Company</th>
                <th>Items</th>
                <th>Status</th>
                <th>Total</th>
                <th>Created Date</th>
                <th>Completed Date</th>
                <th>Phone</th>
                <th>WhatsApp</th>
                <th>City</th>
                <th>Area</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
            <tr>
                <td colspan="12" class="center">No orders found for the selected filters.</td>
            </tr>
            <?php endif; ?>

            <?php foreach ($orders as $order): ?>
            <?php
                $typeLabel = $order['account_type'] === 'company' ? 'Company' : 'Personal';
                $clientName = $order['full_name'] ?: $order['user_full_name'];
                $createdDate = $order['created_date'] ? date('Y-m-d', strtotime($order['created_date'])) : '';
                $completedDate = $order['completed_date'] ? date('Y-m-d', strtotime($order['completed_date'])) : '';
                $status = $order['status'] ?: 'pending';
            ?>
            <tr>
                <td class="center">#<?php echo (int)$order['booking_id']; ?></td>
                <td class="center"><?php echo export_h($typeLabel); ?></td>
                <td><?php echo export_h($clientName); ?></td>
                <td class="center"><?php echo (int)$order['items_count']; ?></td>
                <td class="status <?php echo export_h(export_status_class($status)); ?>"><?php echo export_h($statusLabels[$status] ?? ucfirst($status)); ?></td>
                <td class="money"><?php echo export_h(export_money($order['total_amount'])); ?></td>
                <td class="center"><?php echo export_h($createdDate); ?></td>
                <td class="center"><?php echo export_h($completedDate); ?></td>
                <td><?php echo export_h($order['phone']); ?></td>
                <td><?php echo export_h($order['whatsapp']); ?></td>
                <td><?php echo export_h($order['city']); ?></td>
                <td><?php echo export_h($order['area']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
<?php
exit();
?>
