<?php

class PluginWorkhoursMenu extends CommonGLPI {

   static function getMenuName() {
      return "Work Hours";
   }

   static function getMenuContent() {
      return [
         'title' => "Work Hours",
         'page'  => '/plugins/workhours/front/report.php',
         'icon'  => 'fas fa-clock'
      ];
   }
}