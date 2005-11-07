<?php
/*
 * Copyright (c) 2005 Guillaume Outters
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

require_once('util/module.inc');

class Liste
{
	function Liste() {}
	
	function analyserParams($argv, &$position)
	{
		echo 'Modules d\'export installés:'."\n";
		foreach($this->modules() as $module)
			echo '  '.$module."\n";
		return null;
	}
	
	function analyserChamps($params)
	{
		/* On aura besoin de ça pour la suite (cf. decomposer()). */
		
		html_session();
		
		/* Génération de la page d'interface pour le choix du décompositeur. */
		
		pasTeX_interfaceModules(array('id' => 'decompo', 'aff' => 'Modèle', 'modules' => $this->modules(), 'chargeur' => pasTeX_chargerDecompo, 'champs' => array('compo[session]', 1), 'bouton' => 'Pondre'));
		
		return array();
	}
	
	function decomposer($params, $donnees)
	{
		/* On garde en mémoire les données, afin que, le formulaire validé, le
		 * décompositeur choisi par l'utilisateur puisse les retrouver. */
		
		$_SESSION['donnees'] = $donnees;
		return $this;
	}
	
	function modules()
	{
		$retour = array();
		$modules = module_liste(dirname(dirname(__FILE__)), 1, '.php');
		foreach($modules as $module)
			if($module != 'liste' && $module != 'rien')
				$retour[] = $module;
		return $retour;
	}
}

?>
