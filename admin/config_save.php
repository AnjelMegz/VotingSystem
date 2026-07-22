<?php
	include 'includes/session.php';

	$return = 'home.php';
	if(isset($_GET['return'])){
		$return = $_GET['return'];
	}

	if(isset($_POST['save'])){
		$title = trim($_POST['title']);

		// Validate that the title is not empty
		if(!empty($title)){
			$file = 'config.ini';
			$content = 'election_title = '.$title;

			file_put_contents($file, $content);

			$_SESSION['success'] = 'Election title updated successfully';
		} else {
			$_SESSION['error'] = 'Election title cannot be empty';
		}
	}
	else{
		$_SESSION['error'] = 'Fill up config form first';
	}

	header('location: '.$return);
	exit();
?>