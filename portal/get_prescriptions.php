<?php
/**
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * Copyright (C) 2011 Cassian LUP <cassi.lup@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author Cassian LUP <cassi.lup@gmail.com>
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org
 *
 */

//ALB Added this to show prescriptions on file

        require_once("verify_session.php");
//ALB Need this for date formatting
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/options.inc.php");

    $sql = "SELECT * FROM prescriptions WHERE patient_id = ? AND active = 1 ORDER BY start_date";
    $res = sqlStatement($sql, array($pid));

if (sqlNumRows($res)>0) {
    ?>
    <table class="table table-striped">
        <tr>
        <th><?php echo xlt('Drug'); ?></th>
        <th><?php echo xlt('Directions'); ?></th>
        <th><?php echo xlt('Notes'); ?></th>
        <th><?php echo xlt('Last Prescribed'); ?></th>
        </tr>
    <?php
    $even=false;
    while ($row = sqlFetchArray($res)) {
        $runit = generate_display_field(array('data_type'=>'1','list_id'=>'drug_units'), $row['unit']);
        $rin = generate_display_field(array('data_type'=>'1','list_id'=>'drug_form'), $row['form']);
        $rroute = generate_display_field(array('data_type'=>'1','list_id'=>'drug_route'), $row['route']);
        $rint = generate_display_field(array('data_type'=>'1','list_id'=>'drug_interval'), $row['interval']);
        $unit='';
        if ($row['size'] > 0) {
            $unit = text($row['size']) . " " . $runit . " ";
        }
        
        echo "<tr class='".text($class)."'>";
        echo "<td>".text($row['drug'])." " . $unit . " " . $rin ."</td>";
        echo "<td>".text($row['dosage']) . " " . $rin . " " . $rroute . " " . $rint."</td>";
        echo "<td>".text($row['note'])."</td>";
        echo "<td>".text(oeFormatShortDate($row['date_modified']))."</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo xlt("None");
}
?>
