@echo off

set url=http://localhost/hpkp-reports/

curl -H "Content-Type: application/json" -X POST --data-binary "@hpkp-report.json" %url%


pause