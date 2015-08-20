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
	
	/* À FAIRE?: les projets devraient voir leur poids multiplié par leur durée. */
	
	public $trier = true;
	
	public function pondérer($cv)
	{
		if(!isset($this->profil))
			$this->profil = '';
		$this->_compteursProfils = array();
		
		/*- Calcul des poids -*/
		
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
				isset($catégorie->maîtrise) && $this->_pondérerTableau($catégorie->maîtrise, false);
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
					if(!isset($maîtrises[$techno->__toString()]))
						fprintf(STDERR, "# Attention, utilisation de la techno $techno, non déclarée dans les connaissances.\n");
					else
						$techno->poids *= $maîtrises[$techno->__toString()];
				$this->_ordonnerPoids($projet->techno);
			}
		
		/*- Alertes -*/
		
		foreach($this->_compteursProfils as $profil => $n)
			if($n < 4)
				fprintf(STDERR, "# Attention, le profil $profil n'est utilisé que $n fois dans votre CV. Est-ce une erreur de frappe?\n");
	}
	
	protected function _pondérerTableau(& $t, $surÉchelle1 = true)
	{
		/*- Recherche des poids, selon le profil mentionné -*/
		
		$nSansPoids = 0;
		$poidsMax = 0.8; // En fait poids maximum recensé avant 1. Pour toutes les valeurs indéfinies, on essaiera de les répartir entre cette valeur et 1 (histoire qu'elles soient en dessous du défaut, mais au-dessus de tout poids qui aurait été affecté volontairement "mauvais" à un objet).
		foreach($t as & $e)
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
				++$nSansPoids;
			else if($poidsMax < $e->poids && $e->poids < 1.0)
				$poidsMax = $e->poids;
		}
		
		/*- Remplissage des poids absolus -*/
		
		$n = 0;
		$total = 0.0;
		foreach($t as & $e)
		{
			if(!isset($e->poids))
			{
				if(is_string($e))
					$e = new Texte($e);
				$e->poids = 1.0 - $n * (1.0 - $poidsMax) / $nSansPoids; // Le premier sans poids se voit affecter un poids de 1, le second de mettons 0.98, le suivant de 0.96, puis 0.94, etc. Ainsi ils restent ordonnés les uns par rapport aux autres.
				++$n;
			}
			$total += $e->poids;
		}
		
		/*- Mise à l'échelle -*/
		
		if($surÉchelle1 && $total)
			foreach($t as & $e)
				$e->poids /= $total;
		
		/*- Présentation -*/
		
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
