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

require_once('commun/http/emetteur.inc');
require_once('commun/http/formu.inc');

/* À FAIRE: désolidariser réellement affichage et dialogue avec Viaduc: on a
 * encore des cas trop limite. */

class Viaduc extends Émetteur
{
	function Viaduc() { $this->Émetteur('viaduc'); }
	
	function analyserParams($argv, &$position)
	{
		$retour = array();
		
		/* À FAIRE */
		
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
			case 2:
				$this->pondreContenuPageAjax($champ, $args);
				break;
			case 0:
?>
	<div>Le module Viaduc ajoute vos expériences Viaduc celles qui figurent dans votre CV. ATTENTION! Pour le moment expérimental. Pensez à avoir une copie de secours de vos expériences.</div>
	<div>
		Identifiant Viaduc: <input type="id" name="<?php echo($champ); ?>[id]"></input> Mot de passe: <input type="password" name="<?php echo($champ); ?>[mdp]"></input>
	</div>
<?php
				break;
			default:
				return false;
		}
		
		return true;
	}
	
	/* Fonctions comportementales */
	
	function étapes() { return array(0, 1, array(2, 3, 4), 5); }
	
	function manquant()
	{
		if(!$this->verifPresence('id') || !$this->verifPresence('mdp')) return 0;
		else if($this->données === null) return 1;
		else if($this->petitÀPetit && !$this->verifPresence('clientdemandeur')) return 2;
		return 0x1000;
	}
	
	function gérerÉtape($étape, $manquant, &$page, &$cestdéjàpasmal, &$mouvement, &$params)
	{
		switch($étape)
		{
			case 0: // Connexion.
				if($manquant <= 0) // Si on n'a pas les renseignements pour s'authentifier, on les demande à l'utilisateur et on reprendra la connexion au coup suivant.
					return $cestdéjàpasmal = 1;
				$page = $this->explo->aller('http://www.viaduc.com/connexion/', array('email' => $params['id'], 'password' => $params['mdp']));
				$cestdéjàpasmal = 1;
				$mouvement = 1;
				break;
			case 1: // Étape vide, sauf si on n'a pas encore nos données, auquel cas on fait poireauter l'utilisateur le temps d'une requête HTTP qui va nous permettre de les charger.
				if($manquant <= 1)
					return $cestdéjàpasmal = 1;
				$mouvement = 1;
				break;
			case 2: // Récupération de la page parcours professionnel.
				$page = $this->explo->aller('/profil/monparcours/');
				$cestdéjàpasmal = 1;
				$mouvement = 1;
				break;
			case 3: // Suppression d'un projet.
				$mouvement = 1;
				if(($z = $this->explo->données['à effacer']) !== null)
				{
					$page = $this->explo->aller($z);
					$cestdéjàpasmal = 1;
					$mouvement = 0;
				}
				break;
			case 4: // Ajout d'un projet.
				$mouvement = 1;
				if(!array_key_exists('numExp', $this->explo->données))
					$this->explo->données['numExp'] = array_key_exists('expérience', $this->données) ? count($this->données->expérience->projet) : 0;
				if(--$this->explo->données['numExp'] >= 0)
				{
					$this->pondreProjet($this->explo->données['numExp']);
					$cestdéjàpasmal = 1;
					if($this->explo->données['numExp'] > 0) // Encore des projets à rentrer, on ne laisse pas encore la main à l'étape suivante.
						$mouvement = 0;
					else
						unset($this->explo->données['numExp']); // Tout le monde s'en sert: il ne faudrait pas que le prochain pondeur de liste croit avoir fini dès son lancement.
				}
				break;
			case 5: // Fin.
				return $cestdéjàpasmal = 2;
		}
	}
	
	function préparerÉtape($étape, $manquant, &$page, &$cestdéjàpasmal, &$mouvement, &$params)
	{
		switch($étape)
		{
			case 0: if($manquant > 0) $this->signaler('Connexion', null); break; // Connexion.
			case 1: break; // Étape vide, sauf si on n'a pas encore nos données, auquel cas on fait poireauter l'utilisateur le temps d'une requête HTTP qui va nous permettre de les charger.
			case 2: $this->signaler('Obtention de la page de modification', null); break; // Récupération de la page parcours professionnel.
			case 3: // Suppression d'un projet.
				$r = preg_match('/href="([^"]*\?delete=true[^"]*)"/', $page, $réponses, 0);
				$this->explo->données['à effacer'] = $r ? strtr($réponses[1], array('&amp;' => '&')) : null;
				if($r) $this->signaler('Suppression de projet', null);
				else $cestdéjàpasmal = false; // Si on ne dit pas ça (qu'on compte encore faire quelque chose), la fin de la procédure va se croire obligée de sortir n'importe quoi pour rassurer l'utilisateur; or ce n'importe quoi va faire perdre les infos de session.
				break;
			case 4: // Ajout d'un projet.
				$this->signaler('Ajout d\'un projet', null);
				break;
			case 5: $this->signaler('Fin', ''); break; // Fin.
		}
	}
	
	function pondreProjet($numéro)
	{
		$francheRigolade = $this->données->expérience->projet[$numéro];
		$champs = &$_SESSION['champs'];
		
		/* Remplissage des autres champs. */
		
		$champs['company'] = formu_conc($francheRigolade->société && count($francheRigolade->société) ? $francheRigolade->société : array('indépendant'), 100, ' / ');
		$champs['positionTitle'] = pasTeX_maj(formu_conc($francheRigolade->rôle, 70, '; '));
		
		/* 8192 caractères pour la description */
		
		$description = null;
		if(isset($francheRigolade->nom))
			$description = pasTeX_maj($francheRigolade->nom);
		if(isset($francheRigolade->description))
			if($description === null)
				$description = pasTeX_maj($francheRigolade->description);
			else
				$description .= ': '.$francheRigolade->description;
		if(strlen($description) > 8192) // Et encore, je suis sûr qu'ils vont m'emm… sur les accents.
			$description = substr($description, 0, 8186).' [...]';
		for($i = -1, $n = count($francheRigolade->tâche); ++$i < $n;)
			if(strlen($ajout = ($i > 0 ? "\n" : ($description !== null ? "\n\n" : '')).'- '.$francheRigolade->tâche[$i]) + strlen($description) > 8186)
			{
				$description .= "\n- ...";
				break;
			}
			else
				$description .= $ajout;
		$champs['description'] = $description;
		
		$moments = pasTeX_unionPeriodes($francheRigolade->date);
		$champs['startDateYear'] = $moments[0][0];
		if($moments[1] === null)
			$champs['stillInPosition'] = 1;
		else
			$champs['endDateYear'] = $moments[1][0];
		
		$this->récupérer('/profil/ajoutposte/'); // Bon, je code en dur, pour une fois.
	}
	
	protected static $modules = array(null, null, null, 'exp', 'exp', 'exp', null, 'conn', 'conn', 'conn'); // Pour chacune des listes de bidules, le comportement est similaire (accès à la page de modification, suppressions, ajouts); on va donc passer par le même code, avec ce tableau qui dira pour chaque étape sur quel module elle bosse.
}

?>