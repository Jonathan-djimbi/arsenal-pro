#!/bin/sh

var=$(date +"%FORMAT_STRING")
today=$(date -d "$date -1 months" +"%m-%Y")
cd /var/www/arsenal/factures
tar -cvzf facture-${today}-recap.tar.gz ${today}
mv facture-${today}-recap.tar.gz backup
echo "archive facture OK"