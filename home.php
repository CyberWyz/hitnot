<?php
session_start();
include("php/config.php");

if (!isset($_SESSION['valid'])) {
    header("Location: index.php");
    exit;
}

$id = $_SESSION['id'];
$query = mysqli_query($con, "SELECT * FROM users WHERE Id=$id");

while ($result = mysqli_fetch_assoc($query)) {
    $res_Uname = $result['Username'];
    $res_Lastname = $result['Lastname'];
    $res_Email = $result['Email'];
    $res_Reg_Number = $result['Reg_Number'];
    $res_Phone = $result['Phone'];
    $res_School = $result['School'];
    $res_Photo = $result['myphoto'];
}

$asset_query = mysqli_query($con, "SELECT * FROM assets WHERE reg_number='$res_Reg_Number'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style/style.css">
    <title>Home</title>
</head>
<body>
    <div class="nav">
        <div class="logo">
            <p>Logo</p>
        </div>
        <div class="right-links">
            <a href="edit.php">Change Profile</a>
            <a href="welcome.php"><button class="btn">Log Out</button></a>
        </div>
    </div>
    <main>
        <div class="main-box top">
            <div class="top">
                <div class="box">
                    <p>Hello <b><?php echo $res_Uname; ?></b>, Welcome</p>
                </div>
                <div class="box">
                    <p>Your email is <b><?php echo $res_Email; ?></b>.</p>
                </div>
            </div>
            <div class="bottom">
                <div class="box">
                    <p>Your last name is <b><?php echo $res_Lastname; ?></b>.</p>
                </div>
                <div class="box">
                    <p>Your registration number is <b><?php echo $res_Reg_Number; ?></b>.</p>
                </div>
                <div class="box">
                    <p>Your phone number is <b><?php echo $res_Phone; ?></b>.</p>
                </div>
                <div class="box">
                    <p>Your school is <b><?php echo $res_School; ?></b>.</p>
                </div>
            </div>
        </div>

        <?php if (!empty($res_Photo)): ?>
            <div class="photo-container">
                <img src="<?php echo $res_Photo; ?>" alt="User Photo">
                <p>Registration Number: <?php echo $res_Reg_Number; ?></p>
            </div>
        <?php endif; ?>

        <?php if (mysqli_num_rows($asset_query) > 0): ?>
            <?php while ($asset = mysqli_fetch_assoc($asset_query)): ?>
                <div class="main-box asset-info">
                    <h2>Asset Information</h2>
                    <p><b>Item Description:</b> <?php echo htmlspecialchars($asset['item_description']); ?></p>
                    <p><b>Serial Number:</b> <?php echo htmlspecialchars($asset['serial_number']); ?></p>
                    <p><b>Date Registered:</b> <?php echo htmlspecialchars($asset['date_registered']); ?></p>
                    <p><b>Item Model:</b> <?php echo htmlspecialchars($asset['item_model']); ?></p>
                    
                    <?php if (!empty($asset['picture'])): ?>
                        <div class="asset-image">
                            <h3>Asset Picture</h3>
                            <img src="<?php echo htmlspecialchars($asset['picture']); ?>" alt="Asset Image" width="200">
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($asset['qr_code'])): ?>
                        <div class="qr-code">
                            <h3>QR Code</h3>
                            <img src="<?php echo htmlspecialchars($asset['qr_code']); ?>" alt="QR Code">
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No asset information found.</p>
        <?php endif; ?>
    </main>
</body>
</html>