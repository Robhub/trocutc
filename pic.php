<?php
session_save_path('sess');
session_start();
if (!isset($_SESSION['user'])) die('Not logged in.');
if (!isset($_GET['login']) || !preg_match('#[a-z]{8}#',$_GET['login'])) die('Bad login.');

$path = 'PIC/'.$_GET['login'].'.jpg';
if (!file_exists($path)) unknown();
$content = file_get_contents($path);
if (strlen($content) < 42) unknown();

header('Content-Type: image/jpeg');
echo $content;

function unknown()
{
	header('Content-Type: image/png');
	echo file_get_contents('view/unknown.png');
	die();
}

?>