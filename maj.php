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
	require_once 'incl/MAJ.class.php';
	$edt = MAJ::scrap();
	if ($edt)
	{
		$insert = MAJ::insert($edt); // Si on a fini de scrapper alors on insert dans la BDD
		if ($insert) MAJ::delete(); // Si on a fini d'inserer alors on propose de vider la BDD
	}
?>
<script type="text/javascript">function refresh() { window.location.href=window.location.href } setTimeout("refresh()",4000);</script>
<?php
}
else CAS::login();
?>