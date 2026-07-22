<?php
  session_start();
  if(isset($_SESSION['admin'])){
    header('location: admin/home.php');
  }

  if(isset($_SESSION['voter'])){
    header('location: home.php');
  }
?>
<?php include 'includes/header.php'; ?>

<!-- Added custom CSS for dark button colors and hover states -->
<style>
  .btn-primary-dark {
    background-color: #002D62 !important;
    border-color: #001F4D !important;
  }
  .btn-primary-dark:hover {
    background-color: #001F4D !important;
    border-color: #00122E !important;
  }
  .btn-success-dark {
    background-color: #0B6623 !important;
    border-color: #064215 !important;
  }
  .btn-success-dark:hover {
    background-color: #064215 !important;
    border-color: #042B0D !important;
  }
</style>

<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
    <b>Voting System</b>
  </div>
  
  <div class="login-box-body">
    <p class="login-box-msg">Sign in</p>

    <form action="login.php" method="POST">
      <div class="form-group has-feedback">
        <input type="text" class="form-control" name="voter" placeholder="Voter's ID" required>
        <span class="glyphicon glyphicon-user form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" class="form-control" name="password" placeholder="Password" required>
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
      <div class="row">
        <div class="col-xs-12">
          <!-- Updated Sign In button with dark blue styling -->
          <button type="submit" class="btn btn-primary btn-block btn-flat btn-primary-dark" name="login"><i class="fa fa-sign-in"></i> Sign In</button>
        </div>
      </div>
    </form>

    <!-- Added Sign Up Functionality Link -->
    <hr style="margin-top: 15px; margin-bottom: 15px; border-top: 1px solid #eee;">
    <div class="text-center">
      <p style="margin-bottom: 5px; color: #666; font-size: 13px;">Not yet a registered voter?</p>
      <!-- Updated Sign Up button with dark green styling -->
      <a href="signup.php" class="btn btn-success btn-sm btn-flat btn-block btn-success-dark"><i class="fa fa-user-plus"></i> Create an Account / Sign Up</a>
    </div>
  </div>

  <?php
    if(isset($_SESSION['error'])){
      echo "
        <div class='callout callout-danger text-center mt20'>
          <p>".$_SESSION['error']."</p> 
        </div>
      ";
      unset($_SESSION['error']);
    }
  ?>
</div>
  
<?php include 'includes/scripts.php' ?>
</body>
</html>