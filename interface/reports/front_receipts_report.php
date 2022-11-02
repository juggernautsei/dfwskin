<?php

/**
 * This report lists front office receipts for a given date range.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2015 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once "$srcdir/options.inc.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('acct', 'rep_a')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Front Office Receipts")]);
    exit;
}

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$from_date = (isset($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');
$to_date   = (isset($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
//ALB Added here
$form_patient = $_REQUEST['form_patient'];
$form_pid       = $_REQUEST['form_pid'];
$form_dob       = $_REQUEST['form_dob'];

function bucks($amt)
{
    return ($amt != 0.00) ? oeFormatMoney($amt) : '';
}
?>
<html>
<head>

    <title><?php echo xlt('Front Office Receipts'); ?></title>

    <?php Header::setupHeader('datetime-picker'); ?>

    <script>
        <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

        $(function () {
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

        // The OnClick handler for receipt display.
        function show_receipt(pid,timestamp) {
            dlgopen('../patient_file/front_payment.php?receipt=1&patient=' + encodeURIComponent(pid) +
                '&time=' + encodeURIComponent(timestamp), '_blank', 550, 600, '', '', {
                onClosed: 'reload'
            });
         }

        //ALB Added here
        function setpatient(pid, lname, fname, dob) {
          document.forms[0].elements['form_patient'].value = lname + ', ' + fname;
          document.forms[0].elements['form_pid'].value = pid;
          document.forms[0].elements['form_dob'].value = dob;
        }
        function sel_patient() {
            dlgopen('../main/calendar/find_patient_popup.php?pflag=0', '_blank', 500, 400);
        }

    </script>

    <style>
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
        #report_results {
           margin-top: 30px;
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
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Front Office Receipts'); ?></span>

<div id="report_parameters_daterange">
<?php echo text(oeFormatShortDate($from_date)) . " &nbsp; " . xlt("to{{Range}}") . " &nbsp; " . text(oeFormatShortDate($to_date)); ?>
</div>

<form name='theform' method='post' action='front_receipts_report.php' id='theform' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">

<input type='hidden' name='form_refresh' id='form_refresh' value=''/>

<table>
 <tr>
  <td width='900px'>
    <div style='float:left'>

    <table class='text'>
        <tr>
            <td class='col-form-label'>
                <?php echo xlt('Facility'); ?>:
            </td>
            <td>
                <?php
                $form_facility = $_POST['form_facility'] ?? null;
                dropdown_facility($form_facility, 'form_facility', false);
                ?>
            </td>
            <td class='col-form-label'>
                <?php echo xlt('Provider') ?>:
            </td>
            <td>
            <?php  # Build a drop-down list of providers.
                    # Added (TLH)

                    $query = "SELECT id, lname, fname FROM users WHERE " .
                    "authorized = 1  ORDER BY lname, fname"; #(CHEMED) facility filter

                    $ures = sqlStatement($query);

                    echo "   <select name='form_provider' class='form-control'>\n";
                    echo "    <option value=''>-- " . xlt('All') . " --\n";

            while ($urow = sqlFetchArray($ures)) {
                $provid = $urow['id'];
                echo "    <option value='" . attr($provid) . "'";
                if (!empty($_POST['form_provider']) && ($provid == $_POST['form_provider'])) {
                    echo " selected";
                }

                echo ">" . text($urow['lname']) . ", " . text($urow['fname']) . "\n";
                if (!empty($_POST['form_provider']) && ($provid == $_POST['form_provider'])) {
                    $provider_name = $urow['lname'] . ", " . $urow['fname'];
                }
            }

                    echo "   </select>\n";
            ?>
            </td>
            </tr>
            <tr>
            <td class='col-form-label'>
                <?php echo xlt('From'); ?>:
            </td>
            <td>
               <input type='text' class='datepicker form-control' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($from_date)); ?>'>
            </td>
            <td class='col-form-label'>
                <?php xl('To{{Range}}', 'e'); ?>:
            </td>
            <td>
               <input type='text' class='datepicker form-control' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($to_date)); ?>'>
            </td>

            <!--ALB Adding patient selection here -->
            <td><span class='control-label'><?php echo xlt('Patient'); ?>:&nbsp;&nbsp;</span></td>
            <td>
               <input type='text' size='20' name='form_patient' class='form-control' style='cursor:pointer;' id='form_patient' value='<?php echo ($form_patient) ? attr($form_patient) : xla('Click To Select'); ?>' onclick='sel_patient()' title='<?php echo xla('Click to select patient'); ?>' />
               <input type='hidden' name='form_pid' value='<?php echo attr($form_pid); ?>' />
               <input type='hidden' name='form_dob' value='<?php echo attr($form_dob); ?>' />
            </td>

        </tr>
    </table>

    </div>

  </td>
  <td class='h-100' align='left' valign='middle'>
    <table class='w-100 h-100' style='border-left:1px solid;'>
        <tr>
            <td>
                <div class="text-center">
          <div class="btn-group" role="group">
                      <a href='#' class='btn btn-secondary btn-save' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
                            <?php echo xlt('Submit'); ?>
                      </a>
                        <?php if (!empty($_POST['form_refresh'])) { ?>
                        <a href='#' class='btn btn-secondary btn-print' id='printbutton'>
                                <?php echo xlt('Print'); ?>
                        </a>
                        <?php } ?>
          </div>
                </div>
            </td>
        </tr>
    </table>
  </td>
 </tr>
</table>
</div> <!-- end of parameters -->

<?php
if (!empty($_POST['form_refresh']) || !empty($_POST['form_orderby'])) {
    ?>
<div id="report_results">
<table class='table'>
<thead class='thead-light'>
<th> <?php echo xlt('Time'); ?> </th>
<th> <?php echo xlt('Patient'); ?> </th>
<th> <?php echo xlt('ID'); ?> </th>
<!--ALB Changed a few labels here -->
<th> <?php echo xlt('Payment Method'); ?> </th>
<th> <?php echo xlt('Check/Authorization Number'); ?> </th>
<th style="text-align: right"> <?php echo xlt('Applied to This Visit'); ?> </th>
<th style="text-align: right"> <?php echo xlt('Applied to Previous Visit'); ?> </th>
<th style="text-align: right"> <?php echo xlt('Total'); ?> </th>
</thead>
<tbody>
    <?php
    if (true || $_POST['form_refresh']) {
        $total1 = 0.00;
        $total2 = 0.00;

        $inputArray = array($from_date . ' 00:00:00', $to_date . ' 23:59:59');
        $input_total_Array=array($from_date.' 00:00:00', $to_date.' 23:59:59'); //ALB Added
        $query = "SELECT r.pid, r.dtime, " .
        "SUM(r.amount1) AS amount1, " .
        "SUM(r.amount2) AS amount2, " .
        "MAX(r.method) AS method, " .
        "MAX(r.source) AS source, " .
        "MAX(r.user) AS user, " .
        "p.fname, p.mname, p.lname, p.pubpid " .
        "FROM payments AS r " .
        "JOIN form_encounter AS fe ON fe.encounter=r.encounter " .
        "LEFT OUTER JOIN patient_data AS p ON " .
        "p.pid = r.pid " .
        "WHERE r.void != 1 AND " . //ALB Added not voided only
        "r.dtime >= ? AND " .
        "r.dtime <= ? AND ";

        $tempquery = ''; //ALB Added
        if ($_POST['form_facility'] != "") {
            $inputArray[] = $_POST['form_facility'];
            //$input_total_Array[] = $_POST['form_facility']; //ALB Added
            $query .= "fe.facility_id = ? AND ";
            //$tempquery= "AND p.pid = ? ";

        }
        if ($_POST['form_provider'] != "") {
            $inputArray[] = $_POST['form_provider'];
            //$input_total_Array[] = $_POST['form_provider']; //ALB Added
            $query .= "fe.provider_id = ? AND ";
        }
        //ALB Adding ability to select patient
        if ($_POST['form_pid']!="") {
            $inputArray[]=$_POST['form_pid'];
            $input_total_Array[]=$_POST['form_pid'];
            $query.="r.pid = ? AND ";
            $tempquery= "AND p.pid = ? ";
        }

        $query .= "1 GROUP BY r.dtime, r.pid ORDER BY r.dtime, r.pid";

        //ALB - Adding summary by payment method
        $total_query = "SELECT MAX(p.method) AS method, (SUM(p.amount1) + SUM(p.amount2)) AS total_amt FROM payments AS p " .
            "WHERE p.dtime >= ? AND " .
            "p.dtime <= ? AND " .
            "p.void != 1 " . $tempquery . 
            "GROUP BY p.method ORDER BY p.method";

        // echo " $query \n"; // debugging
        $res = sqlStatement($query, $inputArray);

        while ($row = sqlFetchArray($res)) {
            // Make the timestamp URL-friendly.
            $timestamp = preg_replace('/[^0-9]/', '', $row['dtime']);
            ?>
   <tr>
    <td nowrap>
     <a href="javascript:show_receipt(<?php echo attr_js($row['pid']); ?>, <?php echo attr_js($timestamp); ?>)">
            <?php echo text(oeFormatShortDate(substr($row['dtime'], 0, 10))) . text(substr($row['dtime'], 10, 6)); ?>
   </a>
  </td>
  <td>
            <?php echo text($row['lname']) . ', ' . text($row['fname']) . ' ' . text($row['mname']); ?>
  </td>
  <td>
            <?php echo text($row['pubpid']); ?>
  </td>
  <td>
            <?php echo text($row['method']); ?>
  </td>
  <td>
            <?php echo text($row['source']); ?>
  </td>
  <td align='right'>
            <?php echo text(bucks($row['amount1'])); ?>
  </td>
  <td align='right'>
            <?php echo text(bucks($row['amount2'])); ?>
  </td>
  <td align='right'>
            <?php echo text(bucks($row['amount1'] + $row['amount2'])); ?>
  </td>
 </tr>
            <?php
            $total1 += $row['amount1'];
            $total2 += $row['amount2'];
        }
        ?>

<tr>
 <td colspan='8'>
  &nbsp;
 </td>
</tr>

<tr class="report_totals">
 <td colspan='5'>
        <?php echo xlt('Totals'); ?>
 </td>
 <td align='right'>
        <?php echo text(bucks($total1)); ?>
 </td>
 <td align='right'>
        <?php echo text(bucks($total2)); ?>
 </td>
 <td align='right'>
        <?php echo text(bucks($total1 + $total2)); ?>
 </td>
</tr>

<!-- ALB Adding totals by pmt method -->
<tr class="report_totals">
   <td colspan='5'></td><td></td><td></td><td align='right'>
   <?php xl('Totals by Payment Method','e'); ?>
  </td>
 </tr>

  <?php
  }

 //ALB Adding totals by payment method
 $tres = sqlStatement($total_query, $input_total_Array);
 while ($trow = sqlFetchArray($tres)) {
 ?>
 <tr>
  <td colspan='5 align='right'>
  </td>
  <td></td>
  <td align='right'>
   <?php echo $trow['method'] ?>
  </td>
  <td align='right'>
   <?php echo $trow['total_amt'] ?>
  </td>
 </tr>

        <?php
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
</body>
</html>
