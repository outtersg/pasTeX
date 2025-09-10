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

/* À FAIRE: désolidariser réellement affichage et dialogue avec Monster: on a
 * encore des cas trop limite. */

class Monster extends Émetteur
{
	public function __construct() { parent::__construct('monster'); }
	
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
	
	function étapes() { return array(0, 1, 2, array(3, 4, 5), array(7, 8, 9), 6); }
	
	function manquant()
	{
		if(!$this->verifPresence('id') || !$this->verifPresence('mdp')) return 0;
		else if(!$this->verifPresence('num')) return 1;
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
				$page = $this->explo->aller('http://mon.monster.fr/login.asp', array('user' => $params['id'], 'password' => $params['mdp']));
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
				$page = $this->explo->aller($this->explo->données['cv'][$params['num']].'&mode=edit');
				$params['liens'] = array();
				foreach(array('exp' => 'experience', 'conn' => 'skills') as $cat => $lien)
				{
					preg_match('/<a href="([^"]*'.$lien.'.asp[^"]*)">/', $page, $reponses, 0);
					if(count($reponses[0]) > 0)
						$params['liens'][$cat] = strtr($reponses[1], array('&amp;' => '&'));
					else
						$this->explo->données['pos'] = -1; // On a perdu la session, retour en arrière.
				}
				$cestdéjàpasmal = 1;
				$mouvement = 1;
				break;
			case 3: // Récupération de la page de modification des projets.
			case 7: // Récupération de la page de modification des connaissances.
				if($manquant <= 3) // Si notre session a déjà tous les renseignements nécessaires, mais qu'on est encore dans l'interface de paramétrage, il nous faut laisser au compo le temps de charger le CV.
					return $cestdéjàpasmal = 1;
				$page = $this->récupérer($params['liens'][self::$modules[$étape]]);
				$mouvement = 1;
				break;
			case 4: // Suppression d'un projet.
			case 8: // Suppression d'une connaissance.
				$mouvement = 1;
				if($params['faire'][self::$modules[$étape]] == 2)
					if(($z = $this->explo->données['à effacer']) !== null)
					{
						$page = $this->explo->aller($z);
						$cestdéjàpasmal = 1;
						$mouvement = 0;
					}
				break;
			case 5: // Ajout d'un projet.
			case 9: // Ajout d'une connaissance.
				$mouvement = 1;
				if($params['faire'][self::$modules[$étape]] >= 1 && array_key_exists(self::$modules[$étape], $params['liens']))
				{
					if(!array_key_exists('numExp', $this->explo->données))
					{
						$n = 0;
						switch($étape)
						{
							case 5: if(array_key_exists('expérience', $this->données)) $n = count($this->données->expérience->projet); break;
							case 9: if(array_key_exists('connaissances', $this->données)) { $n = 0; foreach($this->données->connaissances->catégorie as $cat) $n += 1 + count($cat->connaissances); } break;
						}
						$this->explo->données['numExp'] = $n;
					}
					if(--$this->explo->données['numExp'] >= 0)
					{
						switch($étape)
						{
							case 5: $this->pondreProjet($this->explo->données['numExp']); break;
							case 9: $this->pondreConnaissance($this->explo->données['numExp']); break;
						}
						$cestdéjàpasmal = 1;
						if($this->explo->données['numExp'] > 0) // Encore des projets à rentrer, on ne laisse pas encore la main à l'étape suivante.
							$mouvement = 0;
						else
							unset($this->explo->données['numExp']); // Tout le monde s'en sert: il ne faudrait pas que le prochain pondeur de liste croit avoir fini dès son lancement.
					}
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
				preg_match_all('/<a href="([^"]*resumeid=([0-9]*)[^"]*)"> *([^<]*)<\/a>/', $page, $réponses, 0);
				if(count($réponses[0]) != 0) // Sinon, c'est qu'on a dû se faire expirer la session au nez.
				{
					$this->explo->données['cv'] = array();
					$this->explo->données['affcv'] = array();
					for($z = count($réponses[0]); --$z >= 0;)
					{
						$this->explo->données['cv'][$réponses[2][$z]] = $réponses[1][$z]; // Le numéro du CV sert d'indice, son URL est la donnée enregistrée.
						$this->explo->données['affcv'][$réponses[2][$z]] = $réponses[3][$z];
					}
				}
				break;
			case 2: $this->signaler('Obtention de la page de modification du CV', null); break; // Récupération de la page de modification du CV.
			case 3: // Récupération de la page de modification des projets.
			case 7: // Récupération de la page de modification des connaissances.
				/*$this->signaler('Obtention de la page d\'ajout de projets', null);*/
				break;
			case 4: // Suppression d'un projet.
			case 8: // Suppression d'une connaissance.
				if($params['faire'][self::$modules[$étape]] == 2)
				{
					$r = preg_match('/<a href="([^"]*&action=delete[^"]*)"/', $page, $réponses, 0);
					$this->explo->données['à effacer'] = $r ? $réponses[1] : null;
					switch($étape)
					{
						case 4: $machin = 'de projet'; break;
						case 8: $machin = 'de connaissance'; break;
					}
					if($r) $this->signaler('Suppression '.$machin, null);
					else $cestdéjàpasmal = false; // Si on ne dit pas ça (qu'on compte encore faire quelque chose), la fin de la procédure va se croire obligée de sortir n'importe quoi pour rassurer l'utilisateur; or ce n'importe quoi va faire perdre les infos de session.
				}
				break;
			case 5: // Ajout d'un projet.
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
	
	function pondreProjet($numéro)
	{
		$francheRigolade = $this->données->expérience->projet[$numéro];
		$champs = &$_SESSION['champs'];
		
		/* Remplissage des autres champs. */
		
		$champs['company'] = formu_conc($francheRigolade->société, 100, ' / ', true);
		$champs['monsterindustryid'] = 0; /* À FAIRE: mais pour le moment on laisse ce choix (« Tous »), parce que je viens d'une SSII, donc informatique, mais qui bossait pour différents domaines, alors Viaduc et leurs restrictions, ils m'embêtent. */
		$champs['location'] = formu_conc($francheRigolade->lieu, 100);
		$champs['title'] = pasTeX_maj(formu_conc($francheRigolade->rôle, 70, '; '));
		
		$description = null;
		if(isset($francheRigolade->nom))
			$description = pasTeX_maj($francheRigolade->nom);
		if(isset($francheRigolade->description))
			if($description === null)
				$description = pasTeX_maj($francheRigolade->description);
			else
				$description .= ': '.$francheRigolade->description;
		if(strlen($description) > 2000)
			$description = substr($description, 0, 1994).' [...]';
		for($i = -1, $n = count($francheRigolade->tâche); ++$i < $n;)
			if(strlen($ajout = ($i > 0 ? "\n" : ($description !== null ? "\n\n" : '')).'- '.$francheRigolade->tâche[$i]) + strlen($description) > 1994)
			{
				$description .= "\n- ...";
				break;
			}
			else
				$description .= $ajout;
		$champs['description'] = $description; // 2000 car. maxi.
		
		/* Ce modèle-ci ne nous permet pas d'afficher plusieurs périodes
		 * pour le même projet, on fait donc la période englobante du tout. */
		$moments = pasTeX_unionPeriodes($francheRigolade->date);
		$champs['startyear'] = $moments[0][0];
		$champs['startmonth'] = $moments[0][1] > 0 ? $moments[0][1] : 1;
		if($moments[1] === null)
		{
			$champs['endyear'] = '';
			$champs['endmonth'] = '';
		}
		else
		{
			$champs['endyear'] = $moments[1][0];
			$champs['endmonth'] = $moments[1][1] > 0 ? $moments[1][1] : 12;
		}
		
		$this->récupérer('/experience.asp'); // Bon, je code en dur, pour une fois.
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