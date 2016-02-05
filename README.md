HPKP-Reports
============
This is a simple hkpk report endpoint that accepts the JSON data and inserts it into a MySQL database.  
It can also send out an email alert.

Installation
------------
- clone the repository
- create a mysql database using includes/hpkp.sql
- copy includes/config.ini.example to include/config.ini and adjust the values

Roadmap
-------
Eventually this should also include a web ui to view the reports.
