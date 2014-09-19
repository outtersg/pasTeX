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

/* Fondations pour un Décompo remplissant des formulaires sur un site distant. */

require_once('pasTeX.inc');
require_once('commun/http/formu.inc');
require_once('util/navigateur.inc');

/* À FAIRE: désolidariser réellement affichage et dialogue avec le site: on a
 * encore des cas trop limite (rechargement de la page, retour en arrière, etc.). */

class Émetteur extends NavigateurHiérarchique
{
	function Émetteur($quisuisje) { $this->id = $quisuisje; $this->etape = -1; $this->petitÀPetit = true; $this->rienPondu = true; }
	
	function preparerSession()
	{
		html_session(true);
		$this->préparerSession(array('emetteur','navigo')); // NavigateurHiérarchique.
	}
	
	/* Une fois toutes les infos récupérées, on peut commencer à attaquer le
	 * site distant. La page ci-générée renvoie à l'utilisateur une interface
	 * AJA(X) qui se contentera de rappeler notre PHP pour lui dire « et
	 * maintenant, envoie l'info suivante », jusqu'à épuisement des trucs à
	 * émettre.
	 * Cette méthode doit donc être appelée par la sous-classe dans son
	 * pondreContenuPage(), lorsqu'elle n'a plus besoin de rien. */
	function pondreContenuPageAjax($champ, $args)
	{
		if($this->petitÀPetit)
		{
			$args[$champ.'[clientdemandeur]'] = 1; // Si on reçoit par la suite ce paramètre, on sait que le client ne souhaite qu'un peu de XML pour alimenter la page qu'il a déjà, et non pas une page complète.
?>
	<div id="emetteur_conteneur"/>
	<script type="text/javascript" src="js/ajax.js"></script>
	<script type="text/javascript">
		<!--
			var g_req = ajax_chargeur();
			var g_params = '<?php echo formu_paramsVersParamsUrl($args); ?>';
			
			function initSuivants()
			{
				var r;
				r = document.createElement('table');
				document.getElementById('emetteur_conteneur').appendChild(r);
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
	}
	
	/* Crée une liste de liens sur lesquels le clic provoquera l'émission du
	 * formulaire dans lequel ils sont inclus. */
	function pondreContenuLiensÉmission($champ, $params)
	{
?>
	<script type="text/javascript">
		<!--
			function emetteur_hop(e)
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
					document.getElementById('emetteur-num').value = r.id;
					emetteur.submit();
				}
				return false;
			}
		-->
	</script>
	<div onclick="return emetteur_hop(event);">
		<input name="<?php echo($champ); ?>[num]" id="emetteur-num" type="hidden" value="0"/>
<?php
					foreach($params as $num => $aff)
						echo '<div><a href="#" id="'.$num.'">'.htmlspecialchars($aff, ENT_NOQUOTES).'</a></div>';
?>
	</div>
<?php
	}
	
	/* Génère l'interface pour demander au client un renseignement nous
	 * permettant d'avancer (ex.: identifiant/mdp, numéro du CV à modifier, …).
	 * Paramètres:
	 *   $numéroInterface: machin à pondre.
	 */
	function pondrePage($numéroInterface)
	{
		if($this->interfaceIndépendante) // Si on doit générer nous-même la page.
			pasTeX_debutInterface('emetteur: derniers réglages');
		
		/* Comme on bosse en plusieurs étapes, il faut qu'on puisse récupérer
		 * les données plus tard; on a un compo spécial pour ça, et il n'est pas
		 * trop compliqué à utiliser. */
		
		$args = array('compo[session][]' => 1, 'decompo['.$this->id.'][]' => 1);
		if($this->données !== null && !array_key_exists('donnees', $_SESSION))
			$_SESSION['donnees'] = &$this->données;
		
		$champ = pasTeX_debutFormu($this->id, array('id' => 'decompo', 'champs' => $args));
		
		if($numéroInterface == -1)
		{
			$this->preparerSession();
			$this->explo->effacerCookies();
			unset($_SESSION['emetteur']);
			$this->pondreContenuPage($champ, 0, $args); // On revient à la page de départ.
		}
		else
			if(!$this->pondreContenuPage($champ, $numéroInterface, $args))
				header('Location: '.$_SERVER['PHP_SELF'].'?'.formu_paramsVersParamsUrl($args));
		
		if($this->interfaceIndépendante)
		{
			echo '</form>';
			pasTeX_finInterface();
		}
	}
	
	function verifPresence($nomParam)
	{
		if(array_key_exists($nomParam, $this->nouvelles))
			$_SESSION['emetteur'][$nomParam] = $this->nouvelles[$nomParam];
		if(!array_key_exists($nomParam, $_SESSION['emetteur'])) return false;
		return true;
	}
	
	/* Envoie au client une info de l'étape en cours. */
	function signaler($étape, $chose)
	{
		if(array_key_exists('clientdemandeur', $_SESSION['emetteur']))
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
		$params = &$_SESSION['emetteur'];
		$this->données = &$données;
		
		/* On simule le parcours du site de façon hiérarchique. */
		
		$étapes = $this->étapes();
		
		$this->explo = &$this->navigo[count($this->navigo) - 1];
		$mouvement = 0; // Sans autre info, on reste sur place (même étape) au prochain tour.
		$cestdéjàpasmal = 0; // S'il passe à 1, on ressort de la fonction en mode « au coup par coup »: c'est que l'on a passé pas mal de temps dans la procédure, et il est temps de donner un retour à l'utilisateur (or il n'en aura que si on clôt le transfert); s'il passe à 2, c'est que l'étape n'a pu être faite faute de renseignements (donc on pond l'interface, mais on n'avance pas, à la différence d'un $cestdéjàpasmal à 1).
		
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
		
		$manquant = $this->manquant();
		
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
		
		$this->gérerÉtape($étape, $manquant, $this->explo->page, &$cestdéjàpasmal, &$mouvement, &$params);
		if($cestdéjàpasmal == 2)
			return $this->retourAvancéeUnCoup($manquant);
		
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
		
		/* À FAIRE: adapter les deux premiers modèles (Viaduc et Monster) à ce
		 * nouveau fonctionnement de $page, où l'on utilise celui de l'explo
		 * précédent logiquement et non absolument (différence dans le cas où
		 * les explo ont la forme (1, (2, 3), (4, 5)): à l'ancienne, 4 recevait
		 * la page calculée par 3; à la nouvelle, elle reçoit celle en sortie de
		 * 1, ce qui est plus logique). */
		$page = $this->explo->données['pos'] === 0 && count($this->navigo) > 1 ? $this->navigo[count($this->navigo) - 2]->page : $this->explo->page; // Si on est chef de file, la page nous est procurée par le père; sinon par le précédent dans la file des étapes, qui travaille sur le même navigo.
		$this->préparerÉtape($étape, $manquant, &$page, &$cestdéjàpasmal, &$mouvement, &$params);
		if($this->petitÀPetit && $cestdéjàpasmal) // Si on en a déjà trop fait…
			return $this->retourAvancéeUnCoup($manquant); // … on quitte, en donnant un minimum d'affichage.
		return true;
	}
	
	function pondreInterface($champ) { $this->decomposer(array(), null); } // L'interface, c'est comme une de nos étapes, sauf qu'on n'a pas encore les données et les paramètres utilisateur. Notre avancerUnCoup() s'en rendra compte tout seul pour générer l'interface sur mesure.
	
	function decomposer($derniersParams, $données)
	{
		$this->preparerSession();
		$this->nouvelles = &$derniersParams;
		if($données !== null && !array_key_exists('clientdemandeur', $_SESSION['emetteur'])) $this->interfaceIndépendante = true; // Sinon c'est qu'on veut pondre une interface dans le cadre de pasτεχ, ou dans le cadre HTML créé par une précédente requête (en AJAX).
		while($this->avancerUnCoup($données)) {}
	}
	
	/* Récupère une page et stocke ses hidden pour permettre au prochain de
	 * continuer. Utilise $this->explo pour ça, et $_SESSION['champs'] pour les
	 * champs à poster. */
	function récupérer($url)
	{
		$page = $this->explo->aller($url, $_SESSION['champs']);
		
		preg_match_all('/<input type="hidden" name="([^"]*)" value="([^"]*)">/', $page, $reponses, 0);
		for($z = count($reponses[0]); --$z >= 0;)
			$_SESSION['champs'][$reponses[1][$z]] = $reponses[2][$z];
		
		return $page;
	}
	
	protected $etape;
	protected $petitÀPetit; // Si true, on n'essaie pas de tout faire en une fois; on fonctionne en AJAX, permettant à chaque appel PHP de ne faire qu'un appel au site Monster, et donc de donner un retour à l'utilisateur dans un délai raisonnable, tout en évitant que PHP ne coupe le script brutalement en considérant qu'il a vécu trop longtemps. 
	protected $rienPondu;
	protected $id; // Nom du fichier contenant la classe dérivée.
}

?>