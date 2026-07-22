<?php
	session_start();
	include 'includes/conn.php';

	// --- 1. EXISTING LOGIN FUNCTIONALITY ---
	if(isset($_POST['login'])){
		$voter = $_POST['voter'];
		$password = $_POST['password'];

		$sql = "SELECT * FROM voters WHERE voters_id = '$voter'";
		$query = $conn->query($sql);

		if($query->num_rows < 1){
			$_SESSION['error'] = 'Cannot find voter with the ID';
		}
		else{
			$row = $query->fetch_assoc();
			if(password_verify($password, $row['password'])){
				$_SESSION['voter'] = $row['id'];
				header('location: home.php'); // Redirect to dashboard on success
				exit();
			}
			else{
				$_SESSION['error'] = 'Incorrect password';
			}
		}
		header('location: index.php');
		exit();
	}
	// --- 2. NEW SIGN-UP FUNCTIONALITY ---
	else if(isset($_POST['signup'])){
		$firstname = trim($_POST['firstname']);
		$lastname = trim($_POST['lastname']);
		$password = $_POST['password'];

		// Enforce secure password hashing matching your login check
		$hashed_password = password_hash($password, PASSWORD_DEFAULT); 

		// Generate easily memorable Voter ID: 3 letters of First Name + 2 letters of Last Name + 4 random characters
		$clean_first = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $firstname));
		$clean_last = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $lastname));

		$part_first = substr($clean_first, 0, 3);
		$part_last = substr($clean_last, 0, 2);

		$pool = '123456789ABCDEFGHJKLMNOPQRSTUVWXYZ';
		$random_suffix = substr(str_shuffle($pool), 0, 4);
		$voter_id = $part_first . $part_last . '-' . $random_suffix;

		// Default photo fallback layout setup
		$filename = $_FILES['photo']['name'];
		if(!empty($filename)){
			move_uploaded_file($_FILES['photo']['tmp_name'], 'images/'.$filename);
		}
		else{
			$filename = 'profile.jpg';
		}

		// Double-check if a voter under this name already exists
		$check = $conn->query("SELECT * FROM voters WHERE firstname = '$firstname' AND lastname = '$lastname'");
		if($check->num_rows > 0){
			$_SESSION['error'] = 'A voter with this exact name is already registered.';
		}
		else{
			// Insert configuration values directly matching your schema constraints
			$sql = "INSERT INTO voters (voters_id, password, firstname, lastname, photo) VALUES ('$voter_id', '$hashed_password', '$firstname', '$lastname', '$filename')";
			if($conn->query($sql)){
				$_SESSION['registered_id'] = $voter_id;
				$_SESSION['success'] = 'Account generated successfully!';
			}
			else{
				$_SESSION['error'] = 'Database error: ' . $conn->error;
			}
		}
		// Redirect back to show the generated ID block or errors
		header('location: signup.php');
		exit();
	}
	else{
		$_SESSION['error'] = 'Input voter credentials first';
		header('location: index.php');
		exit();
	}
?>