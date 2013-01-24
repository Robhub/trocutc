trocutc
=======

Troc'UTC est une association de l’UTC (www.utc.fr).
Son but est de faciliter l’échange d’UV entre les étudiants.
La version en ligne se situe sur http://wwwassos.utc.fr/trocutc/v2/

Pour l’utiliser en local, d’abord, créez une base de données sur le modèle du fichier BDD.sql
Ensuite, entrez les informations correspondantes dans BDD.json (mais ne commitez pas ce fichier évidemment)

Vous pouvez ensuite lancer le script de mise à jour de la base de donnée (maj.php) qui va télécharger les fichiers d’emploi du temps (*.edt) sur le site du SME (http://wwwetu.utc.fr/sme/EDT/) et qui va ensuite les ajouter à la base de donnée pour rendre l’application fonctionnelle.

ATTENTION : Pour ceux qui ont accès au FTP de Troc'UTC et veulent mettre à jour les fichiers : ne remplacez PAS le fichier BDD.json existant !!!