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

/* À FAIRE: savoir ne pas planter quand des champs sont absents. */

require_once('pasTeX.inc');
require_once('commun/ooo/ooo.inc');

class Altran
{
	public function analyserParams($argv, &$position)
	{
		$retour = array();
		while($position < count($argv))
		{
			switch($argv[$position])
			{
				case 'pdf': // À FAIRE: générer au besoin un .doc.
					$retour['pdf'] = 1;
					break;
				default:
					break 2;
			}
			++$position;
		}
		
		return $retour;
	}
	
	public function analyserChamps($champs)
	{
		/* Préparation du retour */
		
		$suffixe = $champs['pdf'] ? 'pdf' : 'docx';
		$type = $champs['pdf'] ? 'pdf' : 'vnd.openxmlformats-officedocument.wordprocessingml.document';
		header("Content-Disposition: attachment; filename=cv.".$suffixe);
		header("Content-Type: application/".$type);
		
		return $champs;
	}
	
	public function pondreInterface($champ)
	{
		ooo_pondreInterface($champ);
	}
	
	public function decomposer($params, $données)
	{
		require_once dirname(__FILE__).'/../../commun/camionneur.php';
		
		$this->_préparer($données);
		
		$this->_params = $params;
		$nomTemp = tempnam('/tmp', 'temp.openoffice.');
		$dossierTemp = $nomTemp.'.contenu';
		$modele = dirname(__FILE__).'/modele';
		system("cp -R '{$modele}' '{$dossierTemp}'");
		$patrons = array();
		$patrons = array_merge($patrons, glob($dossierTemp.'/*.pat'));
		$patrons = array_merge($patrons, glob($dossierTemp.'/*/*.pat'));
		$patrons = array_merge($patrons, glob($dossierTemp.'/*/*/*.pat'));
		foreach($patrons as $patron)
		{
			$cheminFinal = substr($patron, 0, -4);
			ob_start();
			$rf = new Camionneur($this->_zorglub);
			foreach(get_object_vars($données) as $nom => $val)
				$$nom = $val;
			include $cheminFinal.'.php';
			$content = ob_get_clean();
			file_put_contents($cheminFinal, $content);
			unlink($cheminFinal.'.php');
			unlink($cheminFinal.'.pat');
		}
		$sortie = @$this->_params['pdf'] ? $nomTemp.'.sortie.docx' : '-';
		system("( cd '{$dossierTemp}' && ( ( command -v zip > /dev/null && zip -r -q {$sortie} . ) || ( 7za a /tmp/temp.$$.zip . > /dev/null && cat /tmp/temp.$$.zip && rm /tmp/temp.$$.zip ) ) )");
		if(@$this->_params['pdf']) { ooo_enPDF($sortie); system("rm '{$sortie}'"); }
		system("rm -R '{$dossierTemp}' '{$nomTemp}'");
	}
	
	protected function _préparer(& $données)
	{
		if($données->perso->naissance)
		{
			$maintenant = obtenir_datation(time());
			$âge = $maintenant[0] - $données->perso->naissance[0];
			for($i = 1; $i < 6; ++$i) // Si l'on est avant la date d'anniversaire, on retire un an.
				if(($j = $maintenant[$i] - $données->perso->naissance[$i]) != 0)
				{
					if($j < 0)
						--$âge;
					break;
				}
			$données->perso->âge = $âge;
			$données->perso->ddn = periode_affDate($données->perso->naissance);
		}
		
		if($données->perso->adresse)
		{
			$adresse = array();
			foreach(get_object_vars($données->perso->adresse->données) as $bout)
				$adresse[] = $bout;
			$données->perso->adresse = $adresse;
		}
		
		$this->_trierProjets($données);
		
		$this->_zorglub->colorer($données->expérience->projet, null, 1.0, 0xbf, 0x3f); // 0x59 max chez eux, mais bon on a certains projets vraiment importants à faire ressortir.
	}
	
	protected function _comparerDatesPivot($a, $b)
	{
		return $b->pivot - $a->pivot;
	}
	
	protected function _affichagePériode($p)
	{
		return pasTeX_descriptionPeriode(Date::fem($p[0]), Date::fem($p[1]), Periode::$LE | Periode::$CHIFFRES | Periode::$JOUR_INSECABLE);
	}
	
	protected function _trierProjets(& $données)
	{
		$maintenant = obtenir_datation(time());
		foreach($données->expérience->projet as $num => $francheRigolade)
		{
			// Calcul du pivot (date centrale, plutôt vers la fin quand même).
			
			$moments = array();
			foreach($francheRigolade->date as $plage)
			{
				$f = $plage->f;
				if($f == array(-1, -1, -1, -1, -1, -1))
					$f = $maintenant;
				$moments[] = array($plage->d, $f);
			}
			$période = periode_union($moments);
			$pivot = (2 * Date::calculer($période[1]) + Date::calculer($période[0])) / 3;
			
			// Travail au mois (Celad ne s'intéresse pas aux jours), et regroupement.
			
			$mois = array();
			foreach($francheRigolade->date as $plage)
			{
				$d = array(2 => null, null, null, null) + Date::mef($plage->d);
				$f = array(2 => null, null, null, null) + Date::mef($plage->f);
				$mois[] = array($d, $f);
			}
			$mois = Periode::unionSi($mois);
			
			// Mise en forme et mémoire de tout ça.
			
			$données->expérience->projet[$num]->pivot = $pivot;
			$données->expérience->projet[$num]->quand = ucfirst(implode(', ', array_map(array($this, '_affichagePériode'), $mois)));
		}
		uasort($données->expérience->projet, array($this, '_comparerDatesPivot'));
	}
}
?>
