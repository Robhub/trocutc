<?php
session_save_path('sess');
session_start();
require_once 'incl/CONF.class.php';
require_once 'incl/CAS.class.php';
if (!isset($_SESSION['user']))
{
	$user = CAS::authenticate();
	if ($user != -1) $_SESSION['user'] = $user;
}
if (isset($_SESSION['user']))
{
	$login = $_SESSION['user'];
	//if (!isset($_GET['key']) || $_GET['key'] != CONF::keygen($login)) die('Erreur : Clef invalide.');
	require_once 'incl/BDD.class.php';
	$bdd = new BDD();
	$bdd->exec("UPDATE etudiant SET nospam=1 WHERE login='$login'");
	echo 'L�adresse '.$login.'@etu.utc.fr a bien �t� d�sinscrite sur Troc�UTC.<br/><a href="javascript:history.back()">Retour � la page pr�c�dente.</a>';
}
else CAS::login();
?>