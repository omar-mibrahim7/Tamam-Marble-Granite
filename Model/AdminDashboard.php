<?php
class AdminDashboard {

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

    private static function scalar($conn, $sql, $types = '', $params = []){
        $stmt = mysqli_prepare($conn, $sql);

        if ($types !== '') {
            $bind = [$stmt, $types];
            foreach ($params as $key => $value) {
                $bind[] = &$params[$key];
            }
            call_user_func_array('mysqli_stmt_bind_param', $bind);
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return array_values($row ?: [0])[0] ?? 0;
    }

    public static function getRevenueStats($conn){
        return (float)self::scalar(
            $conn,
            "SELECT COALESCE(SUM(bri.quantity * COALESCE(p.price, 0)), 0) AS total
             FROM booking_request_items bri
             LEFT JOIN products p ON p.product_id = bri.product_id
             LEFT JOIN booking_requests br ON br.booking_id = bri.booking_id
             WHERE br.status <> 'cancelled' OR br.status IS NULL"
        );
    }

    public static function getProfitStats($conn){
        if (self::columnExists($conn, 'products', 'cost_price')) {
            return (float)self::scalar(
                $conn,
                "SELECT COALESCE(SUM(bri.quantity * (COALESCE(p.price, 0) - COALESCE(p.cost_price, 0))), 0) AS total
                 FROM booking_request_items bri
                 LEFT JOIN products p ON p.product_id = bri.product_id
                 LEFT JOIN booking_requests br ON br.booking_id = bri.booking_id
                 WHERE br.status <> 'cancelled' OR br.status IS NULL"
            );
        }

        return self::getRevenueStats($conn) * 0.35;
    }

    public static function getSummaryStats($conn){
        $stats = [
            'totalRevenue' => self::getRevenueStats($conn),
            'estimatedProfit' => self::getProfitStats($conn),
            'pendingRequests' => (int)self::scalar($conn, "SELECT COUNT(*) FROM booking_requests WHERE status = 'pending'"),
            'completedRequests' => (int)self::scalar($conn, "SELECT COUNT(*) FROM booking_requests WHERE status = 'completed'"),
            'cancelledRequests' => (int)self::scalar($conn, "SELECT COUNT(*) FROM booking_requests WHERE status = 'cancelled'"),
            'activeOrders' => (int)self::scalar($conn, "SELECT COUNT(*) FROM booking_requests WHERE status NOT IN ('completed','cancelled')"),
            'totalBookingRequests' => (int)self::scalar($conn, "SELECT COUNT(*) FROM booking_requests"),
            'totalProducts' => (int)self::scalar($conn, "SELECT COUNT(*) FROM products WHERE COALESCE(is_deleted, 0) = 0"),
            'totalCustomers' => (int)self::scalar($conn, "SELECT COUNT(*) FROM users WHERE role = 'customer'"),
            'totalStaffAdmins' => (int)self::scalar($conn, "SELECT COUNT(*) FROM users WHERE role IN ('admin','staff')"),
            'lowStockProducts' => (int)self::scalar($conn, "SELECT COUNT(*) FROM products WHERE COALESCE(is_deleted, 0) = 0 AND COALESCE(stock, 0) <= 10"),
            'escalatedMessages' => 0
        ];

        if (self::columnExists($conn, 'contact_messages', 'is_escalated')) {
            $stats['escalatedMessages'] = (int)self::scalar($conn, "SELECT COUNT(*) FROM contact_messages WHERE is_escalated = 1");
        }

        return $stats;
    }

    private static function monthKeys($months){
        $months = max(1, min((int)$months, 24));
        $keys = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $keys[] = date('Y-m', strtotime("-{$i} months"));
        }

        return $keys;
    }

    public static function getMonthlyRevenue($conn, $months = 6){
        $keys = self::monthKeys($months);
        $data = array_fill_keys($keys, 0.0);
        $months = count($keys);

        $stmt = mysqli_prepare(
            $conn,
            "SELECT DATE_FORMAT(br.booking_date, '%Y-%m') AS month_key,
                    COALESCE(SUM(bri.quantity * COALESCE(p.price, 0)), 0) AS revenue
             FROM booking_requests br
             LEFT JOIN booking_request_items bri ON bri.booking_id = br.booking_id
             LEFT JOIN products p ON p.product_id = bri.product_id
             WHERE br.booking_date >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL ? MONTH), '%Y-%m-01')
               AND br.status <> 'cancelled'
             GROUP BY DATE_FORMAT(br.booking_date, '%Y-%m')"
        );
        mysqli_stmt_bind_param($stmt, "i", $months);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            if (isset($data[$row['month_key']])) {
                $data[$row['month_key']] = (float)$row['revenue'];
            }
        }

        mysqli_stmt_close($stmt);
        return ['labels' => array_keys($data), 'values' => array_values($data)];
    }

    public static function getMonthlyOrders($conn, $months = 6){
        $keys = self::monthKeys($months);
        $data = array_fill_keys($keys, 0);
        $months = count($keys);

        $stmt = mysqli_prepare(
            $conn,
            "SELECT DATE_FORMAT(booking_date, '%Y-%m') AS month_key,
                    COUNT(*) AS orders_count
             FROM booking_requests
             WHERE booking_date >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL ? MONTH), '%Y-%m-01')
             GROUP BY DATE_FORMAT(booking_date, '%Y-%m')"
        );
        mysqli_stmt_bind_param($stmt, "i", $months);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            if (isset($data[$row['month_key']])) {
                $data[$row['month_key']] = (int)$row['orders_count'];
            }
        }

        mysqli_stmt_close($stmt);
        return ['labels' => array_keys($data), 'values' => array_values($data)];
    }

    public static function getOrderStatusStats($conn){
        $labels = ['pending', 'confirmed', 'in_progress', 'ready', 'completed', 'cancelled'];
        $data = array_fill_keys($labels, 0);
        $result = mysqli_query($conn, "SELECT status, COUNT(*) AS total FROM booking_requests GROUP BY status");

        while ($row = mysqli_fetch_assoc($result)) {
            if (isset($data[$row['status']])) {
                $data[$row['status']] = (int)$row['total'];
            }
        }

        return ['labels' => array_keys($data), 'values' => array_values($data)];
    }

    public static function getOrderTypeStats($conn){
        $data = ['Personal' => 0, 'Company' => 0];
        $result = mysqli_query(
            $conn,
            "SELECT COALESCE(u.account_type, 'customer') AS account_type, COUNT(*) AS total
             FROM booking_requests br
             LEFT JOIN users u ON u.user_id = br.customer_id
             GROUP BY COALESCE(u.account_type, 'customer')"
        );

        while ($row = mysqli_fetch_assoc($result)) {
            $label = $row['account_type'] === 'company' ? 'Company' : 'Personal';
            $data[$label] += (int)$row['total'];
        }

        return ['labels' => array_keys($data), 'values' => array_values($data)];
    }

    public static function getTopProducts($conn, $limit = 5){
        $limit = max(1, min((int)$limit, 20));
        $stmt = mysqli_prepare(
            $conn,
            "SELECT bri.product_name,
                    SUM(bri.quantity) AS total_quantity,
                    COALESCE(SUM(bri.quantity * COALESCE(p.price, 0)), 0) AS revenue
             FROM booking_request_items bri
             LEFT JOIN products p ON p.product_id = bri.product_id
             GROUP BY bri.product_id, bri.product_name
             ORDER BY total_quantity DESC, revenue DESC
             LIMIT ?"
        );
        mysqli_stmt_bind_param($stmt, "i", $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [
                'product_name' => $row['product_name'],
                'quantity' => (int)$row['total_quantity'],
                'revenue' => (float)$row['revenue']
            ];
        }

        mysqli_stmt_close($stmt);
        return $rows;
    }

    public static function getLowStockProducts($conn, $limit = 5){
        $limit = max(1, min((int)$limit, 20));
        $stmt = mysqli_prepare(
            $conn,
            "SELECT product_id, product_name, product_type, stock
             FROM products
             WHERE COALESCE(is_deleted, 0) = 0
               AND COALESCE(stock, 0) <= 10
             ORDER BY stock ASC, product_id DESC
             LIMIT ?"
        );
        mysqli_stmt_bind_param($stmt, "i", $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        mysqli_stmt_close($stmt);
        return $rows;
    }

    public static function getLatestOrders($conn, $limit = 5){
        $limit = max(1, min((int)$limit, 20));
        $stmt = mysqli_prepare(
            $conn,
            "SELECT br.booking_id,
                    br.full_name,
                    br.status,
                    br.booking_date,
                    COALESCE(u.account_type, 'customer') AS account_type,
                    COALESCE(SUM(bri.quantity * COALESCE(p.price, 0)), 0) AS total_amount
             FROM booking_requests br
             LEFT JOIN users u ON u.user_id = br.customer_id
             LEFT JOIN booking_request_items bri ON bri.booking_id = br.booking_id
             LEFT JOIN products p ON p.product_id = bri.product_id
             GROUP BY br.booking_id, br.full_name, br.status, br.booking_date, u.account_type
             ORDER BY br.booking_id DESC
             LIMIT ?"
        );
        mysqli_stmt_bind_param($stmt, "i", $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        mysqli_stmt_close($stmt);
        return $rows;
    }

    public static function getLatestEscalations($conn, $limit = 5){
        if (!self::columnExists($conn, 'contact_messages', 'is_escalated')) {
            return [];
        }

        $limit = max(1, min((int)$limit, 20));
        $stmt = mysqli_prepare(
            $conn,
            "SELECT message_id, subject, full_name, phone, escalated_at
             FROM contact_messages
             WHERE is_escalated = 1
             ORDER BY escalated_at DESC, message_id DESC
             LIMIT ?"
        );
        mysqli_stmt_bind_param($stmt, "i", $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        mysqli_stmt_close($stmt);
        return $rows;
    }
}
?>
