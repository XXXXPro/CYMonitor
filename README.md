# CYMonitor

This is very simple Yandex CY monitoring script. 
It checks CY for all domains specified in file domain.json and if there is any changes will send email notification 
and generate file result.htm with report of CY.

Configuration:
* specify your domains in domain.json file in format { "domain1.tld":0, "domain2.tld":0 }
* specify Email addresses for From and To fields in constants MAIL_FROM and MAIL_TO in cy.php
* add cron job for this script on your server
