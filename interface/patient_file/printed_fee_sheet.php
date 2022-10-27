<?php

/**
 * 2012 - Refactored extensively to allow for creating multiple feesheets on demand
 * uses a session array of PIDS by Medical Information Integration, LLC - mi-squared.com
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ron Pulcer <rspulcer_2k@yahoo.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2007-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Ron Pulcer <rspulcer_2k@yahoo.com>
 * @copyright Copyright (c) 2019 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/appointments.inc.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/user.inc");

use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

function genColumn($ix)
{
    global $html;
    global $SBCODES;
    for ($imax = count($SBCODES); $ix < $imax; ++$ix) {
        $a = explode('|', $SBCODES[$ix], 2);
        $cmd = trim($a[0]);
        if ($cmd == '*C') { // column break
            return++$ix;
        }

        if ($cmd == '*B') { // Borderless and empty
            $html .= " <tr><td colspan='5' class='fscode' style='border-width:0 1px 0 0;padding-top:1px;' nowrap>&nbsp;</td></tr>\n";
        } elseif ($cmd == '*G') {
            $title = text($a[1]);
            if (!$title) {
                $title = '&nbsp;';
            }

            $html .= " <tr><td colspan='5' align='center' class='fsgroup' style='vertical-align:middle' nowrap>$title</td></tr>\n";
        } elseif ($cmd == '*H') {
            $title = text($a[1]);
            if (!$title) {
                $title = '&nbsp;';
            }

            $html .= " <tr><td colspan='5' class='fshead' style='vertical-align:middle' nowrap>$title</td></tr>\n";
        } else {
            $title = text($a[1]);
            if (!$title) {
                $title = '&nbsp;';
            }

            $b = explode(':', $cmd);
            $html .= " <tr>\n";
            //$html .= " <td class='fscode' style='vertical-align:middle;width:14pt' nowrap>&nbsp;</td>\n"; //ALB Don't need this here, using below for price

            if (count($b) <= 1) {
                $code = text($b[0]);
                if (!$code) {
                    $code = '&nbsp;';
                }

                $html .= " <td class='fscode' style='vertical-align:middle' nowrap>$code</td>\n";
                $html .= " <td colspan='3' class='fscode' style='vertical-align:middle' nowrap>$title</td>\n";
$html .= " <td class='fscode' style='vertical-align:middle;width:30pt' nowrap>&nbsp;</td>\n"; //ALB Using this column for price
            } else {
                $html .= " <td colspan='2' class='fscode' style='vertical-align:middle' nowrap>" . text($b[0]) . '/' . text($b[1]) . "</td>\n";
                $html .= " <td colspan='2' class='fscode' style='vertical-align:middle' nowrap>$title</td>\n";
$html .= " <td class='fscode' style='vertical-align:middle;width:30pt' nowrap>&nbsp;</td>\n"; //ALB Using this column for price
            }

            $html .= " </tr>\n";
        }
    }

    return $ix;
}

// MAIN Body
//
// Build output to handle multiple pids and and superbill for each patient.
// This value is initially a maximum, and will be recomputed to
// distribute lines evenly among the pages.  (was 55)
$lines_per_page = 100;  //ALB Changed here

$lines_in_stats = 8;

$header_height = 44; // height of page headers in points
// This tells us if patient/encounter data is to be filled in.
// 1 = single PID from popup, 2=array of PIDs for session

if (empty($_GET['fill'])) {
    $form_fill = 0;
} else {
    $form_fill = $_GET['fill'];
}

// Show based on session array or single pid?
$pid_list = array();
$apptdate_list = array();


if (!empty($_SESSION['pidList']) and $form_fill == 2) {
    $pid_list = $_SESSION['pidList'];
    // If PID list is in Session, then Appt. Date list is expected to be a parallel array
    $apptdate_list = $_SESSION['apptdateList'];
} elseif ($form_fill == 1) {
    array_push($pid_list, $pid); //get from active PID
    array_push($apptdate_list, date('Y-m-d', time())); //ALB If superbill popup, create one for today
} else {
    array_push($pid_list, ''); // empty element for blank form
}

// This file is optional. You can create it to customize how the printed
// fee sheet looks, otherwise you'll get a mirror of your actual fee sheet.
//
if (file_exists("../../custom/fee_sheet_codes.php")) {
    include_once("../../custom/fee_sheet_codes.php");
}

// TBD: Move these to globals.php, or make them user-specific.
$fontsize = 7;
$page_height = 700;

$padding = 0;

// The $SBCODES table is a simple indexed array whose values are
// strings of the form "code|text" where code may be either a billing
// code or one of the following:
//
// *H - A main heading, where "text" is its title (to be centered).
// *G - Specifies a new category, where "text" is its name.
// *B - A borderless blank row.
// *C - Ends the current column and starts a new one.
// If $SBCODES is not provided, then manufacture it from the Fee Sheet.
//
if (empty($SBCODES)) {
    $SBCODES = array();
    $last_category = '';

    // Create entries based on the fee_sheet_options table.
    $res = sqlStatement("SELECT * FROM fee_sheet_options " .
            "ORDER BY fs_category, fs_option");
    while ($row = sqlFetchArray($res)) {
        $fs_category = $row['fs_category'];
        $fs_option = $row['fs_option'];
        $fs_codes = $row['fs_codes'];
        if ($fs_category !== $last_category) {
            $last_category = $fs_category;
            // ALB Don't need to repeat these - they are in Medical category         $SBCODES[] = '*G|' . substr($fs_category, 1);
        }
        // ALB Don't need to repeat these - they are in Medical category     $SBCODES[] = " |" . substr($fs_option, 1);
    }

    // Create entries based on categories defined within the codes.
    $pres = sqlStatement("SELECT option_id, title FROM list_options " .
            "WHERE list_id = 'superbill' AND activity = 1 ORDER BY seq");
    while ($prow = sqlFetchArray($pres)) {
        $SBCODES[] = '*G|' . xl_list_label($prow['title']);
        $res = sqlStatement("SELECT code_type, code, code_text FROM codes " .
                "WHERE superbill = ? AND active = 1 " .
                "ORDER BY code", array($prow['option_id'])); //ALB Changed to order by code
        while ($row = sqlFetchArray($res)) {
            $SBCODES[] = $row['code'] . '|' . $row['code_text'];
        }
    }

    // Create one more group, for Products.
    if ($GLOBALS['sell_non_drug_products']) {
        $SBCODES[] = '*G|' . xl('Products');
        $tres = sqlStatement("SELECT " .
                "dt.drug_id, dt.selector, d.name, d.ndc_number " .
                "FROM drug_templates AS dt, drugs AS d WHERE " .
                "d.drug_id = dt.drug_id AND d.active = 1 " .
                "ORDER BY d.name, dt.selector, dt.drug_id");
        while ($trow = sqlFetchArray($tres)) {
            $tmp = $trow['selector'];
            if ($trow['name'] !== $trow['selector']) {
                $tmp .= ' ' . $trow['name'];
            }

            $prodcode = empty($trow['ndc_number']) ? ('(' . $trow['drug_id'] . ')') :
                    $trow['ndc_number'];
            $SBCODES[] = "$prodcode|$tmp";
        }
    }

    // Extra stuff for the labs section.
    //ALB Changed this section
    $SBCODES[] = '*G|' . xl('Diagnoses');
    $percol = intval((count($SBCODES) + 2) / 3);
    $counter = 0;
    while ($counter < 6) { //(count($SBCODES) < $percol * 3) { //ALB Put a counter here for diagnoses
        ++$counter;
        $SBCODES[] = text($counter); //'*B|' . text($counter);
    }
    //ALB Took this out
    //$SBCODES[] = '*G|' . xl('Notes');
    //$percol = intval((count($SBCODES) + 2) / 3);
    //while (count($SBCODES) < $percol * 3) {
    //    $SBCODES[] = '*B|';
    //}

    // Adjust lines per page to distribute lines evenly among the pages.
    $pages = intval(($percol + $lines_in_stats + $lines_per_page - 1) / $lines_per_page);
    $lines_per_page = intval(($percol + $lines_in_stats + $pages - 1) / $pages);

    // Figure out page and column breaks.
    $pages = 1;
    $lines = $percol;
    $page_start_index = 0;
    while ($lines + $lines_in_stats > $lines_per_page) {
        ++$pages;
        $lines_this_page = $lines > $lines_per_page ? $lines_per_page : $lines;
        $lines -= $lines_this_page;
        array_splice($SBCODES, $lines_this_page * 3 + $page_start_index, 0, '*C|');
        array_splice($SBCODES, $lines_this_page * 2 + $page_start_index, 0, '*C|');
        array_splice($SBCODES, $lines_this_page * 1 + $page_start_index, 0, '*C|');
        $page_start_index += $lines_this_page * 3 + 3;
    }

    array_splice($SBCODES, $lines * 2 + $page_start_index, 0, '*C|');
    array_splice($SBCODES, $lines * 1 + $page_start_index, 0, '*C|');
}

$lheight = sprintf('%d', ($page_height - $header_height) / $lines_per_page);

// Common HTML Header information

$html = "<html>
<head>";

$html .= "
<style>
body {
font-family: sans-serif;
font-weight: normal;
}
.bordertbl {
width: 100%;
border-style: solid;
border-width: 0 0 1px 1px;
border-spacing: 0;
border-collapse: collapse;
border-color: #999999;
}
td.toprow {
height: 1px;
padding: 0;
border-style: solid;
border-width: 0 0 0 0;
border-color: #999999;
}
td.fsgroup {
height: " . attr($lheight) . "pt;
font-family: sans-serif;
font-weight: bold;
font-size: " . attr($fontsize) . " pt;
background-color: #cccccc;
padding: " . attr($padding) . "pt 2pt 0pt 2pt;
border-style: solid;
border-width: 1px 1px 0 0;
border-color: #999999;
}
td.fshead {
height: " . attr($lheight) . "pt;
font-family: sans-serif;
font-weight: bold;
font-size: " . attr($fontsize) . "pt;
padding: " . attr($padding) . "pt 2pt 0pt 2pt;
border-style: solid;
border-width: 1px 1px 0 0;
border-color: #999999;
}
td.fscode {
height: " . attr($lheight) . "pt;
font-family: sans-serif;
font-weight: normal;
font-size: " . attr($fontsize) . "pt;
padding: " . attr($padding) . "pt 2pt 0pt 2pt;
border-style: solid;
border-width: 1px 1px 0 0;
border-color: #999999;
}

.ftitletable {
width: 100%;
height: " . attr($header_height) . "pt;
margin: 0 0 8pt 0;
}
.ftitlecell1 {
 width: 33%;
 vertical-align: top;
 text-align: left;
 font-size: 14pt;
 font-weight: bold;
}
.ftitlecell2 {
 width: 33%;
 vertical-align: top;
 text-align: right;
 font-size: 9pt;
}
.ftitlecellm {
 width: 34%;
 vertical-align: top;
 text-align: center;
 font-size: 14pt;
 font-weight: bold;
}

div.pagebreak {
page-break-after: always;
height: " . attr($page_height) . "pt;
}
</style>";

$html .= "<title>" . text($frow['name'] ?? '') . "</title>" .
    Header::setupHeader(['opener', 'topdialog'], false) .
    "<script>";

$html .= "
$(function () {
 var win = top.printLogSetup ? top : opener.top;
 win.printLogSetup(document.getElementById('printbutton'));
});

// Process click on Print button.
function printlog_before_print() {
 var divstyle = document.getElementById('hideonprint').style;
 divstyle.display = 'none';
}

</script>
</head>
<body bgcolor='#ffffff'>
<form name='theform' method='post' action='printed_fee_sheet.php?fill=" . attr_url($form_fill) . "'
onsubmit='return opener.top.restoreSession()'>
<div style='text-align: center;'>";

$today = date('Y-m-d');

$alertmsg = ''; // anything here pops up in an alert box

// Get details for the primary facility.
$frow = $facilityService->getPrimaryBusinessEntity();

// If primary is not set try to old method of guessing...for backward compatibility
if (empty($frow)) {
    $frow = $facilityService->getPrimaryBusinessEntity(array("useLegacyImplementation" => true));
}

// Still missing...
if (empty($frow)) {
    $alertmsg = xl("No Primary Business Entity selected in facility list");
}

$logo = '';
$ma_logo_path = "sites/" . $_SESSION['site_id'] . "/images/ma_logo.png";
if (is_file("$webserver_root/$ma_logo_path")) {
    $logo = "<img src='$web_root/$ma_logo_path' style='height:" . round(9 * 5.14) . "pt' />";
} else {
    $logo = "<!-- '$ma_logo_path' does not exist. -->";
}

// Loop on array of PIDS
$saved_pages = $pages; //Save calculated page count of a single fee sheet
$loop_idx = 0; // counter for appt list

foreach ($pid_list as $pid) {
    $apptdate = $apptdate_list[$loop_idx] ?? null; // parallel array to pid_list
    $appointment = fetchAppointments($apptdate, $apptdate, $pid);  // Only expecting one row for pid
    // Set Pagebreak for multi forms
    if ($form_fill == 2) {
        $html .= "<div class=pagebreak>\n";
    } else {
        $html .= "<div>\n";
    }

    if ($form_fill) {
        // Get the patient's name and chart number.
        $patdata = getPatientData($pid);
        // Get the referring providers info
        $referDoc = getUserIDInfo($patdata['ref_providerID']);
    }

// This tracks our position in the $SBCODES array.
    $cindex = 0;

    while (--$pages >= 0) {
        $html .= genFacilityTitle(xl('Superbill'), $appointment[0]['pc_facility'], $logo);  //ALB Changed to display address of facility for the appt
        $html .= '<table style="width: 100%"><tr>' .
            '<td>' . xlt('Patient') . ': <span style="font-weight: bold;">' . text($patdata['fname'] ?? '') . ' ' . text($patdata['mname'] ?? '') . ' ' . text($patdata['lname'] ?? '') . '</span></td>' .
            '<td>' . xlt('DOB') . ': <span style="font-weight: bold;">' . text(oeFormatShortDate($patdata['DOB'] ?? '')) . '</span></td>' .
            '<td>' . xlt('Date of Service') . ': <span style="font-weight: bold;">' . text(oeFormatShortDate($appointment[0]['pc_eventDate'])) . '</span></td>' . //ALB took out appt time - ' ' . text(oeFormatTime($appointment[0]['pc_startTime'])) .
            //ALB Don't need this here '<td>' . xlt('Ref Prov') . ': <span style="font-weight: bold;">' . text($referDoc['fname']) . ' ' . text($referDoc['lname']) . '</span></td>' .
            '</tr></table>';
        $html .= "
<table class='bordertbl' cellspacing='0' cellpadding='0' width='100%'>
<tr>
<td valign='top'>
<table border='0' cellspacing='0' cellpadding='0' width='100%'>
<tr>
<td class='toprow' style='width:10%'></td>
<td class='toprow' style='width:10%'></td>
<td class='toprow' style='width:25%'></td>
<td class='toprow' style='width:55%'></td>
</tr>";

        $cindex = genColumn($cindex); // Column 1

        if ($pages == 0) { // if this is the last page
            $html .= "<tr>
<td colspan='3' valign='top' class='fshead' style='height:" . $lheight * 2 . "pt'>";
            $html .= xlt('Patient') . ": ";

            if ($form_fill) {
                $html .= text($patdata['fname'] . ' ' . $patdata['mname'] . ' ' . $patdata['lname']) . "<br />\n";
                $html .= text($patdata['street']) . "<br />\n";
                $html .= text($patdata['city'] . ', ' . $patdata['state'] . ' ' . $patdata['postal_code']) . "\n";
            }

            $html .= "</td>
<td valign='top' class='fshead'>";
            $html .= xlt('DOB');
            $html .= ": ";

            if ($form_fill) {
                $html .= text($patdata['DOB']);
                $html .= "<br />";
            }

            $html .= xlt('ID');
            $html .= ": ";

            if ($form_fill) {
                $html .= text($patdata['pubpid']);
            }

            $html .= "</td>
</tr>
<tr>
<td colspan='3' valign='top' class='fshead' style='height:${lheight}pt'>";
            $html .= xlt('Provider');
            $html .= ": ";

            $encdata = false;
            
            //ALB Changed this entire paragraph below
            if ($form_fill) {  //ALB Took out $encounter here, since it hasn't been defined. Query is based on date instead of encounter now - see below
                $query = "SELECT fe.reason, fe.date, fe.provider_id, u.fname, u.mname, u.lname, u.username " .
                        "FROM forms AS f " .
                        "JOIN form_encounter AS fe ON fe.id = f.form_id " .
                        "LEFT JOIN users AS u ON u.id = fe.provider_id " .  //ALB Users defined as providers for the visit, not user entering the info
                        //"WHERE f.pid = ? AND f.encounter = ? AND f.formdir = 'newpatient' AND f.deleted = 0 " .
                        "WHERE f.pid = ? AND date(f.date) = ? AND f.formdir = 'newpatient' AND f.deleted = 0 " .
                        "ORDER BY f.id LIMIT 1";

                $encdata = sqlQuery($query, array($pid, $appointment[0]['pc_eventDate']));
                if (!empty($encdata['username'])) {
                    $html .= $encdata['fname'] . ' ' . $encdata['lname'];
                } else {
                    $html .= $appointment[0]['ufname'] . ' ' . $appointment[0]['ulname'];  //ALB Get provider name from appointment
                }
            }



            $html .= "</td>
<td valign='top' class='fshead'>";

            //ALB Changed this entire paragraph below
            $html .= xlt('CC') . ':'; //ALB Changed format here
            if (!empty($encdata)) {
                $html .= text($encdata['reason']);
            } else {  //ALB added else
                //ALB No forms yet, because it's a future visit, so get the info from $appointment instead
            // Note: You would think that pc_comments would have the Appt. comments,
            // but it is actually stored in pc_hometext in DB table (openemr_postcalendar_events).
                $html .= $appointment[0]['pc_hometext']; //ALB Added [0] here to make it work
            }

            //ALB And take this out
            //$html .= xlt('Reason');
            //$html .= ":<br />";
            //if (!empty($encdata)) {
            //    $html .= text($encdata['reason']);
            //}

            // Note: You would think that pc_comments would have the Appt. comments,
            // but it is actually stored in pc_hometext in DB table (openemr_postcalendar_events).
            //$html .= $appointment['pc_hometext'] ?? '';

            $html .= "</td>
</tr>
<tr>
<td colspan='4' valign='top' class='fshead' style='height:${lheight}pt'>";

            if (empty($GLOBALS['ippf_specific'])) {
                $html .= xlt('Insurance') . ":";

                //ALB Added a variable here
                $visit_date = ($appointment[0]['pc_eventDate'] ? $appointment[0]['pc_eventDate'] : date('Y-m-d'));

                if ($form_fill) {
                    foreach (array('primary', 'secondary', 'tertiary') as $instype) {
                        $query = "SELECT * FROM insurance_data WHERE " .
                                "pid = ? AND type = ? AND date <= ? " . //ALB Added insurance eff date prior to appt
                                "ORDER BY date DESC LIMIT 1";
                        $row = sqlQuery($query, array($pid, $instype, $visit_date)); //ALB Added visit date here
                        if (!empty($row['provider'])) {
                            $icobj = new InsuranceCompany($row['provider']);
                            $adobj = $icobj->get_address();
                            $insco_name = trim($icobj->get_name());
                            if ($instype != 'primary') {
                                $html .= ",";
                            }

                            if ($insco_name) {
                                $html .= "&nbsp;" . text($insco_name);
                            } else {
                                $html .= "&nbsp;<font color='red'><b>Missing Name</b></font>";
                            }
                        }
                    }
                }
            } else {
                // IPPF wants a visit date box with the current date in it.
                $html .= xlt('Visit date');
                $html .= ":<br />\n";
                if (!empty($encdata)) {
                    $html .= text(substr($encdata['date'], 0, 10));
                } else {
                    $html .= text(oeFormatShortDate(date('Y-m-d'))) . "\n";
                }
            }

            $html .= "</td>
</tr>
<tr>
<td colspan='4' valign='top' class='fshead' style='height:${lheight}pt'>";

            //ALB Added all of this
            $html .= xlt('Last Visit: ');
            //ALB Find last visit prior to this one
                $query = "SELECT fe.date " .
                        "FROM forms AS f " .
                        "JOIN form_encounter AS fe ON fe.id = f.form_id " .
                        "LEFT JOIN users AS u ON u.id = fe.provider_id " . 
                        "WHERE f.pid = ? AND date(f.date) < ? AND f.formdir = 'newpatient' AND f.deleted = 0 " .
                        "ORDER BY fe.date DESC, f.id LIMIT 1";

                $encdata = sqlQuery($query, array($pid, $visit_date));
                if (!empty($encdata['date'])) {
                    $html .= oeFormatShortDate($encdata['date']);
                } elseif ($pid) {
                    $html .= xlt('None');
                } else {
                    $html .= xlt('');
                }
                $html .= "<br />



</td>
</tr>

<!--ALB Added all of this -->
<tr>
<td colspan='5' valign='top' class='fshead' style='height:${lheight}pt'>";
            $html .= xlt('Previous Patient Balance: '); //ALB Adding patient balance here
            $html .= text(oeFormatMoney(get_patient_balance($pid, false)));
            $html .= "<br />
</td>
</tr>


<tr>
<td colspan='4' valign='top' class='fshead' style='height:${lheight}pt'>";
            $html .= xlt('Today\'s Charges');
            $html .= ":<br />
</td>
</tr>


<!--ALB Don't need here     <tr>
<td colspan='4' valign='top' class='fshead' style='height:${lheight}pt'>";
            $html .= xlt('Today\'s Balance');
            $html .= ":<br />
</td>
</tr>
<tr>
<td colspan='4' valign='top' class='fshead' style='height:${lheight}pt'>";
            $html .= xlt('Notes');
            $html .= ":<br />
</td>
</tr   -->";


        } // end if last page

        $html .= "</table>
</td>
<td valign='top'>
<table border='0' cellspacing='0' cellpadding='0' width='100%'>
<tr>
<td class='toprow' style='width:10%'></td>
<td class='toprow' style='width:10%'></td>
<td class='toprow' style='width:25%'></td>
<td class='toprow' style='width:55%'></td>
</tr>";

        $cindex = genColumn($cindex); // Column 2

        if ($pages == 0) { // if this is the last page
            $html .= "<tr>
<td colspan='4' valign='top' class='fshead' style='height:" . $lheight * 8 . "pt'>";
            $html .= xlt('Provider\'s Common Diagnosis Codes');  //ALB Changed this
            $html .= ":<br />

  //ALB Searching for provider's most common dx to display
  $where = '';
  if ($appointment[0]['uprovider_id']) $where = "AND fe.provider_id = " . text($appointment[0]['uprovider_id']) . " ";
  $query = "SELECT b.code AS ICD, b.code_text AS description, " .
           "COUNT(b.pid) AS total FROM billing AS b, ".
           "codes AS c, form_encounter as fe WHERE b.code_type LIKE 'ICD%' AND " .
           "b.activity = 1 AND " .
           "date(b.date) >= date_sub(CURRENT_DATE(), interval 3 month) AND date(b.date) <= CURRENT_DATE AND " .
           "b.code = c.code AND fe.encounter = b.encounter " . $where .
           "GROUP BY b.code, b.code_text ORDER BY total DESC LIMIT 20";
  $res = sqlStatement($query);
  while ($row = sqlFetchArray($res)) {
    $ICD = $row['ICD'];
    $description = $row['description'];
    $html .= $ICD . ": " . $description . ", ";
  }
  $html .= "<br />

</td>
</tr>";
        } // end if last page

        $html .= "</table>
</td>
<td valign='top'>
<table border='0' cellspacing='0' cellpadding='0' width='100%'>
<tr>
<td class='toprow' style='width:10%'></td>
<td class='toprow' style='width:10%'></td>
<td class='toprow' style='width:25%'></td>
<td class='toprow' style='width:55%'></td>
</tr>";

        $cindex = genColumn($cindex); // Column 3

        if ($pages == 0) { // if this is the last page
            $html .= "<tr>
<td valign='top' colspan='4' class='fshead' style='height:" . $lheight * 6 . "pt;border-width:0 1px 0 0'>
&nbsp;
</td>
</tr>
<tr>
<td valign='top' colspan='4' class='fshead' style='height:" . $lheight * 2 . "pt'>";
            $html .= xlt('Signature');
            $html .= ":<br />
</td>
</tr>";
        } // end if last page

        $html .= "</table>
</td>
</tr>

</table>";

        $html .= "</div>";  // end of div.pageLetter
    } // end while
    $pages = $saved_pages; // reset
    $loop_idx++; // appt list counter
} // end foreach

// Common End Code
if ($form_fill != 2) {   //use native browser 'print' for multipage
    $html .= "<div id='hideonprint'>
<p>
<input type='button' class='btn btn-secondary btn-print mt-3' value='";

    $html .= xla('Print');
    $html .= "' id='printbutton' />
</div>";
}

$html .= "
</div>
</form>
</body>
</html>";

// Send final result to display
echo $html;
