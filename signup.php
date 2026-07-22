<?php
session_start();
include 'includes/conn.php'; 

if (isset($_POST['signup'])) {
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 

    // Clean strings and extract substrings
    $clean_first = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $firstname));
    $clean_last = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $lastname));

    $part_first = substr($clean_first, 0, 3);
    $part_last = substr($clean_last, 0, 2);

    $pool = '123456789ABCDEFGHJKLMNOPQRSTUVWXYZ';
    $random_suffix = substr(str_shuffle($pool), 0, 4);

    // Combine into structured, easily memorable ID
    $voter_id = $part_first . $part_last . '-' . $random_suffix;

    // Handle Profile Picture Upload
    $filename = $_FILES['photo']['name'];
    if (!empty($filename)) {
        move_uploaded_file($_FILES['photo']['tmp_name'], 'images/' . $filename);
    } else {
        $filename = 'profile.jpg'; 
    }

    // Check if user already exists
    $check = $conn->prepare("SELECT * FROM voters WHERE firstname = ? AND lastname = ?");
    $check->bind_param("ss", $firstname, $lastname);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = 'A voter with this name is already registered.';
    } else {
        // Insert into voters table
        $sql = "INSERT INTO voters (voters_id, password, firstname, lastname, photo) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $voter_id, $password, $firstname, $lastname, $filename);

        if ($stmt->execute()) {
            // Keep the generated ID inside a separate session tracker to display a clear success block
            $_SESSION['registered_id'] = $voter_id;
            $_SESSION['success'] = 'Registration successful!';
        } else {
            $_SESSION['error'] = 'Something went wrong: ' . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Voting System | Sign Up</title>
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
</head>
<body class="hold-transition register-page" style="background: #e9ecef;">
<div class="register-box" style="margin: 5% auto; width: 420px;">
    <div class="register-logo">
        <b>Voting</b>System
    </div>

    <div class="register-box-body" style="background: #fff; padding: 25px; border-top: 3px solid #3c8dbc; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 3px;">
        <p class="login-box-msg">Register a new voter account</p>

        <?php
        if (isset($_SESSION['error'])) {
            echo "<div class='alert alert-danger'>".$_SESSION['error']."</div>";
            unset($_SESSION['error']);
        }
        
        // Visual display block highlighting the newly created Voter ID
        if (isset($_SESSION['success']) && isset($_SESSION['registered_id'])) {
            echo "
                <div class='alert alert-success text-center' style='background-color: #d4edda !important; color: #155724 !important; border-color: #c3e6cb !important;'>
                    <h4><i class='icon fa fa-check'></i> Account Created!</h4>
                    <p>Please save, copy, or memorize your generated Voter ID below to sign in:</p>
                    <div style='background: #fff; border: 2px dashed #28a745; padding: 10px; margin: 10px 0; font-size: 22px; font-weight: bold; letter-spacing: 2px; color: #155724;'>
                        ".$_SESSION['registered_id']."
                    </div>
                    <a href='index.php' class='btn btn-success btn-block btn-flat'>Proceed to Login <i class='fa fa-arrow-right'></i></a>
                </div>
            ";
            unset($_SESSION['success']);
            unset($_SESSION['registered_id']);
        }
        ?>

        <!-- Registration Form Hidden dynamically if they just registered successfully to avoid double submission -->
        <?php if (!isset($_SESSION['registered_id'])): ?>
        <form action="signup.php" method="POST" enctype="multipart/form-data">
            <div class="form-group has-feedback">
                <input type="text" class="form-control" name="firstname" placeholder="First Name" required>
                <span class="glyphicon glyphicon-user form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="text" class="form-control" name="lastname" placeholder="Last Name" required>
                <span class="glyphicon glyphicon-user form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" class="form-control" name="password" placeholder="Password" required>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="form-group">
                <label>Profile Photo (Optional)</label>
                <input type="file" name="photo">
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <button type="submit" name="signup" class="btn btn-primary btn-block btn-flat">Generate Voter ID & Register</button>
                </div>
            </div>
        </form>
        <br>
        <a href="index.php" class="text-center">I already have a Voter ID (Go to Login)</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>