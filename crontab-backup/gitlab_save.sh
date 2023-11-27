#!/bin/sh

DATE=`date '+%F %H:%M:%S'`

cd /var/www/arsenal

#php bin/console cache:clear
#echo "Caches cleared !"
#git add .
#git commit -m "'sauvegarde vers le gitlab $DATE'"
#git push
echo "Script sauvegarde OK !"