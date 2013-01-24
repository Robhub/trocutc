<?php
require_once 'incl/CURL.class.php';
header('Content-Type: application/xml');
$f = 'cache/resto.'.date('ymd').'.xml';
if (!file_exists($f))
{
	array_map('unlink', glob('cache/resto.*.xml'));
	$curl = new CURL(strpos($_SERVER['HTTP_HOST'],'utc') !== false); // Utilisation du proxyweb.utc.fr si on est sur le serveur de l'UTC
	file_put_contents($f, $curl->get('http://www.crous-amiens.fr/iRestos/xml/amiens_menu.xml'));
}
echo file_get_contents($f);
?>