<?php
 // Copyright (C) 2008, 2010 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This report lists referrals for a given date range.

 require_once("../globals.php");
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

 $form_from_date = (!empty($_POST['form_from_date'])) ?  DateToYYYYMMDD($_POST['form_from_date']) : date('Y-01-01');
 $form_to_date   = (!empty($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-12-31');
 $form_facility = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
?>
<html>
<head>
<title><?php echo xlt('Referrals - Incoming'); ?></title>
    <?php Header::setupHeader('datetime-picker'); ?>

<script language="JavaScript">

var win = top.printLogSetup ? top : opener.top;
win.printLogSetup(document.getElementById('printbutton'));

$('.datepicker').datetimepicker({
    <?php $datetimepicker_timepicker = false; ?>
    <?php $datetimepicker_showseconds = false; ?>
    <?php $datetimepicker_formatInput = true; ?>
    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
});


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

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Referrals - Incoming'); ?></span>

<div id="report_parameters_daterange">
<?php echo text(oeFormatShortDate($form_from_date)) ." &nbsp; " . xlt("to") . " &nbsp; ". text(oeFormatShortDate($form_to_date)); ?>
</div>

<form name='theform' id='theform' method='post' action='referrals_incoming_report.php' onsubmit='return top.restoreSession()'>
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<div id="report_parameters">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<table>
 <tr>
  <td width='640px'>
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
</div> <!-- end of parameters -->


<?php
 if ($_POST['form_refresh']) {
?>
<div id="report_results">
<table class="table table-striped">
 <thead>
  <th> <?php echo xlt('Referring Provider'); ?> </th>
  <th> <?php echo xlt('Initial Visit Date'); ?> </th>
  <th> <?php echo xlt('Patient'); ?> </th>
  <th> <?php echo xlt('DOB'); ?> </th>
  <th> <?php echo xlt('Patient ID'); ?> </th>
  <th> <?php echo xlt('Reason'); ?> </th>
 </thead>
 <tbody>
<?php
 if ($_POST['form_refresh']) {
  
   $query = "SELECT p.pid, p.fname, p.lname, p.DOB, p.genericname1, fe.date, fe.reason, fe.facility_id, fe.pc_catid, fe.encounter " .
    "FROM patient_data AS p " .
    "LEFT OUTER JOIN form_encounter AS fe ON p.pid = fe.pid " .
    "WHERE p.genericname1 <> '' AND " .
    "fe.date >= '$form_from_date' AND fe.date <= '$form_to_date' " .
    "ORDER BY p.genericname1 ASC, fe.date ASC";

  $res = sqlStatement($query);

  $prev_provider = "";
  $total_referrals = 0;

  while ($row = sqlFetchArray($res)) {
    // If a facility is specified, ignore rows that do not match.
    if ($form_facility !== '') {
      if ($form_facility) {
        if ($row['facility_id'] != $form_facility) continue;
      }
      else {
        if (!empty($row['facility_id'])) continue;
      }
    }

    //Unless it's a Mohs consult, skip it if it's not the first visit.
    if ($row['pc_catid'] <> 17) {

      $tquery = "SELECT fe.encounter FROM form_encounter AS fe " .
        "WHERE fe.pid = " . $row['pid'] . " " .
        "ORDER BY fe.encounter ASC LIMIT 1";
      $tres = sqlStatement($tquery);
      while ($trow = sqlFetchArray($tres)) {
        $first_encounter = $trow['encounter'];
      }
      if ($first_encounter <> $row['encounter']) continue;
    }

    //Keep a running total of all referrals
    $total_referrals++;

    //Save provider name to skip in table, if same. Also, display total number of referrals for prev provider.
    if ($row['genericname1'] <> $prev_provider) {

      if ($prev_provider <> "") {
?>
    <tr>
      <td>
       &nbsp;&nbsp;&nbsp;<?php echo "Total new referrals from " . $prev_provider . ": " . $total_provider ?>
      </td><td></td><td></td><td></td><td></td><td></td>
    </tr>
<?php
      }
      $provider = $row['genericname1'];
      $prev_provider = $provider;
      $total_provider = 1;
    } else {
      $provider = '';
      $total_provider++; 
    }
    
?>
 <tr>
  <td>
   <b><?php echo text($provider); ?></b>
  </td>
  <td>
   <?php echo text(oeFormatShortDate(substr($row['date'],0,10))); ?>&nbsp;
  </td>
  <td>
   <?php echo text($row['fname']) . " " . text($row['lname']); ?>
  </td>
  <td>
   <?php echo text($row['DOB']); ?>
  </td>
  <td>
   <?php echo text($row['pid']); ?>
  </td>
  <td>
   <?php echo text($row['reason']); ?>
  </td>
 </tr>
<?php
  }

   //Add totals for the last provider
      if ($prev_provider <> "") {
?>
    <tr>
      <td>
      &nbsp;&nbsp;&nbsp;<?php echo "Total new referrals from " . $prev_provider . ": " . $total_provider ?>
      </td><td></td><td></td><td></td><td></td><td></td>
    </tr>
    <tr>
      <td>
      <b><?php echo "Total new referrals: " . $total_referrals ?></b>
      </td><td></td><td></td><td></td><td></td><td></td>
    </tr>

<?php
      }
 }
?>
</tbody>
</table>
</div> <!-- end of results -->
<?php } else { ?>
<div class='text'>
 	<?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
</div>
<?php } ?>
</form>

<script language='JavaScript'>
 
</script>
</div>
</body>
</html>
