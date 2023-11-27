#!/bin/bash

cd /var/www/arsenal
php bin/console app:EnvoiFactureMensuelMail
php bin/console app:MailMensuelReleveUtilisateurs
php bin/console app:mailTermesRecherches