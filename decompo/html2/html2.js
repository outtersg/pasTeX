/*
 * Copyright (c) 2013 Guillaume Outters
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.  IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

var enxlink = 'http://www.w3.org/1999/xlink';

var LignesTemps = {};

LignesTemps.blocs = [];

LignesTemps.preparer = function()
{
	var i, j;
	for(i in this.blocs)
		for(j = this.blocs[i].length; --j >= 0;)
			this.blocs[i][j] = document.getElementById(this.blocs[i][j]);

	sur(window, 'resize', function(ev) { LignesTemps.calculer(); });
	/* À FAIRE: aussi le changement de taille de police… tout ce qui change la hauteur de notre conteneur, en fait. */
	
	LignesTemps.calculer();
};

LignesTemps.jointureSimple = function(pTexte, pBloc)
{
	var d = '';
	d += ' M '+pTexte.x+','+pTexte.y1+' C '+(0.5 * pTexte.x + 0.5 * pBloc.x)+','+pTexte.y1+' '+(0.5 * pTexte.x + 0.5 * pBloc.x)+','+pBloc.y1+' '+pBloc.x+' '+pBloc.y1;
	d += ' L '+pBloc.x+','+pBloc.y1+' L '+pBloc.x+','+pBloc.y0+' C '+(0.5 * pBloc.x + 0.5 * pTexte.x)+','+pBloc.y0+' '+(0.5 * pBloc.x + 0.5 * pTexte.x)+','+pTexte.y0+' '+pTexte.x+' '+pTexte.y0+' z';
	return d;
};

LignesTemps.jointurePotDEchap = function(pTexte, pBloc)
{
	var enFace = true;
	var bout = 1; // 0: accolade; 1: pique: 2: puce.
	var boutSegment = 1; // 0: accolade; 1: embouchure.
	var embouchureB = 10;
	var courbeB = 30;
	var courbeT = 30;
	var embouchureT = 30;
	var demieLargeurTube = 1;
	
	if(pTexte.x - pBloc.x < embouchureB + courbeB + courbeT + embouchureT)
	{
		var proportion = (pTexte.x - pBloc.x) / (embouchureB + courbeB + courbeT + embouchureT);
		embouchureB *= proportion;
		courbeB *= proportion;
		courbeT *= proportion;
		embouchureT *= proportion;
	}
	
	var d = '';
	// Le tube central est composé de deux lignes parallèles à a * x + y = b (chacune avec un offset de n pixels par rapport à cette ligne centrale).
	var yPtB = pBloc.y1 - pBloc.y0 < 20 || ! enFace ? pBloc.ym : (pTexte.ym < pBloc.y0 + 10 ? pBloc.y0 + 10 : (pTexte.ym > pBloc.y1 - 10 ? pBloc.y1 - 10 : pTexte.ym)); // On essaie de placer le point d'arrivée au bloc le plus en face possible de celui de départ du texte.
	var yBbas = Math.min(yPtB + 20, Math.max(pBloc.y1 - 3, yPtB));
	var yBhaut = Math.max(yPtB - 20, Math.min(pBloc.y0 + 3, yPtB));
	var ptB = { x: pBloc.x + embouchureB, y: yPtB };
	var ptT = { x: pTexte.x - embouchureT, y: pTexte.ym };
	var viseeB = { x: pBloc.x + embouchureB + courbeB, y: yPtB };
	var viseeT = { x: pTexte.x - embouchureB - courbeB, y: pTexte.ym };
	var ptM = { x: .5 * (viseeB.x + viseeT.x), y: .5 * (viseeB.y + viseeT.y) };
	var lt = { a: (viseeB.y - viseeT.y) / (viseeT.x - viseeB.x) };
	lt.b = lt.a * viseeB.x + viseeB.y;
	// On cherche le point situé à n pixels à gauche du centre du tube (en allant du texte vers le bloc).
	var racinea2plus1 = Math.sqrt(lt.a * lt.a + 1);
	var dx = demieLargeurTube * lt.a / racinea2plus1;
	var dy = demieLargeurTube / racinea2plus1;
	if(viseeT.x < viseeB.x && lt.a) // Courbe renversée, façon serpent. Mais si lt.a == 0 (ligne complètement horizontale), le calcul est différent, car pc[BT][haut|bas] seront calculés autrement.
	{
		dx = -dx;
		dy = -dy;
	}
	var ptMbas = { x: ptM.x + dx, y: ptM.y + dy }; // p: point; t: tube; M: milieu; bas: bah…
	var ptMhaut = { x: ptM.x - dx, y: ptM.y - dy };
	// On cherche le 'b' des droites du haut et du bas du tube (le 'a' est identique, puisque les droites sont parallèles).
	var lthaut = { b: lt.a * ptMhaut.x + ptMhaut.y };
	var ltbas = { b: lt.a * ptMbas.x + ptMbas.y };
	// Et l'intersection de ces mêmes droites avec le bout de tuyau horizontal partant qui du texte, qui du carreau.
	var pcTbas = { x: lt.a ? (ltbas.b - (viseeT.y + demieLargeurTube)) / lt.a : ptT.x, y: viseeT.y + demieLargeurTube }; // c: coude.
	var pcThaut = { x: lt.a ? (lthaut.b - (viseeT.y - demieLargeurTube)) / lt.a : ptT.x, y: viseeT.y - demieLargeurTube };
	var pcBbas = { x: lt.a ? (ltbas.b - (viseeB.y + demieLargeurTube)) / lt.a : ptB.x, y: viseeB.y + demieLargeurTube }; // c: coude.
	var pcBhaut = { x: lt.a ? (lthaut.b - (viseeB.y - demieLargeurTube)) / lt.a : ptB.x, y: viseeB.y - demieLargeurTube };
	// Nos points de contrôle vont se trouver à l'intersection 
	var xCourburePointe = pTexte.x - embouchureT / 2 - demieLargeurTube;
	switch(bout)
	{
		case 0:
	d += ' M '+pTexte.x+','+pTexte.y1+' C '+(pTexte.x - embouchureT)+','+pTexte.y1+' '+pTexte.x+','+pcTbas.y+' '+(pTexte.x - embouchureT)+','+pcTbas.y; // Accolade texte bas.
			break;
		case 1:
			d += ' M '+(pTexte.x - embouchureT / 2)+','+ptT.y+' C '+xCourburePointe+','+pcTbas.y+' '+xCourburePointe+','+pcTbas.y+' '+ptT.x+','+pcTbas.y;
			break;
	}
	d += ' C '+pcTbas.x+','+pcTbas.y+' '+pcTbas.x+','+pcTbas.y+' '+ptMbas.x+','+ptMbas.y; // Coude, et remontée jusqu'au pivot.
	d += ' C '+pcBbas.x+','+pcBbas.y+' '+pcBbas.x+','+pcBbas.y+' '+(pBloc.x + embouchureB)+','+pcBbas.y;
	d += ' C '+pBloc.x+','+pcBbas.y+' '+(boutSegment == 1 ? pBloc.x+' '+pcBbas.y : (pBloc.x + embouchureB)+','+yBbas)+' '+pBloc.x+' '+yBbas;
	d += ' L '+pBloc.x+','+yBhaut+' C '+(boutSegment == 1 ? pBloc.x+' '+pcBhaut.y : (pBloc.x + embouchureB)+','+yBhaut)+' '+pBloc.x+','+pcBhaut.y+' '+(pBloc.x + embouchureB)+','+pcBhaut.y; // Longement bloc + accolade bloc haute.
	d += ' C '+pcBhaut.x+','+pcBhaut.y+' '+pcBhaut.x+','+pcBhaut.y+' '+ptMhaut.x+','+ptMhaut.y;
	d += ' C '+pcThaut.x+','+pcThaut.y+' '+pcThaut.x+','+pcThaut.y+' '+(pTexte.x - embouchureT)+','+pcThaut.y;
	switch(bout)
	{
		case 0:
	d += ' C '+pTexte.x+','+pcThaut.y+' '+(pTexte.x - embouchureT)+','+pTexte.y0+' '+pTexte.x+','+pTexte.y0;
			break;
		case 1:
			d += ' C '+xCourburePointe+','+pcThaut.y+' '+xCourburePointe+','+pcThaut.y+' '+(pTexte.x - embouchureT / 2)+','+ptT.y;
			break;
	}
	d += ' z';
	return d;
};

LignesTemps.calculer = function()
{
	var svg = document.getElementById('jonctionlignestemps');
	var ensvg = "http://www.w3.org/2000/svg";
	
	var i, j, d, pBloc, pTexte;
	var courbe;
	
	var p = function(elem, droite) { var y0 = elem.offsetTop; var y1 = y0 + elem.offsetHeight; return { x: elem.offsetLeft + (droite ? elem.offsetWidth : 0), y0: y0, y1: y1, ym: (y0 + y1) / 2.0 }; }

	while (svg.lastChild)
		svg.removeChild(svg.lastChild);
	
	for(i in this.blocs)
	{
		courbe = document.createElementNS(ensvg, 'path');
		courbe.setAttributeNS(null, 'class', 'jonction');
		courbe.setAttributeNS(null, 'id', 'jonction'+i);
		d = '';
		pTexte = p(this.blocs[i][0]);
		for(j = this.blocs[i].length; --j >= 1;) // Le bloc 0 est le texte, à lier à tous les autres qui sont la représentation graphique.
		{
			pBloc = p(this.blocs[i][j], true);
			d += this.jointurePotDEchap(pTexte, pBloc);
		}
		courbe.setAttributeNS(null, 'd', d);
		svg.appendChild(courbe);
	}
};

/*--- Coloriage dynamique des liens ---*/
/* Une solution propre consisterait à embarquer chaque lien dans son propre SVG, inséré comme fils du <div> projet (tout comme l'est déjà le <div> barre de temps); on pourrait alors utiliser une simple règle CSS ".projet:hover .jonction". Néanmoins je ne suis pas certain qu'il soit très judicieux de créer une multitude de SVG, un par lien, qui se recouvrent les uns les autres (car les liens courent en se croisant). Du coup une solution Javascript (pour modifier la classe de la jonction) est un pis-aller acceptable. */

LignesTemps.suivreSouris = function(e)
{
	// Une gymnastique pour essayer de convertir notre mouseout en mouseleave.
	e || (e = window.event);
	var els = [ e.target ? e.target : e.srcElement, e.relatedTarget ? e.relatedTarget : e.toElement ];
	var i;
	for(i = 2; --i >= 0;)
		while(els[i] && els[i] !== els[i].parentNode && els[i].getAttributeNS && (' '+els[i].getAttributeNS(null, 'class')+' ').indexOf(' projet ') < 0)
			els[i] = els[i].parentNode;
	if(els[0] !== els[1] && els[0].id)
	{
		var jonction = document.getElementById('jonction'+els[0].id);
		var jonctionAuDessus = document.getElementById('jonctionAuDessus');
		if(e.type == 'mouseover')
		{
			jonction.setAttributeNS(null, 'class', 'jonction jonctionHover');
			// http://stackoverflow.com/a/26277417/1346819
			window.setTimeout(function() { jonctionAuDessus.setAttributeNS(enxlink, 'href', '#jonction'+els[0].id); }, 0);
		}
		else
		{
			jonction.setAttributeNS(null, 'class', 'jonction');
			jonctionAuDessus.setAttributeNS(enxlink, 'href', '');
		}
	}
	return false;
};

/*- Parcours thématique ------------------------------------------------------*/

var Parcours =
{
	couleurs:
	[
		'255, 0, 0',
		'0, 255, 0',
		'255, 127, 0',
		'223, 223, 0',
		'127, 0, 255',
		'255, 127, 127',
		'63, 127, 255'
	],
	preparer: function()
	{
		Parcours.initialiser();
		sur(window, 'resize', function(ev) { Parcours.calculer(); });
		Parcours.calculer();
	},
	initialiser: function()
	{
		// marque = marqué sans son accent. Nous reste à créer un marqueur par marqué.
		var marques = document.getElementsByClassName('marque');
		var marque;
		var classes, classeMarque;
		var i, j;
		Parcours.marqueursParMarque = {};
		var exprMarque = /^marque-/;
		for(i = 0; i < marques.length; ++i)
		{
			marque = marques[i];
			classes = marque.getAttributeNS(null, 'class').split(/ +/);
			for(j = classes.length; --j >= 0;)
				if(classes[j].match(exprMarque))
				{
					classeMarque = classes[j]; // A priori on n'a qu'une seule classe CSS portant la marque.
					if(!Parcours.marqueursParMarque[classeMarque.replace(exprMarque, '')])
						Parcours.marqueursParMarque[classeMarque.replace(exprMarque, '')] = [];
				}
			var centreMarqueur = document.createElement('span');
			centreMarqueur.setAttributeNS(null, 'class', 'centre-marqueur');
			var marqueur = document.createElement('span');
			marqueur.setAttributeNS(null, 'class', 'marqueur');
			centreMarqueur.appendChild(marqueur);
			marque.appendChild(centreMarqueur);
			Parcours.marqueursParMarque[classeMarque.replace(exprMarque, '')].push(marqueur);
		}
		var cssMarques = '';
		j = 0;
		for(i in Parcours.marqueursParMarque)
		{
			cssMarques += '.marque-'+i+' .marqueur { background: rgba('+Parcours.couleurs[j % Parcours.couleurs.length]+', 0.35); }\n';
			++j;
		}
		var style = document.createElement('style');
		style.type = 'text/css';
		style.innerHTML = cssMarques;
		document.getElementsByTagName('head')[0].appendChild(style)
	},
	calculer: function()
	{
		/*- Composition des rails entre les marques. -*/
		
		/* Nos marqueurs ont déjà été regroupés par fil (même marque). Il nous faut maintenant, à l'intérieur d'un même fil, les répartir par bloc (expérience). */
		
		var marqueurs = {}; // Parcours.marqueursParMarque ne possède que les fils, marqueurs sous-classera par bloc.
		var el, elOffset;
		var x, y, yBloc;
		for(marque in Parcours.marqueursParMarque)
		{
			marqueurs[marque] = {};
			for(j = Parcours.marqueursParMarque[marque].length; --j >= 0;)
			{
				marqueur = Parcours.marqueursParMarque[marque][j];
				x = marqueur.offsetWidth / 2.0;
				y = marqueur.offsetHeight / 2.0;
				// On remonte jusqu'au bloc conteneur.
				for(elOffset = el = marqueur; el && el !== el.parentNode && el.getAttributeNS; el = el.parentNode)
				{
					if(el.getAttributeNS(null, 'class') == 'projet')
					{
						yBloc = el.offsetTop;
						if(!marqueurs[marque][yBloc])
							marqueurs[marque][yBloc] = [];
						marqueur.x = x;
						marqueur.y = y;
						marqueurs[marque][yBloc].push(marqueur);
						break;
					}
					if(el === elOffset)
					{
						x += el.offsetLeft;
						y += el.offsetTop;
						elOffset = el.offsetParent;
					}
				}
			}
			// En théorie ici nos tableaux sont déjà triés, car les marques sont embarquées linéairement, donc celles d'un même bloc ont dû être agrégées en même temps.
		}
		
		/* Pour chaque marque, on va tracer le chemin passant par un marqueur de chaque bloc. */
		/* Si un bloc comporte précédent comportait plusieurs marqueurs de la même marque, on rattachera le marqueur du bloc actuel à celui du bloc précédent depuis lequel le coût de déplacement horizontal sera minimisé. Ex.: Soient trois blocs A, B, C comportant pour une marque des marqueurs en a0.x = 268 (A), b0.x = 122 et b1.x = 512 (B), c0.x = 268 et c1.x = 514 (C). Première étape: A, un seul bloc (et pas de bloc précédent) => choix d'a0 avec un coût 0 (privilège du premier bloc). Seconde étape: B: deux chemins possibles, a0 -> b0 (coût: 146) ou a0 -> b1 (coût: 244); on garde les deux (toujours une entrée par marqueur du bloc de travail). Étape 3, bloc C: pour arriver en c0, soit a0 -> b0 -> c0 (coût: 146 + 146), soit a0 -> b1 -> c0 (coût: 244 + 146), donc on garde le premier; pour arriver en c1, soit a0 -> b0 -> c1 (146 + 392), soit a0 -> b1 -> c1 (244 + 2), on garde le second. Fin de parcours, on a deux chemins possibles pour le bloc d'arrivée C, de coûts 538 et 246: le gagnant sera a0 -> b1 -> c1 (qui demande un plus gros effort de déplacement au passage A -> B, mais regagne l'avantage grâce au presqu'alignement de b1 et c1). */
		
		var chemins, cheminsMarque, cheminsTemp;
		var chemin;
		var min;
		chemins = {};
		for(marque in marqueurs)
		{
			cheminsMarque = [];
			for(y in marqueurs[marque])
			{
				cheminsTemp = [];
				for(j = marqueurs[marque][y].length; --j >= 0;)
				{
					marqueur = marqueurs[marque][y][j];
					// Pour ce marqueur, on essaie de trouver le chemin (précédent) nous proposant l'arrivée la plus courte.
					if(!(i = cheminsMarque.length)) // Premier de la lignée.
						chemin = { cout: 0, etapes: [ ], dernier: marqueur };
					else
					{
						min = -1;
						for(i = cheminsMarque.length; --i >= 0;)
							if((x = cheminsMarque[i].cout + Math.abs(cheminsMarque[i].dernier.x - marqueur.x)) < min || min == -1)
							{
								chemin = { cout: x, etapes: cheminsMarque[i].etapes, dernier: marqueur };
								min = x;
							}
					}
					chemin.etapes = chemin.etapes.slice(); // slice() pour être sûrs de repartir d'une copie des étapes du précédent (car un de nos frères peut vouloir tracer son chemin par le même père que nous, donc il faut que l'on préserve le père tel quel jusqu'à ce que tous nos frères aient été parcourus).
					chemin.etapes.push(marqueur);
					cheminsTemp[j] = chemin;
				}
				cheminsMarque = cheminsTemp;
			}
			for(min = cheminsMarque[0].cout, j = cheminsMarque.length; --j >= 0;)
				if(cheminsMarque[j].cout <= min)
					chemins[marque] = cheminsMarque[j];
		}
		
		/*- Tracé des chemins -*/
		
		j = 0;
		var k = 0;
		var svg = document.getElementById('chemins');
		var courbe;
		var ensvg = "http://www.w3.org/2000/svg";
		
		while (svg.lastChild)
			svg.removeChild(svg.lastChild);
		
		for(marque in chemins)
		{
			chemin = chemins[marque].etapes;
			
			/* La courbe passe par chacune de nos étapes. */
			
			PiloteBezier.courber(svg, chemins[marque].etapes, Parcours.couleurs[j % Parcours.couleurs.length]+',0.35');
			
			/* De plus à chaque étape il nous faut raccrocher au marqueur élu pour être sur le tracé du chemin, ceux qui n'ont pas eu cette chance. */
			
			i = 0; // marqueurs et étapes se suivent, simplement le premier est un objet dont les membres représentent l'ordonnée du bloc de texte, le second un tableau indicé classiquement. On va donc parcourir en parallèle les deux, l'un en y, l'autre en i.
			for(y in marqueurs[marque])
			{
				for(k = marqueurs[marque][y].length; --k >= 0;)
					if(marqueurs[marque][y][k] !== chemin[i]) // Un non-élu.
					{
						courbe = document.createElementNS(ensvg, 'line');
						courbe.setAttributeNS(null, 'fill', 'none');
						courbe.setAttributeNS(null, 'stroke', 'rgba('+Parcours.couleurs[j % Parcours.couleurs.length]+',0.35)');
						courbe.setAttributeNS(null, 'stroke-width', 2);
						courbe.setAttributeNS(null, 'x1', chemin[i].x);
						courbe.setAttributeNS(null, 'y1', chemin[i].y);
						courbe.setAttributeNS(null, 'x2', marqueurs[marque][y][k].x);
						courbe.setAttributeNS(null, 'y2', marqueurs[marque][y][k].y);
						svg.appendChild(courbe);
						marqueurs[marque][y][k].setAttributeNS(null, 'class', 'marqueur marqueurSecondaire');
					}
				chemin[i].setAttributeNS(null, 'class', 'marqueur');
				++i;
			}
			
			/* Et l'on avance dans les couleurs. */
			
			++j;
		}
	}
};

/*- Branchement --------------------------------------------------------------*/

var sur = function(objet, evenement, abonne)
{
	if(objet.addEventListener)
		objet.addEventListener(evenement, abonne, false);
	else if(objet.attachEvent)
		objet.attachEvent('on'+evenement, abonne);
};
sur(window, 'load', function()
{
	sur(document.getElementsByClassName('projets')[0], 'mouseover', LignesTemps.suivreSouris);
	sur(document.getElementsByClassName('projets')[0], 'mouseout', LignesTemps.suivreSouris);
});
