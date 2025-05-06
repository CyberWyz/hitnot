<?php
// This file contains functions to retrieve advanced statistics for the admin dashboard

function getWeeklyScanningStats($con) {
    // First check if the scan_logs table exists
    $table_check = mysqli_query($con, "SHOW TABLES LIKE 'scan_logs'");
    if (!$table_check || mysqli_num_rows($table_check) == 0) {
        // Table doesn't exist, return empty array
        return array();
    }
    
    $query = "SELECT 
                DATE(timestamp) as scan_date, 
                COUNT(*) as scan_count 
              FROM scan_logs 
              WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
              GROUP BY DATE(timestamp) 
              ORDER BY scan_date";
    
    $result = mysqli_query($con, $query);
    
    // Check if query was successful
    if (!$result) {
        // Query failed, return empty array
        return array();
    }
    
    $data = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    return $data;
}

function getAssetRegistrationRate($con) {
    $query = "SELECT 
                DATE(date_registered) as reg_date, 
                COUNT(*) as reg_count 
              FROM assets 
              WHERE date_registered IS NOT NULL 
                AND date_registered >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
              GROUP BY DATE(date_registered) 
              ORDER BY reg_date";
    
    $result = mysqli_query($con, $query);
    
    // Check if query was successful
    if (!$result) {
        // Query failed, return empty array
        return array();
    }
    
    $data = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    return $data;
}

function getConfiscationRecoveryStats($con) {
    // Check if the confiscation_date and recovery_date columns exist
    $column_check = mysqli_query($con, "SHOW COLUMNS FROM assets LIKE 'confiscation_date'");
    if (!$column_check || mysqli_num_rows($column_check) == 0) {
        // Column doesn't exist, return empty data
        return [
            'confiscated' => array_fill(0, 12, 0),
            'recovered' => array_fill(0, 12, 0)
        ];
    }
    
    // Confiscated items by month
    $confiscated_query = "SELECT 
                            MONTH(confiscation_date) as month, 
                            COUNT(*) as count 
                          FROM assets 
                          WHERE confiscation_date IS NOT NULL 
                            AND YEAR(confiscation_date) = YEAR(CURRENT_DATE()) 
                          GROUP BY MONTH(confiscation_date)";
    
    // Recovered items by month
    $recovered_query = "SELECT 
                          MONTH(recovery_date) as month, 
                          COUNT(*) as count 
                        FROM assets 
                        WHERE recovery_date IS NOT NULL 
                          AND YEAR(recovery_date) = YEAR(CURRENT_DATE()) 
                        GROUP BY MONTH(recovery_date)";
    
    $confiscated_result = mysqli_query($con, $confiscated_query);
    $recovered_result = mysqli_query($con, $recovered_query);
    
    $confiscated_data = array_fill(1, 12, 0);
    $recovered_data = array_fill(1, 12, 0);
    
    if ($confiscated_result) {
        while ($row = mysqli_fetch_assoc($confiscated_result)) {
            $confiscated_data[$row['month']] = $row['count'];
        }
    }
    
    if ($recovered_result) {
        while ($row = mysqli_fetch_assoc($recovered_result)) {
            $recovered_data[$row['month']] = $row['count'];
        }
    }
    
    return [
        'confiscated' => array_values($confiscated_data),
        'recovered' => array_values($recovered_data)
    ];
}

function getUserCreationStats($con) {
    // Check if the created_at column exists
    $column_check = mysqli_query($con, "SHOW COLUMNS FROM users LIKE 'created_at'");
    if (!$column_check || mysqli_num_rows($column_check) == 0) {
        // Column doesn't exist, return empty array
        return array();
    }
    
    $query = "SELECT 
                DATE(created_at) as creation_date, 
                HOUR(created_at) as creation_hour,
                COUNT(*) as user_count 
              FROM users 
              WHERE created_at IS NOT NULL
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
              GROUP BY DATE(created_at), HOUR(created_at) 
              ORDER BY creation_date, creation_hour";
    
    $result = mysqli_query($con, $query);
    
    // Check if query was successful
    if (!$result) {
        // Query failed, return empty array
        return array();
    }
    
    $data = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    return $data;
}

function getAssetStatusDistribution($con) {
    $query = "SELECT 
                AssetStatus, 
                COUNT(*) as count 
              FROM assets 
              GROUP BY AssetStatus";
    
    $result = mysqli_query($con, $query);
    
    // Check if query was successful
    if (!$result) {
        // Query failed, return empty array
        return array();
    }
    
    $data = array();
    
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    return $data;
}
?>