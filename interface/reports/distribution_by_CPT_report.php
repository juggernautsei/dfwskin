<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

 // This module shows relative insurance usage by unique patients
 // that are seen within a given time period.  Each patient that had
 // a visit is counted only once, regardless of how many visits.

 include_once("../globals.php");
 include_once("../../library/patient.inc");
 include_once("../../library/acl.inc");
 require_once("../../library/formatting.inc.php");
 require_once ("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}
 // Might want something different here.
 //
 // if (! acl_check('acct', 'rep')) die("Unauthorized access.");

$from_date = isset($_POST['form_from_date']) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-01-01');
$to_date   = isset($_POST['form_to_date']) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
//ALB Additional options
$form_provider  = $_POST['form_provider'];
$form_facility  = $_POST['form_facility'];


if ($_POST['form_csvexport']) {
//  header("Pragma: public");
//  header("Expires: 0");
//  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
//  header("Content-Type: application/force-download");
//  header("Content-Disposition: attachment; filename=insurance_distribution.csv");
//  header("Content-Description: File Transfer");
  // CSV headers:
//  if (true) {
//    echo '"Insurance",';
//    echo '"Charges",';
//    echo '"Visits",';
//    echo '"Patients",';
//    echo '"Pt Pct"' . "\n";
//  }
}
else {
?>
<html>
<head>
<title><?php echo xlt('Distribution by CPT Code'); ?></title>
<?php Header::setupHeader(["datetime-picker","report-helper"]); ?>

<script language="JavaScript">
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
        // location.reload();
        document.forms[0].submit();
    }

</script>

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
</head>

<body class="body_top">
<div class="container">
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Distribution by CPT Code'); ?></span>

<div id="report_parameters_daterange">
<?php echo text(oeFormatShortDate($from_date)) ." &nbsp;" .xlt('to'). "&nbsp; ". text(oeFormatShortDate($to_date)); ?>
</div>

<form name='theform' method='post' action='distribution_by_CPT_report.php' id='theform' onsubmit='return top.restoreSession()'>
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<div id="report_parameters">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>

<table>
 <tr>
  <td width='410px'>
	<div style='float:left'>

	<table class='text'>
		<tr>
			<td class='control-label'>
			   <?php echo xlt('From'); ?>:
			</td>
			<td>
                <input type='text' name='form_from_date' id="form_from_date"
                       class='datepicker form-control'
                       size='10' value='<?php echo attr(oeFormatShortDate($from_date)); ?>'>
			</td>
			<td cclass='control-label'>
			   <?php echo xlt('To'); ?>:
			</td>
			<td>
                <input type='text' name='form_to_date' id="form_to_date"
                       class='datepicker form-control'
                       size='10' value='<?php echo attr(oeFormatShortDate($to_date)); ?>'>
			</td>
		</tr>
        <!--ALB Added ability to choose provider and facility here -->
        <tr>
            <td class='control-label'>
                <?php echo xlt('Facility'); ?>:
            </td>
            <td>
            <?php dropdown_facility($form_facility, 'form_facility', false); ?>
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

                 echo "   <select name='form_provider' class='form-control'>\n";
                 echo "    <option value=''>-- " . xlt('All') . " --\n";

                while ($urow = sqlFetchArray($ures)) {
                    $provid = $urow['id'];
                    echo "    <option value='" . attr($provid) . "'";
                    if ($provid == $_POST['form_provider']) {
                        echo " selected";
                    }

                    echo ">" . text($urow['lname']) . ", " . text($urow['fname']) . "\n";
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

					<?php if ($_POST['form_refresh']) { ?>
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

</form>
</div> <!-- end parameters -->

<div id="report_results">
<table class="table table-striped">

 <thead>
  <th align='left'> <?php echo xlt('CPT Code'); ?> </th>
  <th align='left'> <?php echo xlt('Description'); ?> </th>
  <th align='center'> <?php echo xlt('Number of Cases'); ?> </th>
  <th align='center'> <?php echo xlt('Percent of Total'); ?> </th>
 </thead>
 <tbody>
<?php
} // end not export
if ($_POST['form_refresh'] || $_POST['form_csvexport']) {

  $from_date = DateToYYYYMMDD($_POST['form_from_date']);
  $to_date   = DateToYYYYMMDD($_POST['form_to_date'], date('Y-m-d'));

  //ALB Ability to choose by facility and provider
  $where = '';
  if ($form_provider) {
     $where = "AND fe.provider_id = " . $form_provider . " " ;
  }
  if ($form_facility) {
     $where = "AND fe.facility_id = " . $form_facility . " " ;
  }
  //ALB Modified query to choose facility and provider
  $query = "SELECT b.code AS CPT, b.code_text AS description, " .
           "COUNT(b.pid) AS total FROM billing AS b, ".
           "codes AS c, form_encounter as fe WHERE b.code_type LIKE 'CPT%' AND " .
           "b.activity = 1 AND b.code NOT LIKE '99%' AND " .
           "b.date >= '$from_date' AND b.date <= '$to_date' AND " .
           "b.code = c.code AND fe.encounter = b.encounter " . $where . // ALB Will display cosmetic for now AND c.cosmetic = 0 " .
           "GROUP BY b.code ORDER BY total DESC";

  $tempres = sqlStatement($query);
  $overall = 0;
  while ($temprow = sqlFetchArray($tempres)) {
    $overall += $temprow['total'];
  }

  $res = sqlStatement($query);

  while ($row = sqlFetchArray($res)) {
    $CPT = $row['CPT'];
    $description = $row['description'];
    $total = $row['total'];
    $pct = round($total/$overall * 100, 2);

?>
 <tr>
  <td>
   <?php echo text($CPT) ?>
  </td>
  <td>
   <?php echo text($description) ?>
  </td>
  <td align='center'>
   <?php echo text($total) ?>
  </td>
  <td align='center'>
   <?php echo text($pct) ?>
  </td>
 </tr>
<?php
  } // end while
} // end if

if (! $_POST['form_csvexport']) {
?>

</tbody>
</table>
</div> <!-- end of results -->
</div>
</body>


</script>
</html>
<?php
} // end not export
?>
