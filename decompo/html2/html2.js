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
	var embouchureB = 10
	var courbeB = 30;
	var courbeT = 30;
	var embouchureT = 30;
	var demieLargeurTube = 5;
	
	var d = '';
	// Le tube central est composé de deux lignes parallèles à a * x + y = b (chacune avec un offset de n pixels par rapport à cette ligne centrale).
	var ptB = { x: pBloc.x + embouchureB, y: pBloc.ym };
	var ptT = { x: pTexte.x - embouchureT, y: pTexte.ym };
	var viseeB = { x: pBloc.x + embouchureB + courbeB, y: pBloc.ym };
	var viseeT = { x: pTexte.x - embouchureB - courbeB, y: pTexte.ym };
	var ptM = { x: .5 * (viseeB.x + viseeT.x), y: .5 * (viseeB.y + viseeT.y) };
	var lt = { a: (viseeB.y - viseeT.y) / (viseeT.x - viseeB.x) };
	lt.b = lt.a * viseeB.x + viseeB.y;
	// On cherche le point situé à n pixels à gauche du centre du tube (en allant du texte vers le bloc).
	var racinea2plus1 = Math.sqrt(lt.a * lt.a + 1);
	var dx = demieLargeurTube * lt.a / racinea2plus1;
	var dy = demieLargeurTube / racinea2plus1;
	var ptMbas = { x: ptM.x + dx, y: ptM.y + dy }; // p: point; t: tube; M: milieu; bas: bah…
	var ptMhaut = { x: ptM.x - dx, y: ptM.y - dy };
	// On cherche le 'b' des droites du haut et du bas du tube (le 'a' est identique, puisque les droites sont parallèles).
	var lthaut = { b: lt.a * ptMhaut.x + ptMhaut.y };
	var ltbas = { b: lt.a * ptMbas.x + ptMbas.y };
	// Et l'intersection de ces mêmes droites avec le bout de tuyau horizontal partant qui du texte, qui du carreau.
	var pcTbas = { x: (ltbas.b - (ptT.y + demieLargeurTube)) / lt.a, y: ptT.y + demieLargeurTube }; // c: coude.
	var pcThaut = { x: (lthaut.b - (ptT.y - demieLargeurTube)) / lt.a, y: ptT.y - demieLargeurTube };
	var pcBbas = { x: (ltbas.b - (ptB.y + demieLargeurTube)) / lt.a, y: ptB.y + demieLargeurTube }; // c: coude.
	var pcBhaut = { x: (lthaut.b - (ptB.y - demieLargeurTube)) / lt.a, y: ptB.y - demieLargeurTube };
	// Nos points de contrôle vont se trouver à l'intersection 
	d += ' M '+pTexte.x+','+pTexte.y1+' C '+(pTexte.x - embouchureT)+','+pTexte.y1+' '+pTexte.x+','+pcTbas.y+' '+(pTexte.x - embouchureT)+','+pcTbas.y; // Accolade texte bas.
	d += ' C '+(pcTbas.x - embouchureT)+','+pcTbas.y+' '+(pcTbas.x - embouchureT)+','+pcTbas.y+' '+ptMbas.x+','+ptMbas.y; // Coude, et remontée jusqu'au pivot.
	d += ' C '+(pcBbas.x + embouchureB)+','+pcBbas.y+' '+(pcBbas.x + embouchureB)+','+pcBbas.y+' '+(pBloc.x + embouchureB)+','+pcBbas.y;
	d += ' C '+pBloc.x+','+pcBbas.y+' '+(pBloc.x + embouchureB)+','+pBloc.y1+' '+pBloc.x+' '+pBloc.y1;
	d += ' L '+pBloc.x+','+pBloc.y0+' C '+(pBloc.x + embouchureB)+','+pBloc.y0+' '+pBloc.x+','+pcBhaut.y+' '+(pBloc.x + embouchureB)+','+pcBhaut.y; // Longement bloc + accolade bloc haute.
	d += ' C '+(pcBhaut.x + embouchureB)+','+pcBhaut.y+' '+(pcBhaut.x + embouchureB)+','+pcBhaut.y+' '+ptMhaut.x+','+ptMhaut.y;
	d += ' C '+(pcThaut.x - embouchureT)+','+pcThaut.y+' '+(pcThaut.x - embouchureT)+','+pcThaut.y+' '+(pTexte.x - embouchureT)+','+pcThaut.y;
	d += ' C '+pTexte.x+','+pcThaut.y+' '+(pTexte.x - embouchureT)+','+pTexte.y0+' '+pTexte.x+','+pTexte.y0;
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
	
	for(i in this.blocs)
	{
		courbe = document.createElementNS(ensvg, 'path');
		courbe.setAttributeNS(null, 'class', 'jonction');
		d = '';
		pTexte = p(this.blocs[i][0]);
		for(j = this.blocs[i].length; --j >= 1;) // Le bloc 0 est le texte, à lier à tous les autres qui sont la représentation graphique.
		{
			pBloc = p(this.blocs[i][j], true);
			d += this.jointureSimple(pTexte, pBloc);
		}
		courbe.setAttributeNS(null, 'd', d);
		svg.appendChild(courbe);
	}
};
