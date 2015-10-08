<?php
/*
 * Copyright (c) 2004-2005 Guillaume Outters
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

require_once('pasTeX.inc');
require_once dirname(__FILE__).'/commun/poule.php';
require_once dirname(__FILE__).'/commun/zorglub.php';

/* La Poule pond un CV comme elle pondrait un œuf. La ponte est sa raison de vivre,
 * ce n'est pas une telle bassesse du destin qui la détournerait de sa vocation. Et
 * puis, pour ce qu'elle a de cerveau, le temps qu'elle se rende compte du
 * remplacement… */

/* La ponte est découpée en trois étapes:
 * - récupération des données du CV et mise en mémoire
 * - filtrage des données (toutes ne figureront peut-être pas sur le CV final)
 * - génération du fichier final
 * Ce qui nous fait bien trois étapes, et non pas quatre comme ma pauvre tête le
 * croyait au départ (car, étant adepte du saucissonnage à outrance, catégorie
 * tranchage de saucisson, elle imaginait une étape intermédiaire d'un méta-
 * modèle assez confus avec des tableaux, des paragraphes et d'autres notions
 * générales qui planeraient un peu, avant de redescendre sur terre via un
 * transformateur de ce format universel en XHTML, OpenOffice, PDF, texte brut
 * encodé UTF-8, et tout ce votre fertile imagination aura cru bon d'ajouter).
 */

$etPuisQuoiEncore = 1; // Paramètre à partir duquel la chose peut analyser ses paramètres.

/* À FAIRE: les modules devraient pouvoir d'une manière ou d'une autre sortir
 * une liste de compléments de ligne de commande, pour par exemple servir dans
 * un bash_completion. On pourrait dire qu'un paramètre spécial serait passé à
 * la fonction pour qu'elle balance toutes ces possibilités. */
/* À FAIRE: ces modules devront être préfixés par par_, de même que les chargeurs le
 * sont par de_, afin d'éviter les conflits avec les filtres d'export.
 */

$params = array_merge($_GET, $_POST);

/*- Application --------------------------------------------------------------*/

$GLOBALS['poule'] = new Poule;

/*- Recherche du compositeur -------------------------------------------------*/

$decompo = null;

if(isset($argv)) // Appel en ligne de commande
{
	if(count($argv) == $etPuisQuoiEncore)
		$compo = 'liste'; /* À FAIRE: créer le compositeur Liste, qui établit la liste des autres compositeurs du même répertoire. S'inspirer d'ALbum->listeRessources() dans l'alboretum. Vivement une unification. */
	else
		$compo = $argv[$etPuisQuoiEncore];
}
else
{
	if(!is_array(@$params['compo']) || count(array_keys($params['compo'])) < 1)
	{
		$compo = 'liste';
		$decompo = 'rien';
	}
	else
	{
		$machins = array_keys($params['compo']); // Ce PHP est vraiment un abruti fini! On aime les variables intermédiaires!
		$compo = $machins[0];
		$paramsCompo = $params['compo'][$compo];
	}
}

/* À FAIRE: vérifier que les modules à charger ne contiennent pas de .. */

$compo = pasTeX_chargerCompo($compo);
if(isset($argv))
{
	++$etPuisQuoiEncore;
	$paramsCompo = $compo->analyserParams($argv, $etPuisQuoiEncore); // On se demande bien combien il sera capable de nous bouffer de paramètres sur la ligne de commande.
}
else
	$paramsCompo = $compo->analyserChamps(@$paramsCompo);

/*- Chargement du filtre -----------------------------------------------------*/

$zorglub = new Zorglub; // Celui-là nous fournira toute la boîte à outils pour calculer tout ce qui nous passe par la tête.

$pondérera = false;
while($etPuisQuoiEncore < count($argv))
{
	switch($argv[$etPuisQuoiEncore])
	{
		case 'profil':
			if($etPuisQuoiEncore >= count($argv) - 1)
				break 2;
			$profil = $argv[$etPuisQuoiEncore + 1];
			$etPuisQuoiEncore += 2;
			$zorglub->profil = $profil;
			$pondérera = true;
			break;
		case 'pondéré':
			$pondérera = true;
			++$etPuisQuoiEncore;
			break;
		default:
			break 2;
	}
}

/* À FAIRE: si (en CLI) le compo s'est arrêté sur le mot 'par' ou 'filtre' ou
 * ce que vous voulez, on charge un filtre avant d'attaquer la sortie. Et même
 * plusieurs filtres, pourquoi pas. */

/*- Chargement du cul-de-poule -----------------------------------------------*/

if(!$decompo)
{

if(isset($argv)) // Appel en ligne de commande
{
	if(count($argv) <= $etPuisQuoiEncore)
		$decompo = 'liste';
	else
		$decompo = $argv[$etPuisQuoiEncore];
}
else
{
	if(!is_array(@$params['decompo']) || count(array_keys($params['decompo'])) < 1)
		$decompo = 'liste';
	else
	{
		$machins = array_keys($params['decompo']);
		$decompo = $machins[0];
		$paramsDecompo = $params['decompo'][$decompo];
	}
}

}

/* À FAIRE: vérifier que les modules à charger ne contiennent pas de .. */

$decompo = pasTeX_chargerDecompo($decompo);
if(isset($argv))
{
	++$etPuisQuoiEncore;
	$paramsDecompo = $decompo->analyserParams($argv, $etPuisQuoiEncore);
}
else
	$paramsDecompo = $decompo->analyserChamps(@$paramsDecompo);

if($paramsCompo !== null)
	$donnees = $compo->composer($paramsCompo);
if(!$pondérera)
	$zorglub->trier = false;
	$zorglub->pondérer($donnees);
if($paramsDecompo !== null)
{
	$decompo->_zorglub = $zorglub;
	$decompo->decomposer($paramsDecompo, @$donnees);
}

?>
