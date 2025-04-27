import serial
import time
import os
import threading

# Configuration
COM_PORT = 'COM4'  # Change to match your Arduino's COM port
BAUD_RATE = 115200
DATA_FILE = 'rfid_data.txt'  # File to store the RFID data

print(f"Starting RFID bridge on {COM_PORT} at {BAUD_RATE} baud")

# Function to clear the data file after a delay - not used when timer is disabled
def clear_data_after_delay(delay_seconds=20):
    time.sleep(delay_seconds)
    with open(DATA_FILE, 'w') as f:
        f.write("Waiting for card...")
    print(f"RFID data cleared after {delay_seconds} seconds")

# Make sure the data file exists and is writable
with open(DATA_FILE, 'w') as f:
    f.write("Waiting for card...")

try:
    # Open serial connection
    ser = serial.Serial(COM_PORT, BAUD_RATE, timeout=1)
    print(f"Serial port opened successfully")
    
    # Track the active timer thread - not used when timer is disabled
    active_timer = None
    
    while True:
        # Check if there's data available to read
        if ser.in_waiting > 0:
            # Read the data
            rfid_data = ser.readline().decode('utf-8').strip()
            print(f"Received RFID data: {rfid_data}")
            
            # Write to file
            with open(DATA_FILE, 'w') as f:
                f.write(rfid_data)
            
            # Timer code is disabled - RFID data will remain until a new scan
            # No automatic clearing will happen
            
        # Small delay to prevent CPU hogging
        time.sleep(0.1)
        
except Exception as e:
    print(f"Error: {str(e)}")
    # Write error to the data file
    with open(DATA_FILE, 'w') as f:
        f.write(f"Error: {str(e)}")