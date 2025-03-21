<?php
session_start();
include("php/config.php");

if (!isset($_SESSION['valid'])) {
    header("Location: login_scpersonnel.php");
    exit;
}

$id = $_SESSION['id'];
$query = mysqli_query($con, "SELECT * FROM scpersonnel WHERE id=$id");

if ($result = mysqli_fetch_assoc($query)) {
    $officer_id = $result['officer_id'];
    $officer_lastname = $result['lastname'];
} else {
    die("Officer details not found.");
}

$message = "";

if (isset($_POST['submit'])) {
    $item_description = $_POST['item_description'];
    $item_model = $_POST['item_model'];
    $reg_number = $_POST['reg_number'];
    $serial_number = $_POST['serial_number'];
    $date_registered = $_POST['date_registered'];
    $picture = $_FILES['picture']['name'];
    $picture_tmp = $_FILES['picture']['tmp_name'];

    $picture_path = "uploads/" . basename($picture);
    move_uploaded_file($picture_tmp, $picture_path);

    // Generate QR code data with consistent format
    $qr_data_student = "Reg Number: $reg_number, Date Registered: $date_registered, Officer: $officer_lastname, Officer ID: $officer_id";
    $qr_data_asset = "Serial: $serial_number, Model: $item_model, Reg: $reg_number";

    // Check if the registration number already exists in the assets table
    $check_query = "SELECT * FROM assets WHERE reg_number = '$reg_number'";
    $check_result = mysqli_query($con, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        // Registration number already exists, do not generate student QR code
        $qr_data_student = ""; // Empty student QR code data
    }

    $query = "INSERT INTO assets (item_description, item_model, reg_number, serial_number, date_registered, picture, qr_code, asset_qr_code) 
              VALUES ('$item_description', '$item_model', '$reg_number', '$serial_number', '$date_registered', '$picture_path', '$qr_data_student', '$qr_data_asset')";

    if (mysqli_query($con, $query)) {
        $message = "<div class='message success'><p>Asset registered successfully!</p></div>";
    } else {
        $message = "<div class='message error'><p>Error occurred: " . mysqli_error($con) . "</p></div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Register Asset</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <!-- Include html2canvas library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <header>Register Asset</header>
            <?php echo $message; ?>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="field input">
                    <label for="item_description">Item Description</label>
                    <input type="text" name="item_description" id="item_description" required>
                </div>

                <div class="field input">
                    <label for="item_model">Item Model</label>
                    <input type="text" name="item_model" id="item_model" required>
                </div>

                <div class="field input">
                    <label for="reg_number">Registration Number</label>
                    <input type="text" name="reg_number" id="reg_number" required>
                </div>

                <div class="field input">
                    <label for="serial_number">Serial Number</label>
                    <input type="text" name="serial_number" id="serial_number" required>
                </div>

                <div class="field input">
                    <label for="date_registered">Date Registered</label>
                    <input type="date" name="date_registered" id="date_registered" required>
                </div>

                <div class="field input">
                    <label for="picture">Upload Picture</label>
                    <input type="file" name="picture" id="picture" accept="image/*" required>
                </div>

                <div class="field">
                    <input type="button" value="Generate QR Code" onclick="generateQRCode()" class="btn">
                </div>

                <div class="qr-container">
                    <h3>Student QR Code</h3>
                    <div id="qrcode-student"></div>
                    <button type="button" onclick="downloadQRCode('qrcode-student', 'student_qr.png')">Download Student QR Code</button>
                </div>

                <div class="qr-container">
                    <h3>Asset QR Code</h3>
                    <div id="qrcode-asset"></div>
                    <button type="button" onclick="downloadQRCode('qrcode-asset', 'asset_qr.png')">Download Asset QR Code</button>
                </div>

                <div class="field">
                    <input type="submit" name="submit" value="Register Asset" class="btn">
                </div>
            </form>
        </div>
    </div>

    <script>
        function generateQRCode() {
            const regNumber = document.getElementById('reg_number').value;
            const dateRegistered = document.getElementById('date_registered').value;
            const officerLastname = "<?php echo $officer_lastname; ?>";
            const officerId = "<?php echo $officer_id; ?>";
            const serialNumber = document.getElementById('serial_number').value;
            const itemModel = document.getElementById('item_model').value;

            // Check if the registration number already exists in the assets table
            fetch(`check_student.php?reg_number=${encodeURIComponent(regNumber)}`)
                .then(response => response.json())
                .then(data => {
                    const qrCodeStudentElement = document.getElementById('qrcode-student');
                    qrCodeStudentElement.innerHTML = '';

                    if (data.exists) {
                        // Registration number already exists, do not generate student QR code
                        qrCodeStudentElement.innerHTML = "<p>Student QR code not generated (registration number already exists).</p>";
                    } else {
                        // Generate QR code for the student
                        const qrDataStudent = `Reg Number: ${regNumber}, Date Registered: ${dateRegistered}, Officer: ${officerLastname}, Officer ID: ${officerId}`;
                        new QRCode(qrCodeStudentElement, {
                            text: qrDataStudent,
                            width: 128,
                            height: 128
                        });
                    }

                    // Generate QR code for the asset
                    const qrDataAsset = `Serial: ${serialNumber}, Model: ${itemModel}, Reg: ${regNumber}`;
                    const qrCodeAssetElement = document.getElementById('qrcode-asset');
                    qrCodeAssetElement.innerHTML = '';
                    new QRCode(qrCodeAssetElement, {
                        text: qrDataAsset,
                        width: 128,
                        height: 128
                    });
                })
                .catch(error => {
                    console.error("Error checking registration number:", error);
                    alert("Error checking registration number. Please try again.");
                });
        }

        // Download QR Code as Image
        function downloadQRCode(elementId, fileName) {
            const qrElement = document.getElementById(elementId);
            html2canvas(qrElement).then(canvas => {
                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/png');
                link.download = fileName;
                link.click();
            });
        }
    </script>
</body>
</html>