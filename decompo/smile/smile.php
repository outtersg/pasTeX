<?php
/*
 * Copyright (c) 2005,2007 Guillaume Outters
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

require_once('commun/http/emetteur.inc');
require_once('commun/http/formu.inc');

/* À FAIRE: désolidariser réellement affichage et dialogue avec Monster: on a
 * encore des cas trop limite. */

class Smile extends Émetteur
{
	function Smile() { $this->Émetteur('smile'); }
	
	function analyserParams($argv, &$position)
	{
		$retour = array();
		
		if($argv[$position] && ($p = strpos($argv[$position], ':')) !== false)
		{
			$retour['id'] = substr($argv[$position], 0, $p);
			$retour['mdp'] = substr($argv[$position], $p + 1);
			++$position;
		}
		
		if($argv[$position])
		{
			$retour['chemincv'] = $argv[$position];
			++$position;
		}
		
		$this->petitÀPetit = false;
		
		return $retour;
	}
	
	function analyserChamps($champs)
	{
		/* Tout le boulot est fait dans decomposer(), car le module fonctionnant
		 * en plusieurs étapes, tout ne peut pas être fait dans l'écran de
		 * paramétrage qui appelle analyserChamps().
		 */
		return $champs;
	}
	
	/* Génère l'interface pour demander au client un renseignement nous
	 * permettant d'avancer (ex.: identifiant/mdp, numéro du CV à modifier, …).
	 * Paramètres:
	 *   $numéroInterface: machin à pondre.
	 */
	function pondreContenuPage($champ, $numéroInterface, $args)
	{
		switch($numéroInterface)
		{
			case 4:
				$this->pondreContenuPageAjax($champ, $args);
				break;
			case 1:
				$params = $this->explo->données['affcv'];
				if(count($params) != 0) // Sinon, c'est qu'on a dû se faire expirer la session Monster au nez.
				{
					$this->pondreContenuLiensÉmission($champ, $params);
?>
		<table style="text-align: center;">
			<tr><td></td><td>-</td><td>A</td><td>R</td></tr>
<?php
					foreach(array('exp' => 'Expérience et projets', 'conn' => 'Compétences') as $cat => $libellé)
					{
						echo '<tr><td>'.$libellé.'</td>';
						foreach(array(0, 1, 2) as $num)
							echo '<td><input type="radio" '.($num == 2 ? ' checked="checked"' : '').'name="'.$champ.'[faire]['.$cat.']" value="'.$num.'"/></td>';
						echo '</tr>';
					}
?>
		</table>
		<div>-: ne pas modifier la catégorie</div>
		<div>A: ajouter le contenu du CV Pasτεχ</div>
		<div>R: remplacer par le contenu du CV Pasτεχ</div>
<?php
					break;
				}
			case 0:
?>
	<div>Le module Monster ajoute à un de vos CV Monster les expériences de votre CV pasτεχ. ATTENTION! Pour le moment expérimental. Pensez à mettre hors-ligne votre CV auparavant, et à en avoir une copie de secours.</div>
	<div>Concernant les infos transposées, il y aura des pertes, c'est inévitable; en particulier, ce foutu Monster ne prend que de l'ISO-8859-1. Adieu donc les caractères sympathiques, décoratifs, ou orientaux (pour l'ISO-8859-1, l'orient commence en Grèce). À l'heure actuelle les champs suivants sont remplis:<ul><li>Expérience</li></ul></div>
	<div>
		Identifiant Monster: <input type="id" name="<?php echo($champ); ?>[id]"></input> Mot de passe: <input type="password" name="<?php echo($champ); ?>[mdp]"></input>
	</div>
<?php
				break;
			default:
				return false;
		}
		
		return true;
	}
	
	/* Fonctions comportementales */
	
	function étapes() { return array(0, 1, 2, array(3), array(5, 4), array(7, 8, 9), 6); }
	
	function manquant()
	{
		if(!$this->verifPresence('id') || !$this->verifPresence('mdp')) return 0;
		else if(!$this->verifPresence('chemincv')) return 1;
		else if($this->données === null) return 3;
		else if($this->petitÀPetit && !$this->verifPresence('clientdemandeur')) return 4;
		return 0x1000;
	}
	
	function gérerÉtape($étape, $manquant, &$page, &$cestdéjàpasmal, &$mouvement, &$params)
	{
		switch($étape)
		{
			case 0: // Connexion.
				if($manquant <= 0) // Si on n'a pas les renseignements pour s'authentifier, on les demande à l'utilisateur et on reprendra la connexion au coup suivant.
					return $cestdéjàpasmal = 1;
				$this->explo->auth = $params['id'].':'.$params['mdp'];
				$page = $this->explo->aller('https://intranet.smile.fr/qualite/index.php/qualite/user/login', array('Login' => $params['id'], 'Password' => $params['mdp'], 'LoginButton' => 'OK'));
				$cestdéjàpasmal = 1;
				$mouvement = 1;
				break;
			case 1: // Accès au CV.
				if($manquant <= 1)
					return $cestdéjàpasmal = 1;
				$this->verifPresence('faire'); // On ne fait que le mettre en mémoire de session.
				$mouvement = 1;
				break;
			case 2: // Récupération de la page de modification du CV.
				$page = $this->explo->aller('/qualite/index.php/qualite/ressources_humaines/'.$params['chemincv']);
				$cestdéjàpasmal = 1;
				$mouvement = 1;
				break;
			case 3: // Suppression d'un projet.
				/* À FAIRE. */
				$mouvement = 1;
				break;
			case 7: // Récupération de la page de modification des connaissances.
				if($manquant <= 3) // Si notre session a déjà tous les renseignements nécessaires, mais qu'on est encore dans l'interface de paramétrage, il nous faut laisser au compo le temps de charger le CV.
					return $cestdéjàpasmal = 1;
				$page = $this->récupérer($params['liens'][self::$modules[$étape]]);
				$mouvement = 1;
				break;
			case 5: // Suppression d'un projet.
			case 8: // Suppression d'une connaissance.
				$mouvement = 1;
				if(count($z = $this->explo->données['supprimable']))
				{
					if(!array_key_exists('numExp', $this->explo->données))
						$this->explo->données['numExp'] = count($z);
					if(--$this->explo->données['numExp'] < 0)
					{
						$cestdéjàpasmal = 0;
						$mouvement = 1;
						unset($this->explo->données['numExp']);
						break;
					}
					$champs = $z[$this->explo->données['numExp']];
					$adresse = $champs['_adresse'];
					unset($champs['_adresse']);
					$page = $this->explo->aller($adresse, $champs);
					$page = $this->explo->aller('/qualite/index.php/qualite/content/removeobject', array('ConfirmButton' => 'combien de fois devrais-je te dire que oui?', 'SupportsMoveToTrash' => 1, 'MoveToTrash' => 0)); // Bon, je code en dur.
					$cestdéjàpasmal = 1;
					$mouvement = 0;
				}
				break;
			case 4: // Ajout d'un projet.
			case 9: // Ajout d'une connaissance.
				$mouvement = 1;
				$page = $this->explo->aller($this->explo->données['adresse'], $this->explo->données['formu']);
				if(!array_key_exists('numExp', $this->explo->données))
				{
					$n = 0;
					switch($étape)
					{
						case 4:
							if(array_key_exists('expérience', $this->données))
							{
								$n = count($this->données->expérience->projet);
								$this->explo->données['champs'] = array
								(
									'boite' => 'Nom de la Soci.t.',
									'an' => 'Ann.e de d.but',
									'mois' => 'mois de d.but',
									'duree' => 'Dur.e',
									'boiboite' => 'Description de la Soci.t.',
									'projet' => 'Description du Projet',
									'boulot' => 'Description de la Mission',
									'tech' => 'Environnement Technique',
								);
							}
							break;
						case 9:
							if(array_key_exists('connaissances', $this->données))
							{
								$n = 0;
								foreach($this->données->connaissances->catégorie as $cat)
									$n += 1 + count($cat->connaissances);
							}
							break;
					}
					$this->explo->données['numExp'] = $n;
					$this->explo->données['nombreExp'] = $n;
				}
				if(--$this->explo->données['numExp'] >= 0)
				{
					/* Champs HTML à remplir */
					$attributs = $this->obtenirChampsAttributsEZ($this->explo->données['champs'], $page);
					preg_match_all('#<form[ >].*</form>#sU', $page, $réponses);
					foreach($réponses[0] as $réponse)
						if(preg_match('#<form[^>]*action="([^"]*)".*PublishButton.*</form>#sU', $réponse, $réponsePrécise))
							break;
					/* Contenu à y mettre */
					switch($étape)
					{
						case 4: $contenu = $this->contenuPourProjet($this->explo->données['nombreExp'] - $this->explo->données['numExp'] - 1); break;
						case 9: $this->pondreConnaissance($this->explo->données['numExp']); break;
					}
					/* Rapiéçage */
					$champs = array('PublishButton' => 'Dacney');
					foreach($attributs as $nom => $aRemplir)
						if(array_key_exists($nom, $contenu))
						{
							$champs['ContentObjectAttribute_id'][] = $aRemplir[0];
							$champs[$aRemplir[1]] = $contenu[$nom];
						}
					$page = $this->explo->aller($réponsePrécise[1], $champs);
					$cestdéjàpasmal = 1;
					if($this->explo->données['numExp'] > 0) // Encore des projets à rentrer, on ne laisse pas encore la main à l'étape suivante.
						$mouvement = 0;
					else
						unset($this->explo->données['numExp']); // Tout le monde s'en sert: il ne faudrait pas que le prochain pondeur de liste croit avoir fini dès son lancement.
				}
				break;
			case 6: // Fin.
				return $cestdéjàpasmal = 2;
		}
	}
	
	function préparerÉtape($étape, $manquant, &$page, &$cestdéjàpasmal, &$mouvement, &$params)
	{
		switch($étape)
		{
			case 0: if($manquant > 0) $this->signaler('Connexion', null); break; // Connexion.
			case 1: // Accès au CV.
				if($manquant > 1) $this->signaler('Accès au CV', null);
				break;
			case 2: $this->signaler('Obtention de la page de modification du CV', null); break; // Récupération de la page de modification du CV.
			case 3: // Suppression d'un projet.
				/* À FAIRE */
				break;
			case 4: // Ajout d'un projet.
				$r = preg_match('#riences.*<form.*action="([^"]*)".*</form>#sU', $page, $réponses, 0);
				$this->explo->données['adresse'] = $réponses[1];
				preg_match_all('#name="([^"]*)" value="([^"]*)"#sU', $réponses[0], $réponses);
				$this->explo->données['formu'] = array();
				foreach($réponses[1] as $n => $val)
					$this->explo->données['formu'][$val] = $réponses[2][$n];
				break;
			case 7: // Récupération de la page de modification des connaissances.
				/*$this->signaler('Obtention de la page d\'ajout de projets', null);*/
				break;
			case 5: // Suppression d'un projet.
			case 8: // Suppression d'une connaissance.
				$supp = $this->chercherSupprimable($page);
				switch($étape)
				{
					case 5: $machin = 'de projet'; $liste = 'projets'; break;
					case 8: $machin = 'de connaissance'; break;
				}
				$this->explo->données['supprimable'] = $supp[$liste];
				if(count($supp[$liste])) $this->signaler('Suppression '.$machin, null);
				else $cestdéjàpasmal = false; // Si on ne dit pas ça (qu'on compte encore faire quelque chose), la fin de la procédure va se croire obligée de sortir n'importe quoi pour rassurer l'utilisateur; or ce n'importe quoi va faire perdre les infos de session.
				break;
			case 9: // Ajout d'une connaissance.
				/* Trop chiant de tester s'il faut signaler ou non. */
				switch($étape)
				{
					case 5: $machin = 'd\'un projet'; break;
					case 9: $machin = 'd\'une connaissance'; break;
				}
				$this->signaler('Ajout '.$machin, null);
				break;
			case 6: $this->signaler('Fin', ''); break; // Fin.
		}
	}
	
	function chercherSupprimable($page)
	{
		/* On commence par repérer les diverses sections. */
		$cherche = array('formation' => 'Formations', 'projets' => 'Exp.*riences');
		$trouve = array(); // À la fin, on aura ici, pour chaque élément de $cherche, un tableau de forms permettant chacun de supprimer un élément de la section concernée.
		$sections = array();
		preg_match_all('#<b>.*('.join('|', $cherche).')</b>#sU', $page, $réponses, PREG_OFFSET_CAPTURE);
		for($z = count($réponses[1]); --$z >= 0;) // En ordre inverse: ainsi les sections seront classées en ordre inverse, et on pourra « remonter » le tableau en foreach jusqu'à trouver un $posDebutSection < $posOuJAiTrouveUnMachinAAffecterAUneSection.
		{
			foreach($cherche as $n => $c)
				if(preg_match("#$c#", $réponses[1][$z][0]))
				{
					$sections[$réponses[1][$z][1]] = $n; // Indice: position de début de la section; valeur: numéro de la section dans $cherche (et $trouve).
					break;
				}
		}
		/* Maintenant on chope les formulaires et on les dissèque avant d'en
		 * rattacher le contenu à la bonne section. */
		preg_match_all('#<form[ >][^>]*action="([^"]*)".*</form>#sU', $page, $réponses, PREG_OFFSET_CAPTURE);
		foreach($réponses[0] as $numFormu => $réponse)
			if(strpos($réponse[0], 'Supprimer')) // Un bouton supprimer, ça nous intéresse.
				foreach($sections as $posSection => $numSection)
					if($posSection < $réponse[1]) // Première section en partant de la fin qui commence avant ce qu'on vient de trouver: on considère donc qu'on a trouvé quelque chose à elle.
					{
						$trouve[$numSection][] = $this->obtenirChampsHidden($réponse[0]);
						$trouve[$numSection][count($trouve[$numSection]) - 1]['_adresse'] = $réponses[1][$numFormu][0];
						$trouve[$numSection][count($trouve[$numSection]) - 1]['ActionRemove'] = 'au revoir'; // Le Supprimer qu'on recherchait tout-à-l'heure, c'est lui.
						break;
					}
		return $trouve;
	}
	
	function obtenirChampsAttributsEZ($champs, $page)
	{
		$retour = array();
		foreach($champs as $dest => $signal)
		{
			$r = preg_match('#'.$signal.'.*<(input|textarea).*name="([^"]*)".*<input.*hidden.*name="ContentObjectAttribute_id.*".*value="([^"]*)"#sU', $page, $réponses, 0);
			$retour[$dest] = array($réponses[3], $réponses[2]);
		}
		return $retour;
	}
	
	function obtenirChampsHidden($page)
	{
		preg_match_all('#<input.*hidden.*name="([^"]*)".*value="([^"]*)"#', $page, $réponses);
		$champs = array();
		foreach($réponses[1] as $n => $champ)
			$champs[$champ] = $réponses[2][$n];
		return $champs;
	}
	
	function contenuPourProjet($numéro)
	{
		$francheRigolade = $this->données->expérience->projet[$numéro];
		
		/* Remplissage des autres champs. */
		
		$champs['boite'] = formu_conc($francheRigolade->société, 100, ' / ', true);
		$champs['boiboite'] = '-'; /* À FAIRE */
		
		$description = null;
		if(isset($francheRigolade->nom))
			$description = pasTeX_maj($francheRigolade->nom);
		if(isset($francheRigolade->description))
			if($description === null)
				$description = pasTeX_maj($francheRigolade->description);
			else
				$description .= ': '.$francheRigolade->description;
		$champs['projet'] = $description;
		
		$description = '';
		if($francheRigolade->rôle)
			$description = pasTeX_maj(formu_conc($francheRigolade->rôle, 0x100, '; '));
		for($i = -1, $n = count($francheRigolade->tâche); ++$i < $n;)
			$description .= ($i > 0 || $description ? "\n" : '').'- '.$francheRigolade->tâche[$i];
		$champs['boulot'] = $description;
		
		$champs['tech'] = formu_conc($francheRigolade->techno, 0x1000);
		
		/* Ce modèle-ci ne nous permet pas d'afficher plusieurs périodes
		 * pour le même projet, on fait donc la période englobante du tout. */
		$moments = pasTeX_unionPeriodes($francheRigolade->date);
		$champs['an'] = $moments[0][0];
		$champs['mois'] = sprintf('%02.2d', $moments[0][1] > 0 ? $moments[0][1] : 1);
		if(!$moments[1])
			$moments[1] = obtenir_datation(time());
		$champs['duree'] = (Periode::duree(Date::mef($moments[0]), Date::mef($moments[1]), 1) + 1).' mois';
		
		/* Ils veulent des champs avec un peu de quelque chose. */
		foreach($champs as $n => $c)
			if(!$c && $c !== 0)
				$champs[$n] = '-';
		
		return $champs;
	}
	
	function pondreConnaissance($numéro)
	{
		/* Reprenons connaissance parmi les catégories. $numéro est donné en
		 * partant de la fin. */
		
		for($m = count($this->données->connaissances->catégorie); --$m >= 0;)
		{
			$cat = $this->données->connaissances->catégorie[$m];
			if($numéro >= ($n = count($cat->connaissances) + 1))
				$numéro -= $n;
			else
			{
				$numéro = $n - $numéro - 2;
				break;
			}
		}
		
		$noms = $cat->connaissances;
		arsort($noms);
		$noms = array_keys($noms);
		$champs = &$_SESSION['champs'];
		
		/* Remplissage des autres champs. */
		
		$champs['name'] = formu_tronque($numéro >= 0 ? $noms[$numéro] : '=== '.$cat->nom.' ===', 50);
		$champs['years'] = formu_tronque(0, 2);
		$champs['usedid'] = 1;
		if($numéro >= 0)
		{
			$niveau = $cat->connaissances[$noms[$numéro]];
			$champs['levelid'] = ($niveau <= 0x4) ? 1 : (($niveau < 0x9) ? 2 : 3);
		}
		else
			$champs['levelid'] = 1;
		
		/* À FAIRE: utiliser des dates dans le modèle, et les appliquer aux
		 * années d'expérience. */
		
		$this->récupérer('/skills.asp');
	}
	
	protected static $modules = array(null, null, null, 'exp', 'exp', 'exp', null, 'conn', 'conn', 'conn'); // Pour chacune des listes de bidules, le comportement est similaire (accès à la page de modification, suppressions, ajouts); on va donc passer par le même code, avec ce tableau qui dira pour chaque étape sur quel module elle bosse.
}

?>
