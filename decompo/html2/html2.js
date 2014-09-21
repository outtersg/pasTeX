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
