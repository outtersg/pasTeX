<?php
/*
 * Code Copyright (c) 2005 Guillaume Outters
 * Word Template Copyright (c) 1691-2005 CS (and I wouldn't claim it)
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

/* Cette chose est un gros copier-coller baveux de openoffice.php. Bien entendu,
 * je ferai une version avec factorisation du code, mais pas ici au bureau,
 * d'une part parce que je ne suis pas payé pour la beauté du code mais pour
 * envoyer mon CV, et d'autre part je ne souhaite pas polluer mon beau code
 * avec des droits réservés CS.*/
/* À FAIRE: savoir ne pas planter quand des champs sont absents. */

require_once('util/periode.inc');

class BTP
{
	function analyserParams($argv, &$position)
	{
		
	}
	
	function decomposer($params, $donnees)
	{
		$nomTemp = tempnam('/tmp', 'temp.btp.');
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
TERMINE
);
		$totale = $donnees->perso->prénom.' '.$donnees->perso->nom;
		if(strlen($totale) > 0)
		{
			$initiales = $totale{0}.'.';
			for($i = 0; $i < strlen($totale) - 1; ++$i)
				switch($totale{$i})
				{
					case ' ':
					case '-':
						$initiales .= $totale{$i}.htmlspecialchars($totale{$i + 1}, ENT_NOQUOTES).'.';
						break;
				}
		}
		else
			$initiales = '';
		fprintf($fichier, '<text:p text:style-name="P1">'.$initiales.'</text:p>');
		fprintf($fichier, '<text:p text:style-name="P2">'.htmlspecialchars($donnees->titre, ENT_NOQUOTES).'</text:p>');
fprintf($fichier, <<<TERMINE
		<text:p text:style-name="Standard"/>
		<text:p text:style-name="Standard"/>
		<text:p text:style-name="Standard"/>
TERMINE
);
	}
	
	function pondreEtudes($fichier, $donnees)
	{
		if(!array_key_exists('formation', $donnees)) return;
fprintf($fichier, <<<TERMINE
		<text:p text:style-name="CV Intitulé">Formation</text:p>
TERMINE
);
		foreach($donnees->formation->études as $périodeHeureuse)
			fprintf($fichier, '<text:p text:style-name="CV Formation">'.$this->periode($périodeHeureuse->date->d, $périodeHeureuse->date->f, 2).'<text:tab-stop/>'.htmlspecialchars($périodeHeureuse->diplôme, ENT_NOQUOTES).'</text:p>');
	}
	
	function pondreProjets($fichier, $donnees)
	{
		if(!array_key_exists('expérience', $donnees)) return;
		
		fprintf($fichier, '<text:p text:style-name="CV Intitulé">Expérience Professionnelle</text:p>');
		foreach($donnees->expérience->projet as $francheRigolade)
		{
			fprintf($fichier, '<text:p text:style-name="CV Rôle">');

			/* Ce modèle-ci ne nous permet pas d'afficher plusieurs périodes
			 * pour le même projet, on fait donc la période englobante du tout. */
			$moments = array();
			foreach($francheRigolade->date as $moment)
				$moments[] = array($moment->d, $moment->f);
			$moments = periode_union($moments);
			fprintf($fichier, '<text:span text:style-name="CV Date">'.$this->periode($moments[0], $moments[1], 2).'</text:span><text:tab-stop/>');
			
			$desChoses = false;
			if(isset($francheRigolade->rôle))
			{
				fprintf($fichier, htmlspecialchars($francheRigolade->rôle, ENT_NOQUOTES));
				$desChoses = true;
			}
			if(isset($francheRigolade->société))
			{
				$sociétés = $francheRigolade->société[count($francheRigolade->société) - 1]; // Seul le client final nous intéresse.
				if($desChoses) fprintf($fichier, ' / ');
				fprintf($fichier, htmlspecialchars($sociétés, ENT_NOQUOTES));
				$desChoses = true;
			}
			
			if(isset($francheRigolade->description)) { fprintf($fichier, ($desChoses ? '<text:line-break/>' : '').htmlspecialchars($francheRigolade->description, ENT_NOQUOTES)); $desChoses = true; }
			fprintf($fichier, '</text:p>');
			
			foreach($francheRigolade->tâche as $tâche)
			{
				fprintf($fichier, '<text:line-break/>');
fprintf($fichier, <<<TERMINE
		<text:unordered-list text:style-name="List 1">
			<text:list-item>
				<text:p text:style-name="CV Tâche">
TERMINE
);
			fprintf($fichier, htmlspecialchars($tâche, ENT_NOQUOTES));
fprintf($fichier, <<<TERMINE
				</text:p>
			</text:list-item>
		</text:unordered-list>
TERMINE
);
			}
			
			$qqc = false;
			if(isset($francheRigolade->techno))
				foreach($francheRigolade->techno as $techno)
				{
					if($qqc)
						fprintf($fichier, ', ');
					else
					{
fprintf($fichier, <<<TERMINE
		<text:unordered-list text:style-name="List 2">
			<text:list-item>
				<text:p text:style-name="CV Outils">
TERMINE
);
						$qqc = true;
					}
					fprintf($fichier, htmlspecialchars($techno, ENT_NOQUOTES));
				}
if($qqc) fprintf($fichier, <<<TERMINE
				</text:p>
			</text:list-item>
		</text:unordered-list>
TERMINE
);
		}
	}
	
	/* À FAIRE: regrouper les méthodes suivantes. Elles font exactement la même
	 * chose, seule diffère leur façon de récupérer leur texte. */
	function pondreLangues($fichier, $donnees)
	{
		if(!array_key_exists('langues', $donnees)) return;
fprintf($fichier, <<<TERMINE
		<text:p text:style-name="CV Intitulé">Langues</text:p>
TERMINE
);
		foreach($donnees->langues->langue as $chat)
		{
			fprintf($fichier, '<text:p text:style-name="CV Compétences"><text:span text:style-name="CV Catégorie de compétences">'.htmlspecialchars($chat->nom, ENT_NOQUOTES).'</text:span><text:tab-stop/>'.htmlspecialchars($chat->niveau, ENT_NOQUOTES));
			$qqc = false;
			if(count($chat->certificat) > 0)
			{
				foreach($chat->certificat as $certif)
				{
					fprintf($fichier, ($qqc ? ', ' : ' (').htmlspecialchars($certif, ENT_NOQUOTES));
					$qqc = true;
				}
				fprintf($fichier, ')');
			}
			fprintf($fichier, '</text:p>');
		}
	}
	
	function pondreConnaissances($fichier, $donnees)
	{
		if(!array_key_exists('connaissances', $donnees)) return;
		
fprintf($fichier, <<<TERMINE
		<text:p text:style-name="CV Intitulé">Compétences Techniques</text:p>
TERMINE
);
		foreach($donnees->connaissances->catégorie as $catégorie)
		{
			$qqc = false;
			foreach($catégorie->connaissances as $nom => $valeur)
			{
				if($qqc)
					fprintf($fichier, ', ');
				else
				{
					fprintf($fichier, '<text:p text:style-name="CV Compétences"><text:span text:style-name="CV Catégorie de compétences">'.htmlspecialchars($catégorie->nom, ENT_NOQUOTES).'</text:span><text:tab-stop/>');
					$qqc = true;
				}
				fprintf($fichier, htmlspecialchars($nom, ENT_NOQUOTES));
			}
			if($qqc)
				fprintf($fichier, '</text:p>');
		}
	}
	
	function pondreInteret($fichier, $donnees)
	{
		if(!array_key_exists('intérêts', $donnees)) return;
		
fprintf($fichier, <<<TERMINE
		<text:p text:style-name="CV Intitulé">Centres d'intérêt</text:p>
TERMINE
);
		foreach($donnees->intérêts->domaine as $latechniqueamusante)
		{
			fprintf($fichier, '<text:p text:style-name="CV Compétences"><text:tab-stop/>');
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
	
	function periode($d, $f, $mode)
	{
		if($d === null)
			if($f === null)
				return null;
			else
				return 'jusqu\'à'; /* À FAIRE: jusqu'au éventuellement */
		if($f === null)
			return periode_affDate($d, $mode).' - à ce jour';
		return periode_aff($d, $f, $mode);
	}
}
?>
