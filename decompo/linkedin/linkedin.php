<?php
/*
 * Copyright (c) 2014-2015 Guillaume Outters
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
php poule.php xml ~/perso/Boulot/CVs/cv.xml linkedin guillaume-linkedin@outters.eu:$mdp
*/

class Linkedin extends Émetteur
{
	public static $sociétés = array
	(
		'(indépendant)'                               => null,
		'Astria'                                      => 'ASTRIA',
		'Bouygues Telecom'                            => 'Bouygues Telecom Entreprises',
		'Brit Air'                                    => 'HOP!-BRIT AIR',
		'CEA'                                         => 'CEA - Commissariat à l&#39;énergie atomique et aux énergies alternatives',
		'CFnews'                                      => 'CFNEWS (Corporate Finance News)',
		//'DCN'                                         => 'DCN',
		'Lagardère Active'                            => 'Lagardere Active (TV Activities)',
		'La Maison de Valérie'                        => 'Conforama',
		'Manreo'                                      => null,
		'Mendel 3D (groupe DURAN DUBOI)'              => 'Duran duboi',
		'Ministère de l\'Agriculture'                 => null,
		'Ministère de la Défense'                     => null,
		'SAPN'                                        => 'SAPN (Société des Autoroutes Paris Normandie)',
		'Smile'                                       => 'Smile Open Source Solutions',
		'Sjøforsvaret (Marine de guerre Norvégienne)' => null,
		'Ville de Paris'                              => 'Préfecture de police de Paris',
	);
	
	public function __construct() { $this->Émetteur('linkedin'); $this->petitÀPetit = false; /* Dépend en fait de si on est en interf web ou ligne de commande. */ }
	
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
		Identifiant LinkedIn: <input type="id" name="<?php echo($champ); ?>[id]"></input> Mot de passe: <input type="password" name="<?php echo($champ); ?>[mdp]"></input>
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
	
	protected function _champ($page, $nom)
	{
		preg_match_all('/name="'.$nom.'" value="([^"]*)"/', $page, $rés);
		return $rés[1][0];
	}
	
	function gérerÉtape($étape, $manquant, & $page, & $cestdéjàpasmal, & $mouvement, & $params)
	{
		$d = & $this->explo->données;
		if(!isset($this->_session))
			$this->_session = array();
		$s = & $this->_session;
		
		if(!isset($s['treeId']) && isset($page) && preg_match_all('#<meta name="treeID" content="([^"]*)">#', $page, $rés))
			$s['treeId'] = $rés[1][0];
		if(isset($s['treeId']))
			$enTêtesAjax = array
			(
				'X-Requested-With: XMLHttpRequest',
				'X-IsAJAXForm: 1',
				'X-LinkedIn-traceDataContext: X-LI-ORIGIN-UUID='.$s['treeId'],
			);
		
		switch($étape)
		{
			case self::ET_AUTH: // Connexion.
				if($manquant <= $étape) // Si on n'a pas les renseignements pour s'authentifier, on les demande à l'utilisateur et on reprendra la connexion au coup suivant.
					return $cestdéjàpasmal = 1;
				$page = $this->explo->aller('https://www.linkedin.com/uas/login');
				
				$t = time();
				$id = $params['id'];
				$csrfToken = $this->_champ($page, 'csrfToken');
				$s['csrfToken'] = $csrfToken;
				$csrfParam = $this->_champ($page, 'loginCsrfParam');
				$sourceAlias = $this->_champ($page, 'sourceAlias');
				for($i = 3; --$i >= 0;)
					$pif[$i] = rand() % 900000000 + 100000000;
				$pif = implode(':', $pif);
				preg_match_all('#<script>(var jsRandomCalculator=.*)</script>#U', $page, $rés);
				$jsrc = $rés[1][0];
				$jsrc .= "console.log(jsRandomCalculator.compute('$pif', '$id', $t));";
				$ficpif = '/tmp/temp.linkedin.'.rand().'.js';
				file_put_contents($ficpif, $jsrc);
				$crc = exec('node '.$ficpif);
				
				$page = $this->explo->aller('https://www.linkedin.com/uas/login-submit', array
				(
					'session_key' => $params['id'],
					'session_password' => $params['mdp'],
					'clickedSuggestion' => 'false',
					'client_n' => $pif,
					'client_output' => $crc,
					'client_r' => $params['id'].':'.$pif,
					'client_ts' => $t,
					'client_v' => '1.0.1', // Prélevé dans le jsRandomCalculator.
					'csrfToken' => $csrfToken,
					'fromEmail' => '',
					'isJsEnabled' => 'true',
					'loginCsrfParam' => $csrfParam,
					'session_redirect' => '',
					'signin' => 'S\'identifier',
					'sourceAlias' => $sourceAlias,
					'source_app' => '',
					'trk' => '',
					'tryCount' => '',
				));
				
				// La première fois que l'on se connecte depuis une adresse IP, LinkedIn envoie un message pour nous demander confirmation que nous ne sommes pas des pirates. Un minimum d'interaction est alors à prévoir (récup du mot de passe sur notre messagerie, réinjection ici).
				
				if(preg_match('/seems suspicious/', $page))
				{
					$code = $this->_demander('Vous avez dû recevoir un code par mél. Quel est ce code?');
					$page = $this->explo->aller('/uas/ato-pin-challenge-submit', array
					(
						'PinVerificationForm_pinParam' => $code,
						'csrfToken' => $this->_champ($page, 'csrfToken'),
						'dts' => $this->_champ($page, 'dts'),
						'origSourceAlias' => $sourceAlias,
						'security-challenge-id' => $this->_champ($page, 'loginCsrfParam'),
						'signin' => 'Submit',
						'sourceAlias' => $this->_champ($page, 'sourceAlias'),
					));
				}
				$cestdéjàpasmal = 1;
				$mouvement = 1;
				break;
			case self::ET_EXP:
				$page = $this->explo->aller('https://www.linkedin.com/profile/edit?trk=nav_responsive_sub_nav_edit_profile');
				$cestdéjàpasmal = 0; // On n'a pas fichu grand-chose, on peut continuer.
				$mouvement = 1;
				break;
			case self::ET_VIDE_EXP:
				if(!isset($s['datationAntiRejeu']))
					$s['datationAntiRejeu'] = $this->_datationAntiRejeu($page);
				if(!isset($s['exp à faire sauter']))
				{
					preg_match_all('#"link__remove_position":"([^"]*)"#', $page, $résbis);
					$suppr = array();
					foreach($résbis[1] as $lien)
						$suppr[] = json_decode('"'.$lien.'"');
					$s['exp à faire sauter'] = $suppr;
					
					preg_match_all('#{"name":"sourceAlias","id":"sourceAlias[^"]*","type":"hidden","value":"([^"]*)"}}#', $page, $sourceAliases);
					$s['sourceAlias'] = $sourceAliases[1][0];
				}
				if(($lien = array_shift($s['exp à faire sauter'])) !== null)
				{
					$lien = explode('?', $lien, 2);
					$champs = array();
					foreach(explode('&', $lien[1]) as $champValeur)
					{
						list($champ, $valeur) = explode('=', $champValeur, 2);
						$champs[$champ] = urldecode($valeur);
					}
					$url = $lien[0];
					$champs += array
					(
						'locale' => 'fr_FR',
						'timestamp' => $s['datationAntiRejeu'],
						'useJsonResponse' => 'true',
						'experienceId' => '', //$champs['positionID'],
						'csrfToken' => $s['csrfToken'],
						'sourceAlias' => $s['sourceAlias'],
						'profileVersionTag' => '',
						'settingsVersionTag' => '',
					);
					$url .= '-submit';
					$retour = $this->explo->obtenir($url, $champs, false, $enTêtesAjax);
					$s['datationAntiRejeu'] = $this->_datationAntiRejeu($retour);
					$cestdéjàpasmal = 1;
					$mouvement = 0;
				}
				else
				{
					// … on passe…
					$cestdéjàpasmal = 0; // … directement…
					$mouvement = 1; // … à la prochaine étape.
				}
				break;
			case self::ET_CREE_EXP:
				if(!isset($s['num exp à pousser']))
					$s['num exp à pousser'] = count($this->données->expérience->projet) - 1;
				if($s['num exp à pousser'] < 0)
				{
					$cestdéjàpasmal = 0;
					$mouvement = 1;
					break;
				}
				
				$projet = $this->données->expérience->projet[$s['num exp à pousser']];
				
				$société = formu_conc(isset($projet->société) && count($projet->société) ? $projet->société : array('indépendant'), 100, ' / ');
				$dernièreSociété = '(indépendant)';
				if(isset($projet->société))
					foreach($projet->société as $dernièreSociété) {}
				$lienSociété = null;
				isset($s['sociétés']) || $s['sociétés'] = array();
				if(array_key_exists($dernièreSociété, $s['sociétés']))
					$lienSociété = $s['sociétés'][$dernièreSociété];
				else
				{
					if(array_key_exists($dernièreSociété, self::$sociétés))
					{
						$dernièreSociété = self::$sociétés[$dernièreSociété];
						if($dernièreSociété === null) // Si on pointe explicitement sur du null…
							$lienSociété = false; // … ça veut dire qu'on s'attend à n'avoir pas de réponse; le false indique "je ne veux pas de lien", à la différence du null qui signifie "je n'ai pas eu de réponse".
					}
					if($dernièreSociété && mb_strlen($dernièreSociété) > 2)
					{
						$résSociété = $this->explo->obtenir('/ta/company?query='.urlencode($dernièreSociété).'&loc=P');
						$résSociété = json_decode($résSociété);
						if(isset($résSociété->resultList))
							foreach($résSociété->resultList as $unRésSociété)
								if($unRésSociété->displayName == $dernièreSociété)
								{
									$lienSociété = $unRésSociété->id;
									break;
								}
					}
				}
				
				if($lienSociété === null)
					$this->_signaler("Attention! Société \"$dernièreSociété\" non retrouvée.");
				// Autres alternatives: _demander, ou bien (si l'on tourne sous un décompositeur mem), $this->enregistrerEtQuitter = true puis break.
				
				$rôle = pasTeX_maj(formu_conc(isset($projet->rôle) ? $projet->rôle : '-', 70, '; '));
				
				/* 2000 caractères pour la description */
				
				$description = null;
				if(isset($projet->nom))
					$description = pasTeX_maj($projet->nom);
				if(isset($projet->description))
					if($description === null)
						$description = pasTeX_maj($projet->description);
					else
						$description .= ': '.$projet->description;
				if(strlen($description) > 1979)
					$description = substr($description, 0, 1979).' […]';
				for($i = -1, $n = count($projet->tâche); ++$i < $n;)
					if(strlen($ajout = ($i > 0 ? "\n" : ($description !== null ? "\n\n" : '')).'• '.$projet->tâche[$i]) + strlen($description) > 1979)
					{
						$description .= "\n• etc.";
						break;
					}
					else
						$description .= $ajout;
				
				$moments = pasTeX_unionPeriodes($projet->date);
				$encore = !$moments[1] || $moments[1] == array(-1, -1, -1, -1, -1, -1);
				$champs = array
				(
					'checkboxValue' => '',
					'companyDisplayName' => $société,
					'companyID' => isset($lienSociété) ? $lienSociété : '0', // À FAIRE.
					'companyName' => $société,
					'csrfToken' => $s['csrfToken'],
					'defaultLocaleParam' => 'fr_FR',
					'endDateMonth' => $encore ? null : ($moments[1][1] >= 1 ? $moments[1][1] : 12),
					'endDateYear' => $encore ? null : $moments[1][0],
					'experienceId' => '',
					'isCurrent' => $encore ? 'isCurrent' : null,
					'locale' => 'fr_FR',
					'positionID' => '', // À FAIRE.
					'positionLocation' => '', // À FAIRE.
					'positionLocationName' => '', // À FAIRE.
					'profileVersionTag' => '',
					'sendMailCheckboxValue' => '',
					'sourceAlias' => $s['sourceAlias'],
					'startDateMonth' => $moments[0][1] >= 1 ? $moments[0][1] : 1,
					'startDateYear' => $moments[0][0],
					'submit' => 'Enregistrer',
					'summary' => $description,
					'timestamp' => $s['datationAntiRejeu'],
					'title' => $rôle,
					'trk-infoParams' => '',
					'updateHeadline' => 'false',
					'updatedHeadline' => null,
					'useJsonResponse' => 'true',
				);
				foreach($champs as $champ => $val)
					if(!isset($val))
						unset($champs[$champ]);
				$rés = $this->explo->obtenir('https://www.linkedin.com/profile/edit-position-submit', $champs, null, $enTêtesAjax);
				$s['datationAntiRejeu'] = $this->_datationAntiRejeu($rés);
				
				--$s['num exp à pousser'];
				
				$mouvement = 0;
				$cestdéjàpasmal = 1;
				break;
			case self::ET_FIN:
				return $cestdéjàpasmal = 2;
		}
	}
	
	protected function _datationAntiRejeu($àTrouverLàDedans)
	{
		if(!preg_match_all('#"profileTimestamp":{"timestamp":([0-9]*)}#', $àTrouverLàDedans, $rés))
			if(!preg_match_all('#timestamp":{"name":"timestamp","id":"timestamp-[^"]*","type":"hidden","value":"([0-9]*)"}#', $àTrouverLàDedans, $rés))
				throw new Exception('Impossible de dénicher le timestamp anti-rejeu dans le contenu');
		
		return $rés[1][0];
	}
}

?>
