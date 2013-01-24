<?php
session_save_path('sess');
session_start();
if (!isset($_SESSION['user'])) die('Not logged in.');
$ajax = array('alts.json','chooseAlt','cours.json','logins.json','resto.xml');
if (!isset($_GET['a'])) die();
else if (in_array($_GET['a'],$ajax)) require_once 'ajax/'.$_GET['a'].'.php';
?>