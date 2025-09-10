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

/* À FAIRE: savoir ne pas planter quand des champs sont absents. */

require_once('pasTeX.inc');
require_once('commun/ooo/ooo.inc');

class OpenOffice
{
	function analyserParams($argv, &$position)
	{
		$retour = array();
		while($position < count($argv))
		{
			switch($argv[$position])
			{
				case 'en':
				case 'un':
				case 'une':
				case 'le':
				case 'la':
				case 'joli':
				case 'avec':
				case 'sorte':
				case 'de':
				case 'espèce': // UTF-8 NFD
				case 'espèce': // UTF-8 NFC. Maintenant si PHP est capable de reconnaître les deux dans la même chaîne, je ne demande pas mieux.
					break;
				case 'logo':
				case 'truc':
					if(++$position >= count($argv)) { /* À FAIRE: aide en ligne sur la sortie d'erreur. D'ailleurs, passer toutes les aides dessus (Liste, en particulier). */return null; };
					$retour['logo'] = $argv[$position];
					break;
				case 'pdf':
					$retour['pdf'] = 1;
					break;
				default:
					break 2;
			}
			++$position;
		}
		
		return $retour;
	}
	
	function analyserChamps($champs)
	{
		/* Préparation du retour */
		
		$suffixe = $champs['pdf'] ? 'pdf' : 'sxw';
		$type = $champs['pdf'] ? 'pdf' : 'x-starwriter';
		header("Content-Disposition: attachment; filename=cv.".$suffixe);
		header("Content-Type: application/".$type);
		
		return $champs;
	}
	
	function pondreInterface($champ)
	{
		ooo_pondreInterface($champ);
	}
	
	function decomposer($params, $donnees)
	{
		$this->_params = $params;
		$nomTemp = tempnam('/tmp', 'temp.openoffice.');
		$dossierTemp = $nomTemp.'.contenu';
		$modele = dirname(__FILE__).'/modele';
		system("cp -R '{$modele}' '{$dossierTemp}'");
		if(@$this->_params['logo']) system("cp '{$this->_params['logo']}' '{$dossierTemp}/ObjBFFFC666'");
		if(isset($donnees->perso->photo)) copy($donnees->perso->photo, $dossierTemp.'/Photo.jpg');
		system("cat '{$dossierTemp}/content.pre.xml' > '{$dossierTemp}/content.xml'");
		$fichier = popen("tr -d '\\011\\012' >> '{$dossierTemp}/content.xml'", 'w');
		$this->pondreEntete($fichier, $donnees, @$params['boite']);
		$this->pondreProjets($fichier, $donnees);
		$this->pondreEtudes($fichier, $donnees);
		$this->pondreLangues($fichier, $donnees);
		$this->pondreConnaissances($fichier, $donnees);
		$this->pondreInteret($fichier, $donnees);
		$this->pondreAutres($fichier, $donnees);
		pclose($fichier);
		system("cat '{$dossierTemp}/content.post.xml' >> '{$dossierTemp}/content.xml'");
		$sortie = @$this->_params['pdf'] ? $nomTemp.'.sortie.odt' : '-';
		system("( cd '{$dossierTemp}' && ( ( command -v zip > /dev/null && zip -r -q {$sortie} . ) || ( 7za a /tmp/temp.$$.zip . > /dev/null && cat /tmp/temp.$$.zip && rm /tmp/temp.$$.zip ) ) )");
		if(@$this->_params['pdf']) { ooo_enPDF($sortie); system("rm '{$sortie}'"); }
		system("rm -R '{$dossierTemp}' '{$nomTemp}'");
	}
	
	/* Paramètres:
	 *   enBoite: si true, l'en-tête ne contient que le nom et le rôle.
	 */
	function pondreEnTete($fichier, $donnees, $enBoite)
	{
fprintf($fichier, <<<TERMINE
		<text:sequence-decls>
			<text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
			<text:sequence-decl text:display-outline-level="0" text:name="Table"/>
			<text:sequence-decl text:display-outline-level="0" text:name="Text"/>
			<text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
		</text:sequence-decls>
TERMINE
);
if(@$this->_params['logo']) fprintf($fichier, <<<TERMINE
		<text:section text:style-name="Sect1" text:name="En-tête">
			<text:p text:style-name="Texte CV">
				<text:bookmark-start text:name="_9978858911"/>
				<draw:object-ole draw:style-name="fr1" draw:name="Object1" text:anchor-type="as-char" svg:width="5.823cm" svg:height="2.436cm" draw:z-index="0" xlink:href="#./ObjBFFFC666" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>
				<text:bookmark-end text:name="_9978858911"/>
			</text:p>
TERMINE
);
		
		if(isset($donnees->perso->photo))
		{
			$image = imagecreatefromjpeg($donnees->perso->photo);
			$l = imagesx($image);
			$h = imagesy($image);
			imagedestroy($image);
			fprintf($fichier, '<draw:image draw:style-name="cadrephoto" draw:name="Photo" text:anchor-type="page" text:anchor-page-number="1" svg:x="'.(20 - $l * 3.5 / $h).'cm" svg:y="1cm" svg:width="'.($l * 3.5 / $h).'cm" svg:height="3.5cm" draw:z-index="0" xlink:href="Photo.jpg" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>');
		}
		$prénom = htmlspecialchars($donnees->perso->prénom, ENT_NOQUOTES);
		$nom = htmlspecialchars($donnees->perso->nom, ENT_NOQUOTES);
		$titre = htmlspecialchars($donnees->titre, ENT_NOQUOTES);
		if($enBoite)
		{
			fprintf($fichier, "<text:p text:style-name=\"Nom CV\">{$prénom} {$nom}</text:p>");
			fprintf($fichier, "<text:p text:style-name=\"En-tête CV\">{$titre}</text:p>");
		}
		else
		{
			fprintf($fichier, "<text:p text:style-name=\"En-tête CV\">{$prénom} {$nom}</text:p>");
			if($donnees->perso->naissance)
			{
				$maintenant = obtenir_datation(time());
				$âge = $maintenant[0] - $donnees->perso->naissance[0];
				for($i = 1; $i < 6; ++$i) // Si l'on est avant la date d'anniversaire, on retire un an.
					if(($j = $maintenant[$i] - $donnees->perso->naissance[$i]) != 0)
					{
						if($j < 0)
							--$âge;
						break;
					}
				fprintf($fichier, "<text:p text:style-name=\"Texte CV\">{$âge} ans</text:p>");// On espère qu'il a un âge positif et supérieur à 1.
			}
			if($donnees->perso->adresse)
			{
				$adresse = '';
				foreach($donnees->perso->adresse->données as $chose)
					$adresse .= ($adresse === '' ? '' : '<text:line-break/>').htmlspecialchars($chose, ENT_NOQUOTES);
				fprintf($fichier, "<text:p text:style-name=\"Texte CV\">{$adresse}</text:p>");
			}
			if($donnees->perso->mél)
			{
				$adrél = htmlspecialchars($donnees->perso->mél);
				fprintf($fichier, "<text:p text:style-name=\"Texte CV\">{$adrél}</text:p>");
			}
			fprintf($fichier, "<text:p text:style-name=\"En-tête CV centré\">{$titre}</text:p>");
		}
		if(@$this->_params['logo']) fprintf($fichier, "</text:section>");
	}
	
	function pondreEtudes($fichier, $donnees)
	{
		if(!array_key_exists('formation', $donnees)) return;
fprintf($fichier, <<<TERMINE
		<text:p text:style-name="Section CV">Études
		</text:p>
TERMINE
);
		foreach($donnees->formation->études as $périodeHeureuse)
		{
fprintf($fichier, <<<TERMINE
		<text:p text:style-name="Texte CV, dates">
TERMINE
);
			fprintf($fichier, pasTeX_descriptionPeriode($périodeHeureuse->date->d, $périodeHeureuse->date->f).' <text:tab-stop/>'.htmlspecialchars($périodeHeureuse->diplôme, ENT_NOQUOTES));
fprintf($fichier, <<<TERMINE
		</text:p>
TERMINE
);
		}
	}
	
	function pondreProjets($fichier, $donnees)
	{
		if(!array_key_exists('expérience', $donnees)) return;
fprintf($fichier, <<<TERMINE
		<text:p text:style-name="Section CV">Culture informatique, Expérience et Projets
		</text:p>
TERMINE
);
		foreach($donnees->expérience->projet as $francheRigolade)
		{
			/* Dans les styles OpenOffice, on a demandé à ce que les titres de
			 * mission et les points de détails ne soient pas coupés. On aurait
			 * bien voulu un keep-with-previous pour les points, mais OOo 1.1 ne
			 * le comprend pas; on utilise donc des keep-with-next, sauf pour le
			 * dernier paragraphe. Ça devient compliqué. */
			 $cesure = isset($francheRigolade->techno) ? 2 : (count($francheRigolade->tâche) ? 1 : 0);
			 
			fprintf($fichier, '<text:p text:style-name="Texte CV, dates, titre'.($cesure == 0 ? ', césure' : '').'">');
			/* Ce modèle-ci ne nous permet pas d'afficher plusieurs périodes
			 * pour le même projet, on fait donc la période englobante du tout. */
			$moments = pasTeX_unionPeriodes($francheRigolade->date);
			fprintf($fichier, pasTeX_descriptionPeriode($moments[0], $moments[1]).' <text:tab-stop/>');
			$desChoses = true;
			$sociétés = null;
			if(isset($francheRigolade->société))
				$sociétés = htmlspecialchars($francheRigolade->société[count($francheRigolade->société) - 1], ENT_NOQUOTES); // Seul le client final nous intéresse.
				//foreach(array_slice($francheRigolade->société, 1) as $société)
				//{
				//	$société = htmlspecialchars($société, ENT_NOQUOTES);
				//	$sociétés = $sociétés === null ? $société : $sociétés.', '.$société;
				//}
			if(isset($francheRigolade->nom)) fprintf($fichier, '%s%s', htmlspecialchars($francheRigolade->nom, ENT_NOQUOTES), $sociétés === null ? '' : ' ('.$sociétés.')');
			else if($sociétés != null) fprintf($fichier, $sociétés);
			else $desChoses = false;
			
			if(isset($francheRigolade->description)) { fprintf($fichier, ($desChoses ? ': ' : '').htmlspecialchars($francheRigolade->description, ENT_NOQUOTES)); $desChoses = true; }
fprintf($fichier, <<<TERMINE
		</text:p>
TERMINE
			);
			
			for($i = -1, $n = count($francheRigolade->tâche); ++$i < $n;)
			{
				if(!$i)
					fprintf($fichier, '<text:unordered-list text:style-name="Texte CV, petite puce">');
				fprintf($fichier, '<text:list-item><text:p text:style-name="Texte CV, dates, points'.($cesure == 1 && $i == $n - 1 ? ', césure' : '').'">');
				fprintf($fichier, htmlspecialchars($francheRigolade->tâche[$i], ENT_NOQUOTES));
				fprintf($fichier, '</text:p></text:list-item>');
			}
			if($n)
				fprintf($fichier, '</text:unordered-list>');
			
			if(isset($francheRigolade->techno))
				fprintf($fichier, '<text:p text:style-name="Texte CV, dates, technos'.($cesure == 2 ? ', césure' : '').'">'.implode(', ', $francheRigolade->techno).'</text:p>');
		}
	}
	
	function pondreLangues($fichier, $donnees)
	{
		if(!array_key_exists('langues', $donnees)) return;
fprintf($fichier, <<<TERMINE
		<text:p text:style-name="Section CV">Langues
		</text:p>
TERMINE
);
		foreach($donnees->langues->langue as $chat)
		{
		

			fprintf($fichier, '<text:p text:style-name="Texte CV, dates">'.htmlspecialchars($chat->nom, ENT_NOQUOTES).'<text:tab-stop/>'.htmlspecialchars($chat->niveau, ENT_NOQUOTES));
			$qqc = false;
			if(isset($chat->certificat) && count($chat->certificat) > 0)
			{
				foreach($chat->certificat as $certif)
					fprintf($fichier, ($qqc ? ', ' : ' (').htmlspecialchars($certif, ENT_NOQUOTES));
				fprintf($fichier, ')');
			}
			fprintf($fichier, '</text:p>');
		}
	}
	
	function pondreConnaissances($fichier, $donnees)
	{
		if(!array_key_exists('connaissances', $donnees)) return;
		
		$seuils = array(0x0, 0x4, 0x8, 0x10);
		
fprintf($fichier, <<<TERMINE
		<text:p text:style-name="Section CV">Connaissances Informatiques
		</text:p>
		<text:p text:style-name="Texte CV">(dans chacune de ces catégories, les éléments sont groupés par degré de connaissance: ***: maîtrise de l&apos;outil; **: bonne connaissance, utilisation courante; *: notions)</text:p>
TERMINE
);
		foreach($donnees->connaissances->catégorie as $catégorie)
		{
			fprintf($fichier, '<text:p text:style-name="Groupe CV">'.htmlspecialchars($catégorie->nom, ENT_NOQUOTES).'</text:p>');
			for($i = count($seuils) - 1; --$i >= 0;)
			{
				$qqc = false;
				foreach($catégorie->connaissances as $nom => $valeur)
					if($valeur < $seuils[$i + 1] && $valeur >= $seuils[$i]) // Question existentielle: une fois placée la connaissance dans un niveau, faudrait-il l'y classer par rapport aux autres du niveau ou laisse-t-on dans l'ordre d'arrivée pour laisser à l'utilisateur un semblant de contrôle?
					{
						if(!$qqc)
						{
							fprintf($fichier, '<text:p text:style-name="Texte CV, technique">');
							for($j = $i + 1; --$j >= 0;)
								fprintf($fichier, '*');
							fprintf($fichier, '<text:tab-stop/>');
							$qqc = true;
						}
						else
							fprintf($fichier, ', ');
						fprintf($fichier, htmlspecialchars($nom, ENT_NOQUOTES));
					}
				if($qqc)
					fprintf($fichier, '</text:p>');
			}
		}
	}
	
	function pondreInteret($fichier, $donnees)
	{
		if(!array_key_exists('intérêts', $donnees)) return;
		
fprintf($fichier, <<<TERMINE
		<text:p text:style-name="Section CV">Domaines d&apos;intérêts
		</text:p>
TERMINE
);
		foreach($donnees->intérêts->domaine as $latechniqueamusante)
		{
			fprintf($fichier, '<text:p text:style-name="Texte CV">');
			fprintf($fichier, htmlspecialchars($latechniqueamusante->nom, ENT_NOQUOTES));
			$qqc = false;
			if(isset($latechniqueamusante->techno))
				foreach($latechniqueamusante->techno as $aquoicasert)
				{
					fprintf($fichier, ($qqc ? ', ' : ' (').htmlspecialchars($aquoicasert, ENT_NOQUOTES));
					$qqc = true;
				}
			if($qqc) fprintf($fichier, ')');
			fprintf($fichier, '</text:p>');
		}
	}

	function pondreAutres($fichier, $donnees)
	{
		if(!array_key_exists('loisirs', $donnees)) return;
		
fprintf($fichier, <<<TERMINE
		<text:p text:style-name="Section CV">Autres activités et intérêts
		</text:p>
TERMINE
);
		foreach($donnees->loisirs->activité as $ouf)
			fprintf($fichier, '<text:p text:style-name="Texte CV">'.htmlspecialchars($ouf, ENT_NOQUOTES).'</text:p>');
	}
	
	protected $_params;
}
?>
