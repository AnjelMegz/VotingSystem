<?php
  // 1. Prevent output buffering leakage and suppress modern PHP switch/continue warnings in TCPDF
  ob_start();
  error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

  include 'includes/session.php';

  function generateRow($conn){
    $contents = '';
    
    // Header for the leaderboard layout
    $contents .= '
      <tr>
        <td width="15%" align="center"><b>Rank</b></td>
        <td width="65%"><b>Nominee / Candidate Name</b></td>
        <td width="20%" align="center"><b>Final Votes</b></td>
      </tr>
    ';

    // 2. Query only Stage 2 Final Cast Votes (election_proper = 1) sorted highest to lowest
    $sql = "SELECT v.candidate_id, COUNT(v.id) AS total_votes, 
                   vtr.firstname, vtr.lastname 
            FROM votes v
            LEFT JOIN voters vtr ON vtr.id = v.candidate_id
            WHERE v.election_proper = 1
            GROUP BY v.candidate_id 
            ORDER BY total_votes DESC";
            
    $query = $conn->query($sql);
    
    if($query->num_rows == 0){
      $contents .= '
        <tr>
          <td colspan="3" align="center">No final proper votes have been cast yet.</td>
        </tr>
      ';
    } else {
      $rank = 1;
      while($row = $query->fetch_assoc()){
        $fullname = $row['lastname'].", ".$row['firstname'];
        $votes = $row['total_votes'];

        $contents .= '
          <tr>
            <td align="center">'.$rank.'</td>
            <td> '.$fullname.'</td>
            <td align="center">'.$votes.'</td>
          </tr>
        ';
        $rank++;
      }
    }

    return $contents;
  }
    
  $parse = parse_ini_file('config.ini', FALSE, INI_SCANNER_RAW);
    $title = $parse['election_title'];

  require_once('../tcpdf/tcpdf.php');  
    $pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
    $pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('Result: '.$title);  
    $pdf->SetHeaderData('', '', PDF_HEADER_TITLE, PDF_HEADER_STRING);  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);  
    $pdf->SetMargins(PDF_MARGIN_LEFT, '10', PDF_MARGIN_RIGHT);  
    $pdf->setPrintHeader(false);  
    $pdf->setPrintFooter(false);  
    $pdf->SetAutoPageBreak(TRUE, 10);  
    $pdf->SetFont('helvetica', '', 11);  
    $pdf->AddPage();  
    $content = '';  
    $content .= '
      <h2 align="center">'.$title.'</h2>
      <h4 align="center">Final Live Standing Tallies (Highest to Lowest)</h4>
      <table border="1" cellspacing="0" cellpadding="5">  
      ';  
    $content .= generateRow($conn);  
    $content .= '</table>';  
    
    $pdf->writeHTML($content);  
    
    // Clear out any buffered warning strings before giving output delivery headers
    ob_end_clean();
    $pdf->Output('election_result.pdf', 'I');
?>