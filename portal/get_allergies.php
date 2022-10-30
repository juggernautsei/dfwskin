<?php

/**
 *
 * Copyright (C) 2016-2019 Jerry Padgett <sjpadgett@gmail.com>
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
 * @link http://www.open-emr.org
 *
 */

        require_once("verify_session.php");

//ALB Need this for date formatting
require_once("$srcdir/formatting.inc.php");

        $sql = "SELECT * FROM lists WHERE pid = ? AND type = 'allergy' AND activity = 1 ORDER BY begdate"; //ALB Added activity=1
        $res = sqlStatement($sql, array($pid));

if (sqlNumRows($res) > 0) {
    ?>
    <table class="table table-striped">
        <tr class="header">
    <th><?php echo xlt('Allergy'); //ALB Changed a few headers here ?></th>
    <th><?php echo xlt('Reaction'); ?></th>
    <th><?php echo xlt('Start Date'); ?></th>
    <th><?php echo xlt('End Date'); ?></th>
    <th><?php echo xlt('Last Modified'); ?></th>
        </tr>
    <?php
    $even = false;
    while ($row = sqlFetchArray($res)) {
        echo "<tr class='" . ($class ?? '') . "'>";
        echo "<td>" . text($row['title']) . "</td>";
        //ALB modified these rows
        echo "<td>".text($row['reaction'])."</td>";
        echo "<td>".text(oeFormatShortDate($row['begdate']))."</td>"; //ALB added formatting
        echo "<td>".text(oeFormatShortDate($row['enddate']))."</td>";
        echo "<td>".text(oeFormatShortDate($row['modifydate']))."</td>"; 
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo xlt("No Results");
}
?>
