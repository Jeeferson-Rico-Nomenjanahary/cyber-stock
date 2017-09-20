Symfony Standard Edition
========================

Welcome to the Symfony Standard Edition - a fully-functional Symfony
application that you can use as the skeleton for your new applications.

For details on how to download and get started with Symfony, see the
[Installation][1] chapter of the Symfony Documentation.


Pré-requis
========================
- wamp server et php 7 : http://www.wampserver.com/
- composer : https://getcomposer.org/
- git : https://git-scm.com/downloads

#initialisation du projet
php bin/console doctrine:database:create
php bin/console doctrine:schema:update  --dump-sql
php bin/console doctrine:schema:update  --force

#creation utilisateur

php bin/console fos:user:create nomUtilisateur monemail@example.com monp@ssword
php bin/console fos:user:activate nomUtilisateur
php bin/console fos:user:promote nomUtilisateur ROLE_ADMIN

#lancer le serveur
php bin/console server:run

#les routes
php bin/console debug:router
#paramètres
Ajouter dans parameters.yml
-> per_page: 10

Virtual Host
========================
Step 1
aller dans C:\wamp\bin\apache\apache2.4.17\conf ;
editer le fichier httpd.conf
Rechercher " LoadModule vhost_alias_module " et décommenter
Setp 2
aller dans C:\wamp\bin\apache\apache2.4.17\conf\extra
editer le fichier httpd-vhosts.conf puis ajouter
<VirtualHost cyber-stock.local>
    ServerAdmin webmaster@dummy-host2.example.com
    DocumentRoot "c:/wamp/www/cyber-stock/web/app.php"
    ServerName cyber-stock.local
    ServerAlias www.cyber-stock.local
    ErrorLog "logs/dummy-host2.example.com-error.log"
    CustomLog "logs/dummy-host2.example.com-access.log" common
</VirtualHost>

Step 3 aller dans C:\Windows\System32\drivers\etc\
editer le fichier host (admin) ajouter:
127.0.0.1       cyber-stock.local
(cyber-stock.local correspond au Server:Name de l'etape 2)


#source https://john-dugan.com/wamp-vhost-setup/




Enjoy!

[1]:  https://symfony.com/doc/3.3/setup.html
[6]:  https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html
[7]:  https://symfony.com/doc/3.3/doctrine.html
[8]:  https://symfony.com/doc/3.3/templating.html
[9]:  https://symfony.com/doc/3.3/security.html
[10]: https://symfony.com/doc/3.3/email.html
[11]: https://symfony.com/doc/3.3/logging.html
[13]: https://symfony.com/doc/current/bundles/SensioGeneratorBundle/index.html
[14]: https://symfony.com/doc/current/setup/built_in_web_server.html

