<?php

// http://www.jobteaser.com/fr/conseils/30-bien-rediger-son-cv-en-anglais

class Trad
{
	public function tourne($argv)
	{
		$fichiers = array();
		$forcer = false;
		for($i = 0; ++$i < count($argv);)
			switch($argv[$i])
			{
				case '-f':
					$forcer = true;
					break;
				default:
			$fichiers[] = $argv[$i];
					break;
			}
		
		foreach(array('.xml', '.trad', '.xml') as $numFichier => $suffixe)
			if($numFichier < count($fichiers) && substr($fichiers[$numFichier], -strlen($suffixe)) != $suffixe)
				$this->_auSecours();
		if(!in_array(count($fichiers), array(2, 3)))
			$this->_auSecours();
		if(count($fichiers) == 3 && $fichiers[0] == $fichiers[2])
			$this->_auSecours('Entrée et sortie ne doivent pas être identiques');
		
		if(count($fichiers) == 2)
			$this->_pondsPatron($fichiers[0], $fichiers[1]);
		else
			$this->_pondsTrad($fichiers[0], $fichiers[1], $fichiers[2], $forcer);
	}
	
	protected function _auSecours()
	{
		/* À FAIRE */
		exit(1);
	}
	
	const A_BALISE = 1;
	const A_ATTRS = 2;
	const A_CONTENU = 3;
	
	const L_POS_BLOC = 0;
	const L_BALISE = 1;
	const L_ATTRS = 2;
	const L_CONTENU = 3;
	const L_UTILISÉ = 4;
	const L_TAILLE_BLOC = 5;
	
	protected function _pondsPatron($source, $trad)
	{
		$occurrences = $this->_analyse($source);
		foreach($occurrences[self::A_CONTENU] as $num => $contenu)
			if(!in_array($balise = $occurrences[self::A_BALISE][$num][0], array('entre', 'et', 'date')))
			{
				$clé = $balise.'|'.$contenu[0];
				$bouts[$clé] = true; // Pour avoir l'unicité, et dans l'ordre dans lequel on a trouvé les entrées dans le fichier.
			}
		
		if(file_exists($trad))
			throw new Exception("Le fichier $trad existe déjà, je refuse de l'écraser.");
		
		$sortie = fopen($trad, 'w');
		fwrite($sortie, '<'.'?xml version="1.0" encoding="UTF-8"?'.'>'."\n");
		fwrite($sortie, '<traductions>'."\n");
		foreach($bouts as $bout => $rien)
		{
			$pos = strpos($bout, '|');
			$balise = substr($bout, 0, $pos);
			$contenu = substr($bout, $pos + 1);
			fwrite($sortie, '	<'.$balise.' trad="src">'.$contenu.'</'.$balise.'>'."\n");
			fwrite($sortie, '	<'.$balise.' trad="non">'.$contenu.'</'.$balise.'>'."\n");
		}
		fwrite($sortie, '</traductions>'."\n");
		fclose($sortie);
	}
	
	protected function _pondsTrad($source, $trad, $dest, $forcer = false)
	{
		$originaux = $this->_analysePosEtAttr($source, true);
		$traductions = $this->_traductions($trad);
		
		if(file_exists($dest) && !$forcer)
			throw new Exception("Le fichier $dest existe déjà, je refuse de l'écraser.");
		
		$original = $originaux['contenu'];
		unset($originaux['contenu']);
		$dernièrePosÉcrite = 0;
		
		$sortie = fopen($dest, 'w');
		foreach($originaux as $élémOriginal)
		{
			if(!isset($traductions[$élémOriginal[self::L_BALISE]][$élémOriginal[self::L_CONTENU]]))
			{
				$this->_alerte('fragment sans traduction: '.$élémOriginal[self::L_CONTENU]);
				continue;
			}
			
			$ptrTraduction = & $traductions[$élémOriginal[self::L_BALISE]][$élémOriginal[self::L_CONTENU]];
			$ptrTraduction[self::L_UTILISÉ] = true;
			$attrs = array_diff_key($ptrTraduction[self::L_ATTRS], array('trad' => false)) + $élémOriginal[self::L_ATTRS];
			$chaîneAttrs = '';
			foreach($attrs as $attr => $val)
				$chaîneAttrs .= ' '.$attr.'="'.$val.'"';
			$traduction = '<'.$ptrTraduction[self::L_BALISE].$chaîneAttrs.'>'.$ptrTraduction[self::L_CONTENU].'</'.$ptrTraduction[self::L_BALISE].'>';
			
			fwrite($sortie, substr($original, $dernièrePosÉcrite, $élémOriginal[self::L_POS_BLOC] - $dernièrePosÉcrite));
			fwrite($sortie, $traduction);
			
			$dernièrePosÉcrite = $élémOriginal[self::L_POS_BLOC] + $élémOriginal[self::L_TAILLE_BLOC];
		}
		fwrite($sortie, substr($original, $dernièrePosÉcrite));
		fclose($sortie);
		
		/* On liste les traductions qui n'ont pas servi. */
		/* À FAIRE: un levenshtein pour les mettre en rapport avec les traductions en trop (si la source a bougé). */
		
		foreach($traductions as $balise => $sousTraductions)
			foreach($sousTraductions as $traduction)
				if(!isset($traduction[self::L_UTILISÉ]))
					$this->_alerte('traduction inutilisée: '.$traduction[self::L_CONTENU]);
	}
	
	protected function _analyse($cheminFichier, $avecContenu = false)
	{
		$contenu = file_get_contents($cheminFichier);
		$contenu = preg_replace('#<!--([^-]+|-[^-]|--[^>])*-->#s', '', $contenu);
		preg_match_all('#<([^!> /]+)([^>]*)>((?:<m(?: [^>]*|)>(?-1)</m>|[^<]+)+)</(?1)>#s', $contenu, $occurrences, PREG_OFFSET_CAPTURE);
		if($avecContenu)
			$occurrences['contenu'] = $contenu; // Le contenu filtré.
		return $occurrences;
	}
	
	protected function _analysePosEtAttr($cheminFichier, $avecContenu = false)
	{
		$r = array();
		$bruts = $this->_analyse($cheminFichier, $avecContenu);
		foreach($bruts[self::A_BALISE] as $numBrut => $balise)
		{
			$balise = $balise[0];
			if(in_array($balise, array('entre', 'et', 'date')))
				continue;
			
			$attrs = array();
			preg_match_all('#([^ =]*)="([^"]*)"#', $bruts[self::A_ATTRS][$numBrut][0], $trouvés);
			foreach($trouvés[1] as $numAttr => $attr)
				$attrs[$attr] = $trouvés[2][$numAttr];
			
			$contenu = $bruts[self::A_CONTENU][$numBrut][0];
			$r[] = array
			(
				self::L_POS_BLOC =>    $bruts[0][$numBrut][1],
				self::L_BALISE =>      $balise,
				self::L_ATTRS =>       $attrs,
				self::L_CONTENU =>     $contenu,
				self::L_TAILLE_BLOC => strlen($bruts[0][$numBrut][0]),
			);
		}
		if($avecContenu)
			$r['contenu'] = $bruts['contenu'];
		return $r;
	}
	
	protected function _traductions($chemin)
	{
		$r = array();
		$entrées = $this->_analysePosEtAttr($chemin);;
		
		// La traduction est l'entrée qui suit une entrée de type 'src'.
		$dernièreSource = null;
		foreach($entrées as $entrée)
			if($entrée[self::L_ATTRS]['trad'] == 'src')
				$dernièreSource = $entrée;
			else
			{
				$r[$entrée[self::L_BALISE]][$dernièreSource[self::L_CONTENU]] = $entrée;
				unset($dernièreSource);
			}
		
		return $r;
	}
	
	protected function _alerte($quoi)
	{
		fprintf(STDERR, '# '.strtr($quoi, array("\n" => "\n# ")).(substr($quoi, -1) == "\n" ? '' : "\n"));
	}
}

if(!isset($GLOBALS['compo']))
{
$t = new Trad;
$t->tourne($argv);
}

?>
