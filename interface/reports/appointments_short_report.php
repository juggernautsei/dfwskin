<?php
// Copyright (C) 2005-2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This report shows upcoming appointments with filtering and
// sorting by patient, practitioner, appointment type, and date.

//ALB New report

require_once("../globals.php");
require_once("../../library/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/formdata.inc.php";
require_once "$srcdir/appointments.inc.php";

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$alertmsg = ''; // not used yet but maybe later
$patient = $_REQUEST['patient'];

//ALB Adding ability not to show canceled or rescheduled appts
$show_canceled = false;
if ( $_POST['form_show_canceled'] == "on" ) {
	$show_canceled = true;
	var_dump($show_canceled);
}

    $from_date = isset($_POST['form_from_date']) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');
    $to_date = isset($_POST['form_to_date']) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
    $provider  = $_POST['form_provider'];
    $facility  = $_POST['form_facility'];

if ($_POST['form_csvexport']) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=appointments.csv");
  header("Content-Description: File Transfer");
  // CSV headers:
  if (true) {
    echo '"Provider",';
    echo '"Date",';
    echo '"Time",';
    echo '"Patient",';
    echo '"Phone Number"' . "\n";
  }
} else {
?>

<html>

<head>
<title><?php echo xlt('Appointments Report - Short'); ?></title>
<?php Header::setupHeader(["datetime-picker","report-helper"]); ?>

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

 function oldEvt(eventid) {
    dlgopen('../main/calendar/add_edit_event.php?eid=' + eventid, 'blank', 550, 270);
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

<!-- Required for the popup date selectors -->
<div id="overDiv"
	style="position: absolute; visibility: hidden; z-index: 1000;"></div>

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Appointments'). " (". xlt('Short').")"; ?></span>

<div id="report_parameters_daterange"><?php echo text(oeFormatShortDate($from_date))  ." &nbsp;".xlt('to'). "&nbsp; ". text(oeFormatShortDate($to_date)); ?>
</div>

<form method='post' name='theform' id='theform'	action='appointments_short_report.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">

<input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>

<table>
	<tr>
		<td width='1000px'>
		<div style='float: left'>

		<table class='text'>
			<tr>
				<td class='control-label'><?php echo xlt('Facility'); ?>:</td>
				<td><?php dropdown_facility($facility, 'form_facility'); ?>
				</td>
				<td class='control-label'><?php echo xlt('Provider'); ?>:</td>
				<td><?php

				// Build a drop-down list of providers.
				//

				$query = "SELECT id, lname, fname FROM users WHERE ".
				  "authorized = 1 $provider_facility_filter ORDER BY lname, fname"; //(CHEMED) facility filter

				$ures = sqlStatement($query);

				echo "   <select name='form_provider' class='form-control'>\n";
				echo "    <option value=''>-- " . xlt('All') . " --\n";

				while ($urow = sqlFetchArray($ures)) {
					$provid = $urow['id'];
					echo "    <option value='$provid'";
					if ($provid == $_POST['form_provider']) echo " selected";
					echo ">" . $urow['lname'] . ", " . $urow['fname'] . "\n";
				}

				echo "   </select>\n";

				?></td>

			        <td class='control-label'><?php echo xlt('From'); ?>:</td>
				<td><input type='text' name='form_from_date' id="form_from_date" class='datepicker form-control'
                    size='10' value='<?php echo attr(oeFormatShortDate($from_date)); ?>'></td>
				<td class='control-label'><?php echo xlt('To'); ?>:</td>
				<td><input type='text' name='form_to_date' id="form_to_date"
                    class='datepicker form-control' size='10' value='<?php echo attr(oeFormatShortDate($to_date)); ?>'></td>

                     <!--ALB Adding a Show Canceled Appt checkbox -->
				<td>
                                        <input type='checkbox' name='form_show_canceled' class="form-control"
					title='<?php echo xlt('Show Canceled Appointments'); ?>'
					<?php  if ( $show_canceled ) echo ' checked'; ?>> 
                                </td>
                                <td>
                                    <?php  echo xl( 'Show Canceled Appointments'); ?>
                                </td>


			</tr>
		</table>

		</div>

		</td>
		<td align='left' valign='middle' height="100%">
		<table style='border-left: 1px solid; width: 100%; height: 100%'>
			<tr>
				<td>
				<div style='margin-left: 15px'><a href='#' class='css_button'
					onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
				<span> <?php echo xlt('Submit'); ?> </span> </a> <?php if ($_POST['form_refresh']) { ?>
				<a href='#' class='css_button' onclick='window.print()'> <span> <?php echo xlt('Print'); ?>
				</span> </a>
				<a href='#' class='css_button' onclick='$("#form_csvexport").attr("value","true"); $("#theform").submit();'>
				<span><?php echo xlt('Export to CSV'); ?>
				</span></a>
 <?php } ?></div>
				</td>
			</tr>
		</table>
		</td>
	</tr>
</table>

</div>
<!-- end of search parameters --> 

<div id="report_results">
<table>

	<thead>
		<th><?php echo xlt('Provider'); ?></th>
		<th><?php  echo xlt('Date'); ?></th>
		<th><?php  echo xlt('Time'); ?></th>
		<th><?php  echo xlt('Patient'); ?></th>
		<th><?php  echo xlt('Phone Number'); ?></th>
	</thead>
	<tbody>

	<?php
     } //end not export
     if ($_POST['form_refresh'] || $_POST['form_csvexport']) {

	$lastdocname = "";
     //ALB Added one more parameter - do not show cancelled or rescheduled appointments
	$appointments = fetchAppointments( $from_date, $to_date, $patient, $provider, $facility, ($show_canceled ? '' : 'NO_SHOW'));

	foreach ( $appointments as $appointment ) {

    if ($_POST['form_csvexport']) {
        echo '"' . text($appointment['ufname']) . ' ' . text($appointment['umname']) . ' ' . text($appointment['ulname'])                                                . '",';
        echo '"' . text(oeFormatShortDate($appointment['pc_eventDate']))
. '",';
        echo '"' . text(oeFormatTime($appointment['pc_startTime']))
. '",';
        echo '"' . text($appointment['fname']) . " " . text($appointment['lname'])
. '",';
        echo '"' . ($appointment['phone_cell'] == '' ? text($appointment['phone_home']) : text($appointment['phone_cell']))
. '"' . "\n";
    }
    else {
	$patient_id = $appointment['pid'];
	$docname  = $appointment['ulname'] . ', ' . $appointment['ufname'] . ' ' . $appointment['umname'];
	$errmsg  = "";

		?>

	<tr bgcolor='<?php echo $bgcolor ?>'>
		<td class="detail">&nbsp;<?php echo ($docname == $lastdocname ? "" : $docname) ?>
		</td>

		<td class="detail"><?php echo text(oeFormatShortDate($appointment['pc_eventDate'])) ?>
		</td>

		<td class="detail"><?php echo text(oeFormatTime($appointment['pc_startTime'])) ?>
		</td>

		<td class="detail">&nbsp;<?php echo text($appointment['fname'] . " " . $appointment['lname']) ?>
		</td>

		<td class="detail">&nbsp;<?php echo ($appointment['phone_cell'] == '' ? text($appointment['phone_home']) : text($appointment['phone_cell'])) ?>
		</td>

	</tr>

	<?php
	$lastdocname = $docname;
    } // end if
	}  // end foreach

if (! $_POST['form_csvexport']) {	
	?>
	</tbody>
</table>
</div>
<?php }
     }
	?>
 <input type="hidden"
	name="patient" value="<?php echo $patient ?>" /> <input type='hidden'
	name='form_refresh' id='form_refresh' value='' /></form>

<?php
if (! $_POST['form_csvexport']) {	
	?>
<script>

<?php
if ($alertmsg) { echo " alert('$alertmsg');\n"; }
?>

</script>

</body>

<!-- stuff for the popup calendar -->
<style type="text/css">

</style>


</html>
<?php }
?>
