#!/bin/sh
cd /var/www/arsenal
php bin/console app:SupplierUpdater
echo "Tache mise à jour produit fournisseur exécutée !"