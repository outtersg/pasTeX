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

/* À FAIRE: inclure un lien, si module il y a, vers la génération d'un PDF
 * correspondant. */

class Html
{
	function Html() {}
	
	function analyserParams($argv, &$position) { return array(); }
	
	function analyserChamps($params) { return array(); }
	
	function pondreInterface($champ) {}
	
	function decomposer($params, $donnees)
	{
		html_enTete();
	?>
		<title>CV</title>
	<?php
		html_meta('link rel="stylesheet" type="text/css" href="decompo/html/html.css"');
		html_corps();
		$this->pondreEntete($donnees);
		$this->pondreEtudes($donnees);
		$this->pondreProjets($donnees);
		$this->pondreLangues($donnees);
		$this->pondreConnaissances($donnees);
		$this->pondreInteret($donnees);
		$this->pondreAutres($donnees);
		html_fin();
		
		return $this;
	}
	
	function pondreEnTete($donnees)
	{
		$prénom = htmlspecialchars($donnees->perso->prénom, ENT_NOQUOTES);
		$nom = htmlspecialchars($donnees->perso->nom, ENT_NOQUOTES);
		$titre = htmlspecialchars($donnees->titre, ENT_NOQUOTES);
?>
	<div class="enTete">
		<div class="nom"><?php echo $prénom.' '.$nom ?></div>
		<div class="titre"><?php echo $titre ?></div>
	</div>
<?php
	}
	
	function commencerSection($nom)
	{
		/* Secret de fabrication pour les coins: peindre un rectangle de 32x32 en
		 * couleur de fond, faire un masque tout noir sur lequel on trace un disque
		 * de 30x30 en blanc; sur l'image, tracer par-dessus un cercle couleur de
		 * bordure de 32x32. Récupérer les 16x16 pixels intéressants. */
		/* Emmerdements pour faire un dégradé propre:
		 * - des bandes à la transparence diminuant, les unes sur les autres.
		 *   Mais avec un positionnement relatif, ça merde, car les arrondis CSS
		 *   font que parfois la bande de largeur n% posée en n%, ne touchera
		 *   la bande de largeur n% commençant en 0 (un pixel entre les deux).
		 * - la même chose avec un positionnement par JS: quand on agrandit la
		 *   police, je JS n'est pas rappelé.
		 * - image en bg: on ne peut pas dire que le bg est étiré pour faire n%
		 *   de son conteneur.
		 * - image pas en bg: IE est censé avoir des problèmes avec la
		 *   transparence. On va voir avec le forçage à la GoogleMaps. De plus
		 *   ça nous force à créer une image par couleur de fond. Solution
		 *   envisagée.
		 * - bandes de largeur n%, 2*n%, 3*n%, …, commençant toutes en 0. On
		 *   calcule leur transparence pour qu'en s'accumulant, elles fassent
		 *   comme si on avait une bande de transparence voulue. Solution
		 *   envisagée.
		 * Le monsieur à http://forum.hardware.fr/hardwarefr/Programmation/Recherche-code-html-pour-fondu-sujet-75035-1.htm
		 * connaît le moyen de faire faire de la transparence à tout les
		 * navigateurs.
		 * Un jour on aura les CSS3. Enfin si c'est pour avoir quelque chose
		 * d'encore plus tordu que les 2.1…
		 */
?>
	<div class="section">
		<img src="decompo/html/hd.ocre.png" style="position: absolute; right: 0px; top: 0px; z-index: 3;" alt="décoration"/>
		<img src="decompo/html/bg.ocre.png" style="position: absolute; left: 0px; bottom: 0px; z-index: 3;" alt="décoration"/>
		<div class="audessus">
			<div class="titresection">
				<?php echo $nom ?>
				<img style="position: absolute; left: 0px; width: 100%; right: 0px; bottom: 0px; height: 50%; z-index: 1;" src="decompo/html/degrade.ocre.png" alt=""/>
			</div>
<?php
	}
	
	function terminerSection()
	{
?>
		</div>
	</div>
<?php
	}
	
	function pondreEtudes($donnees)
	{
		if(!array_key_exists('formation', $donnees)) return;
		
		$this->commencerSection('Études');
?>
		<table>
<?php
		foreach($donnees->formation->études as $pépère)
		{
?>
			<tr>
				<td class="categorie"><?php echo pasTeX_descriptionPeriode($pépère->date->d, $pépère->date->f) ?></td>
				<td><?php echo htmlspecialchars($pépère->diplôme, ENT_NOQUOTES) ?></td>
			</tr>
<?php
		}
?>
		</table>
<?php
		$this->terminerSection();
	}
	
	function pondreProjets($donnees)
	{
		if(!array_key_exists('expérience', $donnees)) return;
		
		$this->commencerSection('Expérience et Projets');
		$pasLePremier = false;
		foreach($donnees->expérience->projet as $francheRigolade)
		{
			if($pasLePremier) echo '<div class="delair"> </div>'."\n"; else $pasLePremier = true;
			/* Ce modèle-ci ne nous permet pas d'afficher plusieurs périodes
			 * pour le même projet, on fait donc la période englobante du tout. */
			$moments = array();
			foreach($francheRigolade->date as $moment)
				$moments[] = array($moment->d, $moment->f);
			$moments = periode_union($moments);
			echo '<div class="dateexp">'.pasTeX_descriptionPeriode($moments[0], $moments[1]).'</div>'."\n";
			$sociétés = null;
			echo '<div class="titreexp">';
			if(isset($francheRigolade->société))
				$sociétés = htmlspecialchars($francheRigolade->société[count($francheRigolade->société) - 1], ENT_NOQUOTES); // Seul le client final nous intéresse.
				//foreach(array_slice($francheRigolade->société, 1) as $société)
				//{
				//	$société = htmlspecialchars($société, ENT_NOQUOTES);
				//	$sociétés = $sociétés === null ? $société : $sociétés.', '.$société;
				//}
			if(isset($francheRigolade->nom)) echo htmlspecialchars($francheRigolade->nom, ENT_NOQUOTES).($sociétés === null ? '' : ' ('.$sociétés.')');
			else if($sociétés != null) echo($sociétés);
			echo '</div>'."\n";
			
			if(isset($francheRigolade->description))
				echo '<div class="exp">'.htmlspecialchars($francheRigolade->description, ENT_NOQUOTES).'</div>'."\n";
			echo '<div class="exp">';
			$desChoses = false;
			foreach($francheRigolade->tâche as $tâche)
			{
				echo ($desChoses ? '; ' : '').htmlspecialchars($tâche, ENT_NOQUOTES); /* À FAIRE: ne garder la majuscule en début que pour la première; virer les points sauf celui de la dernière. */
				$desChoses = true;
			}
			echo '</div>'."\n";
			
			/* À FAIRE: un machin qui fait que quand on passe la souris par dessus
			 * un projet, s'affichent les outils et technos utilisés. */
		}
		$this->terminerSection();
	}
	
	function pondreLangues($donnees)
	{
		if(!array_key_exists('langues', $donnees)) return;
		$this->commencerSection('Langues');
?>
		<table>
<?php
		foreach($donnees->langues->langue as $chat)
		{
?>
			<tr>
				<td class="categorie"><?php echo htmlspecialchars($chat->nom, ENT_NOQUOTES) ?></td>
				<td>
<?php
			echo htmlspecialchars($chat->niveau, ENT_NOQUOTES);
			$qqc = false;
			if(count($chat->certificat) > 0)
			{
				foreach($chat->certificat as $certif)
					echo ($qqc ? ', ' : ' (').htmlspecialchars($certif, ENT_NOQUOTES);
				echo ')';
			}
?>
				</td>
			</tr>
<?php
		}
?>
		</table>
<?php
		$this->terminerSection();
	}
	
	function pondreConnaissances($donnees)
	{
		/* À FAIRE: un truc délire, où le PHP se contente de cracher les données,
		 * puis un JavaScript place chacune des connaissances dans un span, mesure
		 * sa place, lui attribue une abcisse, et en détermine l'ordonnée en
		 * fonction des autres connaissances qui pourraient la recouvrir du fait
		 * d'une abcisse trop proche.
		 * Le span possèdera le texte en contenu principal, et un point rouge centré
		 * qui indique sa place exacte sur l'échelle des connaissances. */
		
		if(!array_key_exists('connaissances', $donnees)) return;
		
		$seuils = array(0x0, 0x4, 0x8, 0x10);
		
		$this->commencerSection('Connaissances Informatiques');
		$prems = true;
		foreach($donnees->connaissances->catégorie as $catégorie)
		{
			echo '<div class="soussection"'.($prems ? ' style="margin-top: 0px"' : '').'>'.htmlspecialchars($catégorie->nom, ENT_NOQUOTES).'</div>'."\n";
			$prems = false;
			for($i = count($seuils) - 1; --$i >= 0;)
			{
				$qqc = false;
				foreach($catégorie->connaissances as $nom => $valeur)
					if($valeur < $seuils[$i + 1] && $valeur >= $seuils[$i]) // Question existentielle: une fois placée la connaissance dans un niveau, faudrait-il l'y classer par rapport aux autres du niveau ou laisse-t-on dans l'ordre d'arrivée pour laisser à l'utilisateur un semblant de contrôle?
					{
						if(!$qqc)
						{
							echo '<div>';
							$qqc = true;
						}
						else
							echo ', ';
						echo htmlspecialchars($nom, ENT_NOQUOTES);
					}
				if($qqc)
					echo '</div>';
			}
		}
		$this->terminerSection();
	}
	
	function pondreInteret($donnees)
	{
		if(!array_key_exists('intérêts', $donnees)) return;
		
		$this->commencerSection('Domaines d\'intérêt');
		$pasLePremier = false;
		foreach($donnees->intérêts->domaine as $latechniqueamusante)
		{
			if($pasLePremier) echo '<div class="delair"> </div>'."\n"; else $pasLePremier = true;
			echo '<div class="titreexp">'.htmlspecialchars($latechniqueamusante->nom, ENT_NOQUOTES).'</div>'."\n";
			$qqc = false;
			if(isset($latechniqueamusante->techno))
				foreach($latechniqueamusante->techno as $aquoicasert)
				{
					echo ($qqc ? ', ' : '<div class="exp">').htmlspecialchars($aquoicasert, ENT_NOQUOTES);
					$qqc = true;
				}
			if($qqc)
				echo '</div>'."\n";
		}
		$this->terminerSection();
	}

	function pondreAutres($donnees)
	{
		if(!array_key_exists('loisirs', $donnees)) return;
		
		$this->commencerSection('Autres activités et intérêts');
		$pasLePremier = false;
		foreach($donnees->loisirs->activité as $ouf)
			echo '<div class="paraindependant">'.htmlspecialchars($ouf, ENT_NOQUOTES).'</div>'."\n";
		$this->terminerSection();
		
	}
}

?>
