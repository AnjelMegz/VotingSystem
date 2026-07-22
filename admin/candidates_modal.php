<!-- Add New Candidate Modal (Replaces manual inputs with a searchable table of voters) -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog modal-lg" style="width: 85%;">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Promote Registered Voter to Candidate</b></h4>
            </div>
            <div class="modal-body">
              <div class="table-responsive">
                <table class="table table-bordered table-striped" id="voterSelectTable" style="width:100%;">
                  <thead>
                    <th style="width: 8%;">Photo</th>
                    <th>Firstname</th>
                    <th>Lastname</th>
                    <th style="width: 25%;">Assign Position</th>
                    <th style="width: 30%;">Platform / Goals</th>
                    <th style="width: 12%;">Action</th>
                  </thead>
                  <tbody>
                    <?php
                      // Fetch all registered voters from the database
                      $sql = "SELECT * FROM voters";
                      $vquery = $conn->query($sql);
                      while($vrow = $vquery->fetch_assoc()){
                        
                        // Prevent users who are already running from appearing twice
                        $check = $conn->query("SELECT * FROM candidates WHERE firstname = '".$conn->real_escape_string($vrow['firstname'])."' AND lastname = '".$conn->real_escape_string($vrow['lastname'])."'");
                        if($check && $check->num_rows > 0) continue; 

                        $image = (!empty($vrow['photo'])) ? '../images/'.$vrow['photo'] : '../images/profile.jpg';
                        
                        echo "
                          <tr>
                            <td align='center'><img src='".$image."' width='30px' height='30px' style='border-radius:50%;'></td>
                            <td style='vertical-align: middle;'>".$vrow['firstname']."</td>
                            <td style='vertical-align: middle;'>".$vrow['lastname']."</td>
                            
                            <!-- Submit form mapped per row item entry -->
                            <form method='POST' action='candidates_add.php'>
                              <input type='hidden' name='firstname' value='".htmlspecialchars($vrow['firstname'], ENT_QUOTES)."'>
                              <input type='hidden' name='lastname' value='".htmlspecialchars($vrow['lastname'], ENT_QUOTES)."'>
                              <input type='hidden' name='photo' value='".$vrow['photo']."'>
                              
                              <td style='vertical-align: middle;'>
                                <select class='form-control input-sm' name='position' required>
                                  <option value='' selected>- Select Position -</option>";
                                  
                                  // Fetch positions dynamically for the dropdown list selection
                                  $sql_pos = "SELECT * FROM positions";
                                  $pquery = $conn->query($sql_pos);
                                  while($prow = $pquery->fetch_assoc()){
                                    echo "<option value='".$prow['id']."'>".$prow['description']."</option>";
                                  }
                                  
                        echo "
                                </select>
                              </td>
                              <td style='vertical-align: middle;'>
                                <textarea class='form-control input-sm' name='platform' rows='1' placeholder='Enter goals...' required></textarea>
                              </td>
                              <td style='vertical-align: middle;'>
                                <button type='submit' class='btn btn-primary btn-sm btn-flat btn-block' name='add'><i class='fa fa-plus'></i> Promote</button>
                              </td>
                            </form>
                          </tr>
                        ";
                      }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- THE REMAINING UTILITY MODALS                                            -->
<!-- ======================================================================= -->

<!-- Edit Candidate Modal Layout -->
<div class="modal fade" id="edit">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title"><b>Edit Candidate</b></h4>
        </div>
        <div class="modal-body">
          <form class="form-horizontal" method="POST" action="candidates_edit.php">
            <input type="hidden" class="id" name="id">
            <div class="form-group">
                <label for="edit_firstname" class="col-sm-3 control-label">Firstname</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="edit_firstname" name="firstname" required>
                </div>
            </div>
            <div class="form-group">
                <label for="edit_lastname" class="col-sm-3 control-label">Lastname</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" id="edit_lastname" name="lastname" required>
                </div>
            </div>
            <div class="form-group">
                <label for="edit_position" class="col-sm-3 control-label">Position</label>
                <div class="col-sm-9">
                  <select class="form-control" id="edit_position" name="position" required>
                    <option value="" id="posselect" selected></option>
                    <?php
                      $sql = "SELECT * FROM positions";
                      $query = $conn->query($sql);
                      while($row = $query->fetch_assoc()){
                        echo "<option value='".$row['id']."'>".$row['description']."</option>";
                      }
                    ?>
                  </select>
                </div>
            </div>
            <div class="form-group">
                <label for="edit_platform" class="col-sm-3 control-label">Platform</label>
                <div class="col-sm-9">
                  <textarea class="form-control" id="edit_platform" name="platform" rows="5" required></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
          <button type="submit" class="btn btn-success btn-flat" name="edit"><i class="fa fa-check-square-o"></i> Update</button>
          </form>
        </div>
    </div>
  </div>
</div>

<!-- Delete Candidate Modal Layout -->
<div class="modal fade" id="delete">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title"><b>Deleting...</b></h4>
        </div>
        <div class="modal-body">
          <form class="form-horizontal" method="POST" action="candidates_delete.php">
            <input type="hidden" class="id" name="id">
            <div class="text-center">
                <p>DELETE CANDIDATE</p>
                <h2 class="fullname bold"></h2>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
          <button type="submit" class="btn btn-danger btn-flat" name="delete"><i class="fa fa-trash"></i> Delete</button>
          </form>
        </div>
    </div>
  </div>
</div>

<!-- View Platform Modal -->
<div class="modal fade" id="platform">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title"><b><span class="fullname"></span></b></h4>
        </div>
        <div class="modal-body">
            <p id="desc"></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
        </div>
    </div>
  </div>
</div>