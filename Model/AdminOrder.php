<?php
class AdminOrder {

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

    public static function getOrdersByAccountType($conn, $accountType, $completedOnly = false, $limit = null, $offset = 0){
        $statusCondition = $completedOnly ? "br.status = 'completed'" : "br.status <> 'completed'";
        $limitSql = $limit !== null ? " LIMIT ? OFFSET ?" : "";

        $stmt = mysqli_prepare(
            $conn,
            "SELECT
                br.booking_id,
                br.booking_date,
                br.status,
                br.full_name,
                br.phone,
                br.whatsapp,
                br.city,
                br.area,
                br.engineer_requested,
                u.full_name AS user_full_name,
                u.phone AS user_phone,
                u.whatsapp AS user_whatsapp,
                u.account_type,
                COUNT(bri.booking_request_item_id) AS items_count,
                COALESCE(SUM(bri.quantity * COALESCE(p.price, 0)), 0) AS total_amount
             FROM booking_requests br
             INNER JOIN users u ON u.user_id = br.customer_id
             LEFT JOIN booking_request_items bri ON bri.booking_id = br.booking_id
             LEFT JOIN products p ON p.product_id = bri.product_id
             WHERE COALESCE(u.account_type, 'customer') = ?
               AND {$statusCondition}
             GROUP BY
                br.booking_id,
                br.booking_date,
                br.status,
                br.full_name,
                br.phone,
                br.whatsapp,
                br.city,
                br.area,
                br.engineer_requested,
                u.full_name,
                u.phone,
                u.whatsapp,
                u.account_type
             ORDER BY br.booking_id DESC{$limitSql}"
        );

        if ($limit !== null) {
            $limit = max(1, (int)$limit);
            $offset = max(0, (int)$offset);
            mysqli_stmt_bind_param($stmt, "sii", $accountType, $limit, $offset);
        } else {
            mysqli_stmt_bind_param($stmt, "s", $accountType);
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $orders = [];

        while ($row = mysqli_fetch_assoc($result)) {
            $orders[] = $row;
        }

        mysqli_stmt_close($stmt);
        return $orders;
    }

    public static function countOrdersByAccountType($conn, $accountType, $completedOnly = false){
        $statusCondition = $completedOnly ? "br.status = 'completed'" : "br.status <> 'completed'";

        $stmt = mysqli_prepare(
            $conn,
            "SELECT COUNT(*) AS total
             FROM booking_requests br
             INNER JOIN users u ON u.user_id = br.customer_id
             WHERE COALESCE(u.account_type, 'customer') = ?
               AND {$statusCondition}"
        );
        mysqli_stmt_bind_param($stmt, "s", $accountType);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return (int)($row['total'] ?? 0);
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

    public static function getOrderById($conn, $bookingId){
        $stmt = mysqli_prepare(
            $conn,
            "SELECT
                br.booking_id,
                br.booking_date,
                br.status,
                br.full_name,
                br.phone,
                br.whatsapp,
                br.city,
                br.area,
                br.notes,
                br.engineer_requested,
                u.full_name AS user_full_name,
                u.phone AS user_phone,
                u.whatsapp AS user_whatsapp,
                u.city AS user_city,
                u.area AS user_area,
                u.account_type
             FROM booking_requests br
             INNER JOIN users u ON u.user_id = br.customer_id
             WHERE br.booking_id = ?
             LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "i", $bookingId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $order = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$order) {
            return null;
        }

        $stmt = mysqli_prepare(
            $conn,
            "SELECT
                bri.product_id,
                bri.product_name,
                bri.requested_length,
                bri.requested_width,
                bri.quantity,
                p.image,
                p.price
             FROM booking_request_items bri
             LEFT JOIN products p ON p.product_id = bri.product_id
             WHERE bri.booking_id = ?
             ORDER BY bri.booking_request_item_id ASC"
        );
        mysqli_stmt_bind_param($stmt, "i", $bookingId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $items = [];
        $subtotal = 0;

        while ($row = mysqli_fetch_assoc($result)) {
            $row['quantity'] = max(1, (int)$row['quantity']);
            $row['price'] = (float)($row['price'] ?? 0);
            $row['line_total'] = $row['quantity'] * $row['price'];
            $subtotal += $row['line_total'];
            $items[] = $row;
        }

        mysqli_stmt_close($stmt);

        $order['items'] = $items;
        $order['subtotal'] = $subtotal;
        return $order;
    }

    public static function updateStatus($conn, $bookingId, $status){
        $allowed = array_keys(self::statusLabels());

        if (!in_array($status, $allowed, true)) {
            return false;
        }

        if (self::columnExists($conn, 'booking_requests', 'completed_at')) {
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE booking_requests
                 SET status = ?,
                     completed_at = CASE
                         WHEN ? = 'completed' THEN COALESCE(completed_at, NOW())
                         ELSE NULL
                     END
                 WHERE booking_id = ?"
            );
            mysqli_stmt_bind_param($stmt, "ssi", $status, $status, $bookingId);
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE booking_requests SET status = ? WHERE booking_id = ?");
            mysqli_stmt_bind_param($stmt, "si", $status, $bookingId);
        }

        $success = mysqli_stmt_execute($stmt);
        $affectedRows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        if (!$success) {
            return false;
        }

        if ($affectedRows < 1) {
            $stmt = mysqli_prepare($conn, "SELECT booking_id FROM booking_requests WHERE booking_id = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, "i", $bookingId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $exists = mysqli_fetch_assoc($result) !== null;
            mysqli_stmt_close($stmt);

            return $exists;
        }

        return true;
    }
}
?>
