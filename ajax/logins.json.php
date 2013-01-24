<?php
require_once 'incl/BDD.class.php';
header('Content-Type: application/json');
$f = 'cache/logins.'.date('ymd').'.json';
if (!file_exists($f))
{
	$bdd = new BDD();
	$req = $bdd->prepare("SELECT * FROM etudiant ORDER BY login ASC");
	$bdd->execute($req, null);
	array_map('unlink', glob('cache/logins.*.json'));
	file_put_contents($f, json_encode($req->fetchAll(PDO::FETCH_OBJ)));
}
echo file_get_contents($f);
?>