<?php
header('Content-Type: text/html; charset=utf-8');
require_once './incl/BDD.class.php';
$bdd = new BDD();
// BEGIN INSERTION de TOUS les login
function edtbasename($f){ return basename($f,'.edt'); }
$dispo = array_map('edtbasename',glob('./EDT/*.edt'));
$already = array();
foreach ($bdd->query('SELECT login FROM etudiant') as $row)
{
	$already[] = $row['login'];
}
$todo = array_diff($dispo,$already);
$logins = array();
foreach ($todo as $login)
{
	$logins[] = "('$login')";
	if (count($logins) >= 20) // On fait 20 étudiants par INSERT
	{
		$bdd->exec('INSERT INTO etudiant(login) VALUES'.implode(',',$logins));
		$logins = array();
	}
}
if (count($logins) > 0) $bdd->exec('INSERT INTO etudiant(login) VALUES'.implode(',',$logins));
// END INSERTION de TOUS les login


// BEGIN INSERTION de X cours
$reqetudiant = $bdd->prepare('UPDATE etudiant SET semestre=?, nbuv=?, designation=?, email=? WHERE login=?');
$reqcours = $bdd->prepare('INSERT INTO cours(login,uv,type,groupe,jour,debut,fin,frequence,salle) VALUES(?,?,?,?,?,?,?,?,?)');
$finished = 1;
foreach ($bdd->query('SELECT login FROM etudiant WHERE login NOT IN (SELECT login FROM cours) ORDER BY login LIMIT 100') as $d)
{
	$finished = 0;
	$login = $d['login'];

	echo "=== $login ===<br/>";
	$c = file_get_contents('EDT/'.$login.'.edt');
	
	$ls = explode("\n",$c);
	for($i = 0; $i < sizeof($ls); $i++)
	{
		$x = explode('/',$ls[$i]);
		for ($y = 1; $y < sizeof($x); $y++)
		{
			array_push($ls,substr($x[0],0,19).$x[$y]);
		}
		$ls[$i] = $x[0];
	}
	$c = implode("\n",$ls);

	preg_match('#([A-Z]{2}[0-9]{2})(?: )+([0-9]{1})#isU',$c,$m);
	$semestre = $m[1];
	$nbuv = $m[2];
	//$designation = '';
	$designation = substr($ls[3],1,strpos($ls[3],' (')-1);
	$nom = '';
	$prenom = '';
	$email = '';
	preg_match('#\(([a-z-]+.[a-z-]+@etu.utc.fr)\)#isU',$ls[3],$m);
	if (isset($m[1])) $email = $m[1];
	echo "==$designation==$email==<br/>";
	
	
	preg_match_all('#([A-Z]{2}[A-Z0-9]{2})(?: )+(C|D|T)(?: )+([0-9])?(?: )+(LUNDI|MARDI|MERCREDI|JEUDI|VENDREDI|SAMEDI)(?: |\.)+([0-9]{1,2}:[0-9]{2})-([0-9]{1,2}:[0-9]{2}),(F[0-9]{1}),S=([A-Z]{2}[0-9]{3})?(?: )+#isU',$c,$m);
	//print_r($m);
	$n = sizeof($m[0]);
	for($i = 0; $i < $n; $i++)
	{
		
		$debut = explode(':',$m[5][$i]);
		$debut = $debut[0]*60+$debut[1];
		$fin = explode(':',$m[6][$i]);
		$fin = $fin[0]*60+$fin[1];
		$groupe = $m[3][$i];
		if ($groupe == '') $groupe = 0;
		
		$jour = str_replace(array('LUNDI','MARDI','MERCREDI','JEUDI','VENDREDI','SAMEDI'),array(0,1,2,3,4,5),$m[4][$i]);
		echo $m[1][$i].','.$m[2][$i].','.$groupe.','.$jour.','.$debut.','.$fin.','.$m[7][$i].','.$m[8][$i];
		echo '<br/>';

		$reqcours->execute(array($login,$m[1][$i],$m[2][$i],$groupe,$jour,$debut,$fin,$m[7][$i],$m[8][$i]));
	}
	$reqetudiant->execute(array($semestre,$nbuv,$designation,$email,$login));
	echo '<br/>';
}
if ($finished == 1) die();
array_map('unlink', glob("ajax/logins.json.*.cache"));
?>
<script type="text/javascript">function refresh() { window.location.href=window.location.href } setTimeout("refresh()",1000);</script>
