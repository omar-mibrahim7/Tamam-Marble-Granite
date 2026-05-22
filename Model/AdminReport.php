<?php
class AdminReport {

    public static function statusLabels(){
        return [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'in_progress' => 'In Progress',
            'ready' => 'Ready',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled'
        ];
    }

    public static function normalizeFilters($input){
        $orderType = $input['order_type'] ?? 'all';
        if (!in_array($orderType, ['all', 'personal', 'company'], true)) {
            $orderType = 'all';
        }

        $status = $input['status'] ?? 'all';
        $allowedStatuses = array_merge(['all', 'active'], array_keys(self::statusLabels()));
        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'all';
        }

        $dateMode = $input['date_mode'] ?? 'all';
        if (!in_array($dateMode, ['all', 'range', 'month', 'last_months'], true)) {
            $dateMode = 'all';
        }

        $dateField = $input['date_field'] ?? 'auto';
        if (!in_array($dateField, ['auto', 'created', 'completed'], true)) {
            $dateField = 'auto';
        }

        $dateFrom = null;
        $dateTo = null;

        if ($dateMode === 'range') {
            $dateFrom = self::validDate($input['date_from'] ?? '') ? $input['date_from'] : null;
            $dateTo = self::validDate($input['date_to'] ?? '') ? $input['date_to'] : null;
        } elseif ($dateMode === 'month') {
            $month = (int)($input['month'] ?? date('n'));
            $year = (int)($input['year'] ?? date('Y'));
            if ($month >= 1 && $month <= 12 && $year >= 2000 && $year <= 2100) {
                $dateFrom = sprintf('%04d-%02d-01', $year, $month);
                $dateTo = date('Y-m-t', strtotime($dateFrom));
            }
        } elseif ($dateMode === 'last_months') {
            $lastMonths = (int)($input['last_months'] ?? 3);
            $lastMonths = max(1, min($lastMonths, 24));
            $dateFrom = date('Y-m-01', strtotime("-{$lastMonths} months"));
            $dateTo = date('Y-m-d');
        }

        return [
            'order_type' => $orderType,
            'status' => $status,
            'date_mode' => $dateMode,
            'date_field' => $dateField,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'month' => (int)($input['month'] ?? date('n')),
            'year' => (int)($input['year'] ?? date('Y')),
            'last_months' => (int)($input['last_months'] ?? 3)
        ];
    }

    private static function validDate($value){
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$value)) {
            return false;
        }

        $parts = explode('-', $value);
        return checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0]);
    }

    private static function columnExists($conn, $tableName, $columnName){
        $stmt = mysqli_prepare(
            $conn,
            "SELECT COUNT(*) AS total
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?"
        );
        mysqli_stmt_bind_param($stmt, "ss", $tableName, $columnName);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return (int)($row['total'] ?? 0) > 0;
    }

    private static function dateColumns($conn, $filters){
        $createdColumn = self::columnExists($conn, 'booking_requests', 'created_at') ? 'br.created_at' : 'br.booking_date';
        $completedColumn = self::columnExists($conn, 'booking_requests', 'completed_at') ? 'br.completed_at' : 'br.booking_date';
        $filterColumn = 'br.booking_date';

        if ($filters['date_field'] === 'created') {
            $filterColumn = $createdColumn;
        } elseif ($filters['date_field'] === 'completed') {
            $filterColumn = $completedColumn;
        } elseif ($filters['status'] === 'completed') {
            $filterColumn = $completedColumn;
        }

        return [
            'created' => $createdColumn,
            'completed' => $completedColumn,
            'filter' => $filterColumn
        ];
    }

    private static function bindParams($stmt, $types, &$params){
        if ($types === '') {
            return;
        }

        $bind = [$stmt, $types];
        foreach ($params as $key => $value) {
            $bind[] = &$params[$key];
        }

        call_user_func_array('mysqli_stmt_bind_param', $bind);
    }

    public static function getOrders($conn, $filters = [], $limit = null){
        $filters = self::normalizeFilters($filters);
        $dateColumns = self::dateColumns($conn, $filters);
        $where = [];
        $params = [];
        $types = '';

        if ($filters['order_type'] === 'personal') {
            $where[] = "COALESCE(u.account_type, 'customer') = ?";
            $params[] = 'customer';
            $types .= 's';
        } elseif ($filters['order_type'] === 'company') {
            $where[] = "COALESCE(u.account_type, 'customer') = ?";
            $params[] = 'company';
            $types .= 's';
        }

        if ($filters['status'] === 'active') {
            $where[] = "br.status <> 'completed'";
        } elseif ($filters['status'] !== 'all') {
            $where[] = "br.status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }

        if ($filters['date_from']) {
            $where[] = "DATE({$dateColumns['filter']}) >= ?";
            $params[] = $filters['date_from'];
            $types .= 's';
        }

        if ($filters['date_to']) {
            $where[] = "DATE({$dateColumns['filter']}) <= ?";
            $params[] = $filters['date_to'];
            $types .= 's';
        }

        $whereSql = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);
        $limitSql = '';
        if ($limit !== null) {
            $limit = max(1, min((int)$limit, 5000));
            $limitSql = " LIMIT {$limit}";
        }

        $sql = "SELECT
                    br.booking_id,
                    br.booking_date,
                    {$dateColumns['created']} AS created_date,
                    CASE WHEN br.status = 'completed' THEN {$dateColumns['completed']} ELSE NULL END AS completed_date,
                    {$dateColumns['filter']} AS report_date,
                    br.status,
                    br.full_name,
                    br.phone,
                    br.whatsapp,
                    br.city,
                    br.area,
                    COALESCE(u.account_type, 'customer') AS account_type,
                    u.full_name AS user_full_name,
                    COUNT(bri.booking_request_item_id) AS items_count,
                    COALESCE(SUM(bri.quantity * COALESCE(p.price, 0)), 0) AS total_amount
                FROM booking_requests br
                INNER JOIN users u ON u.user_id = br.customer_id
                LEFT JOIN booking_request_items bri ON bri.booking_id = br.booking_id
                LEFT JOIN products p ON p.product_id = bri.product_id
                {$whereSql}
                GROUP BY
                    br.booking_id,
                    br.booking_date,
                    {$dateColumns['created']},
                    {$dateColumns['completed']},
                    {$dateColumns['filter']},
                    br.status,
                    br.full_name,
                    br.phone,
                    br.whatsapp,
                    br.city,
                    br.area,
                    u.account_type,
                    u.full_name
                ORDER BY {$dateColumns['filter']} DESC, br.booking_id DESC
                {$limitSql}";

        $stmt = mysqli_prepare($conn, $sql);
        self::bindParams($stmt, $types, $params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $orders = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }

        mysqli_stmt_close($stmt);
        return $orders;
    }

    public static function getStats($conn){
        $orders = self::getOrders($conn, ['order_type' => 'all', 'status' => 'all', 'date_mode' => 'all', 'date_field' => 'auto']);
        $stats = [
            'personalOrders' => 0,
            'companyOrders' => 0,
            'completedOrders' => 0,
            'pendingOrders' => 0,
            'personalRevenue' => 0,
            'companyRevenue' => 0,
            'totalRevenue' => 0,
            'netProfit' => 0,
            'totalOrders' => count($orders),
            'activeOrders' => 0
        ];

        foreach ($orders as $order) {
            $amount = (float)$order['total_amount'];
            $isCompany = $order['account_type'] === 'company';

            if ($isCompany) {
                $stats['companyOrders']++;
                $stats['companyRevenue'] += $amount;
            } else {
                $stats['personalOrders']++;
                $stats['personalRevenue'] += $amount;
            }

            if ($order['status'] === 'completed') {
                $stats['completedOrders']++;
            } else {
                $stats['pendingOrders']++;
                $stats['activeOrders']++;
            }

            $stats['totalRevenue'] += $amount;
        }

        $stats['netProfit'] = $stats['totalRevenue'] * 0.35;
        return $stats;
    }

    public static function summarizeOrders($orders){
        $summary = [
            'totalOrders' => count($orders),
            'completedOrders' => 0,
            'activeOrders' => 0,
            'cancelledOrders' => 0,
            'totalRevenue' => 0,
            'estimatedProfit' => 0
        ];

        foreach ($orders as $order) {
            $status = $order['status'] ?? '';
            $amount = (float)($order['total_amount'] ?? 0);

            if ($status === 'completed') {
                $summary['completedOrders']++;
            } elseif ($status === 'cancelled') {
                $summary['cancelledOrders']++;
            } else {
                $summary['activeOrders']++;
            }

            if ($status !== 'cancelled') {
                $summary['totalRevenue'] += $amount;
            }
        }

        $summary['estimatedProfit'] = $summary['totalRevenue'] * 0.35;
        return $summary;
    }

    public static function filterDescription($filters){
        $filters = self::normalizeFilters($filters);

        if ($filters['date_mode'] === 'range') {
            return 'Range: ' . ($filters['date_from'] ?: '-') . ' to ' . ($filters['date_to'] ?: '-');
        }

        if ($filters['date_mode'] === 'month') {
            return 'Month: ' . sprintf('%02d/%04d', $filters['month'], $filters['year']);
        }

        if ($filters['date_mode'] === 'last_months') {
            return 'Last ' . (int)$filters['last_months'] . ' months';
        }

        return 'All dates';
    }

    public static function getMonthlyPerformance($conn, $months = 6){
        $months = max(1, min((int)$months, 24));
        $stmt = mysqli_prepare(
            $conn,
            "SELECT
                DATE_FORMAT(br.booking_date, '%Y-%m') AS month_label,
                COUNT(DISTINCT br.booking_id) AS orders_count,
                COALESCE(SUM(bri.quantity * COALESCE(p.price, 0)), 0) AS revenue
             FROM booking_requests br
             LEFT JOIN booking_request_items bri ON bri.booking_id = br.booking_id
             LEFT JOIN products p ON p.product_id = bri.product_id
             WHERE br.booking_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
             GROUP BY DATE_FORMAT(br.booking_date, '%Y-%m')
             ORDER BY month_label DESC"
        );
        mysqli_stmt_bind_param($stmt, "i", $months);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [
                'month' => $row['month_label'],
                'orders' => (int)$row['orders_count'],
                'revenue' => (float)$row['revenue']
            ];
        }

        mysqli_stmt_close($stmt);
        return $rows;
    }
}
?>
