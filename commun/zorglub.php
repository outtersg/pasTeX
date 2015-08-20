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
	public function durée($projet)
	{
		$d = 0;
		if(isset($projet->date))
			foreach($projet->date as $plage)
				$d += pasTeX_durée($plage->d, $plage->f);
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
	
	/*- Niveau ---------------------------------------------------------------*/
	
	/* À FAIRE: les technos devraient voir leur niveau baisser au fil des années où elles sont délaissées (date dernière mention dans un projet). */
		
	/*- Poids ----------------------------------------------------------------*/
	
	const BRUT = 0; // Normalisation: aucune (on prend les poids tels quels).
	const TOTAL_1 = 1; // Normalisation: la somme des poids sur un tableau doit faire 1.
	const MAX_1 = 2; // Normalisation: le poids maximum sur un tableau est normalisé à 1.
	
	/* À FAIRE?: les projets devraient voir leur poids multiplié par leur durée. */
	
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
		if(isset($cv->formation->études))
			$this->_pondérerTableau($cv->formation->études);
		$this->_pondérerTableau($cv->expérience->projet);
		foreach($cv->expérience->projet as $projet)
		{
			isset($projet->tâche) && $this->_pondérerTableau($projet->tâche);
			isset($projet->techno) && $this->_pondérerTableau($projet->techno);
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
		
		$parPoids = array();
		foreach($t as $num => & $e)
		{
			if(isset($e->poids))
			{
				if(is_string($e->poids))
				{
					$poids = null;
					preg_match_all('#(?:([^= ]*)=)?([-.0-9]*) #', $e->poids.' ', $r);
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
			for($nParPoids = count($parPoids); --$nParPoids >= 0;)
				if($parPoids[$nParPoids][0] >= $e->poids)
					break;
			array_splice($parPoids, $nParPoids + 1, 0, array(array($e->poids, $num)));
		}
		
		/*- Différentiation -*/
		/* Les poids identiques sont légèrement décalés les uns par rapport aux autres afin que les tris qui seront appliqués semblent stables. */
		
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
}

?>
