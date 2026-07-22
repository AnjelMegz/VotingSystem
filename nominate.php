<?php
include 'includes/session.php'; // Ensures user is logged in as a voter

if(isset($_POST['submit_nomination'])){
    $position = $_POST['position'];
    $nominee = $_POST['nominee'];
    $voter_id = $voter['id']; // Current logged-in voter ID from session

    if($nominee == $voter_id) {
        $_SESSION['error'] = 'You cannot nominate yourself here!';
    } else {
        // Check if already nominated for this position
        $check = $conn->query("SELECT * FROM nominations WHERE position_id = '$position' AND nominee_id = '$nominee'");
        if($check->num_rows > 0){
            $_SESSION['error'] = 'This person has already been nominated for this position.';
        } else {
            $sql = "INSERT INTO nominations (position_id, nominee_id, nominated_by, status) VALUES ('$position', '$nominee', '$voter_id', 'pending')";
            if($conn->query($sql)){
                $_SESSION['success'] = 'Nomination submitted successfully!';
            } else {
                $_SESSION['error'] = $conn->error;
            }
        }
    }
}
include 'includes/header.php';
?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Nominate a Candidate</h1>
        </section>

        <section class="content">
            <?php
                if(isset($_SESSION['error'])){
                    echo "<div class='alert alert-danger'>".$_SESSION['error']."</div>";
                    unset($_SESSION['error']);
                }
                if(isset($_SESSION['success'])){
                    echo "<div class='alert alert-success'>".$_SESSION['success']."</div>";
                    unset($_SESSION['success']);
                }
            ?>
            <div class="box box-primary">
                <form role="form" method="POST" action="nominate.php">
                    <div class="box-body">
                        <div class="form-group">
                            <label>Select Position</label>
                            <select class="form-control" name="position" required>
                                <option value="" selected>- Select Position -</option>
                                <?php
                                    $sql = "SELECT * FROM positions";
                                    $query = $conn->query($sql);
                                    while($row = $query->fetch_assoc()){
                                        echo "<option value='".$row['id']."'>".$row['description']."</option>";
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Select Nominee (Voter)</label>
                            <select class="form-control" name="nominee" required>
                                <option value="" selected>- Select Person -</option>
                                <?php
                                    $sql = "SELECT * FROM voters WHERE id != '".$voter['id']."'";
                                    $query = $conn->query($sql);
                                    while($row = $query->fetch_assoc()){
                                        echo "<option value='".$row['id']."'>".$row['firstname']." ".$row['lastname']."</option>";
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" name="submit_nomination" class="btn btn-primary">Submit Nomination</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>