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

var LignesTemps = {};

LignesTemps.blocs = [];

LignesTemps.preparer = function()
{
	var i, j;
	for(i in this.blocs)
		for(j = this.blocs[i].length; --j >= 0;)
			this.blocs[i][j] = document.getElementById(this.blocs[i][j]);

	window.onresize = function(ev) { LignesTemps.calculer(); };
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
	var boutSegment = 0; // 0: accolade; 1: embouchure.
	var embouchureB = 2;
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
