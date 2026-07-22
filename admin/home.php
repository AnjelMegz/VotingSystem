<?php include 'includes/session.php'; ?>
<?php include 'includes/slugify.php'; ?>
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
        Dashboard
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Dashboard</li>
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
      <!-- Small boxes (Stat box) -->
      <div class="row">
        <div class="col-lg-4 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-green">
            <div class="inner">
              <?php
                $sql = "SELECT * FROM candidates WHERE status = 'accepted'";
                $query = $conn->query($sql);

                echo "<h3>".$query->num_rows."</h3>";
              ?>
          
              <p>Active Candidates (Accepted)</p>
            </div>
            <div class="icon">
              <i class="fa fa-black-tie"></i>
            </div>
            <a href="candidates.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-4 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-yellow">
            <div class="inner">
              <?php
                $sql = "SELECT * FROM voters";
                $query = $conn->query($sql);

                echo "<h3>".$query->num_rows."</h3>";
              ?>
             
              <p>Total Registered Voters</p>
            </div>
            <div class="icon">
              <i class="fa fa-users"></i>
            </div>
            <a href="voters.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-4 col-xs-6">
          <!-- small box -->
          <div class="small-box bg-red">
            <div class="inner">
              <?php
                // Fixed to reflect only users who cast a final ballot
                $sql = "SELECT * FROM votes WHERE election_proper = 1 GROUP BY voters_id";
                $query = $conn->query($sql);

                echo "<h3>".$query->num_rows."</h3>";
              ?>

              <p>Voters Voted</p>
            </div>
            <div class="icon">
              <i class="fa fa-edit"></i>
            </div>
            <a href="votes.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->
      </div>

      <div class="row">
        <div class="col-xs-12">
          <h3>Top 15 Live Standings Tally (Final Cast Votes)
            <span class="pull-right">
              <a href="print.php" class="btn btn-success btn-sm btn-flat"><span class="glyphicon glyphicon-print"></span> Print</a>
            </span>
          </h3>
        </div>
      </div>

      <!-- Live Top 15 Chart Frame -->
      <div class="row">
        <div class="col-sm-12">
          <div class="box box-solid">
            <div class="box-header with-border">
              <h4 class="box-title"><b>Top 15 Most Voted Nominees (Highest to Lowest)</b></h4>
            </div>
            <div class="box-body">
              
              <!-- 1. Native UI Backup Component List -->
              <div class="row-ranking-list" style="margin-bottom: 30px; padding: 10px 5px;">
                <?php
                  // Added filter WHERE v.election_proper = 1
                  $list_sql = "SELECT v.candidate_id, COUNT(v.id) AS total_votes, 
                                     vtr.firstname, vtr.lastname, vtr.photo 
                              FROM votes v
                              LEFT JOIN voters vtr ON vtr.id = v.candidate_id
                              WHERE v.election_proper = 1
                              GROUP BY v.candidate_id 
                              ORDER BY total_votes DESC 
                              LIMIT 15";
                  $list_query = $conn->query($list_sql);
                  
                  if($list_query->num_rows == 0){
                      echo "<p class='text-center text-muted' style='padding:20px;'>No final proper votes have been cast yet.</p>";
                  } else {
                      $max_votes_row = $conn->query("SELECT COUNT(id) AS max_v FROM votes WHERE election_proper = 1 GROUP BY candidate_id ORDER BY max_v DESC LIMIT 1");
                      $max_votes_res = $max_votes_row->fetch_assoc();
                      $highest_vote_total = ($max_votes_res['max_v'] > 0) ? $max_votes_res['max_v'] : 1;

                      $rank = 1;
                      while($row_list = $list_query->fetch_assoc()){
                          $image = (!empty($row_list['photo'])) ? '../images/'.$row_list['photo'] : '../images/profile.jpg';
                          $fullname = htmlspecialchars($row_list['firstname'] . ' ' . $row_list['lastname']);
                          $votes = $row_list['total_votes'];
                          $percentage = ($votes / $highest_vote_total) * 100;
                          
                          if($rank == 1) { $bar_color = 'progress-bar-success'; }
                          elseif($rank == 2) { $bar_color = 'progress-bar-primary'; }
                          elseif($rank == 3) { $bar_color = 'progress-bar-warning'; }
                          else { $bar_color = 'progress-bar-info'; }
                          ?>
                          <div class="row" style="margin-bottom: 12px; display: flex; align-items: center;">
                              <div class="col-xs-1 text-center">
                                  <span class="badge" style="background-color: #34495e; font-size: 13px; padding: 4px 8px;"><?php echo $rank; ?></span>
                              </div>
                              <div class="col-xs-2 col-md-1 text-center">
                                  <img src="<?php echo $image; ?>" class="img-circle" width="38px" height="38px" style="object-fit: cover; border: 1px solid #ddd;">
                              </div>
                              <div class="col-xs-7 col-md-8">
                                  <span style="font-weight: bold; font-size: 14px; display: block; margin-bottom: 2px;"><?php echo $fullname; ?></span>
                                  <div class="progress progress-xs active" style="margin-bottom: 0; background-color: #f1f1f1;">
                                      <div class="progress-bar <?php echo $bar_color; ?>" role="progressbar" style="width: <?php echo $percentage; ?>%"></div>
                                  </div>
                              </div>
                              <div class="col-xs-2 text-right">
                                  <span style="font-size: 16px; font-weight: bold; color: #2c3e50;"><?php echo $votes; ?></span>
                                  <small class="text-muted" style="display:block; font-size: 9px;">VOTES</small>
                              </div>
                          </div>
                          <?php
                          $rank++;
                      }
                  }
                ?>
              </div>
              
              <hr style="border-top: 2px dashed #eee; margin: 25px 0;">

              <!-- 2. Chart Rendering Frame Box -->
              <div class="chart">
                <canvas id="topCandidatesChart" style="height:400px"></canvas>
              </div>

            </div>
          </div>
        </div>
      </div>

    </section>
  </div>
  <?php include 'includes/footer.php'; ?>

</div>
<!-- ./wrapper -->

<?php include 'includes/scripts.php'; ?>
<?php
  // Added filter WHERE v.election_proper = 1 to chart data parse engine
  $sql = "SELECT vtr.firstname, vtr.lastname, COUNT(v.id) AS total_votes 
          FROM votes v
          LEFT JOIN voters vtr ON vtr.id = v.candidate_id
          WHERE v.election_proper = 1
          GROUP BY v.candidate_id 
          ORDER BY total_votes DESC 
          LIMIT 15";
          
  $query = $conn->query($sql);
  $carray = array();
  $varray = array();
  
  while($row = $query->fetch_assoc()){
    array_push($carray, $row['firstname'].' '.$row['lastname']);
    array_push($varray, $row['total_votes']);
  }
  
  $carray = json_encode($carray);
  $varray = json_encode($varray);
?>
<script>
$(function(){
  var barChartCanvas = $('#topCandidatesChart').get(0).getContext('2d');
  var barChart = new Chart(barChartCanvas);
  var barChartData = {
    labels  : <?php echo $carray; ?>,
    datasets: [
      {
        label               : 'Votes Received',
        fillColor           : 'rgba(60,141,188,0.9)',
        strokeColor         : 'rgba(60,141,188,0.8)',
        pointColor          : '#3b8bba',
        pointStrokeColor    : 'rgba(60,141,188,1)',
        pointHighlightFill  : '#fff',
        pointHighlightStroke: 'rgba(60,141,188,1)',
        data                : <?php echo $varray; ?>
      }
    ]
  };
  
  var barChartOptions = {
    scaleBeginAtZero        : true,
    scaleShowGridLines      : true,
    scaleGridLineColor      : 'rgba(0,0,0,.05)',
    scaleGridLineWidth      : 1,
    scaleShowHorizontalLines: true,
    scaleShowVerticalLines  : true,
    barShowStroke           : true,
    barStrokeWidth          : 2,
    barValueSpacing         : 5,
    barDatasetSpacing       : 1,
    responsive              : true,
    maintainAspectRatio     : false
  };

  barChartOptions.datasetFill = false;
  barChart.HorizontalBar(barChartData, barChartOptions);
});
</script>
</body>
</html>