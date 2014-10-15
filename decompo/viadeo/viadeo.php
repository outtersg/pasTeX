<?php
/*
 * Copyright (c) 2005,2007,2014 Guillaume Outters
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
require_once dirname(__FILE__).'/../../commun/http/formu.inc';

/*
php poule.php xml ~/perso/Boulot/CVs/cv.xml viadeo guillaume@free.fr:…
*/

class Viadeo extends Émetteur
{
	public static $sociétés = array
	(
		'(indépendant)'                               => null,
		'Astria'                                      => 'GIE Astria',
		'Bouygues Telecom'                            => 'Bouygues Telecom Entreprises',
		'CEA'                                         => 'CEA Saclay',
		'CFnews'                                      => null,
		'DCN'                                         => 'DCNS',
		'Lagardère Active'                            => 'Lagardère',
		'La Maison de Valérie'                        => 'Conforama',
		'Manreo'                                      => null,
		'Mendel 3D (groupe DURAN DUBOI)'              => null,
		'Ministère de l\'Agriculture'                 => null,
		'SAPN'                                        => null,
		'Smile'                                       => 'Smile, 1er intégrateur de solutions open source',
		'Sjøforsvaret (Marine de guerre Norvégienne)' => null,
		'Ville de Paris'                              => 'Préfecture de Police',
	);
	
	public function __construct() { $this->Émetteur('viadeo'); $this->petitÀPetit = false; /* Dépend en fait de si on est en interf web ou ligne de commande. */ }
	
	/*- Initialisation -------------------------------------------------------*/
	
	function analyserParams($argv, & $position)
	{
		$retour = array();
		
		if(isset($argv[$position]) && ($p = strpos($argv[$position], ':')) !== false)
		{
			$retour['id'] = substr($argv[$position], 0, $p);
			$retour['mdp'] = substr($argv[$position], $p + 1);
			++$position;
		}
		
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
			case self::ET_AUTH:
?>
	<div>
		Identifiant Viadeo: <input type="id" name="<?php echo($champ); ?>[id]"></input> Mot de passe: <input type="password" name="<?php echo($champ); ?>[mdp]"></input>
		<input type="submit" value="Connexion"/>
	</div>
<?php
				break;
			default:
				return false;
		}
		
		return true;
	}
	
	/*- Envoi du CV ----------------------------------------------------------*/
	
	const ET_AUTH = 0;
	const ET_EXP = 1;
	const ET_VIDE_EXP = 2;
	const ET_CREE_EXP = 3;
	const ET_FIN = 99;
	
	/**
	 * Liste les étapes à franchir.
	 * Chaque étape est listée; les étapes listées directement (sans sous-tableau) servent de point de départ pour la suivante. Ex.: [ 0, 1, [ 2, 3 ], 4 ]: la 1 partira de la page obtenue en soumettant la 0, la 2 partira de la 1, la 3 partira de la 2, la 4 repartira de la 1 (dernière étape au même niveau de profondeur).
	 */
	function étapes()
	{
		return array
		(
			self::ET_AUTH,
			self::ET_EXP, array(self::ET_VIDE_EXP), array(self::ET_CREE_EXP),
			self::ET_FIN,
		);
	}
	
	/**
	 * Vérifie la présence des données nécessaires.
	 * Si une donnée manque, renvoie le numéro de l'étape qui sera bloquée.
	 */
	function manquant()
	{
		switch(false)
		{
			case $this->verifPresence('id') && $this->verifPresence('mdp'): return self::ET_AUTH;
			case $this->données !== null: return 3;
		}
		return 0x1000;
	}
	
	function préparerÉtape($étape, $manquant, & $page, & $cestdéjàpasmal, & $mouvement, & $params)
	{
		// Si on n'a pas toutes les infos nécessaires pour traiter cette étape (ou une précédente), pas la peine d'annoncer qu'on l'aborde.
		if($manquant <= $étape)
			return;
		switch($étape)
		{
			case self::ET_AUTH:
				return $this->_signaler('Connexion');
			case self::ET_EXP:
				return $this->_signaler('Accès au CV (compte '.$params['id'].')');
			case self::ET_VIDE_EXP:
				return $this->_signaler('Suppression des projets', isset($this->explo->données['exp à faire sauter']) ? sprintf('%3d', count($this->explo->données['exp à faire sauter'])) : null);
			case self::ET_CREE_EXP:
				return $this->_signaler('Ajout des projets', isset($this->explo->données['num exp à pousser']) ? sprintf('%3d', $this->explo->données['num exp à pousser']) : null);
		}
	}
	
	protected function _dateIso($date, $fin = false)
	{
		$date = Date::mef($date);
		if($fin)
			$date = Date::obtenir(Date::calculerAvecIndefinis($date, true));
		else
			$date = Date::completer(array(1900, 1, 1, 0, 0, 0), $date);
		return sprintf('%04.4d-%02.2d-%02.2d', $date[0], $date[1], $date[2]);
	}
	
	public function _liTâche($x)
	{
		return '<li>'.htmlspecialchars($x).'</li>';
	}
	
	function gérerÉtape($étape, $manquant, & $page, & $cestdéjàpasmal, & $mouvement, & $params)
	{
		$d = & $this->explo->données;
		
		switch($étape)
		{
			case self::ET_AUTH: // Connexion.
				if($manquant <= $étape) // Si on n'a pas les renseignements pour s'authentifier, on les demande à l'utilisateur et on reprendra la connexion au coup suivant.
					return $cestdéjàpasmal = 1;
				$this->explo->aller('http://www.viadeo.com/fr/');
				$d['lienModif'] = $this->explo->allerEtTrouver('https://secure.viadeo.com/fr/signin', array('email' => $params['id'], 'password' => $params['mdp'], 'lang' => 'fr', 'CSRFToken' => 'null'), 'le lien vers la page de modification', '#window.VNS.member.link = "([^"]*)";#');
				$cestdéjàpasmal = 1;
				$mouvement = 1;
				break;
			case self::ET_EXP:
				$page = $this->explo->aller($d['lienModif']);
				$cestdéjàpasmal = 0; // On n'a pas fichu grand-chose, on peut continuer.
				$mouvement = 1;
				break;
			case self::ET_VIDE_EXP:
				if(!isset($d['exp à faire sauter']))
				{
					preg_match_all('#<a id="[^"]*" class="delete itemEmployment" href="[^"]*" data-id="([0-9a-z]*)">#', $page, $réponses);
					$d['exp à faire sauter'] = $réponses[1];
				}
				if(($entrée = array_shift($d['exp à faire sauter'])) !== null)
				{
					$this->explo->obtenir('http://www.viadeo.com/v/profileTimeline/employment/unregister/'.$entrée.'?ts='.floor(microtime(true) * 1000), array('idEncrypted' => $entrée, '_method' => 'delete'));
					$cestdéjàpasmal = 1;
					$mouvement = 0;
				}
				else
				{
					$cestdéjàpasmal = 0;
					$mouvement = 1;
				}
				break;
			case self::ET_CREE_EXP:
				// À FAIRE: "J'avais des personnes sous ma responsabilité"
				// À FAIRE: secteur d'activité
				// À FAIRE: effectif boîte
				// À FAIRE: "département" (pôle dans la boîte)
				if(!isset($d['num exp à pousser']))
					$d['num exp à pousser'] = count($this->données->expérience->projet) - 1;
				if($d['num exp à pousser'] < 0)
				{
					$cestdéjàpasmal = 0;
					$mouvement = 1;
					break;
				}
				
				$projet = $this->données->expérience->projet[$d['num exp à pousser']];
				
				$société = formu_conc(isset($projet->société) && count($projet->société) ? $projet->société : array('indépendant'), 100, ' / ');
				$dernièreSociété = '(indépendant)';
				if(isset($projet->société))
					foreach($projet->société as $dernièreSociété) {}
				$lienSociété = null;
				isset($d['sociétés']) || $d['sociétés'] = array();
				if(array_key_exists($dernièreSociété, $d['sociétés']))
					$lienSociété = $d['sociétés'][$dernièreSociété];
				else
				{
					if(array_key_exists($dernièreSociété, self::$sociétés))
					{
						$dernièreSociété = self::$sociétés[$dernièreSociété];
						if($dernièreSociété === null) // Si on pointe explicitement sur du null…
							$lienSociété = false; // … ça veut dire qu'on s'attend à n'avoir pas de réponse; le false indique "je ne veux pas de lien", à la différence du null qui signifie "je n'ai pas eu de réponse".
					}
					if($dernièreSociété && mb_strlen($dernièreSociété) > 3)
					{
						$résSociété = $this->explo->obtenir('http://www.viadeo.com/v/profileTimeline/employment/autocompleteNew?ts='.floor(microtime(true) * 1000).'&url=%2Fv%2FprofileTimeline%2Femployment%2FautocompleteNew&id=autocomplete-company&typingDelay&searchString='.urlencode($dernièreSociété));
						$résSociété = json_decode($résSociété);
						if(isset($résSociété->status) && $résSociété->status == 'SUCCESS' && isset($résSociété->data->completion))
							foreach(get_object_vars($résSociété->data->completion) as $id => $unRésSociété)
								if($unRésSociété->name == $dernièreSociété)
								{
									$lienSociété = $id;
									break;
								}
					}
				}
if($lienSociété === null)
$this->_signaler("bouh $dernièreSociété");
				
				$rôle = pasTeX_maj(formu_conc($projet->rôle, 70, '; '));
				
				/* 8192 caractères pour la description */
				
				$description = null;
				if(isset($projet->nom))
					$description = pasTeX_maj($projet->nom);
				if(isset($projet->description))
					if($description === null)
						$description = pasTeX_maj($projet->description);
					else
						$description .= ': '.$projet->description;
				if(strlen($description) > 8192) // Et encore, je suis sûr qu'ils vont m'emm… sur les accents.
					$description = substr($description, 0, 8186).' [...]';
				for($i = -1, $n = count($projet->tâche); ++$i < $n;)
					if(strlen($ajout = ($i > 0 ? "\n" : ($description !== null ? "\n\n" : '')).'- '.$projet->tâche[$i]) + strlen($description) > 8186)
					{
						$description .= "\n- ...";
						break;
					}
					else
						$description .= $ajout;
				
				$moments = pasTeX_unionPeriodes($projet->date);
				$encore = !$moments[1] || $moments[1] == array(-1, -1, -1, -1, -1, -1);
				$champs = array
				(
					//'CSRFToken' => zkMO-jiKOeSgP1xmctdWbe3ohE5jGMKCvmO0l-kX-_k',
					'companyIdEncrypted' => $lienSociété ? $lienSociété : '0020',
					'companySize' => '0',
					'departmentIdEncrypted' => '0020',
					'description' => $description,
					'endMonth' => $encore ? 0 : ($moments[1][1] >= 1 ? $moments[1][1] : 12),
					'endYear' => $encore ? 0 : $moments[1][0],
					'id' => '0',
					'idEncrypted' => '0020',
					'language' => 'fr',
					//'manager' => 'false',
					'membersCount' => '0',
					'name' => $société,
					'profileIdEncrypted' => '0020',
					//'ref' => '1413403468707-139',
					'removeStillIn' => 'false',
					'saveFromForm' => 'true',
					'sectorId' => '0',
					'sectorName' => '',
					'startMonth' => $moments[0][1] >= 1 ? $moments[0][1] : 1,
					'startYear' => $moments[0][0],
					'status' => '1',
					'stillIn' => $encore ? 'true' : 'false',
					'title' => $rôle,
				);
				$rés = $this->explo->obtenir('http://www.viadeo.com/v/profileTimeline/employment/register?ts='.floor(microtime(true) * 1000), $champs);
				if(!preg_match('#^{"status":"SUCCESS"#', $rés))
					$this->_signaler($rés);
				
				--$d['num exp à pousser'];
				
				$mouvement = 0;
				$cestdéjàpasmal = 1;
				break;
			case self::ET_FIN:
				return $cestdéjàpasmal = 2;
		}
	}
}

?>
