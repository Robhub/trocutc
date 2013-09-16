<?php
if (!isset($_GET['login'])) die();
if (!isset($_GET['jour'])) die();
if (!isset($_GET['debut'])) die();
if (!isset($_GET['fin'])) die();
if (!isset($_GET['uv'])) die();
if (!isset($_GET['type'])) die();

require_once 'incl/BDD.class.php';
$bdd = new BDD();
$B = "cours B ON (A.login = B.login AND B.jour=:jour AND B.type!='C' AND (B.debut<:debut AND B.fin>:fin OR B.debut>=:debut AND B.debut<:fin OR B.fin>:debut AND B.fin<=:fin))";
$C = "cours C ON (A.login = C.login AND C.jour=:jour AND C.type='C' AND (C.debut<:debut AND C.fin>:fin OR C.debut>=:debut AND C.debut<:fin OR C.fin>:debut AND C.fin<=:fin))";
$nospam = 'SELECT login FROM etudiant WHERE nospam=1';
$requete = "SELECT A.*,COUNT(B.login) tdtps, COUNT(C.login) cours, A.login NOT IN ($nospam) AS canmail FROM cours A LEFT JOIN $B LEFT JOIN $C WHERE A.uv=:uv AND A.type=:type GROUP BY A.login,A.uv,A.jour,A.debut ORDER BY A.login";
$req = $bdd->prepare($requete);
$bdd->execute($req,array('jour'=>$_GET['jour'],'debut'=>$_GET['debut'],'fin'=>$_GET['fin'],'uv'=>$_GET['uv'],'type'=>$_GET['type']));

file_put_contents('logs/alts.txt', date('Y-m-d H:i:s').' '.$_SESSION['user'].' '.$_GET['login'].' '.$_GET['uv']."\r\n", FILE_APPEND);
header('Content-Type: application/json');
echo json_encode($req->fetchAll(PDO::FETCH_OBJ));
?>