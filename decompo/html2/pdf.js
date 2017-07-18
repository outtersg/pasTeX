// Pour imprimer: virer le masque des chemins; et puis il faudrait introduire un clipRect (par exemple le body avec un overflow: hidden, que l'on scrollerait pour faire les captures d'écran correspondant à chaque page, ce qui nous permettrait de découper nous-mêmes aux endroits stratégiques (ex.: jointures entre expériences). Bon, idéalement, on ferait le boulot de découpe en PDF (un seul PDF sur une trèèèèès longue page, que l'on importerait sous HummusPDF pour en faire des copies par référence clipées sur chaque page (plutôt que des copies réelles, ce que risque de nous donner la copie).

var page = require('webpage').create();
var system = require('system');

var html;
var pdf;
var finesse = 1.0;
var reductions = [];

for(var i = 0; ++i < system.args.length;)
{
	if(/^-[0-9.]+$/.test(system.args[i]))
		finesse = parseFloat(system.args[i].substr(1));
	else if(system.args[i] == '--reductions')
		reductions = system.args[++i].split(/, */);
	else if(typeof(html) == 'undefined')
		html = system.args[i];
	else if(typeof(pdf) == 'undefined')
		pdf = system.args[i];
}

var ppp = 150; // http://stackoverflow.com/questions/22017746/while-rendering-webpage-to-pdf-using-phantomjs-how-can-i-auto-adjust-my-viewpor
var pppAff = 147.515; // Saloperie de rendu PDF géré différemment du reste; apparemment voilà le réglage qui me permet d'avoir exactement le même développement des textes sur mon CV (à savoir: les blocs de texte multilignes sont découpés pile au même endroit). Attention: le moindre décalage explose les chemins, car alors le rendu viewportSize est fait avec une largeur (et donc un wrapping) différent du rendu PDF. Or le SVG n'est pas recalculé au moment de l'impression, donc si le wrapping est différent (un pixel suffit parfois à faire passer un mot à la ligne, ce qui rajoute parfois une ligne), le SVG finira en décalage avec le texte. En outre, même le plus précautionneusement du monde, on va avoir un décalage: le SVG est bien imprimé comme fond de son texte, sauf que lorsqu'une ligne de texte atterrit en fin de page, le rendu le décale pour le faire apparaître en début de page suivante, et ne recale pas le SVG correspondant. De page en page, on a un décalage qui augmente (on pourrait le récupérer en imprimant la page 1, calculant jusqu'où elle arrive, décalant l'intégralité du body via un top: -...px, imprimer, etc., puis recoller les pages entre elles). Mais, pour (vraiment) terminer, le rendu des courbes est foiré (leur masque se décale, et finit donc par masquer ce qu'il ne devrait pas, et démasquer ce qu'il devrait masquer). Je ne suis pas satisfait du tout du résultat. Le rendu PNG est parfait… mais non vectoriel (et non découpé page à page).
pppAff = 147.678;
pppAff = 147.8; // Marche mieux avec mon CV du 2015-06-16.
var pppImpr = 300;

pppAff *= finesse; pppImpr *= finesse; // Histoire que les CSS 1px ne soient quand même pas trop épais. Inconvénient: quel que soit le viewportSize, à l'impression Phantom ramène toutes les mesures au paperSize. Donc si l'on veut un A4 où 1px est plus fin que ce que Phantom a l'habitude de prendre comme 1px en A4, il nous faudra imprimer en A3 (et user d'une entourloupe pour repasser le PDF résultant en A4, mais cela devra se faire hors Phantom).

var facteurBlague = 1.64157; // En fait si on imprime un certain nombre de pixels à 300 ppp, on récupère un document de 34,47 cm (d'après Aperçu de Mac OS X).
pppAff /= facteurBlague;
pppImpr /= facteurBlague;
var cmpp = 2.54;
var hauteur = 29.7, largeur = 21, marge = 0; // La marge est gérée comme margin en CSS.
hauteur = 29.7302; largeur = 21.024; // Dimensions plus exactes.
// 1: installation automatisée pour.
// 0: client / internes

page.viewportSize = { width: (largeur - 2 * marge) / cmpp * pppAff, height: (hauteur - 2 * marge) / cmpp * pppAff };
page.paperSize = { width: (largeur / cmpp * pppImpr)+'px', height: (hauteur / cmpp * pppImpr)+'px', margin: (marge / cmpp * pppImpr)+'px' };

page.open(html, function(res)
{
	if(res !== 'success')
	{
		console.log('Impossible de charger '+html);
		phantom.exit(1);
	}
	
	var trouveCesures = function()
	{
		var yHaut = function(o)
		{
			var y = 0;
			while(o && o.offsetParent !== o)
			{
				y += o.offsetTop;
				o = o.offsetParent;
			}
			return y;
		};
		
		var yBas = function(o)
		{
			var y = o.offsetHeight;
			while(o && o.offsetParent !== o)
			{
				y += o.offsetTop;
				o = o.offsetParent;
			}
			return y;
		};
		
		var cesures = [];
		var sections = document.querySelectorAll('.section');
		var projets = document.querySelectorAll('.projet');
		var i;
		
		for(i = 0; i < sections.length; ++i)
		{
			var y = yHaut(sections[i]);
			cesures.push([ 'd', y, 's' ]);
			cesures.push([ 'f', y + sections[i].offsetHeight, 's' ]);
		}
		for(i = 0; i < projets.length; ++i)
		{
			var y = yHaut(projets[i]);
			cesures.push([ 'd', y, 'p' ]);
			cesures.push([ 'f', y + projets[i].offsetHeight, 'p' ]);
		}
		cesures.sort(function(a, b) { return a[1] - b[1]; });
		
		// On supprime le début des premiers projets (redondants avec le début de la section qui les contient) et la fin des derniers projets.
		
		var cesures2;
		var precedente;
		
		cesures2 = [];
		precedente = 's';
		for(i = 0; i < cesures.length; ++i)
		{
			if(cesures[i][2] != 'p' || precedente == 'p')
				cesures2.push(cesures[i]);
			precedente = cesures[i][2];
		}
		cesures = cesures2;
		
		cesures2 = [];
		precedente = 's';
		for(i = cesures.length; --i >= 0;)
		{
			if(cesures[i][2] != 'p' || precedente == 'p')
				cesures2.push(cesures[i]);
			precedente = cesures[i][2];
		}
		cesures = cesures2;
		cesures.reverse();
		
		/* Tracé d'un trait rouge en dessous de chaque bloc, d'un vert au-dessus de chaque bloc.
		var corps = document.querySelector('.corps');
		for(i = 0; i < cesures.length; ++i)
		{
			var fils = document.createElement('div');
			fils.setAttribute('style', 'position: absolute; width: 100%; height: 1px; top: '+(cesures[i][1] - corps.offsetTop)+'px; background: '+(cesures[i][0] == 'f' ? 'red' : 'green')+';');
			corps.appendChild(fils);
		}
		*/
		
		/* Tracé d'une croix à la jonction des pages A4, afin de s'assurer que nous sommes capables de prévoir en pixels où va tomber la césure PDF.
		corps.style.overflow = 'hidden';
		var ppp = 150;
		var pppAff = 147.515;
		//pppAff = 147.678; // En fait la valeur avec laquelle la croix est bien centrée sur la césure serait plutôt celle-ci. Mais tant pis, car le risque de décaler le texte est bien plus élevé, et la petite erreur sur la hauteur de page estimée est moins grave, c'est juste que la marge basse des pages sera un peu approximative.
		var pppImpr = 300;
		var facteurBlague = 1.64157;
		pppAff /= facteurBlague;
		pppImpr /= facteurBlague;
		var cmpp = 2.54;
		var hauteur = 29.7, largeur = 21, marge = 0;
		hauteur = 29.7302; largeur = 21.024;
		var viewportSize = { width: (largeur - 2 * marge) / cmpp * pppAff, height: (hauteur - 2 * marge) / cmpp * pppAff };
		var paperSize = { width: (largeur / cmpp * pppImpr)+'px', height: (hauteur / cmpp * pppImpr)+'px', margin: (marge / cmpp * pppImpr)+'px' };

		var ensvg = "http://www.w3.org/2000/svg";
		var svg = document.createElementNS(ensvg, 'svg');
		svg.setAttribute('style', 'position: absolute; left: 30%; height: 600px; width: 600px; top: '+(3 * (hauteur - 2 * marge) / cmpp * pppAff - corps.offsetTop - 300)+'px;');
		var courbe;
		courbe = document.createElementNS(ensvg, 'line');
		courbe.setAttributeNS(null, 'fill', 'none');
		courbe.setAttributeNS(null, 'stroke', 'red');
		courbe.setAttributeNS(null, 'stroke-width', '3px');
		courbe.setAttributeNS(null, 'x1', 0);
		courbe.setAttributeNS(null, 'y1', 0);
		courbe.setAttributeNS(null, 'x2', 600);
		courbe.setAttributeNS(null, 'y2', 600);
		svg.appendChild(courbe);
		courbe = document.createElementNS(ensvg, 'line');
		courbe.setAttributeNS(null, 'fill', 'none');
		courbe.setAttributeNS(null, 'stroke', 'red');
		courbe.setAttributeNS(null, 'stroke-width', '3px');
		courbe.setAttributeNS(null, 'x1', 600);
		courbe.setAttributeNS(null, 'y1', 0);
		courbe.setAttributeNS(null, 'x2', 0);
		courbe.setAttributeNS(null, 'y2', 600);
		svg.appendChild(courbe);
		corps.appendChild(svg);
		*/
		
		return cesures;
	};
	
	window.setTimeout(function()
	{
		// Si on nous demande une certaine finesse, il faut adapter la taille de police.
		if(finesse != 1.0)
			page.evaluate(function(finesse)
			{
				reduirePolices(finesse);
			}, finesse);
		
		// Application des réductions.
		
		if(reductions.length)
		{
			var reductionsAppliquees = page.evaluate(function(reductionsVoulues) { return reduire(reductionsVoulues); }, reductions);
			console.log("Réductions appliquées: "+reductionsAppliquees.join(', '));
		}
		
		// Ponte!
		
		var hPage = page.evaluate(function() { return document.querySelector('body').offsetHeight; });
		var hPageImpr = Math.ceil(pppImpr * (hPage / pppAff + 2 * marge / cmpp));
		page.paperSize = { width: (largeur / cmpp * pppImpr)+'px', height: hPageImpr+'px', margin: (marge / cmpp * pppImpr)+'px' }; // On doit redéfinir tout l'objet; PhantomJS ne prend pas en compte la modification de la seule height.
		
		//page.render(pdf+'.png');
		
		// Le rendu PDF introduit des décalages dans les masques, qui font qu'ils ne sont plus bien placés par rapport à leur porteur. Tant pis, on les gicle.
		page.evaluate(function()
		{
			var masques = document.querySelectorAll('svg mask');
			var i;
			for(i = masques.length; --i >= 0;)
				masques[i].parentNode.removeChild(masques[i]);
		});
		var cesures = page.evaluate(trouveCesures);
		
		var paramsCouparchemin = '';
		for(var i = 1; i < cesures.length - 1; i += 2) // Le premier et le dernier ne nous intéressent pas (une ouverture et une fermeture qui seront confondus avec le haut et le bas de document).
			paramsCouparchemin += cesures[i][1]+','+cesures[i + 1][1]+' ';
		paramsCouparchemin += hPage;
		console.log("decouparchemin "+paramsCouparchemin);
		
		page.render(pdf);
		phantom.exit(0);
	}, 0);
});
