<!-- View Ballot Modal -->
<div class="modal fade" id="view">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title">Your Nominated Candidates</h4>
            </div>
            <div class="modal-body">
              <?php
                $voter_id = $voter['id'];
                
                // Query that tracks the submissions made by this voter from the votes log
                // and pulls their real names directly from the base voters table
                $sql = "SELECT v.firstname, v.lastname 
                        FROM votes vt
                        LEFT JOIN voters v ON v.id = vt.candidate_id 
                        WHERE vt.voters_id = '$voter_id'";
                        
                $query = $conn->query($sql);
                if($query && $query->num_rows > 0){
                    echo "<h4>You have selected:</h4>";
                    echo "<ul class='list-group'>";
                    $num = 1;
                    while($row = $query->fetch_assoc()){
                        echo "<li class='list-group-item' style='font-size:16px;'>
                                <strong>Nominee #".$num.":</strong> ".htmlspecialchars($row['firstname'])." ".htmlspecialchars($row['lastname'])."
                              </li>";
                        $num++;
                    }
                    echo "</ul>";
                } else {
                    echo "<p class='text-center text-muted'>No nomination data found for your account.</p>";
                }
              ?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
            </div>
        </div>
    </div>
</div>