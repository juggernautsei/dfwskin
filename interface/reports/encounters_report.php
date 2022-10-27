<?php
/**
 *  Encounters report.
 *
 *  This report shows past encounters with filtering and sorting,
 *  Added filtering to show encounters not e-signed, encounters e-signed and forms e-signed.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Terry Hill <terry@lilysystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2007-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2015 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

//ALB Multiple changes - copy the entire file

require_once("../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/patient.inc");
require_once "$srcdir/options.inc.php";

use OpenEMR\Billing\BillingUtilities;
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
  'encounter'    => 'fe.encounter, fe.date, lower(u.lname), lower(u.fname)',
);

function show_doc_total($lastdocname, $doc_encounters)
{
    if ($lastdocname) {
        echo " <tr>\n";
        echo "  <td class='detail'>" .  text($lastdocname) . "</td>\n";
        echo "  <td class='detail' align='right'>" . text($doc_encounters) . "</td>\n";
        echo " </tr>\n";
    }
}

$form_from_date = (isset($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');
$form_to_date   = (isset($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_provider  = $_POST['form_provider'];
$form_facility  = $_POST['form_facility'];
$form_details   = $_POST['form_details'] ? true : false;
$form_new_patients = $_POST['form_new_patients'] ? true : false;
$form_esigned = $_POST['form_esigned'] ? true : false;
$form_not_esigned = $_POST['form_not_esigned'] ? true : false;
$form_encounter_esigned = $_POST['form_encounter_esigned'] ? true : false;
$form_encounter_not_coded = $_POST['form_encounter_not_coded'] ? true : false;
$form_no_soap = $_POST['form_no_soap'] ? true : false;

$form_orderby = $ORDERHASH[$_REQUEST['form_orderby']] ?
$_REQUEST['form_orderby'] : 'doctor';
$orderby = $ORDERHASH[$form_orderby];

// Get the info.
//
$esign_fields = '';
$esign_joins = '';
if ($form_encounter_esigned) {
    $esign_fields = ", es.table, es.tid ";
    $esign_joins = "LEFT OUTER JOIN esign_signatures AS es ON es.tid = fe.encounter ";

}

if ($form_esigned) {
    $esign_fields = ", es.table, es.tid ";
    //ALB Error here    
    //$esign_joins = "LEFT OUTER JOIN esign_signatures AS es ON es.tid = fe.encounter ";
    $esign_joins = "LEFT OUTER JOIN esign_signatures AS es ON es.tid = f.id ";
}

if ($form_not_esigned) {
    $esign_fields = ", es.table, es.tid ";
    //ALB Error here    
    //$esign_joins = "LEFT JOIN esign_signatures AS es on es.tid = fe.encounter ";
    $esign_joins = "LEFT JOIN esign_signatures AS es on es.tid = f.id ";

}

$sqlBindArray = array();

$query = "SELECT " .
  "fe.encounter, fe.date, fe.reason, " .
  "f.formdir, f.form_name, " .
  "p.fname, p.mname, p.lname, p.pid, p.pubpid, " .
  "u.lname AS ulname, u.fname AS ufname, u.mname AS umname " .
  "$esign_fields" .
  "FROM ( form_encounter AS fe, forms AS f ) " .
  "LEFT OUTER JOIN patient_data AS p ON p.pid = fe.pid " .
  "LEFT JOIN users AS u ON u.id = fe.provider_id " .
  "$esign_joins" .
  "WHERE f.pid = fe.pid AND f.encounter = fe.encounter AND f.deleted != 1 ";

//ALB We need to pull forms other than newpatient to check to see if they are signed or not
if (!$form_esigned && !$form_not_esigned) {
   $query .= "AND f.formdir = 'newpatient' ";
} else {
   //ALB Don't need the misc billing options form to be signed, either
   $query .= "AND f.formdir != 'newpatient' AND f.formdir != 'misc_billing_options' ";
} 

//ALB Fixed this below
//if ($form_to_date) {
//    $query .= "AND fe.date >= ? AND fe.date <= ? ";
//    array_push($sqlBindArray, $form_from_date . ' 00:00:00', $form_to_date . ' 23:59:59');
//} else {
//    $query .= "AND fe.date >= ? AND fe.date <= ? ";
//    array_push($sqlBindArray, $form_from_date . ' 00:00:00', $form_from_date . ' 23:59:59');
//}

if ($form_from_date) {
    $query .= "AND fe.date >= ? ";
    array_push($sqlBindArray, $form_from_date . ' 00:00:00');
}
if ($form_to_date) {
    $query .= "AND fe.date <= ? ";
    array_push($sqlBindArray, $form_to_date . ' 23:59:59');
}


if ($form_provider) {
    $query .= "AND fe.provider_id = ? ";
    array_push($sqlBindArray, $form_provider);
}

if ($form_facility) {
    $query .= "AND fe.facility_id = ? ";
    array_push($sqlBindArray, $form_facility);
}

if ($form_new_patients) {
    $query .= "AND fe.date = (SELECT MIN(fe2.date) FROM form_encounter AS fe2 WHERE fe2.pid = fe.pid) ";
}

if ($form_encounter_esigned) {
    $query .= "AND es.tid = fe.encounter AND es.table = 'form_encounter' ";
}

if ($form_esigned) {
    $query .= "AND es.tid = f.id ";
}

if ($form_not_esigned) {
    $query .= "AND es.tid IS NULL ";
}

$query .= "ORDER BY $orderby";

$res = sqlStatement($query, $sqlBindArray);
?>
<html>
<head>
    <title><?php echo xlt('Encounters Report'); ?></title>

    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

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
            oeFixedHeaderSetup(document.getElementById('mymaintable'));
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
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Encounters'); ?></span>

<div id="report_parameters_daterange">
<?php echo text(oeFormatShortDate($form_from_date)) ." &nbsp; " . xlt('to') . " &nbsp; ". text(oeFormatShortDate($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='encounters_report.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">
<table>
 <tr>
  <td width='550px'>
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
    <tr>
      <td></td>
      <td>
        <div class="checkbox">
          <label><input type='checkbox' name='form_details' checked >
            <?php echo xlt('Details'); ?></label>
        </div>
        <div class="checkbox">
          <label><input type='checkbox' name='form_new_patients' title='<?php echo xla('First-time visits only'); ?>'<?php echo ($form_new_patients) ? ' checked' : ''; ?>>
            <?php  echo xlt('New'); ?></label>
        </div>
      </td>
      <td></td>
      <td>
        <!--ALB A few new checkboxes here -->
        <div class="checkbox">
          <label><input type='checkbox' name='form_encounter_not_coded'<?php echo ($form_encounter_not_coded) ? ' checked' : ''; ?>>
            <?php  echo xlt('Encounter Not Coded or Not Justified'); ?></label>
        </div>
        <div class="checkbox">
          <label><input type='checkbox' name='form_encounter_esigned'<?php echo ($form_encounter_esigned) ? ' checked' : ''; ?>>
            <?php  echo xlt('Encounter Esigned'); ?></label>
        </div>
        <div class="checkbox">
          <label><input type='checkbox' name='form_esigned'<?php echo ($form_esigned) ? ' checked' : ''; ?>>
            <?php  echo xlt('Forms Esigned'); ?></label>
        </div>
        <div class="checkbox">
          <label><input type='checkbox' name='form_not_esigned'<?php echo ($form_not_esigned) ? ' checked' : ''; ?>>
            <?php echo xlt('Forms Not Esigned'); ?></label>
        </div>
        <div class="checkbox">
          <label><input type='checkbox' name='form_no_soap'<?php echo ($form_no_soap) ? ' checked' : ''; ?>>
            <?php echo xlt('No SOAP Note'); ?></label>
        </div>

      </td>
    </tr>
  </table>

    </div>

  </td>
  <td align='left' valign='middle' height="100%">
    <table style='border-left:1px solid; width:100%; height:100%' >
        <tr>
            <td>
                <div class="text-center">
          <div class="btn-group" role="group">
                      <a href='#' class='btn btn-default btn-save' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
                            <?php echo xlt('Submit'); ?>
                      </a>
                        <?php if ($_POST['form_refresh'] || $_POST['form_orderby']) { ?>
              <a href='#' class='btn btn-default btn-print' id='printbutton'>
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

</div> <!-- end report_parameters -->

<?php
if ($_POST['form_refresh'] || $_POST['form_orderby']) {
    ?>
<div id="report_results">
<table id='mymaintable'>
<thead>
    <?php if ($form_details) { ?>
  <th>
   <a href="nojs.php" onclick="return dosort('doctor')"
        <?php echo ($form_orderby == "doctor") ? " style=\"color:#00cc00\"" : ""; ?>><?php echo xlt('Provider'); ?> </a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('time')"
        <?php echo ($form_orderby == "time") ? " style=\"color:#00cc00\"" : ""; ?>><?php echo xlt('Date'); ?></a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('patient')"
        <?php echo ($form_orderby == "patient") ? " style=\"color:#00cc00\"" : ""; ?>><?php echo xlt('Patient'); ?></a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('pubpid')"
        <?php echo ($form_orderby == "pubpid") ? " style=\"color:#00cc00\"" : ""; ?>><?php echo xlt('PID'); ?></a>
  </th>
  <th>
        <?php echo xlt('Billing Status'); ?>
  </th>
  <th>
        <?php echo xlt('Encounter Comments'); ?>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('encounter')"
        <?php echo ($form_orderby == "encounter") ? " style=\"color:#00cc00\"" : ""; ?>><?php echo xlt('Encounter'); ?></a>
  </th>
  <th>
        <?php echo xlt('Forms'); ?>
  </th>
  <th>
        <?php echo xlt('Coding'); ?>
  </th>
<?php } else { ?>
  <th><?php echo xlt('Provider'); ?></td>
  <th><?php echo xlt('Encounters'); ?></td>
<?php } ?>
</thead>
<tbody>
    <?php
    if ($res) {
        $lastdocname = "";
        $doc_encounters = 0;
        $prev_pid = '';
        $prev_enc = '';

        while ($row = sqlFetchArray($res)) {
            $patient_id = $row['pid'];

            //ALB Skip same encounters for the same patients, so that we don't have duplicate entries
            if (($patient_id == $prev_pid) && ($row['encounter'] == $prev_enc)) continue;
            $prev_pid = $row['pid'];
            $prev_enc = $row['encounter'];

            $docname = '';
            if (!empty($row['ulname']) || !empty($row['ufname'])) {
                $docname = $row['ulname'];
                if (!empty($row['ufname']) || !empty($row['umname'])) {
                    $docname .= ', ' . $row['ufname'] . ' ' . $row['umname'];
                }
            }

            $errmsg  = "";
            //ALB Moved from here to just above display on screen     if ($form_details) {
                // Fetch all other forms for this encounter.
                $encnames = '';
                $encarr = getFormByEncounter(
                    $patient_id,
                    $row['encounter'],
                    "formdir, user, form_name, form_id"
                );
                if ($encarr!='') {
                    //ALB If No SOAP Note is selected, skip those that have one
                    $SOAP_note_exists = 0;

                    foreach ($encarr as $enc) {
                        if ($enc['formdir'] == 'newpatient') {
                            continue;
                        }

                        if ($encnames) {
                            $encnames .= '<br />';
                        }

                        $encnames .= text($enc['form_name']); // need to html escape it here for output below
                        //ALB See if the form is SOAP note
                        if ($enc['formdir'] == 'soap') $SOAP_note_exists = 1;
                    }
                    //ALB If No SOAP is selected, skip those that have one
                    if ($form_no_soap && $SOAP_note_exists) continue;
                }

                // Fetch coding and compute billing status.
                $coded = "";
                $billed_count = 0;
                $unbilled_count = 0;
                $cosmetic_encounter = 0;

                if ($billres = BillingUtilities::getBillingByEncounter(
                    $row['pid'],
                    $row['encounter'],
                    "code_type, code, code_text, billed, justify"
                )) {
                    //ALB If "Encounters Not Coded" selected, skip those that are coded fully
                    $encounter_fully_coded = 0; //ALB At first, all CPT codes are unjustified

                    foreach ($billres as $billrow) {
                        // $title = addslashes($billrow['code_text']);
                        if ($billrow['code_type'] != 'COPAY' && $billrow['code_type'] != 'TAX') {
                            $coded .= $billrow['code'] . ', ';
                            //ALB Checking to see if the encounter has been fully coded and CPTs have been justified
                            if (substr($billrow['code_type'],0,3) == 'CPT' && ! stripos($billrow['code_text'], "Added by Ins") && $billrow['code'] != 'Claim') {  //ALB Insurance sometimes adds codes, skip that
                               $tres = sqlQuery("SELECT cosmetic FROM codes WHERE code = '" . $billrow['code'] . "' LIMIT 1"); //ALB If cosmetic, don't need justification, just code
                               if ($tres['cosmetic'] == 1) {
                                  $encounter_fully_coded = 1;
                                  $cosmetic_encounter = 1;
                                  continue;
                               }
                               if ((!is_null($billrow['justify'])) && $billrow['justify'] != '') {
                                  if ($encounter_fully_coded != -1) { 
                                     $encounter_fully_coded = 1;
                                  } 
                               } else {
                                 if ($cosmetic_encounter != 1) $encounter_fully_coded = -1; //ALB If cosmetic visit, don't need to revert back to not fully coded if there is pathology code, for example
                               }
                            }

                            if ($billrow['billed']) {
                                ++$billed_count;
                            } else {
                                ++$unbilled_count;
                            }
                        }
                    }

                  //ALB If "Encounters Not Coded" selected, skip those that are coded fully
                  if ($form_encounter_not_coded && $encounter_fully_coded == 1) continue;

                  $coded = substr($coded, 0, strlen($coded) - 2);
                }

                // Figure product sales into billing status.
                $sres = sqlStatement("SELECT billed FROM drug_sales " .
                "WHERE pid = ? AND encounter = ?", array($row['pid'], $row['encounter']));
                while ($srow = sqlFetchArray($sres)) {
                    if ($srow['billed']) {
                        ++$billed_count;
                    } else {
                        ++$unbilled_count;
                    }
                }

                // Compute billing status.
                if ($billed_count && $unbilled_count) {
                    $status = xl('Mixed');
                } else if ($billed_count) {
                    $status = xl('Closed');
                } else if ($unbilled_count) {
                    $status = xl('Open');
                } else {
                    $status = xl('Not Coded');
                }
            //ALB Moved the next line here from up above
            if ($form_details) {


                ?>
       <tr bgcolor='<?php echo attr($bgcolor); ?>'>
  <td>
                <?php echo ($docname == $lastdocname) ? "" : text($docname) ?>&nbsp;
  </td>
  <td>
                <?php echo text(oeFormatShortDate(substr($row['date'], 0, 10))) ?>&nbsp;
  </td>
  <td>
                <?php echo text($row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname']); ?>&nbsp;
  </td>
  <td>
                <?php echo text($row['pubpid']); ?>&nbsp;
  </td>
  <td>
                <?php echo text($status); ?>&nbsp;
  </td>
  <td>
                <?php echo text($row['reason']); ?>&nbsp;
  </td>
   <td>
                <?php echo text($row['encounter']); ?>&nbsp;
  </td>
  <td>
                <?php echo $encnames; //since this variable contains html, have already html escaped it above ?>&nbsp;
  </td>
  <td>
                <?php echo text($coded); ?>
  </td>
 </tr>
                <?php
            } else {
                if ($docname != $lastdocname) {
                    show_doc_total($lastdocname, $doc_encounters);
                    $doc_encounters = 0;
                }

                  ++$doc_encounters;
            }

            $lastdocname = $docname;
        }

        if (!$form_details) {
            show_doc_total($lastdocname, $doc_encounters);
        }
    }
    ?>
</tbody>
</table>
</div>  <!-- end encresults -->
<?php } else { ?>
<div class='text'>
    <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
</div>
<?php } ?>

<input type="hidden" name="form_orderby" value="<?php echo attr($form_orderby) ?>" />
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>

</form>
</body>

<script language='JavaScript'>
<?php if ($alertmsg) {
    echo " alert(" . js_escape($alertmsg) . ");\n";
} ?>
</script>
</html>
