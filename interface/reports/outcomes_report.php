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
  'patient' => 'lower(p.lname), lower(p.fname), l.date',
  'pid'  => 'lower(p.pid), l.date',
  'time'    => 'l.date, lower(p.lname), lower(p.fname)',
);

$form_from_date = (!empty($_POST['form_from_date'])) ?  DateToYYYYMMDD($_POST['form_from_date']) : '';   // date('Y-01-01');
$form_to_date = (!empty($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d'); // date('Y-12-31');
//$form_provider  = $_POST['form_provider'];
//$form_facility  = $_POST['form_facility'];
$form_outcome  = $_POST['form_outcome'];

$form_orderby = $ORDERHASH[$_REQUEST['form_orderby']] ?
  $_REQUEST['form_orderby'] : 'patient';
$orderby = $ORDERHASH[$form_orderby];

// Get the info.
//
$query = "SELECT " .
  "l.title, l.date, p.fname, p.mname, p.lname, p.pid " .
  "FROM lists AS l " .
  "LEFT OUTER JOIN patient_data AS p ON p.pid = l.pid " .
  "LEFT OUTER JOIN list_options AS lo ON l.outcome = lo.option_id " .
  "WHERE l.activity = 1 AND l.type = 'medical_problem' " .
  "AND lo.title = '$form_outcome' AND lo.list_id = 'outcome'";
if ($form_to_date) {
  $query .= "AND l.date <= '$form_to_date 23:59:59' ";
} 
if ($form_from_date) {
  $query .= "AND l.date >= '$form_from_date 00:00:00' ";
} 
$query .= "ORDER BY $orderby";

$res = sqlStatement($query);
?>
<html>
<head>
<title><?php echo xlt('Problem Outcome Report'); ?></title>
    <?php Header::setupHeader('datetime-picker'); ?>

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


<script type="text/javascript">

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

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Problem Outcomes'); ?></span>

<div id="report_parameters_daterange">
<?php echo text(oeFormatShortDate($form_from_date)) ." &nbsp; " . xlt("to") . " &nbsp; ". text(oeFormatShortDate($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='outcomes_report.php'>
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<div id="report_parameters">
<table>
 <tr>
  <!-- ALB Changed width for better look -->
  <td width='600px'>
	<div style='float:left'>

	<table class='text'>
		<tr>
			<td class='control-label'>
			   <?php echo xlt('From'); ?>:
			</td>
			<td>
                <input type='text' class='datepicker form-control' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'>
			</td>
			<td class='control-label'>
			   <?php echo xlt('To'); ?>:
			</td>
			<td>
                <input type='text' class='datepicker form-control' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'>
            </td>

			<td class='control-label'>
			   <?php echo xlt('Problem Outcome'); ?>:
			</td>
			<td>
			   <?php

			    // Build a drop-down list of outcomes.
     			    $tquery = "SELECT title FROM list_options WHERE ".
				  "list_id = 'outcome' ORDER BY option_id" ;
				 $tres = sqlStatement($tquery);

				 echo "   <select name='form_outcome'>\n";

				 while ($trow = sqlFetchArray($tres)) {
				  $outcome = $trow['title'];
				  echo "    <option value='$outcome'";
				  if ($outcome == $_POST['form_outcome']) echo " selected";
				  echo ">" . text($trow['title']) . "\n";

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
						<?php xl('Submit','e'); ?>
					</span>
					</a>

					<?php if ($_POST['form_refresh'] || $_POST['form_orderby'] ) { ?>
					<a href='#' class='css_button' onclick='window.print()'>
						<span>
							<?php xl('Print','e'); ?>
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
<table class="table table-striped">

 <thead>
  <th>
   <a href="nojs.php" onclick="return dosort('pid')"
   <?php if ($form_orderby == "pid") echo " style=\"color:#00cc00\"" ?>><?php  echo xlt('ID'); ?></a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('patient')"
   <?php if ($form_orderby == "patient") echo " style=\"color:#00cc00\"" ?>><?php  echo xlt('Patient'); ?></a>
  </th>
  <th>
   <?php  echo xlt('Problem'); ?>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('time')"
   <?php if ($form_orderby == "time") echo " style=\"color:#00cc00\"" ?>><?php echo xlt('Date Entered'); ?></a>
  </th>
 </thead>
 <tbody>
<?php
if ($res) {
  while ($row = sqlFetchArray($res)) {
?>
 <tr>
  <td>
   <?php echo text($row['pid']); ?>&nbsp;
  </td>
  <td>
   <?php echo text($row['lname']) . ', ' . text($row['fname']) . ' ' . text($row['mname']); ?>&nbsp;
  </td>
  <td>
   <?php echo text($row['title']); ?>&nbsp;
  </td>
  <td>
   <?php echo text(oeFormatShortDate(substr($row['date'], 0, 10))) ?>&nbsp;
  </td>
 </tr>
<?php
    }
  }
?>
</tbody>
</table>
</div>  <!-- end encresults -->
<?php } else { ?>
<div class='text'>
 	<?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
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
