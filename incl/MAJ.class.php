<?php
class MAJ
{
	// Propose de supprimer la BDD pour la mettre à jour
	public static function delete()
	{
		$b = json_decode(file_get_contents('BDD.json'));
		if (!isset($_POST['pwd']) || $_POST['pwd'] != $b->pass)
		{
			require_once 'view/maj_insert.html';
			die();
		}
		require_once 'incl/BDD.class.php';
		$bdd = new BDD();
		$bdd->exec('DELETE FROM cours');
		$bdd->exec('DELETE FROM etudiant');
	}
	
	// Télécharge tous les fichiers *.edt du serveur du SME (si c'est pas déjà fait)
	// Retourne false si pas fini
	public static function scrap()
	{
		if (!isset($_GET['MODCASID']))
		{
			require_once 'view/maj_modcasid.html';
			die();
		}

		require_once 'incl/CURL.class.php';
		$curl = new CURL(strpos($_SERVER['HTTP_HOST'],'utc') !== false);
		$curl->setCookies('MODCASID='.$_GET['MODCASID']);


		/* On récupère la liste sur /sme/EDT/ */
		$liste = $curl->get('http://wwwetu.utc.fr/sme/EDT/');
		preg_match('#([a-zA-Z]{3})-20([0-9]{2})#isU', $liste, $m);
		if (!isset($m[2])) die('MODCASID errone ou expire..');
		$semestre = ($m[1] == 'Sep' ? 'A' : 'P') . $m[2];
		preg_match_all('#"([a-z]{8}.edt)"#isU', $liste, $m);
		$dispo = $m[1];
		/**/

		/* On récupère la liste des fichiers déjà téléchargés */
		$dossier = 'EDT_' . $semestre;
		if (!is_dir($dossier)) mkdir($dossier);
		$already = scandir($dossier);
		/**/

		/* On télécharge ceux qui ne sont pas déjà téléchargés */
		$todo = array_diff($dispo,$already);
		$i = 0;
		echo 'Nombre de fichiers a telecharger : ' . count($todo) . ' / ' . count($dispo) . ' ['.$semestre.']<br/>';
		foreach($todo as $f)
		{
			$c = $curl->get('http://wwwetu.utc.fr/sme/EDT/' . $f);
			echo $c;
			file_put_contents($dossier . '/' . $f, $c);
			$i++;
			if ($i >= 20) break; // On évite de surcharger le serveur
		}
		if (count($todo) != 0) return false; // On a pas fini
		return $dossier;
	}
	
	// Remplissage de la BDD en fonction des fichiers *.edt
	// Retourne false si pas fini
	public static function insert($edt)
	{
		require_once 'incl/BDD.class.php';
		$bdd = new BDD();
		self::insertLogins($bdd, $edt);
		return self::insertCours($bdd, $edt);
	}
	private static function insertLogins($bdd, $edt)
	{
		function edtbasename($f){ return basename($f,'.edt'); }
		$dispo = array_map('edtbasename',glob($edt . '/*.edt'));
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
			if (count($logins) >= 20) // On fait 20 étudiants par INSERT pour aller plus vite
			{
				$bdd->exec('INSERT INTO etudiant(login) VALUES'.implode(',',$logins));
				$logins = array();
			}
		}
		if (count($logins) > 0) $bdd->exec('INSERT INTO etudiant(login) VALUES'.implode(',',$logins));
		array_map('unlink', glob("ajax/logins.json.*.cache"));
	}
	private static function insertCours($bdd, $edt)
	{
		$reqetudiant = $bdd->prepare('UPDATE etudiant SET semestre=?, nbuv=?, designation=?, email=? WHERE login=?');
		$reqcours = $bdd->prepare('INSERT INTO cours(login,uv,type,groupe,jour,debut,fin,frequence,salle) VALUES(?,?,?,?,?,?,?,?,?)');
		$finished = true;
		foreach ($bdd->query('SELECT login FROM etudiant WHERE login NOT IN (SELECT login FROM cours) ORDER BY login LIMIT 100') as $d)
		{
			$finished = false;
			$login = $d['login'];
			$c = file_get_contents($edt . '/'.$login.'.edt');
					
			/* Quand il y a un "/" alors on fait une copie de la ligne */
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
			/**/

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
			$titre = $login;
			if ($designation != '') $titre .= " '$designation'";
			if ($email != '') $titre .= " : $email";
			echo "<strong>$titre</strong><br/>";
			
			
			preg_match_all('#' . self::regex() . '#isU',$c,$m);
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
		return $finished;
	
	}
	private static function regex()
	{
		$regex = '([A-Z]{2}[A-Z0-9]{2})'; // 1: UV (2 lettres, 2 chiffres)
		$regex .= '(?: )+';
		$regex .= '(C|D|T)'; // 2: Type (Cours,TD,TP)
		$regex .= '(?: )+';
		$regex .= '([0-9])?'; // 3: Groupe de TD ou TP (facultatif)
		$regex .= '(?: )+';
		$regex .= '(LUNDI|MARDI|MERCREDI|JEUDI|VENDREDI|SAMEDI)'; // 4: Jour
		$regex .= '(?: |\.)+';
		$regex .= '([0-9]{1,2}:[0-9]{2})'; // 5: Heure début
		$regex .= '-';
		$regex .= '([0-9]{1,2}:[0-9]{2})'; // 6: Heure fin
		$regex .= ',';
		$regex .= '(F[0-9]{1})'; // 7: Fréquence (1 fois toutes les N semaines)
		$regex .= ',S=';
		$regex .= '([A-Z]{2}[0-9]{3})?'; // 8: Salle (facultatif à cause de SPJE)
		$regex .= "(?: |\n)";
		return $regex;
	}
}
