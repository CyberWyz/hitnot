<?php
// Set appropriate headers for AJAX response
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Function to read RFID from serial port
function readRFIDFromSerial($port = 'COM3', $baudRate = 9600, $timeout = 2) {
    // Check if we're on Windows
    $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    
    if ($isWindows) {
        // Windows implementation using COM port
        try {
            // Open COM port
            $handle = fopen($port, 'r+');
            
            if (!$handle) {
                throw new Exception("Failed to open serial port: $port");
            }
            
            // Set port parameters
            // Note: This requires the com_dotnet extension to be enabled in php.ini
            if (extension_loaded('com_dotnet')) {
                $comPort = new COM("WinMgmts:\\\\.\root\cimv2:Win32_SerialPort='$port'");
                $comPort->BaudRate = $baudRate;
                $comPort->DataBits = 8;
                $comPort->Parity = 'None';
                $comPort->StopBits = 1;
                $comPort->ReadIntervalTimeout = 100;
                $comPort->ReadTotalTimeoutConstant = $timeout * 1000;
            }
            
            // Set stream timeout
            stream_set_timeout($handle, $timeout);
            
            // Read data from serial port
            $data = fread($handle, 128);
            
            // Close the port
            fclose($handle);
            
            // Clean and return the data
            $rfid = trim($data);
            
            if (!empty($rfid)) {
                return $rfid;
            } else {
                throw new Exception("No RFID data received");
            }
        } catch (Exception $e) {
            throw new Exception("Serial port error: " . $e->getMessage());
        }
    } else {
        // Linux/Unix implementation using device file
        try {
            // For Linux/Unix, the port would be something like /dev/ttyUSB0
            $device = str_replace('COM', '/dev/ttyUSB', $port);
            
            // Check if the device exists
            if (!file_exists($device)) {
                throw new Exception("Serial device not found: $device");
            }
            
            // Set up the serial port using stty
            exec("stty -F $device $baudRate cs8 -cstopb -parenb -echo");
            
            // Open the device for reading
            $handle = fopen($device, 'r');
            
            if (!$handle) {
                throw new Exception("Failed to open serial device: $device");
            }
            
            // Set stream timeout
            stream_set_timeout($handle, $timeout);
            
            // Read data from serial port
            $data = fread($handle, 128);
            
            // Close the device
            fclose($handle);
            
            // Clean and return the data
            $rfid = trim($data);
            
            if (!empty($rfid)) {
                return $rfid;
            } else {
                throw new Exception("No RFID data received");
            }
        } catch (Exception $e) {
            throw new Exception("Serial device error: " . $e->getMessage());
        }
    }
}

// Simulate RFID reading for development/testing
function simulateRFIDReading() {
    // Check if we should simulate a successful read (80% chance)
    if (rand(1, 100) <= 80) {
        // Generate a random RFID number
        $rfid = '';
        for ($i = 0; $i < 10; $i++) {
            $rfid .= rand(0, 9);
        }
        return $rfid;
    } else {
        throw new Exception("Simulated read failure");
    }
}

// Main execution
try {
    // Determine if we're in development or production mode
    $isDevelopment = true; // Set to false in production
    
    if ($isDevelopment) {
        // Use simulated RFID in development
        $rfid = simulateRFIDReading();
    } else {
        // Use actual serial port in production
        $rfid = readRFIDFromSerial();
    }
    
    // Return success response with RFID data
    echo json_encode([
        'success' => true,
        'rfid' => $rfid,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}