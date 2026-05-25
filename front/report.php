<?php

include("../../../inc/includes.php");
include_once(__DIR__ . '/../src/report.class.php');

Session::checkRight("ticket", READ);

Html::header(
   "Bouwen Reports",
   $_SERVER['PHP_SELF'],
   "tools",
   "pluginworkhours"
);

// =====================
// INPUTS
// =====================

$start = $_GET['start'] ?? date('Y-m-01');
$end   = $_GET['end'] ?? date('Y-m-d');

$start = Html::cleanInputText($start);
$end   = Html::cleanInputText($end);

$entity = Session::getActiveEntity();

// =====================
// DATA
// =====================

$results = PluginWorkhoursReport::getTicketTasksReport(
   $start,
   $end,
   $entity
);

$totalHours = 0;
$techTotals = [];

// =====================
// PAGE STYLE
// =====================

echo "
<style>

.report-header {
   display:flex;
   justify-content:space-between;
   align-items:center;
   margin-bottom:20px;
}

.report-title {
   font-size:24px;
   font-weight:bold;
}

.report-summary {
   margin:20px 0;
   padding:15px;
   background:#f5f5f5;
   border-radius:6px;
}

.report-summary strong {
   font-size:18px;
}

.print-btn {
   padding:8px 12px;
   background:#1976d2;
   color:white !important;
   border-radius:4px;
   text-decoration:none;
   font-weight:bold;
}

.print-btn:hover {
   opacity:0.9;
}

.ticket-btn {
   padding:4px 8px;
   background:#2e7d32;
   color:white !important;
   border-radius:4px;
   text-decoration:none;
   font-size:12px;
}

.hours-cell {
   text-align:right;
   font-weight:bold;
}

.total-row {
   background:#fafafa;
   font-weight:bold;
}

.tech-table {
   margin-top:25px;
   width:400px;
}

@media print {

   .no-print {
      display:none !important;
   }

   body {
      background:white;
   }

   table {
      font-size:12px;
   }
}

</style>
";

// =====================
// HEADER
// =====================

echo "
<div class='report-header'>
   <div class='report-title'>
      Work Hours Report
   </div>

   <div class='no-print'>
      <button class='vsubmit' onclick='window.print()'>
         Print Report
      </button>
   </div>
</div>
";

// =====================
// FILTER FORM
// =====================

echo "
<form method='GET' class='no-print' style='margin-bottom:20px;'>

<table class='tab_cadre_fixe'>
<tr>

<td>
   Start:
   <input type='date' name='start' value='{$start}'>
</td>

<td>
   End:
   <input type='date' name='end' value='{$end}'>
</td>

<td>
   <input type='submit' class='submit' value='Filter'>
</td>

</tr>
</table>

</form>
";

// =====================
// TABLE
// =====================

echo "
<table class='tab_cadre_fixehov'>

<tr>
   <th>Entity</th>
   <th>Ticket</th>
   <th>Task ID</th>
   <th>Date</th>
   <th>Technician</th>
   <th>Hours</th>
   <th class='no-print'>Actions</th>
</tr>
";

// =====================
// ROWS
// =====================

foreach ($results as $row) {

   $hours = round($row['actiontime'] / 3600, 2);

   $totalHours += $hours;

   $technician = $row['technician'] ?: 'Unknown';

   if (!isset($techTotals[$technician])) {
      $techTotals[$technician] = 0;
   }

   $techTotals[$technician] += $hours;

   $ticketId = (int)$row['ticket_id'];

   $ticketLink = "/front/ticket.form.php?id={$ticketId}";

   $printLink = "/plugins/workhours/front/print_ticket.php?id={$ticketId}";

   echo "
   <tr>

      <td>
         " . Html::clean($row['entity_name']) . "
      </td>

      <td>
         <a href='{$ticketLink}' target='_blank'>
            #{$ticketId} - " . Html::clean($row['ticket_name']) . "
         </a>
      </td>

      <td>
         " . (int)$row['task_id'] . "
      </td>

      <td>
         " . Html::clean($row['date']) . "
      </td>

      <td>
         " . Html::clean($technician) . "
      </td>

      <td class='hours-cell'>
         {$hours}
      </td>

      <td class='no-print'>

         <a class='ticket-btn'
            href='{$printLink}'
            target='_blank'>
            Print Ticket
         </a>

      </td>

   </tr>
   ";
}

// =====================
// TOTAL
// =====================

echo "
<tr class='total-row'>

   <td colspan='5' style='text-align:right;'>
      TOTAL HOURS
   </td>

   <td class='hours-cell'>
      {$totalHours}
   </td>

   <td class='no-print'></td>

</tr>
";

echo "</table>";

// =====================
// SUMMARY
// =====================

echo "
<div class='report-summary'>

<strong>Total Hours:</strong>
{$totalHours}

</div>
";

// =====================
// TECHNICIAN TOTALS
// =====================

echo "
<h2>Technician Totals</h2>

<table class='tab_cadre_fixehov tech-table'>

<tr>
   <th>Technician</th>
   <th>Hours</th>
</tr>
";

arsort($techTotals);

foreach ($techTotals as $tech => $hours) {

   echo "
   <tr>

      <td>
         " . Html::clean($tech) . "
      </td>

      <td class='hours-cell'>
         {$hours}
      </td>

   </tr>
   ";
}

echo "</table>";

// =====================
// FOOTER
// =====================

Html::footer();