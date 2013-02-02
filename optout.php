<?php
session_save_path('sess');
session_start();
require_once 'incl/CAS.class.php';
if (!isset($_SESSION['user']))
{
	$user = CAS::authenticate();
	if ($user != -1) $_SESSION['user'] = $user;
}
if (isset($_SESSION['user']))
{
	$login = $_SESSION['user'];
	require_once 'incl/BDD.class.php';
	$bdd = new BDD();
	$bdd->exec("UPDATE etudiant SET nospam=1 WHERE login='$login'");
	header('Content-Type: text/html; charset=utf-8');
	echo 'L’adresse '.$login.'@etu.utc.fr a bien été désinscrite sur Troc’UTC.<br/><a href="javascript:history.back()">Retour à la page précédente.</a>';
}
else CAS::login();
?>