<?php
/*
 * Copyright (c) 2015 Guillaume Outters
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

/**
 * Notre statisticien fou, qui effectue toute sorte de calculs sur les CV.
 */
class Zorglub
{
	public $profil;
	/** Liste d'objets dotés d'un poids public */
	public $ids = array();
	
	/*- Dates ----------------------------------------------------------------*/
	
	public function durée($projet, $brut = false)
	{
		$d = 0;
		if(isset($projet->date))
			foreach($projet->date as $plage)
				$d += pasTeX_durée($plage->d, $plage->f);
		if($brut)
			return $d;
		if($d > 0)
			return pasTeX_affDurée($d);
	}
	
	public function année($projet)
	{
		$périodes = array();
		foreach($projet->date as $période)
			$périodes[] = array($période->d, $période->f);
		$période = periode_union($périodes);
		if($période[1][0] == -1)
			$période[1] = Date::obtenir(time());
		$période[0] = array($période[0][0], -1, -1, -1, -1, -1);
		$période[1] = array($période[1][0], -1, -1, -1, -1, -1);
		return Periode::aff(Date::mef($période[0]), Date::mef($période[1]));
	}
	
	/**
	 * Nombre d'années depuis lequel le projet s'est terminé.
	 */
	public function obsolescence($projet)
	{
		if(!isset($projet->date))
			return 5;
		$période = pasTeX_unionPeriodes($projet->date);
		$fin = $période[1];
		if($fin == array(-1, -1, -1, -1, -1, -1))
			$années = 0.0;
		else
		{
			// Fin 2012, c'est tout comme le premier janvier 2013.
			for($i = 6; --$i >= 0;)
				if($fin[$i] >= 0)
				{
					++$fin[$i];
					break;
				}
			$fin = calculer_datation($fin);
			$années = (time() - $fin) / (3600 * 24 * 365.25);
		}
		return $années;
	}
	
	/*- Tri sur date ---------------------------------------------------------*/
	
	public function trierParFin($cv)
	{
		foreach($cv->expérience->projet as $numProjet => $projet)
		{
			$projet->_min = null;
			$projet->_max = null;
			foreach($projet->date as $moment)
			{
				if($projet->_min > ($date = Date::calculerAvecIndefinis(Date::mef($moment->d), false)) || !isset($projet->_min))
					$projet->_min = $date;
				if($projet->_max < ($date = Date::calculerAvecIndefinis(Date::mef($moment->f), true)) || !isset($projet->_max))
					$projet->_max = $date;
			}
		}
		uasort($cv->expérience->projet, function($a, $b) {  return $b->_max - $a->_max ? $b->_max - $a->_max : $b->_min - $a->_min; });
	}
	
	/*- Niveau ---------------------------------------------------------------*/
	
	/* À FAIRE: les technos devraient voir leur niveau baisser au fil des années où elles sont délaissées (date dernière mention dans un projet). */
		
	/*- Poids ----------------------------------------------------------------*/
	
	const BRUT = 0; // Normalisation: aucune (on prend les poids tels quels).
	const TOTAL_1 = 1; // Normalisation: la somme des poids sur un tableau doit faire 1.
	const MAX_1 = 2; // Normalisation: le poids maximum sur un tableau est normalisé à 1.
	
	public $trier = true;
	
	/**
	 * Replace sur une échelle (type échelle de couleur) les divers éléments en fonction de leur poids.
	 */
	public function colorer(& $tableau, $min, $max, $couleurMin = 0.5, $couleurMax = 0.0)
	{
		if($min === null)
			foreach($tableau as $e)
				if(!isset($min) || $e->poids < $min)
					$min = $e->poids;
		if($max === null)
			foreach($tableau as $e)
				if(!isset($max) || $e->poids > $max)
					$max = $e->poids;
		foreach($tableau as $e)
			if($e->poids <= $min)
				$e->couleur = $couleurMin;
			else if($e->poids >= $max)
				$e->couleur = $couleurMax;
			else
				$e->couleur = $couleurMin + ($couleurMax - $couleurMin) * ($e->poids - $min) / ($max - $min);
	}
	
	public function pondérer($cv)
	{
		if(!isset($this->profil))
			$this->profil = '';
		$this->_compteursProfils = array();
		
		/*- Calcul des poids -*/
		
		if(is_array($cv->titre))
		{
			$this->_pondérerTableau($cv->titre);
			$cv->titre = array_shift($cv->titre); // Seul le premier nous intéresse (bon, et puis on y est obligés par compatibilité historique).
		}
		if(isset($cv->intro))
			$this->_pondérerTableau($cv->intro);
		if(isset($cv->formation->études))
			$this->_pondérerTableau($cv->formation->études);
		$this->_pondérerTableau($cv->expérience->projet);
		foreach($cv->expérience->projet as $projet)
		{
			isset($projet->rôle) && $this->_pondérerTableau($projet->rôle);
			isset($projet->tâche) && $this->_pondérerTableau($projet->tâche);
			isset($projet->techno) && $this->_pondérerTableau($projet->techno, self::TOTAL_1);
		}
		if(isset($cv->connaissances->catégorie))
			foreach($cv->connaissances->catégorie as $catégorie)
				isset($catégorie->maîtrise) && $this->_pondérerTableau($catégorie->maîtrise, self::BRUT, false);
		if(isset($cv->intérêts->domaine))
		{
			$this->_pondérerTableau($cv->intérêts->domaine);
			foreach($cv->intérêts->domaine as $domaine)
				isset($domaine->techno) && $this->_pondérerTableau($domaine->techno);
		}
		if(isset($cv->motivation))
		{
			$this->_pondérerTableau($cv->motivation);
			$cv->motivation = array_shift($cv->motivation);
		}
		
		/*- Calcul du poids des technos -*/
		
		$maîtrises = array();
		if(isset($cv->connaissances->catégorie))
			foreach($cv->connaissances->catégorie as $catégorie)
				if(isset($catégorie->maîtrise))
					foreach($catégorie->maîtrise as $maîtrise => $connaissance)
						$maîtrises[$maîtrise] = $connaissance->poids;
		
		/*- Mise en avant des technos "projet" jugées intéressantes au global -*/
		
		foreach($cv->expérience->projet as $projet)
			if(isset($projet->techno))
			{
				foreach($projet->techno as $techno)
				{
					$réf = isset($techno->réf) ? $techno->réf : $techno->__toString();
					if(!isset($maîtrises[$réf]))
						fprintf(STDERR, "# Attention, utilisation de la techno $techno, non déclarée dans les connaissances.\n");
					else
						$techno->poids *= $maîtrises[$réf] > 0.1 ? $maîtrises[$réf] : 0.1; // On laisse aux technos délaissées une petite chance de figurer dans les projets.
				}
				$this->_ordonnerPoids($projet->techno);
			}
		
		/* Maintenant que les maîtrises ont été réinjectées dans les projets, on peut virer les technos à 0. */
		
		if(isset($cv->connaissances->catégorie))
			foreach($cv->connaissances->catégorie as $catégorie)
				if(isset($catégorie->maîtrise))
				{
					$this->_pondérerTableau($catégorie->maîtrise, self::BRUT);
					// Au passage, on reflète ce nouveau tri sur les connaissances (tableau simple, maintenu pour compatibilité avec certains vieux décompos).
					$catégorie->connaissances = array();
					foreach($catégorie->maîtrise as $nom => $maîtrise)
						$catégorie->connaissances[$nom] = $maîtrise->niveau;
				}
		
		/*- Alertes -*/
		
		foreach($this->_compteursProfils as $profil => $n)
			if($n < 4)
				fprintf(STDERR, "# Attention, le profil $profil n'est utilisé que $n fois dans votre CV. Est-ce une erreur de frappe?\n");
	}
	
	protected function _pondérerTableau(& $t, $normalisation = self::MAX_1, $ordonnerEtCouper = true)
	{
		/*- Recherche des poids, selon le profil mentionné -*/
		
		foreach($t as $num => & $e)
		{
			if(isset($e->id)) $this->ids['#'.$e->id] = $e;
			if(isset($e->poids))
			{
				if(is_string($e->poids))
				{
					$poids = null;
					preg_match_all('#(?:([^= ]*)[=:])?([-.0-9]*) #', $e->poids.' ', $r);
					foreach($r[2] as $n => $val)
					{
						if((!$r[1][$n] && !isset($poids)) || $this->profil == $r[1][$n]) // Si l'on n'a as encore trouvé de poids spécifique au profil, ou si le profil du poids regardé est celui pour lequel on travaille.
							$poids = $val;
						// On compte aussi les poids par profil.
						if(!isset($this->_compteursProfils[$r[1][$n]]))
							$this->_compteursProfils[$r[1][$n]] = 0;
						++$this->_compteursProfils[$r[1][$n]];
					}
					$poids == '' && $poids = null; // Une mention p="3 toto=" signifie que par défaut on donne un poids de 3, mais qu'avec le profil toto on veut retourner dans le bain des "sans poids".
					$e->poids = $poids;
				}
			}
			if(!isset($e->poids))
			{
				if(is_string($e))
					$e = new Texte($e);
				$e->poids = 1.0;
			}
		}
		
		unset($e);
		
		/*- Différenciation -*/
		/* Les poids identiques sont légèrement décalés les uns par rapport aux autres afin que les tris qui seront appliqués semblent stables. */
		/* À FAIRE?: un poids centre de gravité, pour que, de deux expériences de même poids, la plus ancienne (qui était probablement en fin de comète lorsque la seconde a émergé) soit classée plus bas. */
		
		$parPoids = array();
		foreach($t as $num => $e)
		{
			for($nParPoids = count($parPoids); --$nParPoids >= 0;)
				if($parPoids[$nParPoids][0] >= $e->poids)
					break;
			array_splice($parPoids, $nParPoids + 1, 0, array(array($e->poids, $num)));
		}
		
		if(count($parPoids))
		{
			$dernier = end($parPoids);
			$précédentPoids = 0.0;
			for($n = count($parPoids); --$n >= 0;)
			{
				// Combien de membres successifs ont-ils même poids?
				for($m = $n; --$m >= 0 && $parPoids[$m][0] == $parPoids[$n][0];) {}
				if($m < $n - 1) // Plus d'un, il faut donc les départager.
				{
					$plagePoids = min(0.2, ($parPoids[$n][0] - $précédentPoids) / 2.0); // On va pouvoir les répartir entre la valeur et la valeur - 0.2… sauf si ça empiéterait sur les "plus petits", auquel cas on essaie de se glisser entre notre valeur "voulue" et l'inférieur immédiat.
					$précédentPoids = $parPoids[$n][0];
					++$m;
					$décrément = $plagePoids / ($n - $m);
					++$n;
					while(--$n > $m) // Pas de décrément pour l'élément lui-même, on s'arrête donc en $m + 1.
						$t[$parPoids[$n][1]]->poids -= ($n - $m) * $décrément;
				}
			}
		}
		
		/*- Remplissage des poids absolus -*/
		
		$total = 0.0;
		foreach($t as & $e)
		{
			if($normalisation == self::TOTAL_1)
			$total += $e->poids;
			else if($normalisation == self::MAX_1)
				if($e->poids > $total)
					$total = $e->poids;
		}
		
		/*- Mise à l'échelle -*/
		
		if($normalisation && $total)
			foreach($t as & $e)
				$e->poids /= $total;
		
		/*- Présentation -*/
		
		if($ordonnerEtCouper)
		$this->_ordonnerPoids($t);
	}
	
	protected function _ordonnerPoids(& $t)
	{
		/*- Suppression des 0 -*/
		
		foreach($t as $n => & $e)
			if($e->poids == 0.0)
				unset($t[$n]);
		
		/*- Tri -*/
		
		if($this->trier)
		uasort($t, array($this, '_compPoids'));
	}
	
	public function _compPoids($x, $y)
	{
		return $x->poids >= $y->poids ? -1 : 1;
	}
	
	/*- Poids tempéré par le temps -------------------------------------------*/
	
	public function poidsGlobalTechnos($cv)
	{
		$poids = array();
		foreach($cv->expérience->projet as $projet)
		{
			$durée = $this->durée($projet, true);
			$éloignement = $this->obsolescence($projet);
			$obso = 0.35 * exp(1 - 0.3 * $éloignement);
			if(isset($projet->techno))
			{
				foreach($projet->techno as $techno)
				{
					$réf = isset($techno->réf) ? $techno->réf : $techno->__toString();
					if(!isset($poids[$réf])) $poids[$réf] = 0.0;
					// Poids ajouté par une techno utilisée sur un projet = produit de:
					// - pourcentage de la techno sur le projet * importance de la techno mentionnée dans la section "connaissances" (supposé déjà appliqué)
					// - durée du projet (en fait non: on considère que les projets longs auront un poids approprié)
					// - poids du projet
					// - facteur de décrépitude (plus ça fait longtemps que l'on n'est plus sur le projet, moins la techno est d'actualité)
					// On suppose que les technos projet ont été déjà multipliées par les technos connaissance, et égalisée en mode TOTAL_1.
					$poids[$réf] += $techno->poids /* * $durée */ * $projet->poids * $obso;
				}
			}
		}
		
		arsort($poids, SORT_NUMERIC);
		
		return $poids;
	}
	
	public function poidsGlobalMétiers($cv)
	{
		$poids = array();
		foreach($cv->expérience->projet as $projet)
		{
			$durée = $this->durée($projet, true);
			$éloignement = $this->obsolescence($projet);
			$obso = 0.35 * exp(1 - 0.3 * $éloignement);
			if(isset($projet->domaine))
			{
				isset($poids[$projet->domaine]) || $poids[$projet->domaine] = 0.0;
				$poids[$projet->domaine] += $projet->poids * $obso;
			}
		}
		
		arsort($poids, SORT_NUMERIC);
		
		return $poids;
	}
	
	protected $_compteursProfils;
}

?>
