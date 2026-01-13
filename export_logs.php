<?php
session_start();
include("php/config.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_valid']) || $_SESSION['admin_valid'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Increase memory limit and execution time for large exports
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 300);

// Get export type
$type = isset($_GET['type']) ? $_GET['type'] : 'admin';

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $type . '_logs_' . date('Y-m-d_H-i-s') . '.csv"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Create output stream
$output = fopen('php://output', 'w');

// Export Admin Logs
if ($type == 'admin') {
    // Build query with current filters if they exist
    $where_conditions = array();

    if (isset($_GET['action']) && !empty($_GET['action'])) {
        $action = mysqli_real_escape_string($con, $_GET['action']);
        $where_conditions[] = "action = '$action'";
    }

    if (isset($_GET['admin_id']) && !empty($_GET['admin_id'])) {
        $admin_id = mysqli_real_escape_string($con, $_GET['admin_id']);
        $where_conditions[] = "admin_id = '$admin_id'";
    }

    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $start_date = mysqli_real_escape_string($con, $_GET['start_date']);
        $where_conditions[] = "created_at >= '$start_date 00:00:00'";
    }

    if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
        $end_date = mysqli_real_escape_string($con, $_GET['end_date']);
        $where_conditions[] = "created_at <= '$end_date 23:59:59'";
    }

    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

    // Get total count first
    $count_query = "SELECT COUNT(*) as total FROM admin_logs $where_clause";
    $count_result = mysqli_query($con, $count_query);
    $total_records = mysqli_fetch_assoc($count_result)['total'];

    // Write CSV headers with record count
    fputcsv($output, array('ID', 'Admin ID', 'Action', 'Description', 'IP Address', 'Timestamp'));
    fputcsv($output, array("Total Records: $total_records", '', '', '', '', ''));

    // Get all admin logs (no pagination limit for export)
    $query = "SELECT * FROM admin_logs $where_clause ORDER BY created_at DESC";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, array(
                $row['id'],
                $row['admin_id'],
                $row['action'],
                $row['description'],
                $row['ip_address'],
                $row['created_at']
            ));
        }
    }
}

// Export Scan Logs
elseif ($type == 'scan') {
    // Build query with current filters if they exist
    $where_conditions = array();

    if (isset($_GET['rfid_uid']) && !empty($_GET['rfid_uid'])) {
        $rfid_uid = mysqli_real_escape_string($con, $_GET['rfid_uid']);
        $where_conditions[] = "s.rfid_uid = '$rfid_uid'";
    }

    if (isset($_GET['scanner_id']) && !empty($_GET['scanner_id'])) {
        $scanner_id = mysqli_real_escape_string($con, $_GET['scanner_id']);
        $where_conditions[] = "s.scanner_id = '$scanner_id'";
    }

    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $status = mysqli_real_escape_string($con, $_GET['status']);
        $where_conditions[] = "s.status = '$status'";
    }

    if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
        $start_date = mysqli_real_escape_string($con, $_GET['start_date']);
        $where_conditions[] = "s.scan_time >= '$start_date 00:00:00'";
    }

    if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
        $end_date = mysqli_real_escape_string($con, $_GET['end_date']);
        $where_conditions[] = "s.scan_time <= '$end_date 23:59:59'";
    }

    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

    // Get total count first
    $count_query = "SELECT COUNT(*) as total FROM scan_logs s $where_clause";
    $count_result = mysqli_query($con, $count_query);
    $total_records = mysqli_fetch_assoc($count_result)['total'];

    // Write CSV headers with record count
    fputcsv($output, array('ID', 'RFID UID', 'Serial Number', 'Item Model', 'Item Description', 'Scanner ID', 'Location', 'Status', 'Scan Time'));
    fputcsv($output, array("Total Records: $total_records", '', '', '', '', '', '', '', ''));

    // Get all scan logs with asset information (no pagination limit for export)
    $query = "SELECT s.*, a.item_model, a.item_description, a.serial_number, a.reg_number, a.rfid_status
              FROM scan_logs s
              LEFT JOIN assets a ON s.rfid_uid = a.rfid_uid
              $where_clause
              ORDER BY s.scan_time DESC";

    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            fputcsv($output, array(
                $row['id'],
                $row['rfid_uid'],
                $row['serial_number'] ?? 'N/A',
                $row['item_model'] ?? 'N/A',
                $row['item_description'] ?? 'N/A',
                $row['scanner_id'],
                $row['location'],
                $row['status'],
                $row['scan_time']
            ));
        }
    }
}

// Close output stream
fclose($output);
exit;
?>