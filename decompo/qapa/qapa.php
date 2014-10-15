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
php poule.php xml ~/perso/Boulot/CVs/cv.xml qapa guillaume-qapa@outters.eu:…
*/

class Qapa extends Émetteur
{
	public static $rôles = array
	(
		'administrateur système'                  => 'Administrateur / Administratrice système informatique',
		'architecte'                              => 'Architecte technique informatique',
		'chef de projet'                          => 'Chef de projet informatique',
		'chef de projet technique'                => 'Chef de projet technique web / internet',
		'concepteur'                              => 'Développeur / Développeuse informatique',
		'concepteur boîtiers AIM avec noyau MCPC' => 'Développeur / Développeuse informatique',
		'concepteur eZ publish'                   => 'Concepteur Développeur / Conceptrice Développeuse PHP',
		'concepteur HP/UX'                        => 'Développeur / Développeuse informatique',
		'concepteur HP-UX'                        => 'Développeur / Développeuse informatique',
		'concepteur Java'                         => 'Développeur / Développeuse JAVA',
		'concepteur PHP'                          => 'Concepteur Développeur / Conceptrice Développeuse PHP', // Qapa ne connaît que ce langage de dév en conception?
		'concepteur Visual C++'                   => 'Développeur / Développeuse informatique',
		'développement en loisir'                 => 'Développeur / Développeuse informatique',
		'développeur'                             => 'Développeur / Développeuse informatique',
		'développeur bash'                        => 'Intégrateur / Intégratrice d\'application informatique',
		'développeur eZ publish'                  => 'Développeur / Développeuse PHP',
		'développeur eZ publish'                  => 'Développeur / Développeuse PHP',
		'développeur ILOG Views'                  => 'Développeur / Développeuse informatique',
		'développeur Java'                        => 'Développeur / Développeuse JAVA',
		'développeur Mac OS 8'                    => 'Développeur / Développeuse informatique',
		'développeur Mac OS X'                    => 'Développeur / Développeuse informatique',
		'développeur PHP'                         => 'Développeur / Développeuse PHP',
		'développeur Solaris 9'                   => 'Développeur / Développeuse informatique',
		'étudiant'                                => 'Développeur / Développeuse informatique',
		'formateur'                               => 'Formateur / Formatrice informatique',
		'intégrateur'                             => 'Intégrateur / Intégratrice d\'application informatique',
		'testeur'                                 => 'Testeur / Testeuse informatique',
	);
	
	public function __construct() { $this->Émetteur('qapa'); $this->petitÀPetit = false; /* Dépend en fait de si on est en interf web ou ligne de commande. */ }
	
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
		Identifiant Qapa: <input type="id" name="<?php echo($champ); ?>[id]"></input> Mot de passe: <input type="password" name="<?php echo($champ); ?>[mdp]"></input>
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
				return $this->_signaler('Accès au CV (compte '.$params['uid'].')');
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
		switch($étape)
		{
			case self::ET_AUTH: // Connexion.
				if($manquant <= $étape) // Si on n'a pas les renseignements pour s'authentifier, on les demande à l'utilisateur et on reprendra la connexion au coup suivant.
					return $cestdéjàpasmal = 1;
				$this->explo->suivreLocations = false; // Le code pour se connecter à l'API nous est envoyé dans les données retour, mais un en-tête Location est balancé avec.
				$page = $this->explo->aller('http://www.qapa.fr/rest/v1/sessions', array('email' => $params['id'], 'password' => $params['mdp']));
				$retour = json_decode($page);
				$params['uid'] = $retour->id;
				$params['api_token'] = $retour->api_token;
				$this->explo->auth = base64_decode($params['api_token']);
				$this->api = $this->explo->cloner();
				$this->api->auth = base64_decode($params['api_token']);
				$this->api->aller($retour->_links->self->href);
				$cestdéjàpasmal = 1;
				$mouvement = 1;
				break;
			case self::ET_EXP:
				$page = $this->explo->aller('http://www.qapa.fr/candidats/mon-compte/edit-experiences');
				$cestdéjàpasmal = 0; // On n'a pas fichu grand-chose, on peut continuer.
				$mouvement = 1;
				break;
			case self::ET_VIDE_EXP:
				if(!isset($this->explo->données['exp à faire sauter']))
				{
					preg_match('#var getExperiences = (.*);\n#', $page, $réponses);
					$this->explo->données['exp à faire sauter'] = json_decode($réponses[1]);
				}
				if(($entrée = array_shift($this->explo->données['exp à faire sauter'])) !== null)
				{
					$this->explo->aller('http://www.qapa.fr/rest/v1/applicants/'.$params['uid'].'/experiences/'.$entrée->id.'?_method=DELETE', array('id' => $entrée->id));
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
				if(!isset($this->explo->données['num exp à pousser']))
					$this->explo->données['num exp à pousser'] = count($this->données->expérience->projet) - 1;
				if($this->explo->données['num exp à pousser'] < 0)
				{
					$cestdéjàpasmal = 0;
					$mouvement = 1;
					break;
				}
				
				$projet = $this->données->expérience->projet[$this->explo->données['num exp à pousser']];
				
				// Rôle?
				
				$rôle = isset($projet->rôle[0]) ? $projet->rôle[0] : '';
				if(in_array($rôle, array('concepteur', 'développeur')) && isset($projet->techno[0]))
					$rôle = $rôle.' '.$projet->techno[0];
				
				while(!is_int($rôle))
				{
					if(isset(self::$rôles[$rôle]))
						$rôle = self::$rôles[$rôle];
					if(isset($this->explo->données['rôlesQapa'][$rôle]))
						$rôle = $this->explo->données['rôlesQapa'][$rôle];
					if(!is_int($rôle))
					{
						if(mb_strlen($rôle) > 3)
						{
							$résRôle = $this->explo->obtenir('http://www.qapa.fr/rest/v1/trades?q='.urlencode($rôle));
							$résRôle = json_decode($résRôle);
							if($résRôle->total_items >= 1)
								foreach($résRôle->_embedded->items as $rôlePossible)
									if($rôlePossible->label == $rôle)
									{
										$rôle = $this->explo->données['rôlesQapa'][$rôle] = $rôlePossible->id;
										break 2;
									}
						}
						$this->données->expérience->projet[$this->explo->données['num exp à pousser']]->rôle[0] = $this->_demander("Version Qapa de \"$rôle\"?");
						$cestdéjàpasmal = 0;
						$mouvement = 0;
						break 2;
					}
				}
				
				$moments = pasTeX_unionPeriodes($projet->date);
				$champs['startDateYear'] = $moments[0][0];
				if($moments[1] === null)
					$champs['stillInPosition'] = 1;
				else
					$champs['endDateYear'] = $moments[1][0];
				
				$société = '(indépendant)';
				if(isset($projet->société))
					foreach($projet->société as $société) {}
				
				$nom = array();
				isset($projet->nom) && $nom[] = $projet->nom;
				isset($projet->description) && $nom[] = $projet->description;
				$nomHtml = count($nom) ? '<b>'.htmlspecialchars(implode(': ', $nom)).'</b>' : '';
				
				$exp = array
				(
					'ref_sectors_id' => 22, // Informatique. À FAIRE: moduler.
					'company' => $société,
					'location' => 'Paris', // À FAIRE.
					'description' => $nomHtml.'<ul>'.implode('', array_map(array($this, '_liTâche'), $projet->tâche)).'</ul>', // À FAIRE: 2000 caractères, HTML autorisé.
					'ref_trades_id' => $rôle,
					'start_date' => $this->_dateIso($moments[0]),
					'end_date' => $moments[1] && $moments[1] != array(-1, -1, -1, -1, -1, -1) ? $this->_dateIso($moments[1]) : null,
				);
				$this->explo->obtenir('http://www.qapa.fr/rest/v1/applicants/'.$params['uid'].'/experiences', json_encode($exp), false, array('Content-Type: application/json; charset=UTF-8', 'X-Requested-With: XMLHttpRequest'));
				
				$mouvement = ($this->explo->données['num exp à pousser'] == 0); // On ne passe au suivant que si on n'a plus d'expérience.
				--$this->explo->données['num exp à pousser'];
				
				$mouvement = 0;
				$cestdéjàpasmal = 1;
				
				break;
			case self::ET_FIN:
				return $cestdéjàpasmal = 2;
		}
	}
}

?>
