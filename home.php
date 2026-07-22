<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">

    <?php include 'includes/navbar.php'; ?>
     
    <div class="content-wrapper">
        <div class="container">

            <!-- Main content -->
            <section class="content">
                <h1 class="page-header text-center title"><b>2026 ELECTION PORTAL</b></h1>
                
                <div class="row">
                    <div class="col-sm-10 col-sm-offset-1">
                        
                        <?php
                            $voter_id = mysqli_real_escape_string($conn, $voter['id']);
                            
                            // Fetch locked candidate pools
                            $locked_candidates = [];
                            $locked_query = $conn->query("SELECT id FROM candidates");
                            while($l_row = $locked_query->fetch_assoc()){
                                $locked_candidates[] = $l_row['id'];
                            }

                            // See how many voters are available to be nominated globally
                            $voter_count_query = $conn->query("SELECT id FROM voters WHERE id != '$voter_id'");
                            $available_in_db = 0;
                            while($vc_row = $voter_count_query->fetch_assoc()){
                                if(!in_array($vc_row['id'], $locked_candidates)){
                                    $available_in_db++;
                                }
                            }

                            if(isset($_SESSION['error'])){
                                echo "
                                    <div class='alert alert-danger alert-dismissible'>
                                        <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                                        <h4><i class='icon fa fa-warning'></i> Error!</h4>
                                        ".htmlspecialchars($_SESSION['error'])."
                                    </div>
                                ";
                                unset($_SESSION['error']);
                            }
                            if(isset($_SESSION['success'])){
                                echo "
                                    <div class='alert alert-success alert-dismissible'>
                                        <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                                        <h4><i class='icon fa fa-check'></i> Success!</h4>
                                        ".htmlspecialchars($_SESSION['success'])."
                                    </div>
                                ";
                                unset($_SESSION['success']);
                            }
                        ?>

                        <!-- PHASE GATE CHECK -->
                        <?php
                            $nom_query = $conn->query("SELECT * FROM votes WHERE voters_id = '$voter_id' AND election_proper = 0");
                            
                            // Check if user is an officially accepted candidate
                            $check_accepted = $conn->query("SELECT * FROM candidates WHERE id = '$voter_id' AND status = 'accepted'");
                            $is_accepted_candidate = ($check_accepted && $check_accepted->num_rows > 0);

                            // Check if they previously chose to explicitly skip nominating
                            $check_skipped = $conn->query("SELECT * FROM votes WHERE voters_id = '$voter_id' AND candidate_id = 0 AND election_proper = 0");
                            $has_skipped = ($check_skipped && $check_skipped->num_rows > 0);

                            // The user has cleared Stage 1 if they voted, if they skipped, or if no pool exists
                            $has_nominated = ($nom_query->num_rows > 0 || $available_in_db == 0 || $has_skipped);
                            
                            $check_nom = $conn->query("SELECT * FROM candidates WHERE id = '$voter_id' AND status = 'pending'");
                            if($check_nom && $check_nom->num_rows > 0): 
                        ?>
                                <div class="box box-solid box-warning" style="border: 2px solid #f39c12;">
                                    <div class="box-header with-border" style="background-color: #f39c12; color: #fff;">
                                        <h3 class="box-title"><i class="fa fa-envelope"></i> Nomination Action Required</h3>
                                    </div>
                                    <div class="box-body text-center" style="padding: 30px 20px;">
                                        <h3><strong>You have been nominated!</strong></h3>
                                        <p style="font-size: 16px; margin-bottom: 25px;">
                                            Please choose whether you want to accept or decline before proceeding to the system dashboard.
                                        </p>
                                        <div>
                                            <a href="respond_nomination.php?action=accept&id=<?php echo urlencode($voter['id']); ?>" class="btn btn-success btn-lg btn-flat" style="margin-right: 15px; font-weight: bold;"><i class="fa fa-check"></i> Accept Nomination</a>
                                            <a href="respond_nomination.php?action=decline&id=<?php echo urlencode($voter['id']); ?>" class="btn btn-danger btn-lg btn-flat" style="font-weight: bold;"><i class="fa fa-close"></i> Decline Nomination</a>
                                        </div>
                                    </div>
                                </div>
                        <?php else: ?>

                                <!-- SYSTEM MENU TABS -->
                                <div class="nav-tabs-custom">
                                    <ul class="nav nav-tabs">
                                        <li class="active"><a href="#nomination_tab" data-toggle="tab" style="font-size: 16px; font-weight: bold;"><i class="fa fa-user-plus"></i> 1. Select Nominees</a></li>
                                        <li><a href="#election_tab" data-toggle="tab" style="font-size: 16px; font-weight: bold;"><i class="fa fa-check-square-o"></i> 2. Cast Final Votes</a></li>
                                    </ul>
                                    
                                    <div class="tab-content" style="padding: 20px 10px;">
                                        
                                        <!-- ==================== MENU TAB 1: NOMINATION MATRIX ==================== -->
                                        <div class="tab-pane active" id="nomination_tab">
                                            <?php if($available_in_db == 0){ ?>
                                                    <div class="text-center" style="padding: 30px 0;">
                                                        <div style="font-size: 50px; color: #3c8dbc;"><i class="fa fa-info-circle"></i></div>
                                                        <h3>All Candidates Already Nominated</h3>
                                                        <p class="text-muted">There are no remaining unnominated voters left in the system.</p>
                                                        <a href="#election_tab" class="btn btn-primary btn-flat switch-to-final"><i class="fa fa-arrow-right"></i> Proceed to Cast Final Votes</a>
                                                    </div>
                                            <?php } elseif($has_skipped){ ?>
                                                    <div class="text-center" style="padding: 30px 0;">
                                                        <div style="font-size: 50px; color: #f39c12;"><i class="fa fa-fast-forward"></i></div>
                                                        <h3>You Skipped The Nomination Phase</h3>
                                                        <p class="text-muted">You opted not to nominate anyone. You can now proceed to vote.</p>
                                                        <a href="#election_tab" class="btn btn-success btn-flat switch-to-final"><i class="fa fa-arrow-right"></i> Open Election Ballot</a>
                                                    </div>
                                            <?php } elseif($nom_query->num_rows > 0 && !$has_skipped){ ?>
                                                    <div class="text-center" style="padding: 30px 0;">
                                                        <div style="font-size: 50px; color: #00a65a;"><i class="fa fa-check-circle"></i></div>
                                                        <h3>Nominations Submitted & Locked</h3>
                                                        <p class="text-muted">You have completed Stage 1 of this election.</p>
                                                        <a href="#view" data-toggle="modal" class="btn btn-primary btn-flat btn-sm"><i class="fa fa-eye"></i> View Selections</a>
                                                    </div>
                                            <?php } else {
                                                    // PAGINATION FOR NOMINEES LIST
                                                    $limit_nom = 5; 
                                                    $page_nom = isset($_GET['p_nom']) ? (int)$_GET['p_nom'] : 1;
                                                    if($page_nom < 1) $page_nom = 1;
                                                    $offset_nom = ($page_nom - 1) * $limit_nom;

                                                    $total_nom_stmt = $conn->query("SELECT COUNT(*) AS total FROM voters WHERE id != '$voter_id'");
                                                    $total_nom_rows = $total_nom_stmt->fetch_assoc()['total'];
                                                    $total_nom_pages = ceil($total_nom_rows / $limit_nom);

                                                    $required_nominees = ($available_in_db < 2) ? $available_in_db : 2;
                                                    ?>
                                                    
                                                    <!-- Optional Alert for Nominated Users -->
                                                    <?php if($is_accepted_candidate): ?>
                                                        <div class="callout callout-info">
                                                            <h4><i class="fa fa-info"></i> Candidate Notice</h4>
                                                            <p>Since you are an official nominee, you can choose to nominate others using the form below, or completely skip this phase by clicking the <strong>"I don't want to nominate"</strong> button.</p>
                                                        </div>
                                                    <?php endif; ?>

                                                    <form method="POST" action="submit_ballot.php" id="nominationForm">
                                                        <p>Please select <strong>exactly <?php echo $required_nominees; ?></strong> voter(s) from the list below to nominate them:</p>
                                                        <div class="form-group">
                                                            <?php
                                                                $sql = "SELECT * FROM voters WHERE id != '$voter_id' ORDER BY lastname ASC LIMIT $offset_nom, $limit_nom";
                                                                $query = $conn->query($sql);
                                                                while($row = $query->fetch_assoc()){
                                                                    $image = (!empty($row['photo'])) ? 'images/'.$row['photo'] : 'images/profile.jpg';
                                                                    $is_locked = in_array($row['id'], $locked_candidates);
                                                                    $row_style = $is_locked ? "opacity: 0.55; background-color: #f9f9f9;" : "";
                                                                    $checkbox_attr = $is_locked ? "disabled" : "class='flat-red target-nominee' name='nominated_voters[]' value='".htmlspecialchars($row['id'])."'";
                                                                    
                                                                    echo "
                                                                        <div class='row' style='margin-bottom:15px; border-bottom:1px solid #f4f4f4; padding-bottom:10px; ".$row_style."'>
                                                                            <div class='col-xs-1 text-center' style='padding-top: 12px;'>
                                                                                <input type='checkbox' ".$checkbox_attr." >
                                                                            </div>
                                                                            <div class='col-xs-2 col-md-1'>
                                                                                <img src='".htmlspecialchars($image)."' width='50px' height='50px' class='img-circle' style='object-fit:cover;'>
                                                                            </div>
                                                                            <div class='col-xs-9'>
                                                                                <span style='font-size:16px; font-weight:bold; display:block;'>".htmlspecialchars($row['firstname'])." ".htmlspecialchars($row['lastname'])."</span>
                                                                                ".($is_locked ? "<small class='label label-danger'><i class='fa fa-lock'></i> Already Nominated</small>" : "<small class='text-muted'><i class='fa fa-user-plus'></i> Action: Select Nominee</small>")."
                                                                            </div>
                                                                        </div>
                                                                    ";
                                                                }
                                                            ?>
                                                        </div>
                                                        
                                                        <?php if($total_nom_pages > 1): ?>
                                                        <div class="text-center">
                                                            <ul class="pagination pagination-sm no-margin">
                                                                <li class="<?php if($page_nom <= 1){ echo 'disabled'; } ?>"><a href="<?php echo $page_nom <= 1 ? '#' : '?p_nom='.($page_nom - 1); ?>">&laquo; Prev</a></li>
                                                                <?php for($i = 1; $i <= $total_nom_pages; $i++): ?>
                                                                    <li class="<?php echo $page_nom == $i ? 'active' : ''; ?>"><a href="?p_nom=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                                                                <?php endfor; ?>
                                                                <li class="<?php if($page_nom >= $total_nom_pages){ echo 'disabled'; } ?>"><a href="<?php echo $page_nom >= $total_nom_pages ? '#' : '?p_nom='.($page_nom + 1); ?>">Next &raquo;</a></li>
                                                            </ul>
                                                        </div>
                                                        <?php endif; ?>

                                                        <div class="row" style="margin-top: 15px;">
                                                            <div class="col-sm-6">
                                                                <button type="submit" class="btn btn-primary btn-flat btn-block" name="vote"><i class="fa fa-paper-plane"></i> Submit Nominations</button>
                                                            </div>
                                                            <div class="col-sm-6">
                                                                <?php if($is_accepted_candidate): ?>
                                                                    <button type="submit" class="btn btn-default btn-flat btn-block text-red" style="font-weight: bold; border-color: #dd4b39;" name="skip_nomination" formnovalidate><i class="fa fa-ban"></i> I don't want to nominate anyone</button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </form>
                                                <?php } ?>
                                        </div>

                                        <!-- ==================== MENU TAB 2: ELECTION PROPER ==================== -->
                                        <div class="tab-pane" id="election_tab">
                                            <?php if(!$has_nominated){ ?>
                                                <div class="text-center text-danger" style="padding: 40px 20px;">
                                                    <div style="font-size: 50px;"><i class="fa fa-ban"></i></div>
                                                    <h3>Access Denied: Nominations Required</h3>
                                                    <p class="text-muted" style="font-size:15px;">You must submit your candidate selections or opt-out under the **1. Select Nominees** tab first before you can cast final ballot votes.</p>
                                                </div>
                                            <?php } else { 
                                                $prop_query = $conn->query("SELECT * FROM votes WHERE voters_id = '$voter_id' AND election_proper = 1");
                                                if($prop_query->num_rows > 0){
                                                    ?>
                                                    <div class="text-center" style="padding: 30px 0;">
                                                        <div style="font-size: 50px; color: #00a65a;"><i class="fa fa-check-square"></i></div>
                                                        <h3>Final Ballot Cast Successfully</h3>
                                                        <p class="text-muted">You have already submitted your final votes for this election.</p>
                                                    </div>
                                                    <?php
                                                } else {
                                                    // PAGINATION FOR CANDIDATES LIST
                                                    $limit_can = 5; 
                                                    $page_can = isset($_GET['p_can']) ? (int)$_GET['p_can'] : 1;
                                                    if($page_can < 1) $page_can = 1;
                                                    $offset_can = ($page_can - 1) * $limit_can;

                                                    $total_can_stmt = $conn->query("SELECT COUNT(*) AS total FROM candidates WHERE status = 'accepted'");
                                                    $total_can_rows = ($total_can_stmt) ? $total_can_stmt->fetch_assoc()['total'] : 0;
                                                    $total_can_pages = ceil($total_can_rows / $limit_can);

                                                    $required_votes = ($total_can_rows < 3) ? $total_can_rows : 3;
                                                    ?>
                                                    <form method="POST" action="submit_proper_vote.php" id="electionForm">
                                                        <input type="hidden" name="pool_bypass" value="1">
                                                        
                                                        <p class="text-primary"><i class="fa fa-info-circle"></i> Please select <strong>exactly <?php echo $required_votes; ?></strong> candidate(s) from the verified list below:</p>
                                                        <div class="form-group">
                                                            <?php
                                                                $sql = "SELECT c.id AS canid, v.firstname, v.lastname, v.photo, c.platform 
                                                                        FROM candidates c
                                                                        LEFT JOIN voters v ON c.id = v.id
                                                                        WHERE c.status = 'accepted' 
                                                                        ORDER BY v.lastname ASC 
                                                                        LIMIT $offset_can, $limit_can";
                                                                $query = $conn->query($sql);
                                                                
                                                                if(!$query || $query->num_rows == 0){
                                                                    echo "<p class='text-center text-muted' style='padding:20px;'>No official nominees have accepted their candidacies yet.</p>";
                                                                } else {
                                                                    while($row = $query->fetch_assoc()){
                                                                        $image = (!empty($row['photo'])) ? 'images/'.$row['photo'] : 'images/profile.jpg';
                                                                        echo "
                                                                            <div class='row' style='margin-bottom:15px; border-bottom:1px solid #f4f4f4; padding-bottom:10px;'>
                                                                                <div class='col-xs-1 text-center' style='padding-top: 12px;'>
                                                                                    <input type='checkbox' class='flat-red election-candidate' name='final_votes[]' value='".htmlspecialchars($row['canid'])."'>
                                                                                </div>
                                                                                <div class='col-xs-2 col-md-1'>
                                                                                    <img src='".htmlspecialchars($image)."' width='50px' height='50px' class='img-circle' style='object-fit:cover;'>
                                                                                </div>
                                                                                <div class='col-xs-9'>
                                                                                    <span style='font-size:16px; font-weight:bold; display:block;'>".htmlspecialchars($row['firstname'])." ".htmlspecialchars($row['lastname'])."</span>
                                                                                    <small class='text-muted'><i class='fa fa-briefcase'></i> Platform: ".(!empty($row['platform']) ? htmlspecialchars($row['platform']) : 'No platform stated')."</small>
                                                                                </div>
                                                                            </div>
                                                                        ";
                                                                    }
                                                                }
                                                            ?>
                                                        </div>
                                                        
                                                        <?php if($total_can_pages > 1): ?>
                                                        <div class="text-center">
                                                            <ul class="pagination pagination-sm no-margin">
                                                                <li class="<?php if($page_can <= 1){ echo 'disabled'; } ?>"><a href="<?php echo $page_can <= 1 ? '#' : '?p_can='.($page_can - 1); ?>">&laquo; Prev</a></li>
                                                                <?php for($i = 1; $i <= $total_can_pages; $i++): ?>
                                                                    <li class="<?php echo $page_can == $i ? 'active' : ''; ?>"><a href="?p_can=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                                                                <?php endfor; ?>
                                                                <li class="<?php if($page_can >= $total_can_pages){ echo 'disabled'; } ?>"><a href="<?php echo $page_can >= $total_can_pages ? '#' : '?p_can='.($page_can + 1); ?>">Next &raquo;</a></li>
                                                            </ul>
                                                        </div>
                                                        <?php endif; ?>

                                                        <?php if($total_can_rows > 0): ?>
                                                            <button type="submit" class="btn btn-success btn-flat btn-block" style="margin-top:15px;" name="submit_proper_ballot"><i class="fa fa-check-square-o"></i> Cast Final Ballot</button>
                                                        <?php endif; ?>
                                                    </form>
                                                <?php } 
                                            } ?>
                                        </div>

                                    </div>
                                </div>

                        <?php endif; ?>
                        
                    </div>
                </div>
            </section>
             
        </div>
    </div>
  
    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/ballot_modal.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>

<script>
$(document).ready(function(){
    $('.flat-red').iCheck({
        checkboxClass: 'icheckbox_flat-green',
        radioClass: 'iradio_flat-green'
    });

    var url = window.location.href;
    if(url.indexOf('p_can') !== -1 || url.indexOf('proper') !== -1 || <?php echo ($available_in_db == 0 || $has_skipped) ? 'true' : 'false'; ?>){
        $('.nav-tabs a[href="#election_tab"]').tab('show');
    }
    if(url.indexOf('p_nom') !== -1 && <?php echo ($available_in_db > 0 && !$has_skipped) ? 'true' : 'false'; ?>){
        $('.nav-tabs a[href="#nomination_tab"]').tab('show');
    }

    $('.switch-to-final').on('click', function(e){
        e.preventDefault();
        $('.nav-tabs a[href="#election_tab"]').tab('show');
    });

    var reqNominees = <?php echo isset($required_nominees) ? (int)$required_nominees : 0; ?>;
    var reqVotes = <?php echo isset($required_votes) ? (int)$required_votes : 0; ?>;

    // Stage 1 Validation
    $('input.target-nominee').on('ifChecked', function (evt) {
        if($('input.target-nominee').filter(':checked').length > reqNominees) {
            var self = this;
            setTimeout(function(){ $(self).iCheck('uncheck'); }, 1);
            alert("Validation Restriction: You may only pick " + reqNominees + " nominee(s).");
        }
    });

    $('#nominationForm').on('submit', function(e) {
        // If the skip button was clicked, don't validate checked counts
        if($(document.activeElement).attr('name') === 'skip_nomination') {
            return true; 
        }

        var checkedCount = $('input.target-nominee').filter(':checked').length;
        if(checkedCount !== reqNominees) {
            e.preventDefault();
            alert("Error: You have selected " + checkedCount + " nominee(s). You must select EXACTLY " + reqNominees + " nominee(s) before submitting.");
            return false;
        }
    });

    // Stage 2 Validation
    $('input.election-candidate').on('ifChecked', function (evt) {
        if($('input.election-candidate').filter(':checked').length > reqVotes) {
            var self = this;
            setTimeout(function(){ $(self).iCheck('uncheck'); }, 1);
            alert("Ballot Restriction: You can select a maximum of " + reqVotes + " candidates.");
        }
    });

    $('#electionForm').on('submit', function(e) {
        var checkedCount = $('input.election-candidate').filter(':checked').length;
        if(checkedCount !== reqVotes) {
            e.preventDefault();
            alert("Error: You have selected " + checkedCount + " candidate(s). You must select EXACTLY " + reqVotes + " candidates to cast your final ballot.");
            return false;
        }
    });
});
</script>
</body>
</html>