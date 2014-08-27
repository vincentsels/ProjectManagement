
# Mantis Project Management

*Copyright (C) 2011 Vincent Sels*

## Summary

Project Management is a plugin for the open source [MantisBT](http://www.mantisbt.org) tool (also check out its [Github](https://github.com/mantisbt) page). This plugin attempts to add basic project management features such as estimations, timetracking, project follow-up, and resource management to the existing bugtracking functionality.

## Features

### Advanced time registration

* Allows **different types of work** like analysis, development and testing.
* Optionally supply an **estimation** to be set once. The remaining work todo is calculated.
* Optionally overrule the calculated amount of **work to do**. The difference with calculated amount of work is visualised.
* Displays a **summary** in the bug view details.
* Highly **configurable**: specify for which profiles time registration is enabled, which profiles can alter estimations, which sorts work are usable by which profiles,...

### Time registration overviews

* Time registration worksheet:
  * Displays **recently visited** issues, allows direct input of hours done.
  * Displays **recently registered** hours done, by day, week and month.
* Time registration overview / billing:
  * Limited to managers, an **overview** of all hours registered, the price per hour of the resource, and total costs.
  * The ability to delete or **change** time registration entries *(todo)*.
  * Easily **navigate** to data for a specific project, category, user, type of work,...
  * Displays summary reports per user.
  * Allow easy export to excel or other formats *(todo)*.

### Per-resource Gantt chart

* In addition to the amount of hours per week, also add support to enter non-working days per resource.
* A Gantt chart that lists all work items per resource, shows the progress and expected completion date.
* Play around with expected 'availability' of each resource, expressed as a percentage of their available time,
to discover a realistic date of when this developer's work will be finished.

### Project timeline

* Shows an overview of all projects, sub-projects, categories and tickets for a certain version:
  * A visual representation of how much work a certain project / category is compared to the rest of the work for that version
  * A visual representation of how far the developer(s) have progressed, based on their estimations when supplied.
  * Optionally assign individual colors to developers.

### Resource management

* For each user, optionally specify the amount of hours per week and hourly cost, default type of work,...

### Customer management

* Specify which customer/company your reporting users are part of.
* Allow only a selection of customers to pay for certain features ('custom development'), or all customers ('common development').
* Divide the total billable cost among all participating customers, optionally in proportion to the customer size.
* Allow certain customers to be able to approve of tickets simply as a notion, or in order to be able to assign them.

## Upcoming features

### Resource overviews

* Get detailed overviews per resource - tickets solved, most overdue tickets,...

## Credits

A lot of credit goes to Mantis plugin-guru jreese ([http://noswap.com](http://noswap.com/)), whose plugins supplied a great source of examples and inspiration.

## Compatibility issues

* In order to be able to deviate from the general 'working hours per day' on user-basis, you need Mantis v1.2.9 or onward.
* Starting from v1.1 of this plugin, one small feature will not work with the base installation of Mantis:
  * When 'paying customer' is set as a required field, you will be able to leave it blank when changing a bug's status.
In order to be able to use this feature, two small modifications have to be made to the basic Mantis installation,
as explained in [this Mantis issue](http://www.mantisbt.org/bugs/view.php?id=14329). This is necessary until the Mantis team decides
to include the event in their core, if they deem it appropriate.

## Dependencies

* [Array export to Excel](https://github.com/vincentsels/array-export-excel) (introduced at v1.4.0)

## How-to's

## Import data from [timecard plugin](https://github.com/mantisbt-plugins/timecard) to ProjectManagement plugin

* Import timecard estimate table:
```
    INSERT INTO mantis_plugin_ProjectManagement_work_table
        (bug_id, user_id, work_type, minutes_type, minutes, book_date, timestamp) 
            SELECT 
                bug_id, 
                3 as user_id, -- a real user_id
                50 as work_type, -- develop
                0 as minutes_type, -- estimate
                (estimate*60) as minutes,
                timestamp as book_date,
                timestamp
            FROM 
                mantis_plugin_Timecard_estimate_table 
```

* Import timecard update table:

```
    INSERT INTO mantis_plugin_ProjectManagement_work_table
       (bug_id, user_id, work_type, minutes_type, minutes, book_date, timestamp) 
           SELECT 
               ut.bug_id, 
               ut.user_id, 
               50 as work_type, -- develop
               1 as minutes_type, -- done 
               (ut.spent*60) as minutes,
               nt.date_submitted as book_date,
               nt.date_submitted as timestamp
           FROM 
               mantis_plugin_Timecard_update_table  ut,
               mantis_bugnote_table nt
           WHERE
               ut.bug_id = nt.bug_id
           AND
               ut.user_id = nt.reporter_id
           AND 
               ut.bugnote_id = nt.id 
```