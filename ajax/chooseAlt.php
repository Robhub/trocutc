<?php
if (!isset($_GET['uv'])) die();
if (!isset($_GET['type'])) die();
if (!isset($_GET['groupe'])) die();
require_once 'incl/CONF.class.php';
require_once 'incl/BDD.class.php';
$bdd = new BDD();

$uv = $_GET['uv'];
$type = '';
if ($_GET['type'] == 'T') $type = 'TP';
if ($_GET['type'] == 'D') $type = 'TD';
// On récupère le jour,debut,fin du cours actuel que l’étudiant possède actuellement et veut échanger.
$req = $bdd->prepare("SELECT jour,debut,fin FROM cours WHERE login=:login AND uv=:uv AND type=:type");
$bdd->execute($req,array('login'=>$_SESSION['user'],'uv'=>$_GET['uv'],'type'=>$_GET['type']));
$res = $req->fetch(PDO::FETCH_OBJ);
if (!is_object($res)) die("Tu ne possèdes pas de $type de $uv."); // Petit malin essaye d’échanger un cours qu’il n’a pas.

// On récupère la liste des gens qui ont le cours qui intéresse l’étudiant et qui sont libres au moment de son cours actuel.
$sousrequete = 'SELECT login FROM cours WHERE jour=:jour AND (debut<:debut AND fin>:fin OR debut>=:debut AND debut<:fin OR fin>:debut AND fin<=:fin)';
$nospam = 'SELECT login FROM etudiant WHERE nospam=1';
$requete = "SELECT login FROM cours WHERE login NOT IN ($sousrequete) AND login NOT IN ($nospam) AND uv=:uv AND type=:type AND groupe=:groupe ORDER BY login";
$req = $bdd->prepare($requete);
$bdd->execute($req,array('jour'=>$res->jour,'debut'=>$res->debut,'fin'=>$res->fin,'groupe'=>$_GET['groupe'],'uv'=>$_GET['uv'],'type'=>$_GET['type']));
$logins = $req->fetchAll(PDO::FETCH_OBJ);

// Génération de l’e-mail.
$mymail = $_SESSION['user']." via TrocUTC <".$_SESSION['user'].'@etu.utc.fr>';

$deb = floor($res->debut/60).'h'.$res->debut%60;
$fin = floor($res->fin/60).'h'.$res->fin%60;
$jours = array('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche');
$jour = $jours[$res->jour];

$mails = array();
foreach($logins as $x) { $mails[] = $x->login.'@etu.utc.fr'; }
$subject = "Troc’UTC : Demande d’échange $uv";
//$message = '[DEBUG : vrais mails = '.implode(', ',$mails)."]\r\n";// DEBUG
$message = '';// VERSION FINALE
$message .= "Bonjour,\r\n";
$message .= $_SESSION['user']."@etu.utc.fr aimerais échanger son $type de $uv qu’il/elle a de $deb à $fin le $jour avec le tiens.\r\n";
$message .= "Si tu es intéressé(e), réponds-lui par E-Mail au plus vite.\r\n";
$message .= "\r\n";
$message .= 'Pour ne plus recevoir de messages, va sur '.CONF::serviceurl()."optout.php et connecte-toi avec ton compte UTC.";
$headers = '';
$headers .= "From: $mymail\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/plain; charset=UTF-8\r\n";
$headers .= 'Bcc: ' . $mymail . ', ' . implode(', ',$mails);
//echo htmlspecialchars("==TO==\n$mymail\n");
//echo htmlspecialchars("==SUBJECT==\n$subject\n");
//echo htmlspecialchars("==MESSAGE==\n$message\n");
//echo htmlspecialchars("==HEADERS==\n$headers\n");
//echo "==RESULTAT==\n";
echo date('Y/m/d H:i:s ');
if (@mail('', mutf8($subject), $message, $headers)) echo 'E-Mail envoyé'; // premier param = destinataires visibles
else echo 'Erreur lors de l’envoi E-mail';
// On log
file_put_contents('logs/chooseAlt.txt', date('Y-m-d H:i:s').' '.$_SESSION['user'].' '.$_GET['uv'].' '.$_GET['type'].' '.$_GET['groupe'].' '.implode(', ',$mails)."\r\n", FILE_APPEND);
function mutf8($x)
{
	return '=?UTF-8?B?'.base64_encode($x).'?=';
}
?>