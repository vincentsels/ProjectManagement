
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

### Project timeline

* Shows an overview of all projects, sub-projects, categories and tickets for a certain version:
  * A visual representation of how much work a certain project / category is compared to the rest of the work for that version
  * A visual representation of how far the developer(s) have progressed, based on their estimations when supplied.
  * Optionally assign individual colors to developers.
* See when a certain developer will finish his or her work on a certain project *(work in progress)*.
* Switch between a per-project view or a per-resource view *(work in progress)*

### Resource management

* For each user, optionally specify the amount of hours per week and hourly cost, default type of work,...

## Upcoming features

### Customer management

* Specify which customer/company your reporting users are part of.
* Allow only a selection of customers to pay for certain features ('custom development'), or all customers ('common development').
* Divide the total billable cost among all participating customers, optionally in proportion to the customer size.
* Allow certain customers to be able to approve of tickets simply as a notion, or in order to be able to assign them.

### Prioritizing features

* Give issues a weight, based on configurable parameters.
* Set dependencies for tickets.
* Based on these and/or other parameters, view the order in which tickets should be handled.

### Resource overviews

* Get detailed overviews per resource - tickets solved, most overdue tickets,...

## Credits

A lot of credit goes to Mantis plugin-guru jreese ([http://noswap.com](http://noswap.com/)), whose plugins supplied a great source of examples and inspiration.
