<?php
    include 'includes/session.php';

    // ==================== OPT-OUT ACTION: SKIP NOMINATION PHASE ====================
    if(isset($_POST['skip_nomination'])){
        $voter_id = mysqli_real_escape_string($conn, $voter['id']);
        
        // Ensure they are an officially accepted candidate before allowing them to skip
        $check_accepted = $conn->query("SELECT * FROM candidates WHERE id = '$voter_id' AND status = 'accepted'");
        if($check_accepted && $check_accepted->num_rows > 0){
            
            // Insert placeholder row (candidate_id = 0, election_proper = 0) to flag stage completion
            $sql = "INSERT INTO votes (voters_id, candidate_id, position_id, election_proper) VALUES ('$voter_id', '0', 0, 0)";
            if($conn->query($sql)){
                $_SESSION['success'] = 'Nomination phase skipped successfully. You can now cast your final votes!';
            } else {
                $_SESSION['error'] = 'Failed to record skip flag: ' . $conn->error;
            }
        } else {
            $_SESSION['error'] = 'Access Denied: Only active candidates can skip the nomination phase.';
        }
        header('location: home.php');
        exit();
    }

    // ==================== STANDARD ACTION: SUBMIT NOMINATIONS ====================
    if(isset($_POST['vote'])){
        $voter_id = mysqli_real_escape_string($conn, $voter['id']);

        // Backend calculation for true available remaining entities
        $locked_candidates = [];
        $locked_query = $conn->query("SELECT id FROM candidates");
        while($l_row = $locked_query->fetch_assoc()){
            $locked_candidates[] = $l_row['id'];
        }

        $voter_count_query = $conn->query("SELECT id FROM voters WHERE id != '$voter_id'");
        $available_in_db = 0;
        while($vc_row = $voter_count_query->fetch_assoc()){
            if(!in_array($vc_row['id'], $locked_candidates)){
                $available_in_db++;
            }
        }
        $expected_count = ($available_in_db < 2) ? $available_in_db : 2;

        // Check against dynamic threshold instead of hardcoded 2
        if(!isset($_POST['nominated_voters']) || count($_POST['nominated_voters']) != $expected_count){
            $selected = isset($_POST['nominated_voters']) ? count($_POST['nominated_voters']) : 0;
            $_SESSION['error'] = 'Submission Rejected: You selected ' . $selected . ' voters, but exactly ' . $expected_count . ' are required based on remaining availability.';
            header('location: home.php');
            exit();
        }

        $nominees = $_POST['nominated_voters'];

        $conn->begin_transaction();
        try {
            foreach($nominees as $candidate_id){
                $candidate_id = mysqli_real_escape_string($conn, $candidate_id);
                
                $conn->query("INSERT INTO votes (voters_id, candidate_id, position_id, election_proper) VALUES ('$voter_id', '$candidate_id', 0, 0)");
                
                $check = $conn->query("SELECT * FROM candidates WHERE id = '$candidate_id'");
                if($check->num_rows == 0){
                    $conn->query("INSERT INTO candidates (id, position_id, platform, status) VALUES ('$candidate_id', 0, '', 'pending')");
                }
            }
            $conn->commit();
            $_SESSION['success'] = 'Your candidate nominations have been recorded successfully!';
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = 'Database error encountered during submission: ' . $e->getMessage();
        }
    } else {
        // Fallback error if someone hits the URL without pressing either valid form action
        if(!isset($_POST['skip_nomination'])){
            $_SESSION['error'] = 'Please select your nominees first.';
        }
    }

    header('location: home.php');
    exit();
?>