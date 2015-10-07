<?php
/*
 * Copyright (c) 2005,2013 Guillaume Outters
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

 require_once('util/params.inc');
require_once dirname(__FILE__).'/../../util/processus.php';

/* À FAIRE: inclure un lien, si module il y a, vers la génération d'un PDF
 * correspondant. */

class Html2
{
	function Html2() {}
	
	function analyserParams($argv, &$position)
	{
		$retour = array();
		while($position < count($argv))
		{
			switch($argv[$position])
			{
				case '--respire':
				case '+respire':
					$retour['respire'] = true;
					break;
				case 'pdf':
					$retour['pdf'] = $argv[$position + 1];
					++$position;
					break;
				case '--intro':
				case '+intro':
					$retour['intro'] = true;
					break;
				case '--trad':
				case '+trad':
					$retour['trad'] = $argv[$position + 1];
					++$position;
					break;
				default:
					break 2;
			}
			++$position;
		}
		
		return $retour;
	}
	
	function analyserChamps($params)
	{
		/* Marre de devoir m'adapter à cet incapable. Au fur et à mesure que je
		 * découvrirai des trucs qui ne marchent pas, je virerai. Pour le
		 * moment, avec un IE 6.0.2800.1106 (c'est pas des blagues!):
		 * - un position: absolute dans un position: relative sait repérer son
		 *   top et son left, mais pas son right ni son bottom (il prend ceux
		 *   duposition: absolute encore au-dessus).
		 * - le PNG, comme d'hab
		 */
		if(!array_key_exists('ie', $params))
			$params['ie'] = array_key_exists('HTTP_USER_AGENT', $_SERVER) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false;
		return $params;
	}
	
	function pondreInterface($champ) {}
	
	/**
	 * Renvoie la durée d'une période, que l'on présuppose non totalement nulle.
	 */
	protected function _duree($periode)
	{
		return Date::calculerAvecIndefinis(Date::mef($periode[1]), true) - Date::calculerAvecIndefinis(Date::mef($periode[0]), true);
	}
	
	protected function _lignesDeTemps($donnees)
	{
		/* Calcul des incompatibilités. */
		
		$moments = array();
		$periodes = array(); // Un "moment" peut être constituée de plusieurs périodes disjointes; on se met aussi de côté pour chaque moment la période enveloppant l'ensemble de ses sous-périodes.
		$incompatibilites = array();
		$maintenant = obtenir_datation(time());
		foreach($donnees->expérience->projet as $num => $francheRigolade)
		{
			foreach($francheRigolade->date as $plage)
			{
				$f = $plage->f;
				if($f == array(-1, -1, -1, -1, -1, -1))
					$f = $maintenant;
				$moments[$num][] = array($plage->d, $f);
			}
			$periodes[$num] = periode_union($moments[$num]);
			$incompatibilites[$num] = array();
			for($i = $num; --$i >= 0;)
				if(periode_seChevauchent($moments[$num], $moments[$i], 0.3))
				{
					$incompatibilites[$num][$i] = true;
					$incompatibilites[$i][$num] = true;
				}
/*
$aff = array();
foreach($francheRigolade->date as $p)
$aff[] = '[ '.implode('-', $p->d).' -> '.implode('-', $p->f).' ]';
$affs[] = implode(', ', $aff);
*/
		}
		
		/* Constitution des groupes. */
		/* On va caser les morceaux, en commençant par les plus gros. Pour cela, on calcule deux poids: nombre d'incompatibilités avec des déjà casés, et nombre d'incompatibles avec des restant à caser. Le premier poids indique ce qu'on va avoir le plus de mal à caser immédiatement (et autant commencer par le plus difficile), le second nous indique la pénalité que l'on aura plus tard si on ne case pas cet élément. */
		
		$casage = array(); // Indice: numéro moment; valeur: numéro de groupe.
		$groupes = array(); // Indice: numéro de groupe; vaincompatibilitesleur: liste des moments contenus par le groupe (en indices).
		
		while (count($incompatibilites))
		{
			$max = array(-1, -1); // Max incompatibilités avec des déjà casés, puis max d'incompatibilités avec des non casés.
			$numMax = -1; // Numéro du moment où on a atteint le max (max avec des déjà casés, et, en cas d'égalité, max avec des non casés pour départager).
			
			$nIncomp = array_fill_keys(array_keys($incompatibilites), array(0, 0));
			foreach($incompatibilites as $num => $incomp)
			{
				$nIncompCases = count(array_intersect_key($casage, $incomp));
				if($nIncompCases < $max[0])
					continue;
				$nIncompNonCases = count(array_diff_key($incomp, $casage));
				if($nIncompNonCases < $max[1])
					continue;
				if($nIncompCases == $max[0] && $nIncompNonCases == $max[1] && $this->_duree($periodes[$num]) < $this->_duree($periodes[$numMax])) // En cas d'égalité, c'est le plus court moment qui cède la place au plus long (on case les plus longs en premier, donc à gauche si on affiche les groupes de gauche à droite).
					continue;
				$numMax = $num;
				$max = array($nIncompCases, $nIncompNonCases);
			}
			
			// C'est parti, on case $numMax.
			
			$trouveUnePlace = false;
			foreach($groupes as $numGroupe => $momentsGroupe)
			{
				if(count(array_intersect_key($incompatibilites[$numMax], $momentsGroupe)) == 0)
				{
					$trouveUnePlace = true;
					break;
				}
			}
			if(!$trouveUnePlace)
			{
				$numGroupe = count($groupes);
				$groupes[$numGroupe] = array();
			}
			
			$groupes[$numGroupe][$numMax] = true;
			$casage[$numMax] = $numGroupe;
			
			unset($incompatibilites[$numMax]);
		}
		
		/* À FAIRE: regrouper avec une notion de "plein de petits valent mieux qu'un gros". En fait on préfère une ligne de temps bien dense avec l'essentiel des missions, et puis ceux qui ne rentrent pas mis de côté. Là le risque est d'avoir une ligne de temps principale avec des moyens, les trous étant comblés par des petits, et les autres petits étant mis de côté, au lieu d'avoir tous les petits ensemble. */
		
		/* Matérialisation des périodes. */
		/* On place nos périodes précisément dans le temps. Ainsi, une période 2001-2002 va courir du 1er janvier 2001 au 31 décembre 2002. Cependant, dans certains groupes, peuvent se combiner des périodes "logiquement" incompatibles mais humainement très compatibles, ex. mission 1 de janvier à juin puis mission 2 de juin à août: on comprend que le mois de juin est charnière, et la matérialisation de juin ne s'exprimera pas comme 30 juin (pour le juin "fin") et 1er juin (pour le juin "début"), mais comme 15 juin pour les deux, afin de pouvoir caser les deux d'affilée. */
		
		// L'étape précédente nous garantit qu'on n'aura pas de chevauchement trop important. Donc on peut se contenter de détecter les chevauchements en classant par centre de période, et affinant ensuite les chevauchements éventuels juste deux à deux.
		// N.B.: en réalité, il y a peut-être une faille. On a une règle disant que les périodes "larges" sont réductibles dans une certaine proportion, par exemple 1/3. Ça veut dire que quand on parle de janvier à mai d'une année, on va considérer que la période commence au 20 janvier (donc, du 20 au 31, 1/3 de janvier), couvre tout février à avril, et encore 1/3 de mai jusqu'au 10. Mais si l'on parle de janvier à janvier, je ne suis pas sûr d'avoir blindé pour qu'on ne se retrouve pas dans une situation "du 20 janvier au 10 janvier". Si ça se produit, on ne pourra plus garantir que le calcul de non-chevauchement marche avec cette période tête-bêche.
		
		$resultat = array();
		foreach($groupes as $numGroupe => $groupe)
		{
			/* Simplification des moments (on les éclate en périodes, et on les traduits en time_t). */
			
			$periodes = array();
			
			foreach($groupe as $num => $rien)
			{
				// Pour chaque moment (sous forme de liste de périodes, car composé éventuellement de plusieurs périodes disjointes), on va calculer ses extensions minimale et maximale (ex.: une période 2001-2002 couvrira au minimum quelque chose comme septembre 2001 - avril 2002, et au maximum janvier 2001 - décembre 2002).
				
				foreach($moments[$num] as $periode)
				{
					$minDebut = Date::calculerAvecIndefinis(Date::mef($periode[0]), false, 1);
					$maxDebut = Date::calculerAvecIndefinis(Date::mef($periode[0]), false, 0.3);
					$minFin   = Date::calculerAvecIndefinis(Date::mef($periode[1]), true, 0.3);
					$maxFin   = Date::calculerAvecIndefinis(Date::mef($periode[1]), true, 1);
					$periodes[] = array('minDebut' => $minDebut, 'maxDebut' => $maxDebut, 'minFin' => $minFin, 'maxFin' => $maxFin, 'centre' => ($maxFin + $minDebut) / 2, 'moment' => $num);
				}
			}
			usort($periodes, array($this, 'trierSurCentreMoments'));
			
			/* Choix de la date affichée pour chaque période, tenant compte des chevauchements avec ses voisins. */
			
			$nPeriodes = count($periodes);
			$periodes[0]['debut'] = $periodes[0]['minDebut']; // Lui ne risque pas de chevaucher son prédécesseur, on lui fait prendre toute la place.
			$periodes[$nPeriodes - 1]['fin'] = $periodes[$nPeriodes - 1]['maxFin']; // Idem pour celui-ci avec son successeur.
			for($i = $nPeriodes; --$i > 0;)
			{
				if($periodes[$i]['minDebut'] < $periodes[$i - 1]['maxFin']) // Là, entre l'élément actuel et son prédécesseur, ils vont devoir se partager la place.
				{
					$partage = (max($periodes[$i - 1]['minFin'], $periodes[$i]['minDebut']) + min($periodes[$i - 1]['maxFin'], $periodes[$i]['maxDebut'])) / 2;
					$periodes[$i]['debut'] = $periodes[$i - 1]['fin'] = $partage;
				}
				else // Sinon les deux peuvent s'étaler jusqu'à leur maximum sans craindre de toucher le voisin.
				{
					$periodes[$i]['debut'] = $periodes[$i]['minDebut'];
					$periodes[$i - 1]['fin'] = $periodes[$i - 1]['maxFin'];
				}
			}
			
			foreach($periodes as & $refPeriode)
				$refPeriode['groupe'] = $numGroupe;
			
			$resultat = array_merge($resultat, $periodes);
		}
		
		return $resultat;
	}
	
	/**
	 * Tri par milieu de moment.
	 * En effet, nos Bézier font un peu bazar, on va donc essayer d'en mettre le maximum en face de leur ligne de temps. Idéalement, un JS replacerait les blocs de texte en fonction (en partant des plus ténus, et en tablant sur le fait que ceux qui couvrent une immense période finiront par trouver un petit trou où se caser).
	 */
	protected function _positionsMilieux($expériences, $lignesDeTemps)
	{
		$milieux = array();
		foreach($expériences as $numProjet => $projet)
		{
			$moments = array();
			foreach($projet->date as $moment)
				$moments[] = array($moment->d, $moment->f);
			$moments = periode_union($moments);
			$debut = Date::calculerAvecIndefinis(Date::mef($moments[0]), false);
			$fin = Date::calculerAvecIndefinis(Date::mef($moments[1]), true);
			$milieu = $debut + 2 * $fin; // En fait à 1/3 de la fin. On donne plus d'importance à la fin de projet qu'au début, pour le classement.
			$milieux[$numProjet] = $milieu;
		}
		return $milieux;
	}
	
	protected function _triDébuts($expériences)
	{
		$tri = array();
		foreach($expériences as $numProjet => $projet)
		{
			$min = null;
			foreach($projet->date as $moment)
				if($min > ($date = Date::calculerAvecIndefinis(Date::mef($moment->d), false)) || !isset($min))
					$min = $date;
			if(isset($min))
				$tri[$numProjet] = $min;
		}
		asort($tri, SORT_NUMERIC);
		$num = -1;
		foreach($tri as & $val)
			$val = ++$num;
		return $tri;
	}
	
	protected function _positionsDebroussaillage($expériences, $lignesDeTemps)
	{
		$penaliteTraverseeCouche = 20; // Si la flèche joignant le bloc de texte à son segment temporel doit traverser les segments d'autres moments.
		$penaliteVoisinGenant = 80; // Si le texte du moment, inséré ici, empiéterait sur un autre déjà placé pas loin.
		
		/* Calcul du poids du texte de chaque moment par rapport à l'ensemble. */
		
		// Pour le moment, 1 paragraphe = 1 unité de poids.
		/* À FAIRE: affiner. Mais sur quel critère? Nombre de caractères? Nombre de lignes? Tout dépend du côté client (polices utilisées, etc.). Idéalement on réaffinerait côté Javascript, à partir de la hauteur réelle de chaque bloc, ce qu'on estime ici. */
		$poids = array();
		foreach($lignesDeTemps as $segment)
			$poids[$segment['moment']] = 1;
		$poidsTotal = count($poids);
		
		/* On repère l'ensemble des "plages". Une plage = une période commune à un ensemble défini de moments. C'est le découpillage de toutes les périodes par elles-mêmes. */
		
		$points = array();
		$pointsPonctuels = array(); // On mettra là-dedans tous les points qui servent à la fois de début et de fin à une période: il leur faudra un traitement spécial, du genre une plage rien que pour lui de 0 seconde.
		foreach($lignesDeTemps as $segment)
		{
			$points[] = $d = $segment['debut'];
			$points[] = $f = $segment['fin'];
			if($d === $f)
				$pointsPonctuels[] = $d;
		}
		sort($points, SORT_NUMERIC);
		$points = array_unique($points, SORT_NUMERIC);
		
		$plages = array();
		$dernier = null;
		foreach($points as $point)
		{
			if($dernier !== null)
				$plages[] = array('d' => $dernier, 'f' => $point);
			if(in_array($point, $pointsPonctuels))
				$plages[] = array('d' => $point, 'f' => $point);
			$dernier = $point;
		}
		
		$tempsTotal = $point - $points[0];
		
		/* Quelle place (en équivalent secondes) prend chaque bloc de texte en face de la ligne de temps? */
		
		$equivalentsTemps = array();
		foreach($poids as $numMoment => $poid)
			$equivalentsTemps[$numMoment] = $tempsTotal * $poid / $poidsTotal;
		
		/* Remplissage des plages: on marque, segment par segment, l'ensemble des plages couvertes. */
		
		foreach($lignesDeTemps as $numSegment => $segment)
			foreach($plages as & $ptrPlage)
				if($ptrPlage['d'] == $ptrPlage['f'] && $ptrPlage['d'] == $segment['debut'] && $segment['fin'] == $segment['debut'])
					$ptrPlage['segments'][$numSegment] = 0;
				else if($ptrPlage['d'] < $segment['fin'] && $ptrPlage['f'] > $segment['debut'])
					$ptrPlage['segments'][$numSegment] = 0;
		
		/* On donne maintenant à chaque moment une note pénalisante en fonction de sa place. */
		
		foreach($plages as & $ptrPlage)
		{
			$couches = array();
			foreach($ptrPlage['segments'] as $numSegment => $penalite)
				$couches[$lignesDeTemps[$numSegment]['groupe']][] = $numSegment;
			krsort($couches, SORT_NUMERIC); // Chaque groupe qui possède un bloc constitue une couche à traverser pour les groupes de numéro inférieur (les groupes étant affichés par ordre croissant).
			$nCouchesTraversees = 0;
			foreach($couches as $numSegmentsCouche)
			{
				foreach($numSegmentsCouche as $numSegment)
					$ptrPlage['segments'][$numSegment] += $penaliteTraverseeCouche * $nCouchesTraversees;
				++$nCouchesTraversees;
			}
		}
			
		
		/* Placement des segments (de texte) en face des plages. */
		
		$placements = array();
		
		while(count($placements) < count($equivalentsTemps))
		{
			/* On cherche le moment qui a le pire des meilleurs placements (on le place vite vite vite, de peur que ce qu'il a de meilleur, qui est déjà objectivement dégueulasse, devienne encore pire). */
			
			$meilleurs = array();
			foreach($plages as $plage)
				foreach($plage['segments'] as $numSegment => $penalite)
				{
					$numMoment = $lignesDeTemps[$numSegment]['moment'];
					if(!isset($meilleurs[$numMoment]) || $meilleurs[$numMoment] > $penalite)
					{
						$meilleurs[$numMoment] = $penalite;
						$meilleursTemps[$numMoment] = ($plage['d'] + $plage['f']) / 2;
					}
				}
			
			arsort($meilleurs, SORT_NUMERIC);
			
			foreach($meilleurs as $numMoment => $note)
			{
				$placements[$numMoment] = $meilleursTemps[$numMoment];
				break;
			}
			
			/* On retire notre élu des files d'attente (on ne reviendra pas en arrière). */
			
			foreach($plages as & $ptrPlage)
				foreach($ptrPlage['segments'] as $numSegment => $penalite)
					if($lignesDeTemps[$numSegment]['moment'] == $numMoment)
						unset($ptrPlage['segments'][$numSegment]);
			
			/* On applique maintenant des pénalités aux voisins, histoire qu'ils ne viennent empiéter chez nous que si vraiment ils n'ont pas d'autre solution. */
			/* À FAIRE: le calcul devrait être fait plus strictement: si on place un bloc de texte A qui empiète sur une plage 0 qu'occuperait volontiers un texte B, il faut découper la plage 0 en deux sous-plages 1 et 2, avec "si B était centré sur 1, il n'empiéterait pas sur A centré sur la plage choisie", et 2 étant le complément (B empiéterait). Du coup seule la partie 2 de B recevrait une pénalité. L'inconvénient est que ça demande d'aller voir chaque segment alentour, puisque chacun est potentiellement plus ou moins grand que les autres (donc il y aurait autant de placements possibles pour la frontière que de segments existant: bonjour le découpillage final!). */
			
			// Pour le moment on se contente d'affecter toutes les plages situées autour de nous.
			$debutPenalite = $placements[$numMoment] - $equivalentsTemps[$numMoment] / 2 - ($tempsTotal / count($equivalentsTemps)) / 2; // Autour de notre point, 1/2 de notre bloc + 1/2 du "bloc moyen".
			$finPenalite = $placements[$numMoment] + $equivalentsTemps[$numMoment] / 2 + ($tempsTotal / count($equivalentsTemps)) / 2;
			foreach($plages as & $ptrPlage)
				if(($milieuPlage = ($ptrPlage['d'] + $ptrPlage['f']) / 2) >= $debutPenalite && $milieuPlage <= $finPenalite)
					foreach($ptrPlage['segments'] as $numSegment => $penalite)
						$ptrPlage['segments'][$numSegment] += $penaliteVoisinGenant;
		}
		
		return $placements;
	}
	
	public function trierSurCentreMoments($d0, $d1)
	{
		return $d0['centre'] - $d1['centre'];
	}
	
	function decomposer($params, $donnees)
	{
		$finessePdf = 2.0; // L'épaisseur du border minimal sera déterminé par un savant calcul relatif à la page (quoique l'on fasse avec le viewport, phantom ramène la taille d'1px à quelque chose de relatif à la taille du papier, quand il imprime). En outre cela détermine aussi l'épaisseur minimale des traits (phantom arrondit au px le plus proche, pour les border. Donc un border de 0.1em pourra s'afficher comme 1px, et un border de 0.05em… disparaîtra complètement, arrondi à 0px). Du coup si l'on veut avoir du trait fin, il nous faut imprimer sur de l'A3 par exemple puis effectuer une réduction PDF.
		
		// Par défaut, le gabarit de page adopte la langue du CV.
		if(!isset($params['trad']) && isset($donnees->langue))
			$params['trad'] = $donnees->langue;
		
		$dossierSortie = false;
		$cheminSortieHtml = false;
		if(isset($params['pdf']))
		{
			$cheminSortie = $params['pdf'];
			$cheminSortieParchemin = $params['pdf'].'.parchemin.pdf';
			$cheminSortieHtml = $cheminSortie.'.html';
			$dossierSortie = dirname($cheminSortieHtml);
		}
		if($dossierSortie)
			ob_start();
		
		Texte::$Html = true;
		
		$this->params = $params;
		
		html_enTete();
		html_meta('meta http-equiv="Content-Type" content="text/html; charset=UTF-8"'); // IE
		echo '<script type="text/javascript" src="'.($dossierSortie ? '' : 'decompo/html2/').'html2.js"></script>'."\n";
		echo '<script type="text/javascript" src="'.($dossierSortie ? '' : 'decompo/html2/').'bezier-spline.js"></script>'."\n";
		if($donnees->perso->nom)
			$titre = htmlspecialchars($donnees->perso->prénom, ENT_NOQUOTES).' '.htmlspecialchars($donnees->perso->nom, ENT_NOQUOTES);
		else
			$titre = 'CV';
		echo '<title>'.$titre.'</title>';
		html_meta('link rel="stylesheet" type="text/css" href="'.($dossierSortie ? '' : 'decompo/html2/').'html2.css"');
		html_corps('onload="LignesTemps.preparer(); Parcours.preparer();"');
		echo '<div class="corps" style="position: relative;">'."\n";
		echo '<svg id="chemins" style="position: absolute; left: 0; top: 0; height: 100%; width: 100%;"></svg>'."\n";
		$this->pondreEntete($donnees);
		if(isset($params['intro']))
			$this->pondreIntro($donnees);
		$this->pondreInteret($donnees);
		$this->pondreProjets($donnees);
		$this->pondreEtudes($donnees);
		$this->pondreLangues($donnees);
		$this->pondreConnaissances($donnees);
		$this->pondreAutres($donnees);
		if(array_key_exists('liens', $params))
			$this->pondreLiens($donnees, $params['liens']);
		echo '</div>'."\n";
		html_fin();
		
		if($dossierSortie)
		{
			$sortieHtml = ob_get_clean();
			file_put_contents($cheminSortieHtml, $sortieHtml);
			
			copy(dirname(__FILE__).'/html2.css', $dossierSortie.'/html2.css');
			copy(dirname(__FILE__).'/html2.js', $dossierSortie.'/html2.js');
			copy(dirname(__FILE__).'/bezier-spline.js', $dossierSortie.'/bezier-spline.js');
			
			if(isset($donnees->perso->photo) && file_exists($donnees->perso->photo))
				copy($donnees->perso->photo, $dossierSortie.'/photo.jpg');
		}
		
		if(isset($params['pdf']))
		{
			$phantom = new ProcessusCauseur(array('phantomjs', 'decompo/html2/pdf.js', '-'.$finessePdf, $cheminSortieHtml, $cheminSortieParchemin));
			$phantom->attendre();
			$sortiePhantom = trim($phantom->contenuSortie());
			if(!$sortiePhantom || !preg_match('/^[0-9,]+( [0-9,]+)*$/', $sortiePhantom))
				fprintf(STDERR, "# phantomjs nous transmet un message inintelligible.");
			else
			{
				$paramsDecoupe = explode(' ', $sortiePhantom);
				if($finessePdf != 1.0)
				{
					$redim = new Processus(array('/home/gui/src/projets/adochants/tourneetdouble', $cheminSortieParchemin, '-'.(1 / ($finessePdf * $finessePdf)), '-o', $cheminSortie.'.1'));
					$redim->attendre();
					$coupar = new Processus(array_merge(array('/home/gui/src/projets/adochants/couparchemin', $cheminSortie.'.1', $cheminSortie), $paramsDecoupe));
					$coupar->attendre();
				}
				else
				{
				$coupar = new Processus(array_merge(array('/home/gui/src/projets/adochants/couparchemin', $cheminSortieParchemin, $cheminSortie), $paramsDecoupe));
				$coupar->attendre();
				}
			}
		}
		
		return $this;
	}
	
	function pondreEnTete($donnees)
	{
		$prénom = htmlspecialchars($donnees->perso->prénom, ENT_NOQUOTES);
		$nom = htmlspecialchars($donnees->perso->nom, ENT_NOQUOTES);
		$titre = htmlspecialchars($donnees->titre, ENT_NOQUOTES);
?>
	<div class="enTete">
		<?php if(isset($donnees->perso->photo) && file_exists($donnees->perso->photo)) echo '<img src="photo.jpg" style="position: absolute; height: 8em; top: 0px; right: 0px;"/>'; ?>
		<div class="nom"><?php echo $prénom.' '.$nom ?></div>
		<div class="titre"><?php echo $titre ?></div>
<?php
		if($donnees->perso->naissance)
		{
			$maintenant = obtenir_datation(time());
			$âge = $maintenant[0] - $donnees->perso->naissance[0];
			for($i = 1; $i < 6; ++$i) // Si l'on est avant la date d'anniversaire, on retire un an.
				if(($j = $maintenant[$i] - $donnees->perso->naissance[$i]) != 0)
				{
					if($j < 0)
						--$âge;
					break;
				}
			echo '<div>'.$âge.' ans</div>';
		}
		if(isset($donnees->perso->état))
			echo '<div>'.htmlspecialchars($donnees->perso->état).'</div>';
		if($donnees->perso->mél)
		{
			$adrél = htmlspecialchars($donnees->perso->mél);
			echo '<div>'.$adrél.'</div>';
		}
		if($donnees->perso->tél)
		{
			$tél = htmlspecialchars($donnees->perso->tél);
			echo '<div>'.$tél.'</div>';
		}
		if($donnees->perso->adresse)
		{
			$adresse = implode('<br/>', array_map('htmlspecialchars', get_object_vars($donnees->perso->adresse->données)));
			echo '<div>'.$adresse.'</div>';
		}
?>
	</div>
<?php
	}
	
	function commencerSection($nom, $autresClasses = null)
	{
		if (isset($this->params['trad'])) {
			$nom = $this->trad($nom);
		}
		
		/* Secret de fabrication pour les coins: peindre un rectangle de 32x32 en
		 * couleur de fond, faire un masque tout noir sur lequel on trace un disque
		 * de 30x30 en blanc; sur l'image, tracer par-dessus un cercle couleur de
		 * bordure de 32x32. Récupérer les 16x16 pixels intéressants. */
		/* Emmerdements pour faire un dégradé propre:
		 * - des bandes à la transparence diminuant, les unes sur les autres.
		 *   Mais avec un positionnement relatif, ça merde, car les arrondis CSS
		 *   font que parfois la bande de largeur n% posée en n%, ne touchera
		 *   la bande de largeur n% commençant en 0 (un pixel entre les deux).
		 * - la même chose avec un positionnement par JS: quand on agrandit la
		 *   police, je JS n'est pas rappelé.
		 * - image en bg: on ne peut pas dire que le bg est étiré pour faire n%
		 *   de son conteneur.
		 * - image pas en bg: IE est censé avoir des problèmes avec la
		 *   transparence. On va voir avec le forçage à la GoogleMaps. De plus
		 *   ça nous force à créer une image par couleur de fond. Solution
		 *   envisagée.
		 * - bandes de largeur n%, 2*n%, 3*n%, …, commençant toutes en 0. On
		 *   calcule leur transparence pour qu'en s'accumulant, elles fassent
		 *   comme si on avait une bande de transparence voulue. Solution
		 *   envisagée.
		 * Le monsieur à http://forum.hardware.fr/hardwarefr/Programmation/Recherche-code-html-pour-fondu-sujet-75035-1.htm
		 * connaît le moyen de faire faire de la transparence à tout les
		 * navigateurs.
		 * Un jour on aura les CSS3. Enfin si c'est pour avoir quelque chose
		 * d'encore plus tordu que les 2.1…
		 */
?>
	<div class="section<?php if(isset($autresClasses)) echo ' '.$autresClasses; ?>">
		<div class="audessus">
			<h2 class="titresection">
				<?php echo $nom ?>
			</h2>
<?php
	}
	
	function terminerSection()
	{
?>
		</div>
	</div>
<?php
	}
	
	function pondreEtudes($donnees)
	{
		if(!array_key_exists('formation', $donnees)) return;
		
		$this->commencerSection('Études');
?>
		<table>
<?php
		foreach($donnees->formation->études as $pépère)
		{
?>
			<tr>
				<td class="categorie"><?php echo pasTeX_descriptionPeriode($pépère->date->d, $pépère->date->f) ?></td>
				<td><?php echo htmlspecialchars($pépère->diplôme, ENT_NOQUOTES) ?></td>
			</tr>
<?php
		}
?>
		</table>
<?php
		$this->terminerSection();
	}
	
	public function descriptionPeriode($entree)
	{
		return pasTeX_descriptionPeriode($entree->d, $entree->f);
	}
	
	/**
	 * Pour ne pas <li>er les éléments d'une liste (liste horizontale, super mal gérée par le CSS, comme à peu près tout ce qui sort du strict minimum auquel les gars qui ont pondu le CSS ont pu songer d'ailleurs).
	 */
	public function _palier($ligne)
	{
		$html = pasTeX_html($ligne);
		// Pour éviter que la puce soit orpheline, on cherche à agréger au moins $N octets derrière. On va donc créer un span nowrap jusqu'à ce n-ième octet. Bien entendu on ne coupe pas en milieu de mot, ni au milieu d'un sous-span.
		$N = 20;
		$pos = 0;
		$bonnePos = null;
		$longueurTexte = 0;
		$bonneLongueurTexte = null;
		$imbriqués = 0;
		$tailleHtml = strlen($html);
		preg_match_all('#(</[^>]*>)|(<[^>]*/>)|(<[^>]*>)|([^ <]+)|( +)#', $html, $corrs, PREG_OFFSET_CAPTURE|PREG_SET_ORDER, $pos);
		foreach($corrs as $corr)
		{
			// isset car https://bugs.php.net/bug.php?id=50887 (cf. page de référence de preg_match).
			for($i = 6; --$i >= 1;)
				if(isset($corr[$i]) && $corr[$i][1] = -1)
					break;
			switch($i)
			{
				// Espace.
				case 5:
					// Si l'espace n'est pas dans un sous-élément XML, et se trouve une fois passé le cap des n octets significatifs, alors on a trouvé notre point de césure.
					if(!isset($bonnePos) && $imbriqués == 0 && $longueurTexte > $N)
					{
						$bonnePos = $pos;
						$bonneLongueurTexte = $longueurTexte;
					}
					break;
				// Mot.
				case 4:
					$longueurTexte += strlen($corr[0][0]);
					break;
				// Balise ouvrante.
				case 3:
					++$imbriqués;
					break;
				// Balise auto-fermante.
				case 2:
					break;
				// Balise fermante.
				case 1:
					--$imbriqués;
					break;
			}
			$pos = $corr[0][1] + strlen($corr[0][0]);
		}
		// Si on n'a pas trouvé à s'insérer, ou si on risque de laisser une orpheline à l'autre bout, on englobe tout.
		if(!isset($bonnePos) || $bonneLongueurTexte > $longueurTexte - $N)
			$bonnePos = $tailleHtml;
		$dEnrobage = $fEnrobage = '';
		if(isset($this->params['respire']))
		{
			$dEnrobage = '<span class="re">';
			$fEnrobage = '</span>';
		}
		return $dEnrobage.'<span class="ml">• '.substr($html, 0, $bonnePos).'</span>'.substr($html, $bonnePos).$fEnrobage.' '; // Un espace pour séparer du • suivant.
	}
	
	function pondreProjets($donnees)
	{
		if(!array_key_exists('expérience', $donnees)) return;
		
		$lignesDeTemps = $this->_lignesDeTemps($donnees);
		$minTemps = $maxTemps = null;
		$nGroupes = 0;
		foreach($lignesDeTemps as $segmentDeTemps)
		{
			if(!isset($minTemps) || $minTemps > $segmentDeTemps['debut'])
				$minTemps = $segmentDeTemps['debut'];
			if(!isset($maxTemps) || $maxTemps < $segmentDeTemps['fin'])
				$maxTemps = $segmentDeTemps['fin'];
			if($segmentDeTemps['groupe'] + 1 > $nGroupes)
				$nGroupes = $segmentDeTemps['groupe'] + 1;
		}
		
		$nom = 'Expérience et Projets';
?>
	<div class="section projets">
		<div class="audessus">
			<div class="" style="position: relative;">
				<div class="titresection" style="display: none;"> <!-- La première expérience est déjà bien encombrée par les fils d'ariane de compétences qui lui passent dessous, pour qu'on ajoute aussi le titre de section. -->
					<?php echo $nom ?>
				</div>
<?php
		echo '<svg id="jonctionlignestemps" style="position: absolute; width: '.(1.5 * $nGroupes + 1).'em; height: 100%; position: absolute;"></svg>'."\n";
		$premier = true;
		
		/* Tri. */
		
		$positions = $this->_positionsDebroussaillage($donnees->expérience->projet, $lignesDeTemps);
		
		arsort($positions, SORT_NUMERIC);
		
		$positionsDébut = $this->_triDébuts($donnees->expérience->projet);
		
		foreach($positions as $numProjet => $position)
		{
			$francheRigolade = $projet = $donnees->expérience->projet[$numProjet];
			echo '<div id="p'.$numProjet.'" class="projet'.($premier ? ' projetPremier' : '').'" style="margin-left: '.(1.5 * $nGroupes + 1).'em;">'."\n";
			if($premier) $premier = false;
			echo '<div class="dateexp">'.implode('; ', array_map(array($this, 'descriptionPeriode'), $projet->date)).'</div>'."\n";
			$sociétés = null;
			if(isset($positionsDébut[$numProjet]))
				echo '<a name="'.$positionsDébut[$numProjet].'"/>';
			echo '<div class="titreexp">';
			if(isset($francheRigolade->société))
				$sociétés = htmlspecialchars($francheRigolade->société[count($francheRigolade->société) - 1], ENT_NOQUOTES); // Seul le client final nous intéresse.
				//foreach(array_slice($francheRigolade->société, 1) as $société)
				//{
				//	$société = htmlspecialchars($société, ENT_NOQUOTES);
				//	$sociétés = $sociétés === null ? $société : $sociétés.', '.$société;
				//}
			isset($projet->nom) && $sociétés = htmlspecialchars($projet->nom, ENT_NOQUOTES).($sociétés ? ' ('.$sociétés.')' : '');
			if($sociétés) echo '<h3>'.$sociétés.'</h3>';
			if(isset($projet->description)) echo '<div class="descrexp">'.htmlspecialchars($sociétés ? ': '.$projet->description : pasTeX_maj($projet->description), ENT_NOQUOTES).'</div>'."\n";
			echo '</div>'."\n";
			
			echo '<div class="exp">';
			echo implode('', array_map(array($this, '_palier'), $projet->tâche));
			
			foreach($lignesDeTemps as $numSegment => $segmentDeTemps)
				if($segmentDeTemps['moment'] == $numProjet)
				{
					echo '<div id="p'.$numProjet.'s'.$numSegment.'" class="afftemps" style="top: '.(($maxTemps - $segmentDeTemps['fin']) / ($maxTemps - $minTemps) * 100).'%; height: '.(($segmentDeTemps['fin'] - $segmentDeTemps['debut']) / ($maxTemps - $minTemps) * 100).'%; left: '.(1.5 * $segmentDeTemps['groupe']).'em;">&nbsp;</div>';
				}
			
			echo '</div>'."\n";
			
			if(isset($projet->techno))
				echo '<div class="techno">'.htmlspecialchars(implode(', ', $projet->techno)).'</div>';
			/* À FAIRE: un machin qui fait que quand on passe la souris par dessus
			 * un projet, s'affichent les outils et technos utilisés. */
			echo '</div>'."\n";
		}
		$segmentsParLigne = array();
		foreach($lignesDeTemps as $numSegment => $segmentDeTemps)
			$segmentsParLigne[$segmentDeTemps['moment']][] = "'p".$segmentDeTemps['moment']."s$numSegment'";
		foreach($segmentsParLigne as $numProjet => & $refLibelleMoment)
			$refLibelleMoment = "p$numProjet:['p$numProjet',".implode(',', $refLibelleMoment).']';
		echo '<script type="text/javascript">LignesTemps.blocs = {'.implode(',', $segmentsParLigne).'};</script>';
		echo '<svg style="pointer-events: none; position: absolute; top: 0; width: '.(1.5 * $nGroupes + 1).'em; height: 100%; position: absolute;"><use id="jonctionAuDessus"/></svg>'."\n";
		echo '</div>'."\n";
		$this->terminerSection();
	}
	
	function pondreLangues($donnees)
	{
		if(!array_key_exists('langues', $donnees)) return;
		$this->commencerSection('Langues');
?>
		<table>
<?php
		foreach($donnees->langues->langue as $chat)
		{
?>
			<tr>
				<td class="categorie"><?php echo htmlspecialchars($chat->nom, ENT_NOQUOTES) ?></td>
				<td>
<?php
			echo htmlspecialchars($chat->niveau, ENT_NOQUOTES);
			$qqc = false;
			if(isset($chat->certificat) && count($chat->certificat) > 0)
			{
				foreach($chat->certificat as $certif)
					echo ($qqc ? ', ' : ' (').htmlspecialchars($certif, ENT_NOQUOTES);
				echo ')';
			}
?>
				</td>
			</tr>
<?php
		}
?>
		</table>
<?php
		$this->terminerSection();
	}
	
	function pondreConnaissances($donnees)
	{
		/* À FAIRE: un truc délire, où le PHP se contente de cracher les données,
		 * puis un JavaScript place chacune des connaissances dans un span, mesure
		 * sa place, lui attribue une abcisse, et en détermine l'ordonnée en
		 * fonction des autres connaissances qui pourraient la recouvrir du fait
		 * d'une abcisse trop proche.
		 * Le span possèdera le texte en contenu principal, et un point rouge centré
		 * qui indique sa place exacte sur l'échelle des connaissances. */
		
		if(!array_key_exists('connaissances', $donnees)) return;
		
		$seuils = array(0x0, 0x6, 0x9, 0x10);
		
		$this->commencerSection('Connaissances Informatiques', 'technos');
		$prems = true;
		foreach($donnees->connaissances->catégorie as $catégorie)
		{
			echo '<div class="soussection"'.($prems ? ' style="margin-top: 0px"' : '').'>'.htmlspecialchars($catégorie->nom, ENT_NOQUOTES).'</div>'."\n";
			$prems = false;
			for($i = count($seuils) - 1; --$i >= 0;)
			{
				$qqc = false;
				foreach($catégorie->connaissances as $nom => $valeur)
					if($valeur < $seuils[$i + 1] && $valeur >= $seuils[$i]) // Question existentielle: une fois placée la connaissance dans un niveau, faudrait-il l'y classer par rapport aux autres du niveau ou laisse-t-on dans l'ordre d'arrivée pour laisser à l'utilisateur un semblant de contrôle?
					{
						if(!$qqc)
						{
							echo '<div class="niveau">'.str_repeat('•', $i + 1).'</div>';
							echo '<div class="techno">';
							$qqc = true;
						}
						else
							echo ', ';
						echo htmlspecialchars($nom, ENT_NOQUOTES);
					}
				if($qqc)
					echo '</div>';
			}
		}
		$this->terminerSection();
	}
	
	function pondreIntro($données)
	{
		if(!array_key_exists('intro', $données)) return;
		
		$this->commencerSection('');
		foreach($données->intro as $intro) break;
		echo implode('<br/>', array_map('pasTeX_html', explode("\n", $intro)));
		$this->terminerSection();
	}
	
	function pondreInteret($donnees)
	{
		if(!array_key_exists('intérêts', $donnees)) return;
		
		$this->commencerSection('Domaines d\'expertise'); // Domaines d'intérêt, Domaines de compétences, Compétences, Domaines d'expertise?
		$premier = true;
		foreach($donnees->intérêts->domaine as $latechniqueamusante)
		{
			echo '<div class="projet'.($premier ? ' projetPremier' : '').'">'."\n";
			echo '<h3 class="titreexp">'.pasTeX_html($latechniqueamusante->nom).'</h3>'."\n";
			if($premier) $premier = false;
			$qqc = false;
			if(isset($latechniqueamusante->techno))
				foreach($latechniqueamusante->techno as $aquoicasert)
				{
					echo ($qqc ? ', ' : '<div class="exp">').pasTeX_html($aquoicasert);
					$qqc = true;
				}
			if($qqc)
				echo '</div>'."\n";
			echo '</div>'."\n";
		}
		$this->terminerSection();
	}

	function pondreAutres($donnees)
	{
		if(!array_key_exists('loisirs', $donnees)) return;
		
		$this->commencerSection('Autres activités et intérêts');
		foreach($donnees->loisirs->activité as $ouf)
			echo '<div class="paraindependant">'.htmlspecialchars($ouf, ENT_NOQUOTES).'</div>'."\n";
		$this->terminerSection();
		
	}
	
	function pondreLiens($donnees, $voulus)
	{
		$this->commencerSection('…');
		echo '<div style="text-align: center;"><table style="text-align: center; margin-left: auto; margin-right: auto;"><tr>';
		foreach($voulus as $voulu)
		{
			if(array_key_exists('p', $voulu)) // Lien vers du pasτεχ.
			{
				$url = ereg_replace('[^a-zA-Z]', '', $voulu['p'][0]); // Le module choisi doit se trouver en 0.
				$params = $GLOBALS['params'];
				unset($params['decompo']);
				$params['decompo'][$url] = $voulu['p'];
				$params['decompo'][$url][] = 1;
				$url = params_decomposer(null, $params, 0);
				if($url{0} == '&') $url = substr($url, 1);
				$url = basename($_SERVER['PHP_SELF']).'?'.$url;
			}
			else if(array_key_exists('u', $voulu))
				$url = $voulu['u'];
			$url = htmlspecialchars($url);
			echo '<td><div><a href="';
			if(array_key_exists('i', $voulu))
				echo $url.'"><img src="'.htmlspecialchars($voulu['i']).'" alt=""/></a></div><div><a href="';
			echo $url.'">'.htmlspecialchars($voulu['n']).'</a></div></td>';
		}
		echo '</tr></table></div>';
		$this->terminerSection();
	}
	
	protected $params;
	
	public function trad($chaîne)
	{
		if(!$chaîne)
			return $chaîne;
		putenv('LC_ALL='.$this->params['trad']);
		bindtextdomain('app', dirname(__FILE__).'/../../share/locale');
		textdomain('app');
		return gettext($chaîne);
	}
}

?>
