<?php
/**
 * Functions to globally validate and prepare data for sql database insertion.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    MMF Systems, Inc
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2010 MMF Systems, Inc
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

//ALB Multiple changes - copy entire file

require_once(dirname(__file__)."/../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/report.inc");
require_once("$srcdir/calendar.inc");
require_once("$srcdir/edi.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

//  File location (URL or server path)
$target = $GLOBALS['edi_271_file_path'];
$batch_log = '';

if (isset($_FILES) && !empty($_FILES)) {
    $target = $target .time().basename($_FILES['uploaded']['name']);

    if ($_FILES['uploaded']['size'] > 350000) {
        $message .=  xlt('Your file is too large')."<br>";
    }
    //ALB Commented this out to accept .271 files
    //if ($_FILES['uploaded']['type'] != "text/plain") {
    //    $message .= xlt('You may only upload .txt files')."<br>";
    //}
    if (!isset($message)) {
        $file_moved = move_uploaded_file($_FILES['uploaded']['tmp_name'], $target);
        if ($file_moved) {
            $message = xlt('The following EDI file has been uploaded') . ': "' . text(basename($_FILES['uploaded']['name'])) . '"';
            $Response271 = file($target);
            if ($Response271) {
                $batch_log = parseEdi271($Response271);
            } else {
                $message = xlt('The following EDI file upload failed to open') . ': "' . text(basename($_FILES['uploaded']['name'])) . '"';
            }
        } else {
            $message = xlt('The following EDI file failed save to archive') . ': "' . text(basename($_FILES['uploaded']['name'])) . '"';
        }
    } else {
        $message .= xlt('Sorry, there was a problem uploading your file') . "<br><br>";
    }
}
if ($batch_log && !$GLOBALS['disable_eligibility_log']) {
    $fn = sprintf(
        'elig-batch_log_%s.html', //ALB Changed to html format
        date("Y-m-d:H:i:s")
    );
    $batch_log = str_replace('~', "~\r", $batch_log);
    while (@ob_end_flush()) {
    }
    header('Content-Type: text/html'); //ALB Changed to html format
    header("Content-Length: " . strlen($batch_log));
    header('Content-Disposition: attachment; filename="' . $fn . '"');
    ob_start();
    echo $batch_log;
    exit();
}
?>
<html>
<head>
<title><?php echo xlt('Eligibility Response File Upload'); ?></title>

<?php Header::setupHeader(['no_bootstrap', 'no_fontawesome']); ?>

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
    function edivalidation() {
        var mypcc = <?php echo xlj('Required Field Missing: Please choose the EDI-271 file to upload'); ?>;
        if (document.getElementById('uploaded').value == "") {
            alert(mypcc);
            return false;
        } else {
            $("#theform").trigger("submit");
        }
    }
</script>
</head>
<body class="body_top">
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
    <?php if (isset($message) && !empty($message)) { ?>
                <div style="margin-left:25%;width:50%;color:RED;text-align:center;font-family:arial;font-size:15px;background:#ECECEC;border:1px solid;" ><?php echo $message; ?></div>
        <?php
                $message = "";
    }
    if (isset($messageEDI)) { ?>
    <div style="margin-left:25%;width:50%;color:RED;text-align:center;font-family:arial;font-size:15px;background:#ECECEC;border:1px solid;" >
            <?php echo xlt('Please choose the proper formatted EDI-271 file'); ?>
    </div>
        <?php
        $messageEDI = "";
    } ?>
<div>
<span class='title'><?php echo xlt('Eligibility Response File (EDI-271) Upload'); ?></span>
<form enctype="multipart/form-data" name="theform" id="theform" action="edi_271.php" method="POST" onsubmit="return top.restoreSession()">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<div id="report_parameters">
    <table>
        <tr>
            <td width='550px'>
                <div style='float:left'>
                    <table class='text'>
                        <tr>
                            <td style='width:125px;' class='label_custom'> <?php echo xlt('Select EDI-271 file'); ?>:   </td>
                            <td> <input name="uploaded" id="uploaded" type="file" size=37 /></td>
                        </tr>
                    </table>
                </div>
            </td>
            <td align='left' valign='middle' height="100%">
                <table style='border-left:1px solid; width:100%; height:100%' >
                    <tr>
                        <td>
                            <div style='margin-left:15px'>
                                <a href='#' class='css_button' onclick='return edivalidation(); '><span><?php echo xlt('Upload and Display'); ?></span>
                                </a>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
<input type="hidden" name="form_orderby" value="<?php echo attr($form_orderby); ?>" />
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
</form>
</body>
</html>
