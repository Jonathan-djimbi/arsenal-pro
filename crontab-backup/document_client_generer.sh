#!/bin/sh

cd /var/www/arsenal/public/uploads/documents
tar -cvzf document-client.tar.gz .
mv document-client.tar.gz /var/www/arsenal/compte/utilisateurs
echo "archive documents OK"