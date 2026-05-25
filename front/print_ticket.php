<?php

include("../../../inc/includes.php");

Session::checkRight("ticket", READ);

$ticket_id = (int)($_GET['id'] ?? 0);

$ticket = new Ticket();

if (!$ticket->getFromDB($ticket_id)) {
   Html::displayErrorAndDie("Ticket not found");
}

// ======================================
// HEADER
// ======================================

Html::header(
   "Print Ticket",
   '',
   '',
   ''
);

// ======================================
// HELPERS
// ======================================

function formatHours(int $seconds): string
{
   return round($seconds / 3600, 2) . " h";
}

// ======================================
// LOAD USERS
// ======================================

$requester = new User();
$technician = new User();

$requester_name = "N/A";
$technician_name = "N/A";

if (!empty($ticket->fields['users_id_recipient'])) {
   if ($requester->getFromDB($ticket->fields['users_id_recipient'])) {
      $requester_name = $requester->fields['name'];
   }
}

if (!empty($ticket->fields['users_id_lastupdater'])) {
   if ($technician->getFromDB($ticket->fields['users_id_lastupdater'])) {
      $technician_name = $technician->fields['name'];
   }
}

// ======================================
// STYLE
// ======================================

echo "
<style>

body {
   font-family: Arial, sans-serif;
   margin: 20px;
   color: #222;
}

h1, h2, h3 {
   margin-bottom: 10px;
}

.section {
   margin-top: 25px;
}

.ticket-header {
   border-bottom: 2px solid #333;
   padding-bottom: 10px;
}

.info-table {
   width: 100%;
   border-collapse: collapse;
   margin-top: 10px;
}

.info-table td,
.info-table th {
   border: 1px solid #ccc;
   padding: 8px;
   vertical-align: top;
}

.timeline-item {
   border: 1px solid #ddd;
   border-radius: 5px;
   padding: 15px;
   margin-bottom: 15px;
   background: #fafafa;
}

.timeline-header {
   margin-bottom: 10px;
   font-size: 14px;
   color: #555;
}

.task-time {
   font-weight: bold;
   color: #1565c0;
}

.print-button {
   margin-bottom: 20px;
}

.content-box {
   background: #fff;
   border: 1px solid #ddd;
   padding: 15px;
   border-radius: 5px;
}

pre {
   white-space: pre-wrap;
   word-wrap: break-word;
   font-family: Arial;
}

@media print {

   .no-print {
      display: none;
   }

   body {
      margin: 0;
   }
}

</style>
";

// ======================================
// PRINT BUTTON
// ======================================

echo "
<div class='no-print print-button'>
   <button onclick='window.print()' class='vsubmit'>
      Print Ticket
   </button>
</div>
";

// ======================================
// TICKET HEADER
// ======================================

echo "
<div class='ticket-header'>

<h1>
   Ticket #{$ticket->fields['id']}
</h1>

<h2>
   " . Html::clean($ticket->fields['name']) . "
</h2>

</div>
";

// ======================================
// GENERAL INFO
// ======================================

echo "
<div class='section'>

<h3>General Information</h3>

<table class='info-table'>

<tr>
   <th>Requester</th>
   <td>" . Html::clean($requester_name) . "</td>

   <th>Status</th>
   <td>" . Ticket::getStatus($ticket->fields['status']) . "</td>
</tr>

<tr>
   <th>Created</th>
   <td>{$ticket->fields['date']}</td>

   <th>Last Update</th>
   <td>{$ticket->fields['date_mod']}</td>
</tr>

<tr>
   <th>Technician</th>
   <td>" . Html::clean($technician_name) . "</td>

   <th>Total Time</th>
   <td>" . formatHours((int)$ticket->fields['actiontime']) . "</td>
</tr>

</table>

</div>
";

// ======================================
// DESCRIPTION
// ======================================

echo "
<div class='section'>

<h3>Description</h3>

<div class='content-box'>
" . nl2br(Html::clean($ticket->fields['content'])) . "
</div>

</div>
";

// ======================================
// TASKS
// ======================================

echo "
<div class='section'>

<h3>Tasks</h3>
";

global $DB;

$query = "
   SELECT
      tt.id,
      tt.date,
      tt.content,
      tt.actiontime,
      u.name AS technician
   FROM glpi_tickettasks tt
   LEFT JOIN glpi_users u
      ON u.id = tt.users_id_tech
   WHERE tt.tickets_id = $ticket_id
   ORDER BY tt.date ASC
";

$result = $DB->request($query);

$totalTaskTime = 0;

if (count($result) === 0) {

   echo "<p>No tasks found.</p>";

} else {

   foreach ($result as $t) {

      $taskHours = (int)$t['actiontime'];

      $totalTaskTime += $taskHours;

      echo "
      <div class='timeline-item'>

         <div class='timeline-header'>

            <strong>Date:</strong>
            {$t['date']}

            &nbsp; | &nbsp;

            <strong>Technician:</strong>
            " . Html::clean($t['technician'] ?? 'Unknown') . "

            &nbsp; | &nbsp;

            <span class='task-time'>
               " . formatHours($taskHours) . "
            </span>

         </div>

         <div>
            " . nl2br(Html::clean($t['content'])) . "
         </div>

      </div>
      ";
   }

   echo "
   <p>
      <strong>Total Task Time:</strong>
      " . formatHours($totalTaskTime) . "
   </p>
   ";
}

echo "</div>";

// ======================================
// FOLLOWUPS / RESPONSES
// ======================================

echo "
<div class='section'>

<h3>Responses / Followups</h3>
";

$followup = new ITILFollowup();

$followups = $followup->find(
   ['items_id' => $ticket_id],
   ['date' => 'ASC']
);

if (count($followups) === 0) {

   echo "<p>No followups found.</p>";

} else {

   foreach ($followups as $f) {

      $followupUser = new User();

      $followupUserName = "Unknown";

      if (!empty($f['users_id'])) {

         if ($followupUser->getFromDB($f['users_id'])) {
            $followupUserName = $followupUser->fields['name'];
         }
      }

      $isPrivate = (int)$f['is_private'];

      echo "
      <div class='timeline-item'>

         <div class='timeline-header'>

            <strong>Date:</strong> {$f['date']}
            &nbsp; | &nbsp;

            <strong>User:</strong>
            " . Html::clean($followupUserName) . "

            &nbsp; | &nbsp;
      ";

      if ($isPrivate) {
         echo "<span style='color:red;font-weight:bold;'>PRIVATE</span>";
      } else {
         echo "<span style='color:green;font-weight:bold;'>PUBLIC</span>";
      }

      echo "
         </div>

         <div>
            " . nl2br(Html::clean($f['content'])) . "
         </div>

      </div>
      ";
   }
}

echo "</div>";

// ======================================
// SOLUTION
// ======================================

if (!empty($ticket->fields['solution'])) {

   echo "
   <div class='section'>

   <h3>Solution</h3>

   <div class='content-box'>
      " . nl2br(Html::clean($ticket->fields['solution'])) . "
   </div>

   </div>
   ";
}

// ======================================
// FOOTER
// ======================================

echo "
<div class='section' style='margin-top:50px;font-size:12px;color:#777;'>

Generated by Bouwen WorkHours Plugin

</div>
";

Html::footer();