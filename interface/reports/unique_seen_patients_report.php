<?php
/**
 * This report lists patients that were seen within a given date
 * range.
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

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$form_from_date = (!empty($_POST['form_from_date'])) ?  DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');
$form_to_date   = (!empty($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_payer_id = (!empty($_POST['form_payer_id'])) ? $_POST['form_payer_id'] : "";

if ($_POST['form_labels']) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=labels.txt");
    header("Content-Description: File Transfer");
} else {
    ?>
<html>
<head>

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
<title><?php echo xlt('Unique Seen Patients'); ?></title>

    <?php Header::setupHeader('datetime-picker'); ?>

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

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Unique Seen Patients'); ?></span>

<div id="report_parameters_daterange">
    <?php echo text(oeFormatShortDate($form_from_date)) ." &nbsp; " . xlt("to") . " &nbsp; ". text(oeFormatShortDate($form_to_date)); ?>
</div>

<form name='theform' method='post' action='unique_seen_patients_report.php' id='theform' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_labels' id='form_labels' value=''/>

<table>
<tr>
 <td width='600px'>
   <div style='float:left'>

   <table class='text'>
       <tr>
           <td class='control-label'>
                <?php echo xlt('Visits From'); ?>:
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

           <!-- ALB Added insurance drop-down box -->
           <td class='control-label'>
                <?php echo xlt('Insurance'); ?>:
           </td>

           <td>
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

           <!--ALB Added an option to sort by date, name, insurances, also sort asc or desc -->
           <td class='control-label'>
               <?php echo xlt('Sort By'); ?>:
           </td>
           <td>
               <select name='form_sortby' id='form_sortby'>
                   <?php echo "<option value='patient'";
                   if ($_POST['form_sortby'] == 'patient') echo " selected";
                   echo ">Patient Name</option>" . "\n";
                   echo "<option value='pid'";
                   if ($_POST['form_sortby'] == 'pid') echo " selected";
                   echo ">PID</option>" . "\n";
                   echo "<option value='date'";
                   if ($_POST['form_sortby'] == 'date') echo " selected";
                   echo ">Last Visit</option>" . "\n";
                   //echo "<option value='pins'";
                   //if ($_POST['form_sortby'] == 'pins') echo " selected";
                   //echo ">Primary Insurance</option>" . "\n";
                   //echo "<option value='sins'";
                   //if ($_POST['form_sortby'] == 'sins') echo " selected";
                   //echo ">Secondary Insurance</option>" . "\n";
                   ?>
               </select>
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
                     <a href='#' class='btn btn-default btn-save' onclick='$("#form_refresh").attr("value","true"); $("#form_labels").val(""); $("#theform").submit();'>
                        <?php echo xlt('Submit'); ?>
                     </a>
                    <?php if ($_POST['form_refresh']) { ?>
                        <a href='#' class='btn btn-default btn-print' id='printbutton'>
                                <?php echo xlt('Print'); ?>
                        </a>
                        <a href='#' class='btn btn-default btn-transmit' onclick='$("#form_labels").attr("value","true"); $("#theform").submit();'>
                            <?php echo xlt('Labels'); ?>
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

<div id="report_results">
<table>

<thead>
<th> <?php echo xlt('Last Visit'); ?> </th>
<th> <?php echo xlt('Patient'); ?> </th>
<th> <?php echo xlt('PID'); ?> </th>
<th> <?php echo xlt('Visits'); ?> </th>
<th> <?php echo xlt('Age'); ?> </th>
<th> <?php echo xlt('Sex'); ?> </th>
<th> <?php echo xlt('Primary Insurance'); ?> </th>
<th> <?php echo xlt('Secondary Insurance'); ?> </th>
<th> <?php echo xlt('Tertiary Insurance'); //ALB Need this here ?> </th>
</thead>
<tbody>
    <?php
} // end not generating labels

if ($_POST['form_refresh'] || $_POST['form_labels']) {
    $totalpts = 0;

    //ALB Added ability to sort by date, pt name, insurances and order asc vs desc

    switch ($_POST['form_sortby'])
    {
        case 'patient':
            $order = "p.lname, p.fname, p.mname, p.pid";
            break;
        case 'pid':
            $order = "p.pid, p.lname, p.fname, p.mname";
            break;
        case 'date':
            $order = "e.date, p.lname, p.fname, p.mname, p.pid";
            break;
        default:
            $order = "p.lname, p.fname, p.mname, p.pid";
    }
//    $query = "SELECT " .
//   "p.pid, p.fname, p.mname, p.lname, p.DOB, p.sex, p.ethnoracial, " .
//    "p.street, p.city, p.state, p.postal_code, " .
//    "count(e.date) AS ecount, max(e.date) AS edate, " .
//    "i1.date AS idate1, i2.date AS idate2, i3.date AS idate3, " .
//    "c1.name AS cname1, c2.name AS cname2, c3.name AS cname3, c1.id AS cid1, c2.id AS cid2, c3.id AS cid3 " .
//    "FROM patient_data AS p " .
//    "JOIN form_encounter AS e ON " .
//    "e.pid = p.pid AND " .
//    "e.date >= ? AND " .
//    "e.date <= ? " .
//    "LEFT OUTER JOIN (SELECT * from insurance_data as id1 where id1.type = 'primary' order by id1.date DESC) as i1 ON i1.pid = p.pid AND i1.date <= e.date " .
//    "LEFT OUTER JOIN insurance_companies AS c1 ON " .
//   "c1.id = i1.provider " .
//    "LEFT OUTER JOIN (SELECT * from insurance_data as id2 where id2.type = 'secondary' order by id2.date DESC) as i2 ON i2.pid = p.pid AND i2.date <= e.date " .
//    "LEFT OUTER JOIN insurance_companies AS c2 ON " .
//    "c2.id = i2.provider " .
//    "LEFT OUTER JOIN (SELECT * from insurance_data as id3 where id3.type = 'tertiary' order by id3.date DESC) as i3 ON i3.pid = p.pid AND i3.date <= e.date " .
//    "LEFT OUTER JOIN insurance_companies AS c3 ON " .
//    "c3.id = i3.provider " .
//    "GROUP BY p.lname, p.fname, p.mname, p.pid, i1.date, i2.date, i3.date, c1.name, c2.name, c3.name, c1.id, c2.id, c3.id " .
    //"ORDER BY p.lname, p.fname, p.mname, p.pid, i1.date DESC, i2.date DESC";
//        "ORDER BY $order";

    $query = "SELECT " .
    "p.pid, p.fname, p.mname, p.lname, p.DOB, p.sex, p.ethnoracial, " .
    "p.street, p.city, p.state, p.postal_code, " .
    "count(e.date) AS ecount, max(e.date) AS edate " .
    "FROM patient_data AS p " .
    "JOIN form_encounter AS e ON " .
    "e.pid = p.pid AND " .
    "e.date >= ? AND " .
    "e.date <= ? " .
    "GROUP BY p.lname, p.fname, p.mname, p.pid " .
    "ORDER BY $order";

    $res = sqlStatement($query, array($form_from_date . ' 00:00:00', $form_to_date . ' 23:59:59'));

    $prevpid = 0;
    while ($row = sqlFetchArray($res)) {
        if ($row['pid'] == $prevpid) {
            continue;
        }

        $prevpid = $row['pid'];

        $crow1 = getInsuranceDataByDate($row['pid'], $row['edate'], 'primary');
        $cid1 = $crow1['provider'];
        $cname1 = getInsuranceNameByDate($row['pid'], $row['edate'], 'primary');
        $crow2 = getInsuranceDataByDate($row['pid'], $row['edate'], 'secondary');
        $cid2 = $crow2['provider'];
        $cname2 = getInsuranceNameByDate($row['pid'], $row['edate'], 'secondary');
        $crow3 = getInsuranceDataByDate($row['pid'], $row['edate'], 'tertiary');
        $cid3 = $crow3['provider'];
        $cname3 = getInsuranceNameByDate($row['pid'], $row['edate'], 'tertiary');

        //ALB If searching by payer, skip all with wrong payer
        if ($form_payer_id <> "") {
           //if ($row['cid1'] <> $form_payer_id && $row['cid2'] <> $form_payer_id && $row['cid3'] <> $form_payer_id) continue;
           if ($cid1 != $form_payer_id && $cid2 != $form_payer_id && $cid3 != $form_payer_id) continue;
        }

        $age = '';
        if ($row['DOB']) {
            $dob = $row['DOB'];
            $tdy = $row['edate'];
            $ageInMonths = (substr($tdy, 0, 4)*12) + substr($tdy, 5, 2) -
                   (substr($dob, 0, 4)*12) - substr($dob, 5, 2);
            $dayDiff = substr($tdy, 8, 2) - substr($dob, 8, 2);
            if ($dayDiff < 0) {
                --$ageInMonths;
            }

            $age = intval($ageInMonths/12);
        }

        if ($_POST['form_labels']) {
            echo '"' . $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'] . '","' .
             $row['street'] . '","' . $row['city'] . '","' . $row['state'] . '","' .
             $row['postal_code'] . '"' . "\n";
        } else { // not labels
            ?>
       <tr>
        <td>
            <?php echo text(oeFormatShortDate(substr($row['edate'], 0, 10))); ?>
   </td>
   <td>
            <?php echo text($row['lname']) . ', ' . text($row['fname']) . ' ' . text($row['mname']); ?>
   </td>
   <td>
            <?php echo text($row['pid']); ?>
   </td>
   <td> 
            <?php echo text($row['ecount']); ?>
   </td>
   <td>
            <?php echo text($age); ?>
   </td>
   <td>
            <?php echo text($row['sex']); ?>
   </td>
   <td>
            <?php echo text($cname1); ?>
   </td>
   <td>
            <?php echo text($cname2); ?>
   </td>
   <td>
            <?php echo text($cname3); ?>
   </td>

  </tr>
            <?php
        } // end not labels
        ++$totalpts;
    }

    if (!$_POST['form_labels']) {
        ?>
   <tr class='report_totals'>
    <td colspan='2'>
        <?php echo xlt('Total Number of Patients'); ?>
  </td>
  <td style="padding-left: 20px;">
        <?php echo text($totalpts); ?>
  </td>
  <td colspan='5'>&nbsp;</td>
 </tr>

        <?php
    } // end not labels
} // end refresh or labels

if (!$_POST['form_labels']) {
    ?>
</tbody>
</table>
</div>
</form>
</body>

</html>
    <?php
} // end not labels
?>
