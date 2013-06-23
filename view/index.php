<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Troc’UTC 2.01b (02/02/13)</title>
		<link rel="stylesheet" href="view/style.css" />
		<link rel="stylesheet" href="view/jquery-ui.css" />
		<script type="text/javascript" src="libs/jquery/1.8.1/jquery.min.js"></script>
		<script type="text/javascript" src="libs/jqueryui/1.8.23/jquery-ui.min.js"></script>
		<script type="text/javascript" src="trocutc.js"></script>
	</head>
	<body>
		<div style="position: absolute; bottom: 0; left:0; padding: 4px; background-color: #bbf">remarques/suggestion : <a href="mailto:trocutc@assos.utc.fr">trocutc@assos.utc.fr</a></div>
		<a href="https://github.com/robhub/trocutc"><img style="height: 120px; position: absolute; top: 0; right: 0; border: 0;" src="https://s3.amazonaws.com/github/ribbons/forkme_right_gray_6d6d6d.png" alt="Fork me on GitHub"></a>
		<h1>Troc’UTC 2.01b (version beta)</h1>
		<p id="bienvenue">
			Bienvenue, tu es bien connecté en tant que « <span id="login"><?php echo $_SESSION['user']; ?></span> » — Ce n’est pas toi ? <a href="logout.php">Déconnexion</a><br/>
			Veux-tu recevoir les demandes des autres étudiants par e-mail : <a href="optin.php">Oui</a> — <a href="optout.php">Non</a><br/>
			Pour le choix de publication des photos, c'est sur <a href="https://demeter.utc.fr/pls/portal30/TROMBINOSCOPE.PUBLI_PHOTO_AUTORISATION_DYN.show">Mon dossier : Ma photo - Autorisation de publication</a>
		</p>
		<div id="infos" class="cachable cache">
			<div class="cacher">Cliquez sur ce panneau pour le dérouler</div>
			<h2>C’est quoi ce site ?</h2>
			<p>Fonctionnalités :</p>
			<ul>
				<li>Trouver les logins des étudiants ayant vos TD/TP dans un autre groupe pour leur demander un échange.</li>
				<li>Afficher l’emploi du temps de plusieurs élèves en même temps pour faciliter les réunions de vos projets.</li>
			</ul>
			<p>Mode d’emploi :</p>
			<ul>
				<li>Entrer un login dans le champ de texte ci-dessous et appuyer sur entrée ou sur le bouton OK pour l’ajouter. (clic sur le rectangle gris pour l’enlever)</li>
				<li>Cliquer sur le rectangle d’un TD/TP pour faire apparaître en gris les TD/TP alternatifs (des autres groupes).</li>
				<li>Cliquer sur une des alternatives pour voir le nombre d’élèves pour un échange et éventuellement faire la demande.</li>
			</ul>
		</div>
		<table>
			<tr>
				<td>
					<div id="addlogin">
						<label for="troclogin">Login</label>
						<input size="12" id="troclogin" type="text" />
						<button id="loadcours">OK</button>
						<ol id="logins">
						<!--<li><?php echo $_SESSION['user']; ?></li>-->
						</ol>
					</div>
					<div id="edt">
						<img id="loading" src="view/loading51.gif" alt="loading" />
					</div>
					<canvas width="780" height="480" id="cedt"></canvas>
					<div id="resto" class="cachable cache">
						<div class="cacher">Cliquez sur ce panneau pour le dérouler</div>
						Restaurant Universitaire « Port à Bateaux »
						<table id="menu"></table>
					</div>
				</td>
				<td>
					<h2 id="uv"></h2>
					<div id="res"></div>
					<div id="pic"></div>
					<div id="etu"></div>
				</td>
			</tr>
		</table>
	</body>
</html>