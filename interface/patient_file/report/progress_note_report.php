<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
// edited by sherwin 2020-11-05

require_once("../../globals.php");
require_once("$srcdir/forms.inc");
//require_once("$srcdir/billing.inc"); //This is not being used 
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/lists.inc");
require_once("$srcdir/report.inc");
require_once("$srcdir/classes/Document.class.php");
require_once("$srcdir/classes/Note.class.php");
require_once("$srcdir/formatting.inc.php");
require_once(dirname(__file__) . "/../../../custom/code_types.inc.php");

use OpenEMR\Core\Header;

// get various authorization levels
$auth_notes_a  = acl_check('encounters', 'notes_a');
$auth_notes    = acl_check('encounters', 'notes');
$auth_coding_a = acl_check('encounters', 'coding_a');
$auth_coding   = acl_check('encounters', 'coding');
$auth_relaxed  = acl_check('encounters', 'relaxed');
$auth_med      = acl_check('patients'  , 'med');
$auth_demo     = acl_check('patients'  , 'demo');

$printable = empty($_GET['printable']) ? false : true;
unset($_GET['printable']);

$N = 6;
$first_issue = 1;

function postToGet($arin) {
  $getstring="";
  foreach ($arin as $key => $val) {
    if (is_array($val)) {
      foreach ($val as $k => $v) {
        $getstring .= urlencode($key . "[]") . "=" . urlencode($v) . "&";
      }
    }
    else {
      $getstring .= urlencode($key) . "=" . urlencode($val) . "&";
    }
  }
  return $getstring;
}
?>
<html>
<head>
<?php html_header_show();?>
    <?php Header::setupHeader();?>

<?php // do not show stuff from report.php in forms that is encaspulated
      // by div of navigateLink class. Specifically used for CAMOS, but
      // can also be used by other forms that require output in the 
      // encounter listings output, but not in the custom report. ?>
<style> div.navigateLink {display:none;} </style>

</head>

<body class="body_top">
<div class="container">
<?php
if (sizeof($_GET) > 0) { $ar = $_GET; }
else { $ar = $_POST; }

if (!$printable) {
?>
<th align='left'>
<a href="<?php echo $_SERVER["HTTP_REFERER"]; ?>">
 <span class='title'><?php xl('Progress Note','e'); ?></span>
 <span class='back'><?php echo text($tback);?></span>
</a><br><br>
<a href="progress_note_report.php?printable=1&<?php print postToGet($ar); ?>" class='link_submit' target='new'>
 [<?php xl('Printable Version','e'); ?>]
</a><br>
</th>
<?php } // end not printable

  /*******************************************************************
  $titleres = getPatientData($pid, "fname,lname,providerID");
  $sql = "SELECT * FROM facility ORDER BY billing_location DESC LIMIT 1";
  *******************************************************************/
  $titleres = getPatientData($pid, "fname,lname,providerID,sex,title,usertext7,genericname1,genericval1, DOB,DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS");
  if ($titleres['usertext7']<>'') {
    $nickname = " ''" . $titleres['usertext7'] . "''";
  } else {
    $nickname = '';
  }
  if ($_SESSION['pc_facility']) {
    $sql = "select * from facility where id=" . $_SESSION['pc_facility'];
  } else {
    $sql = "SELECT * FROM facility ORDER BY billing_location DESC LIMIT 1";
  }
  /******************************************************************/
  $db = $GLOBALS['adodb']['db'];
  $results = $db->Execute($sql);
  $facility = array();
  if (!$results->EOF) {
    $facility = $results->fields;
  }
?>
<table style='width:100%' class="table">
<tr>
<th colspan='3'>
<h3 align='center'><?php xl('Progress Note','e'); ?></h3>
<!-- Don't need it for now
<div align='right'><?php echo $facility['name'] ?><br>
<?php echo $facility['street'] ?><br>
<?php echo $facility['city'] ?>, <?php echo $facility['state'] ?> <?php echo $facility['postal_code'] ?><br clear='all'></div>
</p>
-->
<h3 align='center'><span class='title'><?php xl('Patient Name','e'); ?>: <u><?php echo $titleres['title'] . " " . $titleres['lname'] . ", " . $titleres['fname'] . $nickname ; ?></u>
<?php xl('Patient ID','e'); ?>: <u><?php echo $pid; ?></u>
<?php xl('Date','e'); ?>: <u><?php echo oeFormatShortDate(); ?></u><p>
<?php xl('Age','e'); ?>: <u><?php echo getPatientAge($titleres['DOB']); ?></u>
<?php xl('Gender','e'); ?>: <u><?php echo $titleres['sex']; ?></u>
<?php xl('DOB','e'); ?>: <u><?php echo $titleres['DOB_TS']; ?></u>
</span>
</h3>
</th></tr>

<tr>

<!--1st column -->
<td width='30%' valign='top'>
<table border='1' class="table" id="pronote">
<?php
 if ($titleres['genericname1']<>'') {
   echo '<tr><td><span class="text"><b>';
   echo xl('Referring MD: ','e') . '<u>' . $titleres['genericname1'] . '</u></b></span></td></tr>';
 } ?>
<?php
 if ($titleres['genericval1']<>'') {
   echo '<tr><td><span class="text"><b>';
   echo xl('PCP: ','e') . '<u>' . $titleres['genericval1'] . '</u></b></span></td></tr>';
 } 
$numcols = '1';
$ix = 0;
$old_key="";$display_current_medications_below=1;


foreach ($ISSUE_TYPES as $key => $arr) {

    $query = "SELECT * FROM lists WHERE pid = ? AND type = ? AND ";
    $query .= "(enddate is null or enddate = '' or enddate = '0000-00-00') ";
    $query .= "ORDER BY begdate DESC, id DESC";
    $pres = sqlStatement($query, array($pid, $key) );

    if (sqlNumRows($pres) > 0 || $ix == 0 || $key == "allergy" || $key == "medication") {
	$old_key=$key;
    ?>
            
            <td>
            <span class="text"><b><?php echo htmlspecialchars($arr[0],ENT_NOQUOTES); ?></b></span>

            </td>
            </tr>
        <?php 
     //echo "<table>";    
	if (sqlNumRows($pres) == 0) {
          if ( getListTouch($pid,$key) ) {
            // Data entry has happened to this type, so can display an explicit None.
            echo "  <tr><td><span class='text'>&nbsp;&nbsp;" . htmlspecialchars( xl('None'), ENT_NOQUOTES) . "</span></td></tr>\n";
          }
          else {
            // Data entry has not happened to this type, so show 'Nothing Recorded"
            echo "  <tr><td><span class='text'>&nbsp;&nbsp;" . htmlspecialchars( xl('Nothing Recorded'), ENT_NOQUOTES) . "</span></td></tr>\n";
          }
	}
        	    
        while ($row = sqlFetchArray($pres)) {
            // output each issue for the $ISSUE_TYPE
            if (!$row['enddate'] && !$row['returndate'])
                $rowclass="noend_noreturn";
            else if (!$row['enddate'] && $row['returndate'])
                $rowclass="noend";
            else if ($row['enddate'] && !$row['returndate'])
                $rowclass = "noreturn";

            echo " <tr class='text $rowclass;'>\n";

	    //turn allergies red and bold and show the reaction (if exist)
	    if ($key == "allergy") {
                $reaction = "";
                if (!empty($row['reaction'])) {
                    $reaction = " (" . $row['reaction'] . ")";
                }
                echo "<td style='color:red;font-weight:bold;'>&nbsp;&nbsp;" . htmlspecialchars($row['title'] . $reaction, ENT_NOQUOTES) . "</td>\n";
	    }

         //ALB - Problems with notes describing color in the Outcome list are turned that color and bold
	    elseif ($key == "medical_problem") {
            $tempres = sqlStatement("SELECT * FROM list_options " .
                       "WHERE list_id = 'outcome' AND option_id = ".
			       $row['outcome'] . " LIMIT 1");
            while ($temprow = sqlFetchArray($tempres)) {
              if ($temprow['notes'] <> '') {
                $color = $temprow['notes'];
                echo "<td style='color:$color; font-weight:bold;'>&nbsp;&nbsp;" . htmlspecialchars($row['title'], ENT_NOQUOTES) . "</td>\n";
              } else {
	        echo "  <td>&nbsp;&nbsp;" . htmlspecialchars($row['title'],ENT_NOQUOTES) . "</td>\n";
	        }
            }
	    }
	    else {
	        echo "<td>&nbsp;&nbsp;" . htmlspecialchars($row['title'],ENT_NOQUOTES) . "</td>\n";
	    }

            echo " </tr>\n";
        }
	
    }
    ++$ix;
}
?>
</table>
</td>

<!--2nd column -->
<td width='40%' valign='top'>
<?php
$cres = sqlQuery("SELECT COUNT(*) as forms from form_encounter where pid = ? AND DATE(date) < NOW()", [$pid]);
$row = $cres['forms'];
if ($row>0) {
  echo "<span class='text'><b>".xl('Established Patient') ."</b></span><p>";

  //Get last skin check date
  $TBSE_date = '';
  $cres = sqlStatement("SELECT DATE(date) AS TBSE_date from rule_patient_data where pid = ? AND item='act_tbse' and DATE(date) < NOW() AND complete = 'YES' ORDER BY date DESC LIMIT 1", [$pid]);
  while($result = sqlFetchArray($cres)) {
    $TBSE_date = $result['TBSE_date'];
  }
  if ($TBSE_date == '') {
    $TBSE_date = 'None';
  }
  echo "<span class='text'><b>".xl('Last TBSE').": <u>$TBSE_date</u></b></span><p>";
  

  // Only show last SOAP note. Include form's report.php files
  $inclookupres = sqlStatement("SELECT formdir, form_id, f.encounter, fe.date FROM forms AS f JOIN form_encounter AS fe ON f.pid = fe.pid
AND f.encounter = fe.encounter WHERE f.pid = ? AND f.formdir = 'soap' AND f.deleted = 0 AND DATE( fe.date ) < NOW()
ORDER BY fe.date DESC LIMIT 1", [$pid]);
  while($result = sqlFetchArray($inclookupres)) {
    $formdir = $result['formdir'];
    $form_id = $result['form_id'];
    $form_encounter = $result['encounter'];
    $form_date = substr($result['date'],0,10);

    echo "<span class='text'><b>".xl('Last Visit Note ('). $form_date ."):</b></span><p>";
    echo "<table border='1' class='table'><tr><td>";
    include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
    call_user_func("soap_report", $pid, $form_encounter, 1, $form_id);
  }
    echo "</td></tr></table>";
} else {
  echo "<span class='text'><b>".xl('New Patient') ."</b></span><p>";
}

?>
<hr>
<?php
$cres = sqlStatement("SELECT pc_hometext from openemr_postcalendar_events where pc_pid = ? AND pc_eventDate = CURDATE() LIMIT 1", [$pid]);
$reason_for_visit = '';
while($result = sqlFetchArray($cres)) {
  $reason_for_visit = $result['pc_hometext'];
}
echo "<span class='text'><b>". xl('Chief Complaint/Reason for Visit').": </b><u>$reason_for_visit</u></span><br><br>";

?>
<span class='text'><b>Subjective:
<br><br><br>
Objective:
<br><br><br>
Assessment:
<br><br><br>
Plan:
<br><br><br>
Follow Up:<hr></b></span>
</td>

<!--3rd column -->
<td width='30%' valign='top'><table border='1' class="table"><tr><td>
<?php


 $result1 = getInsuranceData($pid, "primary", "copay, provider, DATE_FORMAT(`date`,'%Y-%m-%d') as effdate");
 $insco_name1 = "";

 if ($result1['provider']) {   // Use provider in case there is an ins record w/ unassigned insco
     $insco_name1 = getInsuranceProvider($result1['provider']);
     $insco_effdate = $result1['effdate'];
 }

 $result2 = getInsuranceData($pid, "secondary", "copay, provider, DATE_FORMAT(`date`,'%Y-%m-%d') as effdate");
 $insco_name2 = "";

 if ($result2['provider']) {   // Use provider in case there is an ins record w/ unassigned insco
     $insco_name2 = getInsuranceProvider($result2['provider']);
 }

 $result3 = getInsuranceData($pid, "tertiary", "copay, provider, DATE_FORMAT(`date`,'%Y-%m-%d') as effdate");
 $insco_name3 = "";

 if ($result3['provider']) {   // Use provider in case there is an ins record w/ unassigned insco
     $insco_name3 = getInsuranceProvider($result3['provider']);
 }


echo "<div class='text insurance'>";

echo "<b>". xlt(' Patient Due') . ":</b> " . 
(get_patient_balance($pid, false) <> 0 ? "<span class=bold style='color:red'>" . text(oeFormatMoney(get_patient_balance($pid, false))) . "</span><br>"
                                      : text(oeFormatMoney(get_patient_balance($pid, false))) . "<br>");


echo "<b>". xlt(' Total Balance') . ":</b> " . 
text(oeFormatMoney(get_patient_balance($pid, true)));
echo "<hr style='margin-top:0;margin-bottom:0;color:black;height:5px'/>";
echo "<b>".xl('Insurance Data').":</b><br>";
if ($insco_name1) {
  echo "<span class=bold>".xl('Primary').": $insco_name1</span><br>";
} else {
  echo "<span class=bold style='color:red'>".xl('Self-Pay')."</span><br>";
}
if ($insco_name2) {
  echo "<span class=bold>".xl('Secondary').": $insco_name2</span><br>";
}
if ($insco_name3) {
  echo "<span class=bold>".xl('Tertiary').": $insco_name3</span><br>";
}

$cres = sqlStatement("SELECT body from pnotes where pid = '$pid' AND title='Insurance' AND deleted=0 AND activity=1 AND date(date) >= '$insco_effdate' ORDER by id DESC LIMIT 3");
while($result = sqlFetchArray($cres)) {
  $ins_note = $result['body'];
  echo "<span class=bold>".xl('Ins Note').": </span>$ins_note<br>";
}

?>
</div></td></tr></table>





</td></tr>
</table>
</div>
</body>
</html>
