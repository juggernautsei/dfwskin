<?php
// Copyright (C) 2007-2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This report shows past encounters with filtering and sorting.

require_once("../globals.php");
require_once("$srcdir/forms.inc");
//require_once("$srcdir/billing.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/formdata.inc.php";

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$alertmsg = ''; // not used yet but maybe later

// For each sorting option, specify the ORDER BY argument.
//
$ORDERHASH = array(
  'doctor'  => 'lower(u.lname), lower(u.fname), fe.date',
  'patient' => 'lower(p.lname), lower(p.fname), fe.date',
  'pubpid'  => 'lower(p.pubpid), fe.date',
  'time'    => 'fe.date, lower(u.lname), lower(u.fname)',
);

function bucks($amount) {
  if ($amount) printf("%.2f", $amount);
}

function show_doc_total($lastdocname, $coded, $doc_encounters) {
  if ($lastdocname) {
    echo " <tr>\n";
    echo "  <td class='detail'>$lastdocname</td>\n";
    echo "  <td class='detail'>$coded</td>\n";
    echo "  <td class='detail' align='left'>$doc_encounters</td>\n";
    echo " </tr>\n";
  }
}

$form_from_date = (isset($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-01-01');
$form_to_date = (isset($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_provider  = $_POST['form_provider'];
$form_facility  = $_POST['form_facility'];
$form_CPT = $_POST['form_CPT'];
$form_details   = $_POST['form_details'] ? true : false;
$form_insurance   = $_POST['form_insurance'] ? true : false;
$form_medicare_only   = $_POST['form_medicare_only'] ? true : false;
$form_new_pt_visit = $_POST['form_new_pt_visit'] ? true : false;
$form_est_pt_visit = $_POST['form_est_pt_visit'] ? true : false;
$form_new_patients = $_POST['form_new_patients'] ? true : false;
//ALB New var here
$form_payer_id  = (!empty($_POST['form_payer_id'])) ? $_POST['form_payer_id'] : "";
$form_hide_name = $_POST['form_hide_name'] ? true : false;

$form_orderby = $ORDERHASH[$_REQUEST['form_orderby']] ?
  $_REQUEST['form_orderby'] : 'doctor';
$orderby = $ORDERHASH[$form_orderby];

// Get the info.
//
 $query = "SELECT " .
  "fe.encounter, fe.date, fe.reason, " .
  "f.formdir, f.form_name, " .
  "b.code, b.id, " .
  "p.fname, p.mname, p.lname, p.pid, p.pubpid, p.DOB, FLOOR(DATEDIFF(fe.date, p.DOB)/365) AS age, " .
  "u.lname AS ulname, u.fname AS ufname, u.mname AS umname " .
  "FROM ( form_encounter AS fe, forms AS f, billing as b ) " .
  "LEFT JOIN patient_data AS p ON p.pid = fe.pid " .
  "LEFT JOIN users AS u ON u.id = fe.provider_id " .
  "WHERE f.encounter = fe.encounter AND f.formdir ='newpatient' " .
  "AND b.pid = p.pid AND b.encounter = fe.encounter AND " .
  "b.activity = 1 AND b.authorized = 1 AND b.code_type <> 'COPAY' ";

//ALB Changed to allow multiple CPT codes
if ($form_CPT) {
   $CPT = explode(',', $form_CPT);
   $query .= "AND (";
   foreach($CPT as $value) {
       $query .= "b.code LIKE '$value' OR ";
   }
   $query = substr($query, 0, -4) . ") "; //remove last OR and add the end parenthesis
}

//ALB Change here to 2 if statements below
//if ($form_to_date) {
//  $query .= "AND fe.date >= '$form_from_date 00:00:00' AND fe.date <= '$form_to_date 23:59:59' ";
//} else {
//  $query .= "AND fe.date >= '$form_from_date 00:00:00' AND fe.date <= '$form_from_date 23:59:59' ";
//}

if ($form_from_date) $query .= "AND fe.date >= '$form_from_date 00:00:00' ";
if ($form_to_date) $query .= "AND fe.date <= '$form_to_date 23:59:59' ";


if ($form_provider) {
  $query .= "AND fe.provider_id = '$form_provider' ";
}
if ($form_facility) {
  $query .= "AND fe.facility_id = '$form_facility' ";
}
if ($form_new_patients) {
  $query .= "AND fe.date = (SELECT MIN(fe2.date) FROM form_encounter AS fe2 WHERE fe2.pid = fe.pid) ";
}

//ALB Only one result per patient encounter or one patient if "Unique record" is checked

if ($form_unique) {
  $query .= "GROUP BY p.pid ";
} else {
  $query .= "GROUP BY p.pid, fe.encounter ";
}

$query .= "ORDER BY $orderby";

$res = sqlStatement($query);
?>
<html>
<head>
<title><?php echo xlt('Encounters by Code Report'); ?></title>
    <?php Header::setupHeader(["common","datetime-picker","report-helper"]); ?>

<style type="text/css">

/* specifically include & exclude from printing */
@media print {
    #report_parameters {
        visibility: hidden;
        display: none;
    }
    #report_parameters_daterange {
        visibility: visible;
        display: inline;
    }
    #report_results table {
       margin-top: 0px;
    }
}

/* specifically exclude some from the screen */
@media screen {
    #report_parameters_daterange {
        visibility: hidden;
        display: none;
    }
}

</style>


<script LANGUAGE="JavaScript">
    $(function() {
        var win = top.printLogSetup ? top : opener.top;
        win.printLogSetup(document.getElementById('printbutton'));

        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = true; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });

    });

 function dosort(orderby) {
  var f = document.forms[0];
  f.form_orderby.value = orderby;
  f.submit();
  return false;
 }

 function refreshme() {
  document.forms[0].submit();
 }

</script>

</head>
<body class="body_top">
<div class="container">
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Encounters by CPT') . "/". xlt('ICD'); ?></span>

<div id="report_parameters_daterange">
    <?php echo text(oeFormatShortDate($form_from_date)) . " &nbsp; " . xlt('to') . "&nbsp; ". text(oeFormatShortDate($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='encounters_by_code_report.php'  onsubmit='return top.restoreSession()'>
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<div id="report_parameters">
<table>
 <tr>
  <td width='1000px'>
	<div style='float:left'>

	<table class='text'>
		<tr>
			<td class='control-label'>
				<?php echo xlt('Facility'); ?>:
			</td>
			<td>
			<?php dropdown_facility($form_facility, 'form_facility', true); ?>
			</td>
			<td class='control-label'>
			   <?php echo xlt('Provider'); ?>:
			</td>
			<td>
				<?php

				 // Build a drop-down list of providers.
				 //

				 $query = "SELECT id, lname, fname FROM users WHERE ".
				  "authorized = 1 $provider_facility_filter ORDER BY lname, fname"; //(CHEMED) facility filter

				 $ures = sqlStatement($query);

				 echo "   <select name='form_provider'>\n";
				 echo "    <option value=''>-- " . xlt('All') . " --\n";

				 while ($urow = sqlFetchArray($ures)) {
				  $provid = $urow['id'];
				  echo "    <option value='$provid'";
				  if ($provid == $_POST['form_provider']) echo " selected";
				  echo ">" . $urow['lname'] . ", " . $urow['fname'] . "\n";
				 }

				 echo "   </select>\n";

				?>
			</td>
			<!--td>
        <input type='checkbox' name='form_new_patients' title='First-time visits only'<?php  if ($form_new_patients) echo ' checked'; ?>>
        <?php  echo xlt('New'); ?>
			</td-->
			<td class='control-label'>
                   <?php echo xlt('CPT or ICD Codes'); ?>:
                </td>
                <td>
                   <input type='text' name='form_CPT' id='form_CPT' size='17' value='<?php echo $form_CPT ?>' title='Enter CPT code. If left blank, all CPT codes will be displayed. % may be used as a placeholder. Multiple codes may be separated by commas.'>
                   </td>                


		</tr>
		<tr>
			<td class='control-label'>
			   <?php echo xlt('From'); ?>:
			</td>
			<td>
                <input type='text' name='form_from_date' id="form_from_date"
                       class='datepicker form-control'
                       size='10' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'>
			</td>
			<td class='control-label'>
			   <?php echo xlt('To'); ?>:
			</td>
            
			<td>
                <input type='text' name='form_to_date' id="form_to_date"
                       class='datepicker form-control'
                       size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'>
			</td>
<td>  <input type='checkbox' id='form_details' name='form_details' onclick='document.getElementById("form_insurance").disabled = !this.checked; document.getElementById("form_new_pt_visit").disabled = !this.checked; document.getElementById("form_est_pt_visit").disabled = !this.checked; document.getElementById("form_hide_name").disabled = !this.checked; document.getElementById("form_medicare_only").disabled = true;' <?php  if ($form_details) echo ' checked'; ?>>
			   <?php  echo xlt('Show Details'); ?></td>
 <td> <table>
                   <tr><td>

			   <input type='checkbox' id='form_new_pt_visit' name='form_new_pt_visit' <?php if (!$form_details) echo '  disabled';  if ($form_new_pt_visit) echo ' checked';?>>
			   <?php  echo xlt('New Patient Visit'); ?>
			   <input type='checkbox' id='form_est_pt_visit' name='form_est_pt_visit' <?php if (!$form_details) echo '  disabled';  if ($form_est_pt_visit) echo ' checked';?>>
			   <?php  echo xlt('Established Patient Visit'); ?>
			   <input type='checkbox' id='form_hide_name' name='form_hide_name' <?php if (!$form_details) echo '  disabled';  if ($form_hide_name) echo ' checked';?>>
			   <?php  echo xlt('Initials Only'); ?>
			 </td></tr><tr><td>
			   <input type='checkbox' id='form_insurance' name='form_insurance' onclick='document.getElementById("form_medicare_only").disabled = !this.checked;' <?php if (!$form_details) echo '  disabled'; if ($form_insurance) echo ' checked';?>>
			   <?php echo xlt('Insurance Info'); ?>
			   <input type='checkbox' id='form_medicare_only' name='form_medicare_only' <?php if (!($form_details AND $form_insurance)) echo '  disabled'; if ($form_medicare_only) echo ' checked';?>>
			   <?php  echo xlt('Medicare Only'); ?>
			   <input type='checkbox' id='form_unique' name='form_unique' <?php if ($form_unique) echo ' checked';?>>
			   <?php  echo xlt('Unique Records Only'); ?></td></tr>
			</table>

                </tr><tr>
                <!-- ALB Added insurance drop-down box -->
                                       <td class='control-label'>
                                          <?php echo xlt('Insurance'); ?>:
                                       </td>

                                       <td colspan=3 >
                                          <?php  # added dropdown for payors (TLH)
                                           $insurancei = getInsuranceProviders();
                                           echo "   <select name='form_payer_id' class='form-control'>\n";
                                           echo "    <option value='0'>-- " . xlt('All') . " --</option>\n";
                                           foreach ($insurancei as $iid => $iname) {
                                              echo "<option value='" . attr($iid) . "'";
                                              if ($iid == $_POST['form_payer_id']) {
                                                echo " selected";
                                              }

                                              echo ">" . text($iname) . "</option>\n";
                                              if ($iid == $_POST['form_payer_id']) {
                                                $ins_co_name = $iname;
                                              }
                                          }

                                          echo "   </select>\n";
                                         ?>
                                        </td>



		</tr>
	</table>

	</div>

  </td>
  <td align='left' valign='middle' height="100%">
	<table style='border-left:1px solid; width:100%; height:100%' >
		<tr>
			<td>
				<div style='margin-left:15px'>
					<a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
					<span>
						<?php echo xlt('Submit'); ?>
					</span>
					</a>

					<?php if ($_POST['form_refresh'] || $_POST['form_orderby'] ) { ?>
					<a href='#' class='css_button' onclick='window.print()'>
						<span>
							<?php echo xlt('Print'); ?>
						</span>
					</a>
					<?php } ?>
				</div>
			</td>
		</tr>
	</table>
  </td>
 </tr>
</table>

</div> <!-- end report_parameters -->

<?php
 if ($_POST['form_refresh'] || $_POST['form_orderby']) {
?>
<div id="report_results">
<table>

 <thead>

<?php if ($form_details) { ?>
  <th>
   <a href="nojs.php" onclick="return dosort('doctor')"
   <?php if ($form_orderby == "doctor") echo " style=\"color:#00cc00\"" ?>><?php  echo xlt('Provider'); ?> </a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('time')"
   <?php if ($form_orderby == "time") echo " style=\"color:#00cc00\"" ?>><?php  echo xlt('Date'); ?></a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('patient')"
   <?php if ($form_orderby == "patient") echo " style=\"color:#00cc00\"" ?>><?php  echo xlt('Patient'); ?></a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('pubpid')"
   <?php if ($form_orderby == "pubpid") echo " style=\"color:#00cc00\"" ?>><?php  echo xlt('Patient ID'); ?></a>
  </th>
  <th>
   <?php echo xlt('DOB'); ?>
  </th>
  <th>
   <?php echo xlt('Age'); ?>
  </th>
  <th>
   <?php echo ($form_CPT) ?  xlt('CPT Code/Procedure') :  xlt('CPT Codes/Procedures'); ?>
  </th>
  <th>
   <?php if ($form_insurance) echo xlt('Insurance'); ?>
  </th>
<?php } else { ?>
  <th><?php echo  xlt('Provider'); ?></td>
  <th>
   <?php echo ($form_CPT) ?  xlt('CPT Code/Procedure') :  xlt(''); ?>
  </th>
  <th><?php echo  xlt('Encounters'); ?></td>
<?php } ?>
 </thead>
 <tbody>
<?php
if ($res) {
  $lastdocname = "";
  $doc_encounters = 0;
  while ($row = sqlFetchArray($res)) {
    $patient_id = $row['pid'];
    $docname = '';
    if (!empty($row['ulname']) || !empty($row['ufname'])) {
      $docname = $row['ulname'];
      if (!empty($row['ufname']) || !empty($row['umname']))
        $docname .= ', ' . $row['ufname'] . ' ' . $row['umname'];
    }

    $errmsg  = "";

      if ($form_insurance || $form_payer_id) {  //ALB Changing this to pull insurance info if searching by payer_id
        $irow = sqlQuery("SELECT insurance_companies.name, insurance_companies.id " .
          "FROM insurance_data, insurance_companies WHERE " .
          "insurance_data.pid = $patient_id AND " .
          "insurance_data.type = 'primary' AND " .
          "insurance_data.date <= '" . $row['date'] . "' AND " .
          "insurance_companies.id = insurance_data.provider " .
          "ORDER BY insurance_data.date DESC LIMIT 1");
       $plan = $irow['name'] ? $irow['name'] : '-- No Insurance --';
       $plan1 = $plan;
       $plan1_id = $irow['id'] ? $irow['id'] : '';

       $irow = sqlQuery("SELECT insurance_companies.name, insurance_companies.id " .
          "FROM insurance_data, insurance_companies WHERE " .
          "insurance_data.pid = $patient_id AND " .
          "insurance_data.type = 'secondary' AND " .
          "insurance_data.date <= '" . $row['date'] . "' AND " .
          "insurance_companies.id = insurance_data.provider " .
          "ORDER BY insurance_data.date DESC LIMIT 1");
       if ($irow['name']) {
          $plan .= " / " . $irow['name'];
          $plan2 = $irow['name'];
       } else {
          $plan2 = "";
       }
       $plan2_id = $irow['id'] ? $irow['id'] : '';


       $irow = sqlQuery("SELECT insurance_companies.name, insurance_companies.id " .
          "FROM insurance_data, insurance_companies WHERE " .
          "insurance_data.pid = $patient_id AND " .
          "insurance_data.type = 'tertiary' AND " .
          "insurance_data.date <= '" . $row['date'] . "' AND " .
          "insurance_companies.id = insurance_data.provider " .
          "ORDER BY insurance_data.date DESC LIMIT 1");
       if ($irow['name']) {
          $plan .= " / " . $irow['name'];
          $plan3 = $irow['name'];
       } else {
          $plan3 = "";
       }
       $plan3_id = $irow['id'] ? $irow['id'] : '';
      
      //ALB For PQRS measures, only Medicare/RR Medicare patients need to be selected
      if ($form_medicare_only and
          ($plan1 <> "Medicare") and
          ($plan1 <> "RailRoad Medicare") and
          ($plan2 <> "Medicare") and
          ($plan2 <> "RailRoad Medicare") and
          ($plan3 <> "Medicare") and
          ($plan3 <> "RailRoad Medicare")
      ) continue;

      }

      //ALB If payer_id is selecter, skip all that have a different insurance
      if ($form_payer_id && ($plan1_id != $form_payer_id) && ($plan2_id != $form_payer_id) && ($plan3_id != $form_payer_id)) continue;


    if ($form_details) {
      // Fetch all other forms for this encounter.
      $encnames = '';      
      $encarr = getFormByEncounter($patient_id, $row['encounter'],
        "formdir, user, form_name, form_id");
      if($encarr!='') {
	      foreach ($encarr as $enc) {
	        if ($enc['formdir'] == 'newpatient') continue;
	        if ($encnames) $encnames .= '<br />';
	        $encnames .= $enc['form_name'];
	      }
      }     


      // Fetch coding and compute billing status.
      $coded = "";
      $billed_count = 0;
      $unbilled_count = 0;
      $new_pt_visit = false;
      $est_pt_visit = false;

      if ($billres = getBillingByEncounter($row['pid'], $row['encounter'],
        "code_type, code, code_text, billed"))
      {
        foreach ($billres as $billrow) {
          $title = addslashes($billrow['code_text']);
          //ALB Find if new patient or est pt visit
          if (strpos($billrow['code_text'], 'New patient') !== false) $new_pt_visit = true;
          if (strpos($billrow['code_text'], 'Established patient') !== false) $est_pt_visit = true;

          if ($billrow['code_type'] != 'COPAY' && $billrow['code_type'] != 'TAX' ) {
            $coded .= $billrow['code'] . ' - ' . $title . '; ' ;
            if ($billrow['billed']) ++$billed_count; else ++$unbilled_count;
          }
        }

        $coded = substr($coded, 0, strlen($coded) - 2);
        if ($form_CPT) {
           $cres = sqlStatement("SELECT code_text FROM codes " .
                  "WHERE code = '$form_CPT'");
           while ($crow = sqlFetchArray($cres)) {
             if ($crow['code_text']) {
               $coded = $form_CPT . " - " . $crow['code_text'];
             } else {
               //ALB Code text (description) was not found, because a placeholder (%) was used in the CPT/ICD code text box, so need to find a description for each individual code
               $cres = sqlStatement("SELECT code_text FROM codes " .
                  "WHERE code = '$coded'");
               while ($crow = sqlFetchArray($cres)) {
                if ($crow['code_text']) {
                  $coded .= " - " . $crow['code_text'];
                }
               }
             }
           }
        }
      }

     //ALB If CPT with an office visit is selected, skip the ones without the corresponding office visit
        if (($form_new_pt_visit AND $form_est_pt_visit) AND !($new_pt_visit OR $est_pt_visit)) continue;
        if (($form_new_pt_visit AND !($form_est_pt_visit)) AND !($new_pt_visit)) continue;
        if ((!($form_new_pt_visit) AND $form_est_pt_visit) AND !($est_pt_visit)) continue;


      // Figure product sales into billing status.
      $sres = sqlStatement("SELECT billed FROM drug_sales " .
        "WHERE pid = '{$row['pid']}' AND encounter = '{$row['encounter']}'");
      while ($srow = sqlFetchArray($sres)) {
        if ($srow['billed']) ++$billed_count; else ++$unbilled_count;
      }

      // Compute billing status.
      if ($billed_count && $unbilled_count) $status =  xlt('Mixed' );
      else if ($billed_count              ) $status =  xlt('Closed');
      else if ($unbilled_count            ) $status =  xlt('Open'  );
      else                                  $status =  xlt('Empty' );
?>
 <tr bgcolor='<?php echo $bgcolor ?>'>
  <td>
   <?php echo ($docname == $lastdocname) ? "" : xlt($docname) ?>&nbsp;
  </td>
  <td>
   <?php echo oeFormatShortDate(substr($row['date'], 0, 10)) ?>&nbsp;
  </td>
  <td>
   <!--ALB Adding the ability to hide patient name -->
   <?php if (!$form_hide_name) { ?>
     <?php echo text($row['lname']) . ', ' . text($row['fname']) . ' ' . text($row['mname']); ?>&nbsp;
   <?php } else { ?>
     <?php echo text(substr($row['fname'],0,1)) . text(substr($row['lname'],0,1)); ?>&nbsp;
   <?php } ?>
  </td>
  <td>
   <?php echo text($row['pubpid']); ?>&nbsp;
  </td>
  <td>
   <?php echo text($row['DOB']); ?>
  </td>
  <td>
   <?php echo text($row['age']); ?>
  </td>
  <td>
   <?php echo text($coded); ?>
  </td>
  <td>
   <?php if ($form_insurance) echo text($plan); ?>
  </td>
 </tr>
<?php
    } else {
  if ((!$form_details) && ($form_CPT)) {
     $coded = $form_CPT;
     $cres = sqlStatement("SELECT code_text FROM codes " .
             "WHERE code = '$form_CPT'");
     while ($crow = sqlFetchArray($cres)) {
       if ($crow['code_text']) $coded .= " - " . $crow['code_text'];
     }
  }
      if ($docname != $lastdocname) {
        show_doc_total($lastdocname, $coded, $doc_encounters);
        $doc_encounters = 0;
      }
      ++$doc_encounters;
    }
    $lastdocname = $docname;
  }


  if (!$form_details) show_doc_total($lastdocname, $coded, $doc_encounters);
}
?>
</tbody>
</table>
</div>  <!-- end encresults -->
<?php } else { ?>
<div class='text'>
 	<?php echo  xlt('Please input search criteria above, and click Submit to view results. If CPT code is left blank, all CPT codes will be displayed.', 'e' ); ?>
</div>
<?php } ?>

<input type="hidden" name="form_orderby" value="<?php echo $form_orderby ?>" />
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>

</form>
</div>
</body>

<script language='JavaScript'>


<?php if ($alertmsg) { echo " alert('$alertmsg');\n"; } ?>

</script>

</html>
