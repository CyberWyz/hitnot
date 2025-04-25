<?php
$uid_display = "Waiting for card...";
$data_file = "rfid_data.txt"; // File where Python writes the RFID data

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['read'])) {
    // Set proper JSON header
    header('Content-Type: application/json');
    
    // Read from the data file
    if (file_exists($data_file) && is_readable($data_file)) {
        $rfid_data = file_get_contents($data_file);
        $uid = trim($rfid_data);
        echo json_encode(['uid' => $uid, 'status' => 'success']);
    } else {
        echo json_encode(['uid' => 'Error: Cannot read RFID data file', 'status' => 'error']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Live RFID Reader</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; text-align: center; padding-top: 50px; }
        #uid { font-size: 2em; color: #007bff; }
        .error { color: #dc3545; }
    </style>
    <script>
        async function getRFID() {
            try {
                const response = await fetch("rfidreader.php?read=1");
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                console.log("RFID response:", data);
                
                const uidElement = document.getElementById("uid");
                uidElement.textContent = data.uid;
                
                if (data.status === 'error') {
                    uidElement.classList.add('error');
                } else {
                    uidElement.classList.remove('error');
                }
            } catch (e) {
                console.error("RFID Error:", e);
                document.getElementById("uid").textContent = "Connection error: " + e.message;
                document.getElementById("uid").classList.add('error');
            }
        }
        
        // Initial call
        getRFID();
        
        // Poll every second
        setInterval(getRFID, 1000);
    </script>
</head>
<body>
    <h1>RFID Card UID</h1>
    <p><span id="uid"><?= $uid_display ?></span></p>
</body>
</html>