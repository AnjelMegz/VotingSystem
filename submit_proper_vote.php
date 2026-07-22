<?php
include 'includes/session.php';

if(isset($_POST['submit_proper_ballot'])){
    if(!isset($_POST['final_votes']) || count($_POST['final_votes']) == 0){
        $_SESSION['error'] = 'Please select candidates before submitting your ballot.';
        header('location: home.php');
        exit();
    }

    $voter_id = mysqli_real_escape_string($conn, $voter['id']);
    $final_votes = $_POST['final_votes'];

    // 1. Check if the user has already cast their final ballot
    $check_already_voted = $conn->query("SELECT * FROM votes WHERE voters_id = '$voter_id' AND election_proper = 1");
    if($check_already_voted->num_rows > 0){
        $_SESSION['error'] = 'You have already submitted your final votes.';
        header('location: home.php');
        exit();
    }

    // 2. CHECK STAGE 1 COMPLIANCE: Verify if they have the right to access the final ballot
    // Condition A: Did they submit normal nominations?
    $nom_query = $conn->query("SELECT * FROM votes WHERE voters_id = '$voter_id' AND election_proper = 0");
    
    // Condition B: Did they explicitly choose to skip nominating anyone?
    $check_skipped = $conn->query("SELECT * FROM votes WHERE voters_id = '$voter_id' AND candidate_id = 0 AND election_proper = 0");
    $has_skipped = ($check_skipped && $check_skipped->num_rows > 0);
    
    // Condition C: Are they an officially accepted candidate?
    $check_accepted = $conn->query("SELECT * FROM candidates WHERE id = '$voter_id' AND status = 'accepted'");
    $is_accepted_candidate = ($check_accepted && $check_accepted->num_rows > 0);

    // Condition D: Is the remaining database pool completely exhausted?
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

    // Validate access rules: Deny if they have not nominated, have not skipped, are not an accepted candidate, and a pool exists
    if($nom_query->num_rows == 0 && !$has_skipped && !$is_accepted_candidate && $available_in_db > 0){
        $_SESSION['error'] = 'Access Violation: You must submit your candidate nominations or opt-out before you can cast final ballot votes.';
        header('location: home.php');
        exit();
    }

    // 3. Dynamic Calculation for Required Target Votes
    $total_can_stmt = $conn->query("SELECT COUNT(*) AS total FROM candidates WHERE status = 'accepted'");
    $total_can_rows = ($total_can_stmt) ? $total_can_stmt->fetch_assoc()['total'] : 0;
    $required_votes = ($total_can_rows < 3) ? $total_can_rows : 3;

    if(count($final_votes) !== $required_votes){
        $_SESSION['error'] = 'Invalid selection. You must select exactly ' . $required_votes . ' candidate(s).';
        header('location: home.php');
        exit();
    }

    // 4. Transaction-Safe Batch Process Ballot Entries
    $conn->begin_transaction();
    try {
        foreach($final_votes as $candidate_id){
            $candidate_id = mysqli_real_escape_string($conn, $candidate_id);
            
            // Ensure the selected individual is a valid accepted candidate
            $verify_candidate = $conn->query("SELECT * FROM candidates WHERE id = '$candidate_id' AND status = 'accepted'");
            if($verify_candidate->num_rows == 0){
                throw new Exception('One or more selected candidates are invalid or unverified.');
            }

            // Insert standard final ballot structure row (position_id = 0 matching structural consistency)
            $conn->query("INSERT INTO votes (voters_id, candidate_id, position_id, election_proper) VALUES ('$voter_id', '$candidate_id', 0, 1)");
        }
        
        $conn->commit();
        $_SESSION['success'] = 'Your final ballot has been cast successfully!';
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Database error encountered during submission: ' . $e->getMessage();
    }

} else {
    $_SESSION['error'] = 'Please fill out the official voting form first.';
}

header('location: home.php');
exit();
?>