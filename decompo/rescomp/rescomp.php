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

/**
 * Réseaux & Compétences, cabinet de recrutement avec un dossier de candidature fouillé et intéressant (mais en .doc, même pas x).
 */
class Rescomp
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
		system("cp -R '{$modele}' '{$dossierTemp}' ; find '{$dossierTemp}' -name .\\*.swp -exec rm {} \\;");
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
		if(isset($données->perso->photo))
			copy($données->perso->photo, $dossierTemp.'/word/media/photo.jpeg');
		system("( cd '{$dossierTemp}' && ( ( command -v zip > /dev/null && zip -r -q {$sortie} . ) || ( 7za a /tmp/temp.$$.zip . > /dev/null && cat /tmp/temp.$$.zip && rm /tmp/temp.$$.zip ) ) )"); // Attention, 7za génère un ZIP avec une version non reconnue par Word 2010.
		if(@$this->_params['pdf']) { ooo_enPDF($sortie); system("rm '{$sortie}'"); }
		system("rm -R '{$dossierTemp}' '{$nomTemp}'");
	}
	
	protected function _préparer(& $données)
	{
		if(isset($données->salaire))
		{
			if(is_array($données->salaire))
				$données->salaire = $données->salaire[0];
			$total = 0;
			foreach(array('brut', 'variable', 'intéressement', 'participation') as $somme)
				if(isset($données->salaire->$somme))
					$total += $données->salaire->$somme;
			$données->salaire->total = $total;
		}

		$this->_zorglub->trierParFin($données);

		$sDate = new Date;
		foreach($données->expérience->projet as & $projet)
		{
			$projet->mois = new stdClass;
			foreach(array('_min' => 'd', '_max' => 'f') as $champSecondes => $champMois)
			{
				$date = $sDate->obtenir($projet->$champSecondes);
				$projet->mois->$champMois = sprintf("%02d/%02d", $date[1], $date[0]);
			}
		}
		unset($projet);

		/* Morale et faits marquants. */

		$leTout = array();

		if(isset($données->personnalité->carrière->morale))
			$leTout = array_merge($leTout, $données->personnalité->carrière->morale);
		if(isset($données->personnalité->carrière->marquant))
			$leTout = array_merge($leTout, $données->personnalité->carrière->marquant);
		foreach(array_reverse($données->expérience->projet) as $projet)
		{
			$libellé = array();
			if(isset($projet->société))
			{
				$nom = null;
				foreach($projet->société as $nom) {}
				$libellé[] = $nom;
			}
			if(isset($projet->nom))
				$libellé[] = $projet->nom;
			$libellé = implode(': ', $libellé);
			foreach(array('morale', 'marquant') as $chose)
				if(isset($projet->$chose))
					foreach($projet->$chose as $élément)
						$leTout[] = '['.$libellé.'] '.$élément;
		}

		$données->personnalité->carrière->moraleEtFaitsMarquants = $leTout;
		
		/* Photo. */

		if(isset($données->perso->photo))
		{
			$image = imagecreatefromjpeg($données->perso->photo);
			$l = imagesx($image);
			$h = imagesy($image);
			imagedestroy($image);

			if(!is_object($données->perso->photo))
				$données->perso->photo = new Texte($données->perso->photo);
			$données->perso->photo->h = $h;
			$données->perso->photo->l = $l;
		}

		/* Connaissances. */

		$toutes = array();
		foreach($données->connaissances->catégorie as $catégorie)
			foreach($catégorie->connaissances as $connaissance => $niveau)
				if($niveau >= 5)
					$toutes[$connaissance] = true;
		foreach($toutes as $toute => $non)
			$toutes[preg_replace('/[^a-z]/', '', strtolower($toute))] = true;
		$données->connaissances->toutes = $toutes;

		$demandées = array
		(
			array
			(
				'Symfony2' => false,
				'Html5' => false,
				'CSS3' => false,
				'Javascript' => false,
				'JQuery' => false,
				'Linux' => false,
				'LESS' => false,
				'Bootstrap' => false,
			),
			array
			(
				'Nodejs' => false,
				'Behat' => false,
				'MYSQL' => false,
				'VSphere' => false,
				'Docker' => false,
				'AngularJs' => false,
				'ASP Classic' => false,
				'API.net MVC' => false,
			),
		);
		foreach($demandées as & $colonneDemandée)
			foreach($colonneDemandée as $technoDemandée => & $ouiOuNon)
			{
				$technoDemandée = preg_replace('/[^a-z]/', '', strtolower($technoDemandée));
				$ouiOuNon = isset($toutes[$technoDemandée]) ? true : false;
			}
		$données->connaissances->demandées = $demandées;

		/* Métaphores. */

		$données->personnalité->métaphores = array();
		foreach(array
		(
			'musique' => 'Une musique',
			'climat' => 'Un climat',
			'véhicule' => 'Un véhicule',
			'plat' => 'Un plat',
			'couleur' => 'Une couleur',
		) as $champ => $aff)
		{
			foreach($données->personnalité->métaphore as $méta)
				if($méta->comme == $champ)
				{
					$métaphore = $méta;
					$métaphore->libellé = $aff;
					$données->personnalité->métaphores[] = $métaphore;
				}
		}

		/* Perso. */

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
	}
}

?>
