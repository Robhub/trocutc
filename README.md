trocutc
=======

Troc'UTC est une association de l�UTC (www.utc.fr).
Son but est de faciliter l��change d�UV entre les �tudiants.
La version en ligne se situe sur http://wwwassos.utc.fr/trocutc/v2/

Pour l�utiliser en local, d�abord, cr�ez une base de donn�es sur le mod�le du fichier BDD.sql
Ensuite, entrez les informations correspondantes dans BDD.json (mais ne commitez pas ce fichier �videmment)

Vous pouvez ensuite lancer le script de mise � jour de la base de donn�e (maj.php) qui va t�l�charger les fichiers d�emploi du temps (*.edt) sur le site du SME (http://wwwetu.utc.fr/sme/EDT/) et qui va ensuite les ajouter � la base de donn�e pour rendre l�application fonctionnelle.

ATTENTION : Pour ceux qui ont acc�s au FTP de Troc'UTC et veulent mettre � jour les fichiers : ne remplacez PAS le fichier BDD.json existant !!!