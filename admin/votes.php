<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Votes
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Votes</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
      <?php
        if(isset($_SESSION['error'])){
          echo "
            <div class='alert alert-danger alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Error!</h4>
              ".$_SESSION['error']."
            </div>
          ";
          unset($_SESSION['error']);
        }
        if(isset($_SESSION['success'])){
          echo "
            <div class='alert alert-success alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-check'></i> Success!</h4>
              ".$_SESSION['success']."
            </div>
          ";
          unset($_SESSION['success']);
        }
      ?>
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <a href="#reset" data-toggle="modal" class="btn btn-danger btn-sm btn-flat"><i class="fa fa-refresh"></i> Reset</a>
            </div>
            <div class="box-body">
              <table id="example1" class="table table-bordered">
                <thead>
                  <th class="hidden"></th>
                  <th>Position</th>
                  <th>Candidate</th>
                  <th>Voter</th>
                </thead>
                <tbody>
  <?php
    // We use LEFT JOINs to fetch name strings from the voters directory table for BOTH roles
    $sql = "SELECT v.id AS voteid, 
                   v.position_id, 
                   v.election_proper, 
                   vtr.firstname AS v_first, vtr.lastname AS v_last,
                   cand_vtr.firstname AS c_first, cand_vtr.lastname AS c_last,
                   p.description AS pos_description
            FROM votes v
            LEFT JOIN voters vtr ON vtr.id = v.voters_id
            LEFT JOIN voters cand_vtr ON cand_vtr.id = v.candidate_id
            LEFT JOIN positions p ON p.id = v.position_id
            ORDER BY v.id DESC";
            
    $query = $conn->query($sql);
    while($row = $query->fetch_assoc()){
      
      // 1. Identify Voting Stage / Position
      if($row['election_proper'] == 0){
          $position_display = "<span class='label label-warning'><i class='fa fa-user-plus'></i> Stage 1: Nomination</span>";
      } else if($row['election_proper'] == 1){
          $position_display = "<span class='label label-success'><i class='fa fa-check-square-o'></i> Stage 2: Final Vote</span>";
      } else {
          $position_display = (!empty($row['pos_description'])) ? htmlspecialchars($row['pos_description']) : 'Standard Position';
      }

      // 2. Resolve Candidate Full Name
      $candidate_name = (!empty($row['c_first'])) ? htmlspecialchars($row['c_first'].' '.$row['c_last']) : 'Unknown Nominee';
      
      // 3. Resolve Voter Full Name
      $voter_name = (!empty($row['v_first'])) ? htmlspecialchars($row['v_first'].' '.$row['v_last']) : 'System Voter';

      echo "
        <tr>
          <td>".$position_display."</td>
          <td>".$candidate_name."</td>
          <td>".$voter_name."</td>
        </tr>
      ";
    }
  ?>
</tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>   
  </div>
    
  <?php include 'includes/footer.php'; ?>
  <?php include 'includes/votes_modal.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>
