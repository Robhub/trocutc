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
	$('#troclogin').keyup(function(e){ if (e.which == 13) addLogin(); });
	$('#loadcours').click(addLogin);
	loadAllCours();
	loadResto();
	loadLogins();
	draw(CANVAS.getContext("2d"));
});
addLogin = function()
{
	var login = $('#troclogin').val();
	if (!$.grep(LOGINS,function(o){ return o.login == login})[0]) return;
	$('#troclogin').val('');
	$('#logins').append('<li>'+login+'</li>');
	loadAllCours();
}
loadAllCours = function()
{
	$('#edt .bloc').remove();
	$('#loading').show();
	var nlogins = $('#logins > li').length;
	$('#logins > li').each(function(i,elt)
	{
		$(elt).click(function(e){ $(this).remove(); loadAllCours(); });
		var login = $(elt).text();
		$.getJSON('ajax.php?a=cours.json', {login: login}, function(data){ loadCours(data,nlogins,i); });
	});
}
loadCours = function(data,nlogins,i)
{
	var total = 0;
	$.each(data,function(c,cours)
	{
		var groupe = cours.groupe > 0 ? groupe = ' (grp '+cours.groupe+')' : '';
		var content = nlogins == 1 ? cours.uv+groupe+'<br/>'+cours.salle : cours.uv+'&nbsp;'+cours.login;
		var bloc = $('<div class="bloc '+cours.type+'">'+content+'</div>');
		var marge = bloc.outerHeight()-bloc.innerHeight();
		var wratio = EDTW/60;
		total += (cours.fin - cours.debut);
		bloc.css({left:EDTX+(cours.debut-HEURE_DEB*60)*wratio,width:(cours.fin-cours.debut)*wratio,height:EDTH-marge});
		if (nlogins == 1) bloc.css({top:EDTY+cours.jour*EDTH});
		else bloc.css({top:EDTY+cours.jour*EDTH+i*(EDTH/nlogins),height:(EDTH+marge)/nlogins-marge});
		bloc.data('cours',cours);
		bloc.click(clickCours);
		$('#edt').append(bloc);
	});
	//$('#infos').append(total/60);// total horaire
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
		$('.alt').remove();
		alts = {};
		logins = [];
		$.each(data,function(c,cours)
		{
			if (cours.groupe == target.data('cours').groupe)
			{
				logins.push('<span class="login">'+cours.login+'</span>');
				return;
			}
			var grp = 'grp'+cours.groupe;
			if (alts[grp] == undefined)
			{
				var groupe = ''; if (cours.groupe > 0) groupe = ' (grp '+cours.groupe+')';
				var bloc = $('<div class="alt bloc '+cours.type+'">'+cours.uv+groupe+'<br/>'+cours.salle+'</div>');
				var wratio = EDTW/60;
				var gene = false;
				$('.bloc').each(function(){
					var moncours = $(this).data('cours');
					if (moncours.jour == cours.jour && parseInt(moncours.fin) > parseInt(cours.debut) && parseInt(moncours.debut) < parseInt(cours.fin))
					{
						
						$(this).css({height:EDTH/2});
						gene = true;
					}
				});
				var h = EDTH/(gene?2:1);
				var y = EDTY+cours.jour*EDTH+(gene?1:0)*(EDTH/2);
				bloc.css({left:EDTX+(cours.debut-HEURE_DEB*60)*wratio,top:y,width:(cours.fin-cours.debut)*wratio,height:h});
				
				
				//bloc.mouseover(overCoursAlt);
				//bloc.mouseout(outCoursAlt);
				bloc.click(clickCoursAlt);
				bloc.data('cours',cours);
				bloc.data('logins-cours',[]);
				bloc.data('logins-tdtps',[]);
				bloc.data('logins-libre',[]);
				$('#edt').append(bloc);
				alts[grp] = bloc;
			}
			if (cours.cours > 0) alts[grp].data('logins-cours').push(cours.login+'@etu.utc.fr');
			else if (cours.tdtps > 0) alts[grp].data('logins-tdtps').push(cours.login+'@etu.utc.fr');
			else alts[grp].data('logins-libre').push(cours.login+'@etu.utc.fr');
			
		});
		$('#res').html(logins.join(', <br/>'));
		$('.login').each(function()
		{
			var login = $(this).text();
			$(this).data('login',login);
			var designation = $.grep(LOGINS,function(o){ return o.login == login})[0].designation;
			var affichage = login+'@etu.utc.fr';
			if (designation) affichage = '"'+designation+'" <'+affichage+'>';
			$(this).text(affichage);
		});
		$('.login').click(function(e)
		{
			$('#logins').append('<li>'+$(this).data().login+'</li>');
			loadAllCours();
		});
		$('#loading').hide();
	});
}
function sel(elt)
{
	//$('.bloc').css({height:EDTH});
	var cours = elt.data('cours');
	var groupe = ''; if (cours.groupe > 0) groupe = ' (grp '+cours.groupe+')';
	$('#uv').text(cours.uv+groupe);
	$('.sel').removeClass('sel');
	elt.addClass('sel');
}
function clickCoursAlt(e)
{
	var target = $(e.target);
	$('#res').html('');
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
		$('#res').append('Il y a '+target.data('logins-libre').length+' personnes pour un éventuel échange<br/>');
		$('#res').append(envoi);
	}
	else $('#res').append('Désolé, aucune personne trouvée');
	
	// Avant on avait toutes les adresses, c’était bien.
	//$('#res').append('<p><div class="llibre">Étudiants avec un emplacement libre (échange possible)</div>'+target.data('logins-libre').join(', ')+'</p>');
	//$('#res').append('<p><div class="ltdtps">Étudiants avec un TD ou un TP (double-échange requis)</div>'+target.data('logins-tdtps').join(', ')+'</p>');
	//$('#res').append('<p><div class="lcours">Étudiants avec un Cours (échange quasi-impossible)</div>'+target.data('logins-cours').join(', ')+'</p>');
}
function reset()
{
	if ($('#logins > li').length == 1) $('.bloc').css({height:EDTH});//marche pas quand plusieurs logins chargés BUG TODO
	$('.sel').removeClass('sel');
	$('.alt').remove();
	$('#uv').text('');
	$('#res').html('');
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