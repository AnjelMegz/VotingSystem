<?php
    include 'includes/session.php';

    if(isset($_GET['action']) && isset($_GET['id'])){
        $id = $_GET['id'];
        $action = $_GET['action'];

        if($action == 'accept'){
            // Update the status to 'accepted' so they show up on the admin candidates list
            $sql = "UPDATE candidates SET status = 'accepted' WHERE id = '$id'";
            if($conn->query($sql)){
                $_SESSION['success'] = 'You have officially accepted the nomination!';
            } else {
                $_SESSION['error'] = 'Database error: ' . $conn->error;
            }
        } 
        elseif($action == 'decline') {
            // Remove them from the candidates table if they decline
            $sql = "DELETE FROM candidates WHERE id = '$id'";
            if($conn->query($sql)){
                $_SESSION['success'] = 'Nomination declined successfully.';
            } else {
                $_SESSION['error'] = 'Database error: ' . $conn->error;
            }
        }
    } else {
        $_SESSION['error'] = 'Invalid request parameters.';
    }

    // Redirect back to home.php
    header('location: home.php');
    exit();
?>