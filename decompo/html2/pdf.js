// Pour imprimer: virer le masque des chemins; et puis il faudrait introduire un clipRect (par exemple le body avec un overflow: hidden, que l'on scrollerait pour faire les captures d'écran correspondant à chaque page, ce qui nous permettrait de découper nous-mêmes aux endroits stratégiques (ex.: jointures entre expériences). Bon, idéalement, on ferait le boulot de découpe en PDF (un seul PDF sur une trèèèèès longue page, que l'on importerait sous HummusPDF pour en faire des copies par référence clipées sur chaque page (plutôt que des copies réelles, ce que risque de nous donner la copie).

var page = require('webpage').create();
var system = require('system');

var pdf = system.args[1];

var ppp = 150; // http://stackoverflow.com/questions/22017746/while-rendering-webpage-to-pdf-using-phantomjs-how-can-i-auto-adjust-my-viewpor
var pppAff = 147.515; // Saloperie de rendu PDF géré différemment du reste; apparemment voilà le réglage qui me permet d'avoir exactement le même développement des textes sur mon CV (à savoir: les blocs de texte multilignes sont découpés pile au même endroit). Attention: le moindre décalage explose les chemins, car alors le rendu viewportSize est fait avec une largeur (et donc un wrapping) différent du rendu PDF. Or le SVG n'est pas recalculé au moment de l'impression, donc si le wrapping est différent (un pixel suffit parfois à faire passer un mot à la ligne, ce qui rajoute parfois une ligne), le SVG finira en décalage avec le texte. En outre, même le plus précautionneusement du monde, on va avoir un décalage: le SVG est bien imprimé comme fond de son texte, sauf que lorsqu'une ligne de texte atterrit en fin de page, le rendu le décale pour le faire apparaître en début de page suivante, et ne recale pas le SVG correspondant. De page en page, on a un décalage qui augmente (on pourrait le récupérer en imprimant la page 1, calculant jusqu'où elle arrive, décalant l'intégralité du body via un top: -...px, imprimer, etc., puis recoller les pages entre elles). Mais, pour (vraiment) terminer, le rendu des courbes est foiré (leur masque se décale, et finit donc par masquer ce qu'il ne devrait pas, et démasquer ce qu'il devrait masquer). Je ne suis pas satisfait du tout du résultat. Le rendu PNG est parfait… mais non vectoriel (et non découpé page à page).
var pppImpr = 300;
var facteurBlague = 1.64157; // En fait si on imprime un certain nombre de pixels à 300 ppp, on récupère un document de 34,47 cm (d'après Aperçu de Mac OS X).
pppAff /= facteurBlague;
pppImpr /= facteurBlague;
var cmpp = 2.54;
var hauteur = 29.7, largeur = 21, marge = 0; // La marge est gérée comme margin en CSS.
// 1: installation automatisée pour.
// 0: client / internes

page.viewportSize = { width: (largeur - 2 * marge) / cmpp * pppAff, height: (hauteur - 2 * marge) / cmpp * pppAff };
page.paperSize = { width: (largeur / cmpp * pppImpr)+'px', height: (hauteur / cmpp * pppImpr)+'px', margin: (marge / cmpp * pppImpr)+'px' };

page.open('cv.html', function(res)
{
	if(res !== 'success')
	{
		console.log('Impossible de charger '+'cv.html');
		phantom.exit(1);
	}
	
	window.setTimeout(function()
	{
		page.render(pdf+'.png');
		// Le rendu PDF introduit des décalages dans les masques, qui font qu'ils ne sont plus bien placés par rapport à leur porteur. Tant pis, on les gicle.
		page.evaluate(function()
		{
			var masques = document.querySelectorAll('svg mask');
			var i;
			for(i = masques.length; --i >= 0;)
				masques[i].parentNode.removeChild(masques[i]);
		});
		page.render(pdf);
		phantom.exit(0);
	}, 2000);
});
