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

require_once('pasTeX.inc');
require_once('util/navigateur.inc');
require_once('util/params.inc');

/* À FAIRE: désolidariser réellement affichage et dialogue avec Monster: on a
 * encore des cas trop limite. */

class Monster extends NavigateurHiérarchique
{
	protected $etape;
	
	function Monster() { $this->etape = -1; $this->petitÀPetit = true; $this->rienPondu = true; }
	
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
	
	function preparerSession()
	{
		html_session();
		$this->préparerSession(array('monster','navigo')); // NavigateurHiérarchique.
	}
	
	function paramsVersParamsUrl($params)
	{
		$params[session_name()] = session_id();
		return substr(params_decomposer(null, $params, 0), 1);
	}
	
	/* Génère l'interface pour demander au client un renseignement nous
	 * permettant d'avancer (ex.: identifiant/mdp, numéro du CV à modifier, …).
	 * Paramètres:
	 *   $numéroInterface: machin à pondre.
	 */
	function pondrePage($numéroInterface)
	{
		if($this->interfaceIndépendante) // Si on doit générer nous-même la page.
			pasTeX_debutInterface('monster: derniers réglages');
		
		/* Comme on bosse en plusieurs étapes, il faut qu'on puisse récupérer
		 * les données plus tard; on a un compo spécial pour ça, et il n'est pas
		 * trop compliqué à utiliser. */
		
		$args = array('compo[session][]' => 1, 'decompo[monster][]' => 1);
		if($this->données !== null && !array_key_exists('donnees', $_SESSION))
			$_SESSION['donnees'] = &$this->données;
		
		$champ = pasTeX_debutFormu('monster', array('id' => 'decompo', 'champs' => $args));
		
		switch($numéroInterface)
		{
			case 4: // Interface de chargement en AJAX.
				if($this->petitÀPetit)
				{
					$args[$champ.'[clientdemandeur]'] = 1; // Si on reçoit par la suite ce paramètre, on sait que le client ne souhaite qu'un peu de XML pour alimenter la page qu'il a déjà, et non pas une page complète.
?>
	<div id="monster_conteneur"/>
	<script type="text/javascript" src="js/ajax.js"></script>
	<script type="text/javascript">
		<!--
			var g_req = ajax_chargeur();
			var g_params = '<?php echo $this->paramsVersParamsUrl($args); ?>';
			
			function initSuivants()
			{
				var r;
				r = document.createElement('table');
				document.getElementById('monster_conteneur').appendChild(r);
				return r;
			}
			
			var g_table = initSuivants();
			var g_derniereEtape = null;
			var g_nDerniereEtape;
			var g_affDerniereEtape;
			
			function suivantRecu()
			{
				if (g_req.readyState == 4)
				{
					var t1, t2;
					if(g_req.responseText && (t1 = g_req.responseText.indexOf(':')) > 0)
					{
						t2 = g_req.responseText.substr(t1 + 1);
						t1 = g_req.responseText.substr(0, t1);
						if(t1 != g_derniereEtape)
						{
							g_nDerniereEtape = 0;
							g_derniereEtape = t1;
							var l, e;
							l = document.createElement('tr');
							e = document.createElement('td');
							e.appendChild(document.createTextNode(t1));
							l.appendChild(e);
							e = document.createElement('td');
							e.appendChild(g_affDerniereEtape = document.createTextNode());
							l.appendChild(e);
							g_table.appendChild(l);
						}
						++g_nDerniereEtape;
						g_affDerniereEtape.data = ''+g_nDerniereEtape;
					}
					if(g_req.responseText) suivant();
				}
			}
			
			function suivant()
			{
				g_req.onreadystatechange = suivantRecu;
				g_req.open("GET", document.location+'?'+g_params, true);
				g_req.send("");
			}
			
			suivant();
		-->
	</script>
<?php
				}
				break;
			case 1:
				$params = $this->explo->données['affcv'];
				if(count($params) != 0) // Sinon, c'est qu'on a dû se faire expirer la session Monster au nez.
				{
?>
	<script type="text/javascript">
		<!--
			function monster_hop(e)
			{
				if(!e) e = window.event;
				var r = e.target;
				if(!r) r = e.srcElement;
				while(r)
				{
					if(r.href)
						break;
					r = r.parentNode;
				}
				if(r)
				{
					for(emetteur = r.parentNode; emetteur; emetteur = emetteur.parentNode)
						if(emetteur.action)
							break;
					document.getElementById('monster-num').value = r.id;
					emetteur.submit();
				}
				return false;
			}
		-->
	</script>
	<div onclick="return monster_hop(event);">
		<input name="<?php echo($champ); ?>[num]" id="monster-num" type="hidden" value="0"/>
<?php
					foreach($params as $num => $aff)
						echo '<a href="#" id="'.$num.'">'.htmlspecialchars($aff, ENT_NOQUOTES).'</a>';
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
	</div>
<?php
					break;
				}
			case -1:
					$this->preparerSession();
					$this->explo->effacerCookies();
					unset($_SESSION['monster']);
			case 0:
?>
	<div>Le module Monster ajoute à un de vos CV Monster les expériences de votre CV pasτεχ. ATTENTION! Pour le moment expérimental. Pensez à mettre hors-ligne votre CV auparavant, et à en avoir une copie de secours.</div>
	<div>Concernant les infos transposées, il y aura des pertes, c'est inévitable; en particulier, ce foutu Monster ne prend que de l'ISO-8859-1. Adieu donc les caractères sympathiques, décoratifs, ou orientaux (pour l'ISO-8859-1, l'orient commence en Grèce). À l'heure actuelle les champs suivants sont remplis:<ul><li>Expérience</li></ul></div>
	<div>
		Identifiant Monster: <input type="id" name="<?php echo($champ); ?>[id]"></input> Mot de passe: <input type="password" name="<?php echo($champ); ?>[mdp]"></input>
	</div>
<?php
				break;
			default: // Rien, attente.
				header('Location: '.$_SERVER['PHP_SELF'].'?'.$this->paramsVersParamsUrl($args));
				break;
		}
		
		if($this->interfaceIndépendante)
		{
			echo '</form>';
			pasTeX_finInterface();
		}
	}
	
	function verifPresence($nomParam)
	{
		if(array_key_exists($nomParam, $this->nouvelles))
			$_SESSION['monster'][$nomParam] = $this->nouvelles[$nomParam];
		if(!array_key_exists($nomParam, $_SESSION['monster'])) return false;
		return true;
	}
	
	/* Envoie au client une info de l'étape en cours. */
	function signaler($étape, $chose)
	{
		if(array_key_exists('clientdemandeur', $_SESSION['monster']))
		{
			print $étape.':'.$chose."\n";
			$this->rienPondu = false;
		}
	}
	
	function retourAvancéeUnCoup($manquant)
	{
		if($this->rienPondu && $manquant >= 0)
		{
			$this->pondrePage($manquant);
			$this->rienPondu = false;
		}
		return false;
	}
	
	function avancerUnCoup($données)
	{
		$params = &$_SESSION['monster'];
		$this->données = &$données;
		
		/* On simule le parcours du site de façon hiérarchique. */
		
		$étapes = array(0, 1, 2, array(3, 4, 5), array(7, 8, 9), 6);
		$modules = array(null, null, null, 'exp', 'exp', 'exp', null, 'conn', 'conn', 'conn'); // Pour chacune des listes de bidules, le comportement est similaire (accès à la page de modification, suppressions, ajouts); on va donc passer par le même code, avec ce tableau qui dira pour chaque étape sur quel module elle bosse.
		
		$this->explo = &$this->navigo[count($this->navigo) - 1];
		$mouvement = 0; // Sans autre info, on reste sur place (même étape) au prochain tour.
		$cestdéjàpasmal = 0; // S'il passe à 1, on ressort de la fonction en mode « au coup par coup »: c'est que l'on a passé pas mal de temps dans la procédure, et il est temps de donner un retour à l'utilisateur (or il n'en aura que si on clôt le transfert).
		
		$tableau = &$étapes;
		for($i = -1; ++$i < count($this->navigo);)
		{
			$pos = $this->navigo[$i]->données['pos'];
			if(is_array($tableau[$pos]))
				$tableau = &$tableau[$pos];
			else
				$étape = $tableau[$pos];
		}
		
		/* Y a-t-il des données qui nous manquent pour la suite? On génère les
		 * interfaces pour les obtenir. */
		
		$manquant = 0x1000;
		if(!$this->verifPresence('id') || !$this->verifPresence('mdp')) $manquant = 0;
		else if(!$this->verifPresence('num')) $manquant = 1;
		else if($données === null) $manquant = 3;
		else if($this->petitÀPetit && !$this->verifPresence('clientdemandeur')) $manquant = 4;
		
		/* Code de l'étape. Le boulot se fait en deux fois: interprétation
		 * de la page courante, et chargement de la nouvelle page (par
		 * exemple par POST pour les nouvelles données). Pour optimiser, les
		 * étapes sont tenues d'être civiques: lorsqu'elles chargent la
		 * nouvelle, elles sont priées d'en stocker le contenu dans $page;
		 * ainsi la suivante aura déjà sa page de départ en mémoire.
		 * Le travail de POST se fait dans ce switch-ci, l'interprétation
		 * dans le suivant; le passage de données entre les deux peut se
		 * faire par $this->explo->données[…], enregistré en session. On
		 * peut au pire y stocker $page pour que l'interprétation se fasse
		 * aussi ici, mais ça n'est pas élégant.
		 * L'étape indique qu'elle a fini en passant $mouvement à 1.
		 * Le tracé de l'interface générée à cette étape DOIT être provoqué, en
		 * appelant $this->retourAvancéeUnCoup($manquant) juste avant le return.
		 */
		
		switch($étape)
		{
			case 0: // Connexion.
				if($manquant <= 0) // Si on n'a pas les renseignements pour s'authentifier, on les demande à l'utilisateur et on reprendra la connexion au coup suivant.
					return $this->retourAvancéeUnCoup($manquant);
				$page = $this->explo->aller('http://mon.monster.fr/login.asp', array('user' => $params['id'], 'password' => $params['mdp']));
				$cestdéjàpasmal = 1;
				$mouvement = 1;
				break;
			case 1: // Accès au CV.
				if($manquant <= 1)
					return $this->retourAvancéeUnCoup($manquant);
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
					return $this->retourAvancéeUnCoup($manquant);
				$page = $this->récupérer($params['liens'][$modules[$étape]]);
				$mouvement = 1;
				break;
			case 4: // Suppression d'un projet.
			case 8: // Suppression d'une connaissance.
				$mouvement = 1;
				if($params['faire'][$modules[$étape]] == 2)
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
				if($params['faire'][$modules[$étape]] >= 1 && array_key_exists($modules[$étape], $params['liens']))
				{
					if(!array_key_exists('numExp', $this->explo->données))
					{
						$n = 0;
						switch($étape)
						{
							case 5: if(array_key_exists('expérience', $données)) $n = count($données->expérience->projet); break;
							case 9: if(array_key_exists('connaissances', $données)) { $n = 0; foreach($données->connaissances->catégorie as $cat) $n += 1 + count($cat->connaissances); } break;
						}
						$this->explo->données['numExp'] = $n;
					}
					if(--$this->explo->données['numExp'] >= 0)
					{
						switch($étape)
						{
							case 5: $this->pondreProjet($données, $this->explo->données['numExp']); break;
							case 9: $this->pondreConnaissance($données, $this->explo->données['numExp']); break;
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
				return false;
				break;
		}
		
		if($mouvement)
		{
			/* On incrémente la position dans le dernier navigo. */
			++$this->explo->données['pos']; /* À FAIRE: je ne sais pas, mais l'inconvénient de cette solution, c'est que le rechargement de la page par le client ne le fait pas retomber sur la même page puisque la session a enregistré cette augmentation. */
			/* On vérifie qu'ainsi augmenté, on tombe toujours sur une
			 * étape; sinon on remonte au navigo du dessus, dont on
			 * incrémente l'étape, et ainsi de suite jusqu'à stabilisation. */
			do
			{
				//$this->blater('==== Nouveau mouvement');
				//$this->blater('mise au propre');
				$tableau = &$étapes;
				$retenue = false;
				for($i = -1; ++$i < count($this->navigo);)
				{
					$pos = $this->navigo[$i]->données['pos'];
					//$this->blater('étage '.$i.': n°'.$pos.' sur '.count($tableau));
					if($pos >= count($tableau))
					{
						while(count($this->navigo) > $i)
							$this->supprimerDernierNavigo();
						//$this->blater('remonte: '.$i);
						if($i == 0) // On a fini de parcourir $étapes, donc tout est fait.
						{
							/* On réinitialise pour que le client, en
							 * rechargeant la page, ait quelque chose qui se
							 * passe. */
							$this->ajouterOuRecupérerDernierNavigo();
							return $this->retourAvancéeUnCoup($manquant);
						}
						else
						{
							++$this->navigo[$i - 1]->données['pos'];
							$retenue = true;
						}
					}
					else if(is_array($tableau[$pos]))
					{
						//$this->blater('rentre: '.$i.' / '.count($this->navigo));
						if($i == count($this->navigo) - 1)
						{
							$this->ajouterOuRecupérerDernierNavigo();
							//$this->blater('ajout; déjà à '.$this->navigo[count($this->navigo) - 1]->données['pos']);
							$retenue = true; // Des fois qu'il faille après ça encore rentrer dans un tableau.
						}
						$tableau = &$tableau[$pos];
					}
					else
						$étape = $tableau[$pos]; // La dernière sera conservée comme étape courante.
				}
			} while($retenue);
			//$this->blater('==== Mouvement fini');
		}
		
		/* On prépare la nouvelle étape: la précédente ayant peut-être
		 * récupéré une page, on laisse une chance à celle-ci de
		 * l'interpréter dès maintenant (cf. le long commentaire au-dessus
		 * du précédent switch).
		 * On prévient aussi via signaler() ce qu'on va faire… si on le fait
		 * (c'est pourquoi on effectue avant de l'appeler les mêmes tests que
		 * plus haut). */
		
		switch($étape)
		{
			case 0: if($manquant > 0) $this->signaler('Connexion', null); break; // Connexion.
			case 1: // Accès au CV.
				if($manquant > 1) $this->signaler('Accès au CV', null);
				preg_match_all('/<a href="([^"]*resumeid=([0-9]*)[^"]*)"> *([^<]*)<\/a>/', $page, $réponses, 0);
				if(count($réponses[0]) != 0) // Sinon, c'est qu'on a dû se faire expirer la session Monster au nez.
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
				if($params['faire'][$modules[$étape]] == 2)
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
		
		if($this->petitÀPetit && $cestdéjàpasmal) // Si on en a déjà trop fait…
			return $this->retourAvancéeUnCoup($manquant); // … on quitte, en donnant un minimum d'affichage.
		return true;
	}
	
	function pondreInterface($champ) { $this->decomposer(array(), null); } // L'interface, c'est comme une de nos étapes, sauf qu'on n'a pas encore les données et les paramètres utilisateur. Notre avancerUnCoup() s'en rendra compte tout seul pour générer l'interface sur mesure.
	
	function decomposer($derniersParams, $données)
	{
		$this->preparerSession();
		$this->nouvelles = &$derniersParams;
		if($données !== null && !array_key_exists('clientdemandeur', $_SESSION['monster'])) $this->interfaceIndépendante = true; // Sinon c'est qu'on veut pondre une interface dans le cadre de pasτεχ, ou dans le cadre HTML créé par une précédente requête (en AJAX).
		while($this->avancerUnCoup($données)) {}
	}
	
	function tronque($bidule, $limite)
	{
		return strlen($bidule) > $limite ? substr($bidule, 0, $limite - 5).'[...]' : $bidule;
	}
	
	function conc($bidules, $limite, $separateur = ', ', $inverse = false)
	{
		if(isset($bidules) && ($n = count($bidules)) > 0)
		{
			$bidule = $bidules[$inverse ? --$n : 0];
			if($inverse)
				while(--$n >= 0)
					$bidule .= $separateur.$bidules[$n];
			else
				for($z = 0; ++$z < $n;)
					$bidule .= $separateur.$bidules[$z];
			return strlen($bidule) > $limite ? substr($bidule, 0, $limite - 6).' [...]' : $bidule;
		}
		return '-';
	}
	
	/* Récupère une page et stocke ses hidden pour permettre au prochain de
	 * continuer. Utilise $this->explo pour ça, et $_SESSION['champs'] pour les
	 * champs à poster. */
	function récupérer($url)
	{
		$page = $this->explo->aller($url, $_SESSION['champs']); // Bon, je code en dur, pour une fois.
		
		preg_match_all('/<input type="hidden" name="([^"]*)" value="([^"]*)">/', $page, $reponses, 0);
		for($z = count($reponses[0]); --$z >= 0;)
			$_SESSION['champs'][$reponses[1][$z]] = $reponses[2][$z];
		
		return $page;
	}
	
	function pondreProjet($donnees, $numéro)
	{
		$francheRigolade = $donnees->expérience->projet[$numéro];
		$champs = &$_SESSION['champs'];
		
		/* Remplissage des autres champs. */
		
		$champs['company'] = $this->conc($francheRigolade->société, 100, ' / ', true);
		$champs['monsterindustryid'] = 0; /* À FAIRE: mais pour le moment on laisse ce choix (« Tous »), parce que je viens d'une SSII, donc informatique, mais qui bossait pour différents domaines, alors Monster et leurs restrictions, ils m'embêtent. */
		$champs['location'] = $this->conc($francheRigolade->lieu, 100);
		$champs['title'] = pasTeX_maj($this->conc($francheRigolade->rôle, 70, '; '));
		
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
	
	function pondreConnaissance($données, $numéro)
	{
		/* Reprenons connaissance parmi les catégories. $numéro est donné en
		 * partant de la fin. */
		
		for($m = count($données->connaissances->catégorie); --$m >= 0;)
		{
			$cat = $données->connaissances->catégorie[$m];
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
		
		$champs['name'] = $this->tronque($numéro >= 0 ? $noms[$numéro] : '=== '.$cat->nom.' ===', 50);
		$champs['years'] = $this->tronque(0, 2);
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
	
	protected $petitÀPetit; // Si true, on n'essaie pas de tout faire en une fois; on fonctionne en AJAX, permettant à chaque appel PHP de ne faire qu'un appel au site Monster, et donc de donner un retour à l'utilisateur dans un délai raisonnable, tout en évitant que PHP ne coupe le script brutalement en considérant qu'il a vécu trop longtemps. 
	protected $rienPondu;
}

?>