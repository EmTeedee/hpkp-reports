HPKP-Reports
============
This is a simple hkpk report endpoint that accepts the JSON data and inserts it into a MySQL database.  
It can also send out an email alert.

Installation
------------
- clone the repository
- create a mysql database using includes/hpkp.sql
- copy includes/config.ini.example to include/config.ini and adjust the values
- point your pins to the reporter e.g. ```report-uri="http://yourhostname/hpkp-reports/"```

Note: Make sure that the reporter is installed on a different domain to avoid problems with HSTS or HPKP preventing contact to the reporting service.

Roadmap
-------
Eventually this should also include a web ui to view the reports.
