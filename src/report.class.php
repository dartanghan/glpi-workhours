<?php

class PluginworkhoursReport extends CommonGLPI {

   static function getMenuName() {
      return "Bouwen Reports";
   }

   static function getMenuContent() {
      return [
         'title' => self::getMenuName(),
         'page'  => '/plugins/workhours/front/report.php'
      ];
   }

   public static function getTicketTasksReport($start, $end, $entity_id) {
      global $DB;

      $query = "
         SELECT 
               tt.id AS task_id,
               tt.date,
               tt.actiontime,
               tt.begin,
               tt.end,
               t.id AS ticket_id,
               t.name AS ticket_name,
               t.date_creation,
               e.id AS entity_id,
               e.name AS entity_name,
               e.completename,
               u.name AS technician
            FROM glpi_tickettasks tt
            INNER JOIN glpi_tickets t 
               ON t.id = tt.tickets_id
            LEFT JOIN glpi_entities e 
               ON e.id = t.entities_id
            LEFT JOIN glpi_users u 
               ON u.id = tt.users_id_tech
            WHERE 
               t.is_deleted = 0
               AND tt.date BETWEEN '$start' AND DATE_ADD('$end', INTERVAL 1 DAY)
               AND (e.id = $entity_id)
               AND tt.actiontime > 0
            ORDER BY tt.date DESC
      ";

      return $DB->request($query);
   }
}