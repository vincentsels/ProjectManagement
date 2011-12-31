
# Mantis Project Management

*Copyright (C) 2011 Vincent Sels*

## Summary

Project Management is a plugin for the open source [MantisBT](http://www.mantisbt.org) tool (also check out its [Github](https://github.com/mantisbt) page). This plugin attempts to add basic project management features such as estimations, timetracking, project follow-up, and resource management to the existing bugtracking functionality.

## Current features

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

### Resource management

* For each user, optionally specify the amount of hours per week and hourly cost, default type of work, *(todo)*...

## Upcoming features

### Project timeline

* Show an MS project-like matrix of projects, tickets, versions, resources, work done and todo.
* Allow for easy redistribution of work among available resources.

### Resource overviews

* Get detailed overviews per resource - tickets solved, most overdue tickets,...


## Credits

A lot of credit goes to Mantis plugin-guru jreese ([http://noswap.com](http://noswap.com/)), whose plugins supplied a great source of examples and inspiration.
