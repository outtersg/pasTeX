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

require_once('util/periode.inc');

class OpenOffice
{
	function OpenOffice()
	{
	
	}
	
	function analyserParams($argv, &$position)
	{
		 /* À FAIRE: oh ben si, il doit bien y avoir quelqu'un qui finira par
		  * avoir besoin de cette emplacement libre. */
	}
	
	function decomposer($params, $donnees)
	{
		$nomTemp = tempnam('/tmp', 'temp.openoffice.');
		$dossierTemp = $nomTemp.'.contenu';
		$modele = dirname(__FILE__).'/modele';
		system("cp -R '{$modele}' '{$dossierTemp}'");
		system("cat '{$dossierTemp}/content.pre.xml' > '{$dossierTemp}/content.xml'");
		$fichier = popen("tr -d '\\011\\012' >> '{$dossierTemp}/content.xml'", 'w');
		$this->pondreEntete($fichier, $donnees);
		$this->pondreEtudes($fichier, $donnees);
		$this->pondreProjets($fichier, $donnees);
		$this->pondreLangues($fichier, $donnees);
		$this->pondreConnaissances($fichier, $donnees);
		$this->pondreInteret($fichier, $donnees);
		$this->pondreAutres($fichier, $donnees);
		pclose($fichier);
		system("cat '{$dossierTemp}/content.post.xml' >> '{$dossierTemp}/content.xml'");
		system("( cd '{$dossierTemp}' && zip -r -q - . )");
		system("rm -R '{$dossierTemp}'");
	}
	
	function pondreEnTete($fichier, $donnees)
	{
fprintf($fichier, <<<TERMINE
		<text:sequence-decls>
			<text:sequence-decl text:display-outline-level="0" text:name="Illustration"/>
			<text:sequence-decl text:display-outline-level="0" text:name="Table"/>
			<text:sequence-decl text:display-outline-level="0" text:name="Text"/>
			<text:sequence-decl text:display-outline-level="0" text:name="Drawing"/>
		</text:sequence-decls>
		<text:section text:style-name="Sect1" text:name="En-tête">
			<text:p text:style-name="Texte CV">
				<text:bookmark-start text:name="_9978858911"/>
				<draw:object-ole draw:style-name="fr1" draw:name="Object1" text:anchor-type="as-char" svg:width="5.823cm" svg:height="2.436cm" draw:z-index="0" xlink:href="#./ObjBFFFC666" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>
				<text:bookmark-end text:name="_9978858911"/>
			</text:p>
TERMINE
);
		
		$prénom = htmlspecialchars($donnees->perso->prénom, ENT_NOQUOTES);
		$nom = htmlspecialchars($donnees->perso->nom, ENT_NOQUOTES);
		$titre = htmlspecialchars($donnees->perso->titre, ENT_NOQUOTES);
		fprintf($fichier, "<text:p text:style-name=\"Nom CV\">{$prénom} {$nom}</text:p>");
		fprintf($fichier, "<text:p text:style-name=\"En-tête CV\">{$titre}</text:p>");
		fprintf($fichier, "</text:section>");
	}
	
	function pondreEtudes($fichier, $donnees)
	{
		if(!array_key_exists('formation', $donnees)) return;
fprintf($fichier, <<<TERMINE
		<text:p text:style-name="Section CV">Études
			<text:line-break/>
			<draw:line text:anchor-type="as-char" svg:y="0cm" draw:z-index="6" draw:style-name="gr1" draw:text-style-name="P1" svg:x2="17cm" svg:y2="0cm"/>
		</text:p>
TERMINE
);
		foreach($donnees->formation->études as $périodeHeureuse)
		{
fprintf($fichier, <<<TERMINE
		<text:p text:style-name="Texte CV, dates">
TERMINE
);
			fprintf($fichier, $this->periode($périodeHeureuse->date->d, $périodeHeureuse->date->f).'<text:tab-stop/>'.htmlspecialchars($périodeHeureuse->diplôme, ENT_NOQUOTES));
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
			<text:line-break/>
			<draw:line text:anchor-type="as-char" svg:y="0cm" draw:z-index="5" draw:style-name="gr1" draw:text-style-name="P1" svg:x2="17cm" svg:y2="0cm"/>
		</text:p>
TERMINE
);
		foreach($donnees->expérience->projet as $francheRigolade)
		{
fprintf($fichier, <<<TERMINE
		<text:p text:style-name="Texte CV, dates">
TERMINE
);
			/* Ce modèle-ci ne nous permet pas d'afficher plusieurs périodes
			 * pour le même projet, on fait donc la période englobante du tout. */
			$moments = array();
			foreach($francheRigolade->date as $moment)
				$moments[] = array($moment->d, $moment->f);
			$moments = periode_union($moments);
			fprintf($fichier, $this->periode($moments[0], $moments[1]).'<text:tab-stop/>');
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
			foreach($francheRigolade->tâche as $tâche)
			{
				fprintf($fichier, ($desChoses ? '<text:line-break/>' : '').htmlspecialchars($tâche, ENT_NOQUOTES));
				$desChoses = true;
			}
fprintf($fichier, <<<TERMINE
		</text:p>
TERMINE
);
		}
	}
	
	function pondreLangues($fichier, $donnees)
	{
		if(!array_key_exists('langues', $donnees)) return;
fprintf($fichier, <<<TERMINE
		<text:p text:style-name="Section CV">Langues
			<text:line-break/>
			<draw:line text:anchor-type="as-char" svg:y="0cm" draw:z-index="1" draw:style-name="gr1" draw:text-style-name="P1" svg:x2="17cm" svg:y2="0cm"/>
		</text:p>
TERMINE
);
		foreach($donnees->langues->langue as $chat)
		{
		

			fprintf($fichier, '<text:p text:style-name="Texte CV, dates">'.htmlspecialchars($chat->nom, ENT_NOQUOTES).'<text:tab-stop/>'.htmlspecialchars($chat->niveau, ENT_NOQUOTES));
			$qqc = false;
			if(count($chat->certificat) > 0)
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
			<text:line-break/>
			<draw:line text:anchor-type="as-char" svg:y="0cm" draw:z-index="2" draw:style-name="gr1" draw:text-style-name="P1" svg:x2="17cm" svg:y2="0cm"/>
		</text:p>
		<text:p text:style-name="Texte CV">(dans chacune de ces catégories, les éléments sont groupés par degré de connaissance: ***: maîtrise de l&apos;outil; **: bonne connaissance, utilisation courante; *: notions)</text:p>
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
		if(!array_key_exists('connaissances', $donnees)) return;
		
fprintf($fichier, <<<TERMINE
		<text:p text:style-name="Section CV">Domaines d&apos;intérêts
			<text:line-break/>
			<draw:line text:anchor-type="as-char" svg:y="0cm" draw:z-index="3" draw:style-name="gr1" draw:text-style-name="P1" svg:x2="17cm" svg:y2="0cm"/>
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
			<text:line-break/>
			<draw:line text:anchor-type="as-char" svg:y="0cm" draw:z-index="4" draw:style-name="gr1" draw:text-style-name="P1" svg:x2="17cm" svg:y2="0cm"/>
		</text:p>
TERMINE
);
		foreach($donnees->loisirs->activité as $ouf)
			fprintf($fichier, '<text:p text:style-name="Texte CV">'.htmlspecialchars($ouf, ENT_NOQUOTES).'</text:p>');
	}
	
	function periode($d, $f)
	{
		if($d === null)
			if($f === null)
				return null;
			else
				return 'jusqu\'à'; /* À FAIRE: jusqu'au éventuellement */
		if($f === null)
			return 'depuis '.periode_affDate($d);
		return periode_aff($d, $f);
	}
}
?>
