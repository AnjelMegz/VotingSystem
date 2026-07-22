<?php
	include 'includes/session.php';

	if(isset($_POST['add_from_voter'])){
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		$position = $_POST['position'];
		$platform = $_POST['platform'];
		
		// Seamlessly carry over the voter's existing profile image string
		$filename = (!empty($_POST['existing_photo'])) ? $_POST['existing_photo'] : 'profile.jpg';

		$sql = "INSERT INTO candidates (position_id, firstname, lastname, photo, platform) VALUES ('$position', '$firstname', '$lastname', '$filename', '$platform')";
		if($conn->query($sql)){
			$_SESSION['success'] = 'Voter successfully promoted to candidate';
		}
		else{
			$_SESSION['error'] = $conn->error;
		}
	}
	// --- 2. YOUR ORIGINAL MANUAL ENTRY FALLBACK ---
	else if(isset($_POST['add'])){
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		$position = $_POST['position'];
		$platform = $_POST['platform'];
		$filename = $_FILES['photo']['name'];
		if(!empty($filename)){
			move_uploaded_file($_FILES['photo']['tmp_name'], '../images/'.$filename);	
		}

		$sql = "INSERT INTO candidates (position_id, firstname, lastname, photo, platform) VALUES ('$position', '$firstname', '$lastname', '$filename', '$platform')";
		if($conn->query($sql)){
			$_SESSION['success'] = 'Candidate added successfully';
		}
		else{
			$_SESSION['error'] = $conn->error;
		}
	}
	else{
		$_SESSION['error'] = 'Fill up add form first';
	}

	header('location: candidates.php');
?>