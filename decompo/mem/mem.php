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

require_once dirname(__FILE__).'/../../commun/http/emetteur.inc';

class Mem extends Émetteur
{
	protected $_bosseurConstruit = true; // Si false, c'est qu'on l'a rechargé d'une version sérialisée.
	
	public function __construct()
	{
		parent::__construct('mem');
		$this->petitÀPetit = false; /* Dépend en fait de si on est en interf web ou ligne de commande. */
	}
	
	public function analyserParams($argv, & $position)
	{
		if(isset($argv[$position]))
		{
			$this->_chemin = $argv[$position];
			++$position;
			
			if(isset($argv[$position]))
			{
				$this->_bosseur = pasTeX_chargerDecompo($argv[$position]);
				++$position;
				return $this->_bosseur->analyserParams($argv, /*&*/ $position);
			}
			
			if(file_exists($this->_chemin))
			{
				$série = file_get_contents($this->_chemin);
				if(preg_match_all('/^O:[0-9]*:"([^"]*)"/', $série, /*&*/ $rés))
				{
					$this->_bosseurConstruit = false;
					pasTeX_chargerDecompo(strtolower($rés[1][0]));
					$this->_bosseur = unserialize($série);
					return array();
				}
			}
		}
				
		echo '# Sortie \'mem\': doit être suivie du nom du fichier qui fera mémoire. Pour un premier lancement, le nom et les paramètres du module de sortie à mémoriser sont à ajouter encore après; pour repartir de l\'état enregistré après le dernier lancement, ces paramètres suivants doivent au contraire être omis.'."\n";
		exit(1);
	}
	
	public function decomposer($derniersParams, $données)
	{
		if($this->_bosseurConstruit)
		{
			$this->_bosseur->preparerSession();
			$this->_bosseur->nouvelles = &$derniersParams;
			if($données !== null && !array_key_exists('clientdemandeur', $_SESSION['emetteur'])) $this->_bosseur->interfaceIndépendante = true; // Sinon c'est qu'on veut pondre une interface dans le cadre de pasτεχ, ou dans le cadre HTML créé par une précédente requête (en AJAX).
		}
		return parent::decomposer($derniersParams, $données);
	}
	
	public function avancerUnCoup($données)
	{
		$r = $this->_bosseur->avancerUnCoup($données);
		file_put_contents($this->_chemin, serialize($this->_bosseur));
		if(isset($this->_bosseur->enregistrerEtQuitter)) // Pour débogage.
			exit(0);
		return $r;
	}
	
	public function étapes() { return $this->_bosseur->étapes(); }
	public function manquant() { return $this->_bosseur->manquant(); }
}

?>
