<?php
include 'includes/session.php';

if(isset($_POST['action'])){
    $nomination_id = $_POST['nomination_id'];
    $status = $_POST['action']; // 'accepted' or 'declined'

    // Update status
    $sql = "UPDATE nominations SET status = '$status' WHERE id = '$nomination_id' AND nominee_id = '".$voter['id']."'";
    
    if($conn->query($sql)){
        if($status == 'accepted'){
            // Automatically push them into the main candidates table if accepted
            $nom_query = $conn->query("SELECT * FROM nominations WHERE id = '$nomination_id'");
            $nom_data = $nom_query->fetch_assoc();
            
            // Fetch nominee details to populate candidate profiles
            $voter_query = $conn->query("SELECT * FROM voters WHERE id = '".$voter['id']."'");
            $v_data = $voter_query->fetch_assoc();
            
            $firstname = $v_data['firstname'];
            $lastname = $v_data['lastname'];
            $position_id = $nom_data['position_id'];

            // Check if already an active candidate for that position
            $cand_check = $conn->query("SELECT * FROM candidates WHERE firstname='$firstname' AND lastname='$lastname' AND position_id='$position_id'");
            if($cand_check->num_rows == 0){
                $conn->query("INSERT INTO candidates (position_id, firstname, lastname, photo) VALUES ('$position_id', '$firstname', '$lastname', 'profile.jpg')");
            }
        }
        $_SESSION['success'] = "Nomination status updated to " . $status;
    } else {
        $_SESSION['error'] = $conn->error;
    }
}

include 'includes/header.php';
?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <?php include 'includes/navbar.php'; ?>
    <div class="content-wrapper">
        <section class="content-header">
            <h1>My Nominations</h1>
        </section>
        <section class="content">
            <div class="box">
                <div class="box-body">
                    <table class="table table-bordered">
                        <thead>
                            <th>Position</th>
                            <th>Nominated By</th>
                            <th>Current Status</th>
                            <th>Action</th>
                        </thead>
                        <tbody>
                            <?php
                                $sql = "SELECT n.id as nid, n.status, p.description, v.firstname, v.lastname 
                                        FROM nominations n 
                                        LEFT JOIN positions p ON p.id=n.position_id 
                                        LEFT JOIN voters v ON v.id=n.nominated_by 
                                        WHERE n.nominee_id = '".$voter['id']."'";
                                $query = $conn->query($sql);
                                while($row = $query->fetch_assoc()){
                                    echo "
                                        <tr>
                                            <td>".$row['description']."</td>
                                            <td>".$row['firstname']." ".$row['lastname']."</td>
                                            <td><span class='label label-".($row['status']=='accepted'?'success':($row['status']=='declined'?'danger':'warning'))."'>".$row['status']."</span></td>
                                            <td>";
                                    if($row['status'] == 'pending'){
                                        echo "
                                            <form method='POST' style='display:inline;'>
                                                <input type='hidden' name='nomination_id' value='".$row['nid']."'>
                                                <button type='submit' name='action' value='accepted' class='btn btn-success btn-sm'><i class='fa fa-check'></i> Accept</button>
                                                <button type='submit' name='action' value='declined' class='btn btn-danger btn-sm'><i class='fa fa-close'></i> Decline</button>
                                            </form>
                                        ";
                                    } else {
                                        echo "Decision finalized";
                                    }
                                    echo "</td></tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>