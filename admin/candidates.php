<?php 
    include 'includes/session.php'; 
?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <section class="content-header">
      <h1>Candidates List</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Candidates</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> New</a>
            </div>
            <div class="box-body">
              <table id="example1" class="table table-bordered">
                <thead>
                  <th>Position</th>
                  <th>Photo</th>
                  <th>Firstname</th>
                  <th>Lastname</th>
                  <th>Platform</th>
                  <th>Tools</th>
                </thead>
                <tbody>
                  <?php
                    // Enforces filtering so only accepted nominees are visible to the admin
                    $sql = "SELECT c.id AS canid, v.firstname, v.lastname, v.photo, p.description AS pos_description, c.platform 
                            FROM candidates c 
                            LEFT JOIN voters v ON c.id = v.id 
                            LEFT JOIN positions p ON p.id = c.position_id
                            WHERE c.status = 'accepted'";
                            
                    $query = $conn->query($sql);
                    
                    if(!$query){
                        echo "<tr><td colspan='6' class='text-center text-danger'>Database Error: " . $conn->error . "</td></tr>";
                    } else {
                        if($query->num_rows == 0) {
                            echo "<tr><td colspan='6' class='text-center text-muted'>No official candidates have accepted their nominations yet.</td></tr>";
                        } else {
                            while($row = $query->fetch_assoc()){
                              $image = (!empty($row['photo'])) ? '../images/'.$row['photo'] : '../images/profile.jpg';
                              echo "
                                <tr>
                                  <td>".htmlspecialchars($row['pos_description'] ?? 'No Position Assigned')."</td>
                                  <td>
                                    <img src='".$image."' width='30px' height='30px' class='img-circle' style='object-fit: cover;'>
                                    <a href='#upload_photo' data-toggle='modal' class='pull-right photo' data-id='".$row['canid']."'><i class='fa fa-edit'></i></a>
                                  </td>
                                  <td>".htmlspecialchars($row['firstname'])."</td>
                                  <td>".htmlspecialchars($row['lastname'])."</td>
                                  <td><a href='#platform' data-toggle='modal' class='btn btn-info btn-sm btn-flat clist platform' data-id='".$row['canid']."'><i class='fa fa-search'></i> View</a></td>
                                  <td>
                                    <button class='btn btn-success btn-sm edit btn-flat' data-id='".$row['canid']."'><i class='fa fa-edit'></i> Edit</button>
                                    <button class='btn btn-danger btn-sm delete btn-flat' data-id='".$row['canid']."'><i class='fa fa-trash'></i> Delete</button>
                                  </td>
                                </tr>
                              ";
                            }
                        }
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
  <?php include 'includes/candidate_modal.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>