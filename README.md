# WorkHours GLPI Plugin

WorkHours is a GLPI plugin that adds a ticket task time report and ticket printing view to help track technician hours across tickets and entities.

## Features

* Adds a **Work Hours** report under the GLPI Tools menu.
* Filters ticket task hours by **start date**, **end date**, and the current **active entity**.
* Displays ticket task records with:
  * Ticket ID and name
  * Task ID and date
  * Technician name
  * Hours spent per task
* Shows a **total hours** summary and technician-level totals.
* Provides a **Print Ticket** view for each ticket with:
  * Ticket metadata
  * Task timeline and hours
  * Followups / responses
  * Ticket solution

## Requirements

* GLPI `>= 10.0.0` and `< 11.0.569`
* PHP `>= 8.2`

## Installation

1. Copy the plugin folder to your GLPI plugins directory, for example:
   ```bash
   cp -R /path/to/workhours /var/www/glpi/plugins/workhours
   ```
2. In GLPI, go to **Configuration > Plugins**.
3. Locate **WorkHours** and click **Install**.
4. Activate the plugin.

## Usage

1. Open GLPI and select **Tools > Work Hours** from the sidebar menu.
2. Use the date filters and click **Filter** to refresh the report.
3. Review the returned ticket tasks and total hours.
4. Click **Print Ticket** for a detailed, printable ticket summary.

## Notes

* The report automatically limits results to the current active entity.
* Only ticket tasks with `actiontime > 0` are included.
* The plugin does not create additional database tables.

## Plugin Metadata

* Name: `WorkHours`
* Version: `1.0.56`
* Author: `Bouwen`
* License: `MIT`
* Homepage: `http://www.bouwen.com.br`

## Contributing

* Use a fork or branch for any changes.
* Follow GLPI plugin development guidelines.
* Submit pull requests with bug fixes or enhancements.
