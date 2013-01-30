// Développé par Robin BERGÈRE (robin dot bergere at gmail dot com)

if (typeof console == "undefined" || typeof console.log == "undefined") var console = { log: function() {} }; 

var CANVAS = document.getElementById('cedt');
var HEURE_DEB = 8;
var EDTX = 12;
var EDTY = 12;
var EDTW = 0;
var EDTH = 0;
var JOURS = 6;
var HEURES = 12;
var LOGINS = [];

$(document).ready(function()
{
	CANVAS = document.getElementById('cedt');
	EDTW = (CANVAS.width-EDTX)/HEURES;
	EDTH = (CANVAS.height-EDTY)/JOURS;
	console.log(EDTW+' '+EDTW/4+' '+EDTH);
	$('.cachable').click(function(e){ $(this).toggleClass('cache', 500); });
	$('body').keydown(function(e){ if (e.which == 27) reset(); });
	$('#cedt').click(reset);
	$('#troclogin').keyup(addInputLogin);
	$('#loadcours').click(addInputLogin);
	addLogin($('#login').text(), false);
	loadResto();
	loadLogins();
	draw(CANVAS.getContext("2d"));
});
addInputLogin = function(e)
{
	if (e.which == 13) addLogin($('#troclogin').val(), true);
}
addLogin = function(login, checkIfExists)
{
	if (checkIfExists && !$.grep(LOGINS,function(o){ return o.login == login})[0]) return;
	$('#troclogin').val('');
	var button = $('<li>'+getPic(login)+login+'</li>');
	button.click(function(e){ $(this).remove(); loadAllCours(); });
	$('#logins').append(button);
	loadAllCours();
}
loadAllCours = function()
{
	$('#edt .bloc').remove();
	$('#loading').show();
	$('#logins > li').each(function(i,elt)
	{
		var login = $(elt).text();
		$.getJSON('ajax.php?a=cours.json', {login: login}, function(data){ loadCours(data, i); });
	});
}

// Regarde la liste des cours pour voir si le cours donné serait par dessus un autre cours, auquel cas il faut réduire la hauteur des cours
reduceBloc = function(bloc, samelogin)
{
	var cours = bloc.data('cours');
	if (bloc.data('changed')) return;
	$('.bloc').each(function()
	{
		if ($(this).data('changed')) return;
		var moncours = $(this).data('cours');
		if (moncours == cours) return;
		if (samelogin && moncours.login != cours.login) return;
		if (moncours.jour != cours.jour) return;
		if (parseInt(moncours.fin) > parseInt(cours.debut) && parseInt(moncours.debut) < parseInt(cours.fin))
		{
			//console.log("- " + moncours.login + "@" + moncours.uv + " : " + parseInt(moncours.debut) + " ; " + parseInt(moncours.fin));
			bloc.data('rows', bloc.data('rows')*2);
			bloc.data('changed', true);
			$(this).data('rows', $(this).data('rows')*2);
			$(this).data('row', 1);
			$(this).data('changed', true);
		}
	});
}
updateBlocs = function()
{
	var wratio = EDTW/60; // Minutes -> Pixels
	var nlogins = $('#logins > li').length;
	$('.bloc').each(function() // Initialisations
	{
		$(this).data('changed', false);
		$(this).data('row', 0);
		$(this).data('rows', $(this).data('cours').frequence.substr(1,1));
	});
	$('.bloc').each(function()
	{
		var bloc = $(this);
		var cours = bloc.data('cours');
		var nlogin = bloc.data('nlogin');
		
		
		// Contenu
		var groupe = cours.groupe > 0 ? groupe = ' (grp '+cours.groupe+')' : '';
		var display = cours.uv+groupe+'<br/>'+cours.salle;
		if (nlogins > 1) display = cours.uv+'&nbsp;'+cours.login; // Multi-emploi-du-temps : affichage restreint
		bloc.html(display);

		// Bordures
		var borderW = (bloc.outerWidth() - bloc.innerWidth())/2;
		var borderH = (bloc.outerHeight() - bloc.innerHeight())/2;
		
		// Positionnement axe X
		bloc.css('left', EDTX+(cours.debut-HEURE_DEB*60)*wratio);
		bloc.css('width', (cours.fin-cours.debut)*wratio - borderW);
		
		// Positionnement axe Y
		var height = EDTH/parseInt(); 
		var top = EDTY+cours.jour*EDTH;
		reduceBloc(bloc, nlogins > 1);
		if (nlogins > 1) bloc.css('top', top + EDTH*((nlogin*bloc.data('rows')+bloc.data('row'))/(bloc.data('rows')*nlogins)));
		else bloc.css('top', top + EDTH*(bloc.data('row')/bloc.data('rows')));
		bloc.css('height', EDTH/bloc.data('rows')/nlogins - borderH);
	});
}

loadCours = function(data, nlogin)
{
	$.each(data,function(c,cours)
	{
		var bloc = $('<div/>');
		bloc.addClass('bloc');
		bloc.addClass(cours.type); // Pour colorier en fonction du type
		bloc.data('nlogin', nlogin);
		bloc.data('cours', cours);
		bloc.click(clickCours);
		$('#edt').append(bloc);
	});
	updateBlocs();
	$('#loading').hide();
}
clickCours = function(e)
{
	
	var target = $(e.target);//{uv:target.data('cours').uv, type:target.data('cours').type}
	sel(target);
	if ($('#logins > li').length > 1) return; // Pas d'alternatives en mode groupe
	
	$('#loading').show();
	$.getJSON('ajax.php?a=alts.json', target.data('cours'), function(data)
	{
		$('.alt').remove(); // On enlève les alternatives précédentes
		alts = {};
		logins = [];
		$.each(data,function(c,cours)
		{
			if (cours.groupe == target.data('cours').groupe)
			{
				logins.push('<div class="login">'+cours.login+'</div>');
				return;
			}
			var grp = 'grp'+cours.groupe;
			if (alts[grp] == undefined)
			{
				var groupe = ''; if (cours.groupe > 0) groupe = ' (grp '+cours.groupe+')';
				var bloc = $('<div class="alt bloc '+cours.type+'">'+cours.uv+groupe+'<br/>'+cours.salle+'</div>');
				bloc.click(clickCoursAlt);
				bloc.data('cours',cours);
				bloc.data('logins-cours',[]);
				bloc.data('logins-tdtps',[]);
				bloc.data('logins-libre',[]);
				$('#edt').append(bloc);
				alts[grp] = bloc;
			}
			if (cours.canmail > 0 && cours.cours > 0) alts[grp].data('logins-cours').push(cours.login+'@etu.utc.fr');
			else if (cours.canmail > 0 && cours.tdtps > 0) alts[grp].data('logins-tdtps').push(cours.login+'@etu.utc.fr');
			else if (cours.canmail > 0) alts[grp].data('logins-libre').push(cours.login+'@etu.utc.fr');
			
		});
		
		$('#res').html(logins.join(''));//, <br/>
		
		$('.login').each(function()
		{
			var login = $(this).text();
			$(this).data('login',login);
			var designation = $.grep(LOGINS,function(o){ return o.login == login})[0].designation;
			var affichage = login+'@etu.utc.fr';
			if (designation) affichage = '"'+designation+'" &lt;'+affichage+'&gt;';
			$(this).html(getPic(login)+affichage);
		});
		$('.login').mouseover(function(){setPic($(this).data('login'));});
		//$('.login').mouseout(setFirstPic);
		setFirstPic();
		
		$('.login').click(function(e)
		{
			addLogin($(this).data().login);
		});
		updateBlocs();
		$('#loading').hide();
	});
}
setFirstPic = function()
{
	setPic($('.login:first').data('login'));
}
setPic = function(login)
{
	$('#pic').html(getPic(login));
}
getPic = function(login)
{
	return '<img src="pic.php?login='+login+'" alt="?" />';
}
biggen = function() // Agrandissement des images au survol de la souris
{
	var elt = $(this);
	var clone = elt.clone();
	$('body').append(clone);
	clone.css('height','');
	if (elt.width() == clone.width() && elt.height() == clone.height()) clone.remove();
	else
	{
		clone.css('position','absolute');
		clone.css('left',elt.offset().left+elt.width()/2-clone.width()/2);
		clone.css('top',elt.offset().top+elt.height()/2-clone.height()/2);
		clone.mouseout(function(){ clone.remove(); });
	}
}

function sel(elt)
{
	var cours = elt.data('cours');
	var groupe = ''; if (cours.groupe > 0) groupe = ' (grp '+cours.groupe+')';
	$('#uv').text(cours.uv+groupe);
	$('.sel').removeClass('sel');
	elt.addClass('sel');
}
function clickCoursAlt(e)
{
	var target = $(e.target);
	$('#pic').html('');
	$('#res').html('');
	$('#exchange').html('');
	sel(target);
	
	
	var params = '';
	var envoi = $('<button>Demander l’échange par E-Mail</button><pre></pre>').click(function()
	{
		var show = $(this).next();
		//TODO : demander une confirmation ^_^
		$.get('ajax.php?a=chooseAlt', target.data('cours'), function(data) { show.html(data); });
	});
	if (target.data('logins-libre').length >= 1)
	{
		$('#exchange').append('Il y a '+target.data('logins-libre').length+' personnes pour un éventuel échange<br/>');
		if ($.trim($('#logins').text()) == $.trim($('#login').text())) $('#exchange').append(envoi); // Pas de bouton si c'est pas nous
	}
	else $('#res').append('Désolé, aucune personne trouvée');
	
	// Avant on avait toutes les adresses, c’était bien.
	//$('#res').append('<p><div class="llibre">Étudiants avec un emplacement libre (échange possible)</div>'+target.data('logins-libre').join(', ')+'</p>');
	//$('#res').append('<p><div class="ltdtps">Étudiants avec un TD ou un TP (double-échange requis)</div>'+target.data('logins-tdtps').join(', ')+'</p>');
	//$('#res').append('<p><div class="lcours">Étudiants avec un Cours (échange quasi-impossible)</div>'+target.data('logins-cours').join(', ')+'</p>');
}
function reset()
{
	$('.sel').removeClass('sel');
	$('.alt').remove();
	$('#uv').text('');
	$('#pic').html('');
	$('#res').html('');
	$('#exchange').html('');
	updateBlocs();
}

function loadLogins()
{
	$.get('ajax.php?a=logins.json', function(data)
	{
		LOGINS = [];
		$.each(data,function(e,etud)
		{
			LOGINS.push(etud);
			etud.label = '['+etud.semestre+']'+' '+etud.login;
			if (etud.designation) etud.label += ' ('+etud.designation+')';
			etud.value = etud.login;
		});
		$("#troclogin").autocomplete({
			source: function(request, response)
			{
				var results = $.ui.autocomplete.filter(data, request.term);
				if (results.length < 12) response(results);
				else
				{
					results = results.slice(0,12);
					results.push({label:'...',value:''});
					response(results);
				}
			}
		});
	});
}


function loadResto()
{
	var jours = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
	var mois = ['Jan.','Fév.','Mars','Avr.','Mai','Juin','Juil.','Août','Sept.','Oct.','Nov.','Déc.'];
	$.get('ajax.php?a=resto.xml', function(data)
	{
		$('#r11 > menu',$(data)).each(function()
		{
			var menu = $(this);
			var date = new Date(menu.attr('date'));
			var dateprint = jours[date.getDay()]+' '+date.getDate()+' '+mois[date.getMonth()];
			$('#menu').append('<tr><th colspan="2">'+dateprint+'</th></tr><tr><td>'+menu.text()+'</td><td></td></tr>');
		});
		
		$('h4').remove();
		$('h2 + ul').each(function()
		{
			var prev = $(this).prev();
			if (prev.text() == 'soir') $(this).parent().next().append(prev.andSelf());
		});
	});
}
function draw(ctx)
{
	// RestoU
	ctx.beginPath();
	ctx.fillStyle = "#FFBFBF";
	ctx.rect(EDTX+(11.5-HEURE_DEB)*EDTW+1,EDTY,EDTW*2,EDTH*5);
	ctx.rect(EDTX+(18.5-HEURE_DEB)*EDTW+1,EDTY,EDTW*1.5,EDTH*5);
	ctx.rect(EDTX+(12-HEURE_DEB)*EDTW+1,EDTY+EDTH*5,EDTW,EDTH);
	ctx.fill();
	
	// Quarts d'heures
	ctx.beginPath();
	ctx.fillStyle = "#000000";
	ctx.strokeStyle = "#808080";
	for (var x = EDTX; x <= CANVAS.width; x += EDTW/4)
	{
		ctx.moveTo(Math.round(x)+0.5,EDTY);
		ctx.lineTo(Math.round(x)+0.5,CANVAS.height);
	}
	ctx.stroke();
	
	// Heures
	ctx.beginPath();
	ctx.strokeStyle = "#000000";
	var h = HEURE_DEB;
	for (var x = EDTX; x <= CANVAS.width; x += EDTW)
	{
		ctx.fillText(h+'h',x-6,8);
		h++;
		ctx.moveTo(Math.round(x)+0.5,EDTY);
		ctx.lineTo(Math.round(x)+0.5,CANVAS.height);
	}
	
	// Jours
	for (var y = EDTY; y <= CANVAS.height; y += EDTH)
	{
		ctx.moveTo(EDTX,Math.round(y)+0.5);
		ctx.lineTo(CANVAS.width,Math.round(y)+0.5);
	}
	ctx.stroke();
}