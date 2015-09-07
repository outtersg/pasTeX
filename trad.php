<?php

// http://www.jobteaser.com/fr/conseils/30-bien-rediger-son-cv-en-anglais

class Trad
{
	public function tourne($argv)
	{
		$fichiers = array();
		for($i = 0; ++$i < count($argv);)
			$fichiers[] = $argv[$i];
		
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
			$this->_pondsTrad($fichiers[0], $fichiers[1], $fichiers[2]);
	}
	
	protected function _auSecours()
	{
		/* À FAIRE */
		exit(1);
	}
	
	protected function _pondsPatron($source, $trad)
	{
		$occurrences = $this->_analyse($source);
		foreach($occurrences[2] as $num => $contenu)
			if(!in_array($balise = $occurrences[1][$num][0], array('entre', 'et', 'date')))
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
	
	protected function _pondsTrad($source, $trad, $dest)
	{
	
	}
	
	protected function _analyse($cheminFichier)
	{
		$contenu = file_get_contents($cheminFichier);
		$contenu = preg_replace('#<!--([^-]+|-[^-]|--[^>])*-->#s', '', $contenu);
		preg_match_all('#<([^!> /]+)[^>]*>((?:<m(?: [^>]*|)>(?-1)</m>|[^<]+)+)</(?1)>#s', $contenu, $occurrences, PREG_OFFSET_CAPTURE);
		return $occurrences;
	}
}

$t = new Trad;
$t->tourne($argv);

?>
