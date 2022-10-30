<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

 // ALB - New report: Payments by insurance companies

 include_once("../globals.php");
 include_once("../../library/patient.inc");
 include_once("../../library/acl.inc");
 require_once("../../library/formatting.inc.php");

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
$date_string = date('Y-m-d', strtotime(date('Y-01-01')));
 $form_from_date = (!empty($_POST['form_from_date'])) ?  DateToYYYYMMDD($_POST['form_from_date']) : $date_string;
 $form_to_date   = (!empty($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');

if ($_POST['form_csvexport']) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=payment_distribution.csv");
  header("Content-Description: File Transfer");
  // CSV headers:
  if (true) {
    echo '"Insurance",';
    echo '"Payments",';
    echo '"Percentage of Total"' . "\n";
  }
}
else {
?>
<html>
<head>
<title><?php echo xlt('Insurance Payment Distribution'); ?></title>
    <?php Header::setupHeader('datetime-picker'); ?>
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

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Insurance Payment Distribution'); ?></span>

<div id="report_parameters_daterange">
<?php echo text(oeFormatShortDate($form_from_date)) ." &nbsp;" . xlt("to") . " &nbsp; ". text(oeFormatShortDate($form_to_date)); ?>
</div>

<form name='theform' method='post' action='insurance_payment_report.php' id='theform'>
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
					<a href='#' class='css_button' onclick='$("#form_csvexport").attr("value","true"); $("#theform").submit();'>
					<span>
						<?php echo xlt('Export to CSV'); ?>
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
  <th> <?php echo xlt('Insurance Carrier'); ?> </th>
  <th style="text-align:right"> <?php echo xlt('Payments'); ?> </th>
  <th style="text-align:right"> <?php echo xlt('Percentage of Total'); ?> </th>
 </thead>
 <tbody>
<?php
} // end not export
if ($_POST['form_refresh'] || $_POST['form_csvexport']) {

    $from_date = isset($_POST['form_from_date']) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-01-01');
    $to_date = isset($_POST['form_to_date']) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');

  
  //Calculate grand total

  $tquery = sqlQuery("SELECT sum(aa.pay_amount) as payments from ar_activity as aa, ar_session as ar " .
           "where aa.session_id = ar.session_id AND ar.post_to_date >= ? " . 
           "AND ar.post_to_date <= ? AND ar.payment_type = 'insurance'", array($from_date, $to_date));
  $grand_total = $tquery['payments'];

  $query = "SELECT ic.name, i.provider, sum(aa.pay_amount) as payments from ar_activity as aa, " . 
           "ar_session as ar, form_encounter as fe, insurance_data as i, insurance_companies as ic " .
           "where ic.id = i.provider AND aa.session_id = ar.session_id AND ar.post_to_date >= ? " .
           "AND ar.post_to_date <= ? AND ar.payment_type = 'insurance' and fe.pid=aa.pid " . 
           "AND fe.encounter = aa.encounter and i.pid=fe.pid and i.date <= fe.date and " . 
           "aa.payer_type = (case when i.type = 'primary' then 1 " .
           "when i.type = 'secondary' then 2 " .
           "when i.type = 'tertiary' then 3 " .
           "end) and aa.pay_amount != 0 and " .
           "i.date = (Select max(i1.date) from insurance_data as i1 " .
           "where i1.pid = aa.pid and i1.date <= fe.date) group by i.provider order by ic.name";

  $res = sqlStatement($query, array($from_date, $to_date));

  $total_payments = 0;
  $total_percentage = 0;
  while ($row = sqlFetchArray($res)) {
    if ($_POST['form_csvexport']) {
        echo '"' . text($row['name']) . '",';
        echo '"' . text(oeFormatMoney($row['payments'])) . '",';
        echo '"' . text(sprintf('%0.2f', $row['payments']/$grand_total * 100)) . '"' . "\n";
    }
    else {
?>
 <tr>
  <td>
   <?php echo text($row['name']) ?>
  </td>
  <td align='right'>
   <?php echo text(oeFormatMoney($row['payments'])) ?>
  </td>
  <td align='right'>
   <?php echo text(sprintf('%0.2f', $row['payments']/$grand_total * 100)) ?>
  </td>
 </tr>
<?php
    } // end not export
      $total_payments += $row['payments'];
      $total_percentage += sprintf('%0.2f', $row['payments']/$grand_total * 100);
  } // end while
} // end if

if (! $_POST['form_csvexport']) {
?>
<?php if (!empty($_POST)) { ?>
<tr>
    <td><strong>Total Payments:</strong></td>
    <td align='center'><?php echo text(oeFormatMoney($total_payments)); ?></td>
    <td align="center"><?php echo text($total_percentage) . "%"; ?></td>
</tr>
 <?php } ?>
</tbody>
</table>
</div> <!-- end of results -->
</div>
</body>

</html>
<?php
} // end not export
?>
