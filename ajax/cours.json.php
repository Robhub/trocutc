<?php
if (!isset($_GET['login'])) die();
require_once 'incl/BDD.class.php';
$bdd = new BDD();
$req = $bdd->prepare("SELECT * FROM cours WHERE login=?");
$bdd->execute($req,array($_GET['login']));
header('Content-Type: application/json');
echo json_encode($req->fetchAll(PDO::FETCH_OBJ));
?>