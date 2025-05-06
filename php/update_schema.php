<?php
// This script updates the database schema to support the new dashboard statistics
include("config.php");

// Check if scan_logs table exists
$check_scan_logs = mysqli_query($con, "SHOW TABLES LIKE 'scan_logs'");
if (mysqli_num_rows($check_scan_logs) == 0) {
    // Create scan_logs table
    $create_scan_logs = "CREATE TABLE scan_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        asset_id INT,
        user_id INT,
        officer_id INT,
        scan_type VARCHAR(50),
        status VARCHAR(50),
        location VARCHAR(255),
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE SET NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (officer_id) REFERENCES scpersonnel(id) ON DELETE SET NULL
    )";
    
    if (mysqli_query($con, $create_scan_logs)) {
        echo "scan_logs table created successfully<br>";
    } else {
        echo "Error creating scan_logs table: " . mysqli_error($con) . "<br>";
    }
}

// Check if assets table has confiscation_date and recovery_date columns
$check_confiscation = mysqli_query($con, "SHOW COLUMNS FROM assets LIKE 'confiscation_date'");
if (mysqli_num_rows($check_confiscation) == 0) {
    // Add confiscation_date column
    $add_confiscation = "ALTER TABLE assets ADD COLUMN confiscation_date DATETIME NULL";
    
    if (mysqli_query($con, $add_confiscation)) {
        echo "confiscation_date column added to assets table<br>";
    } else {
        echo "Error adding confiscation_date column: " . mysqli_error($con) . "<br>";
    }
}

$check_recovery = mysqli_query($con, "SHOW COLUMNS FROM assets LIKE 'recovery_date'");
if (mysqli_num_rows($check_recovery) == 0) {
    // Add recovery_date column
    $add_recovery = "ALTER TABLE assets ADD COLUMN recovery_date DATETIME NULL";
    
    if (mysqli_query($con, $add_recovery)) {
        echo "recovery_date column added to assets table<br>";
    } else {
        echo "Error adding recovery_date column: " . mysqli_error($con) . "<br>";
    }
}

// Check if users table has created_at column
$check_created_at = mysqli_query($con, "SHOW COLUMNS FROM users LIKE 'created_at'");
if (mysqli_num_rows($check_created_at) == 0) {
    // Add created_at column
    $add_created_at = "ALTER TABLE users ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP";
    
    if (mysqli_query($con, $add_created_at)) {
        echo "created_at column added to users table<br>";
    } else {
        echo "Error adding created_at column: " . mysqli_error($con) . "<br>";
    }
}

echo "Schema update completed.";
?>