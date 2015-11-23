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
		system("( cd '{$dossierTemp}' && ( ( command -v zip > /dev/null && zip -r -q {$sortie} . ) || ( 7za a /tmp/temp.$$.zip . > /dev/null && cat /tmp/temp.$$.zip && rm /tmp/temp.$$.zip ) ) )"); // Attention, 7za génère un ZIP avec une version non reconnue par Word 2010.
		if(@$this->_params['pdf']) { ooo_enPDF($sortie); system("rm '{$sortie}'"); }
		system("rm -R '{$dossierTemp}' '{$nomTemp}'");
	}
	
	protected function _préparer(& $données)
	{
		if(isset($données->salaire))
		{
			$total = 0;
			foreach(array('brut', 'variable', 'intéressement', 'participation') as $somme)
				if(isset($données->salaire->$somme))
					$total += $données->salaire->$somme;
			$données->salaire->total = $total;
		}
	}
}

?>
