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


/* Merci aux notes de bas de page sur php.net! */
if (!function_exists('iconv') && function_exists('libiconv')) { function iconv($input_encoding, $output_encoding, $string) { return libiconv($input_encoding, $output_encoding, $string); } }

/* Simule un navigateur web: retient l'état cookies, referer, dernière URL
 * absolue pour calculer les relatives. */

class Navigateur
{
	function effacerCookies() { $this->cookies = array(); $this->cookiesTasses = null; }
	function Navigateur() { $this->derniere = null; $this->effacerCookies(); }
	
	function tasserCookies()
	{
		$noms = array_keys($this->cookies);
		$n = count($noms) - 1;
		if($n < 0) { $this->cookiesTasses = null; return; }
		$this->cookiesTasses = $noms[$n].$this->cookies[$noms[$n]];
		while(--$n >= 0)
			$this->cookiesTasses .= '; '.$noms[$n].$this->cookies[$noms[$n]];
	}
	
	/* Récupère une URL.
	 * Paramètres:
	 *   url: URL à obtenir
	 *   champs: tableau associatif envoyé en POST
	 *   continuer: si true, le Navigateur est mis-à-jour avec les nouvelles
	 *     infos collectées.
	 */
	function obtenir($url, $champs = null, $continuer = false)
	{
		$this->suivre = $url;
		$this->continuer = $continuer;
		
		while($this->suivre !== null) // On suit les Location: à la main, car sinon nos cookies reçus dans une réponse contenant à la fois un Location et un Set-Cookie ne sont pas pris en compte au fur et à mesure. Couillon de PHP ou de curl. /* À FAIRE?: récupérer la connexion au lieu de faire des curl_init()/curl_close() à chaque fois. */
		{
			$url = $this->suivre;
			$this->suivre = null;
			
			$c = curl_init();
			if($this->derniere) curl_setopt($c, CURLOPT_REFERER, $this->derniere);
			//curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
			//curl_setopt($c, CURLOPT_MAXREDIRS, 0x8);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
			if($url{0} == '/') // URL absolue sur le même site.
			{
				$pos = strpos($this->derniere, '/', strpos($this->derniere, '://') + 3);
				$url = ($pos === false ? $this->derniere : substr($this->derniere, 0, $pos)).$url;
			}
			else if(strpos($url, 'http://') !== 0) // URL relative
			{
				if(($pos = strrpos($this->derniere, '/')) != 0)
					$url = substr($this->derniere, 0, $pos + 1).$url;
			}
			curl_setopt($c, CURLOPT_URL, $url);
			curl_setopt($c, CURLOPT_COOKIE, &$this->cookiesTasses); // Par référence, car entre deux redirections, ils doivent avoir été mis à jour; et on ne peut intervenir que sur cette variable.
			if($champs !== null)
			{
				/* PHP est bourrin, il poste en multipart si $champs est un tableau.
				 * Ça ne nous plaît pas du tout, enfin nous si, mais pas Monster
				 * pour l'authentification. */
				$champsTasses = '';
				foreach($champs as $cle => $valeur)
					$champsTasses .= '&'.$cle.'='.urlencode(iconv('UTF-8', 'ISO-8859-1//IGNORE', $valeur)); /* À FAIRE: ne pas coder en dur cette saleté d'ISO-8859-1 (qui n'accepte même pas mon pasτεχ); dépendre des directives du formulaire rempli. */
				curl_setopt($c, CURLOPT_POSTFIELDS, substr($champsTasses, 1));
			}
			$GLOBALS['monster_recuperateurEnTetes'] = &$this;
			curl_setopt($c, CURLOPT_HEADERFUNCTION, 'monster_recupEnTete');
			if($this->continuer)
				$this->derniere = $url;
			$r = curl_exec($c);
			curl_close($c);
			$this->tasserCookies();
			
			/* Pour continuer (redirection) */
			
			$champs = null;
		}
		
		return $r;
	}
	
	function aller($url, $champs = null) { return $this->obtenir($url, $champs, true); }
	
	function recupererEnTete($enTete)
	{
		if($this->continuer && (strpos($enTete, 'Set-Cookie: ') === 0))
		{
			if(($pos = strpos($enTete, ';', 0xc)) !== false)
			{
				$enTete = substr($enTete, 0xc, $pos - 0xc);
				$pos = strpos($enTete, '=');
				$this->cookies[substr($enTete, 0, $pos)] = substr($enTete, $pos);
			}
		}
		else if(strpos($enTete, 'Location: ') === 0)
			$this->suivre = strtr(substr($enTete, 0xa), array("\n" => '', "\r" => '')); // Un caractère retour à la fin.
	}
	
	function rattacherALaSession(&$session)
	{
		if(is_array($session))
		{
			$this->derniere = &$session['derniere'];
			$this->cookies = &$session['cookies'];
			$this->tasserCookies();
		}
		else
		{
			$session['derniere'] = &$this->derniere;
			$session['cookies'] = &$this->cookies;
		}
	}
	
	protected $derniere;
	protected $cookies;
	protected $suivre; // État: a-t-on reçu un Location: dans la dernière réponse?
}

function monster_recupEnTete($curl, $enTete)
{
	$GLOBALS['monster_recuperateurEnTetes']->recupererEnTete($enTete);
	return strlen($enTete);
}

function monster_chargerDecompo($module)
{
	return $GLOBALS['monster_monstre'];
}

class Monster
{
	protected $navigo;
	protected $etape;
	
	function Monster() { $this->etape = -1; }
	
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
		session_start();
		if(!array_key_exists('monster', $_SESSION))
			$_SESSION['monster'] = array();
		$this->navigo = new Navigateur(); // PHP outrepassant tout ce qu'un esprit malade pourrait concevoir en matière de langage merdique à souhait, on ne peut pas sérialiser notre Navigateur dans la sessions car celle-ci est déroulée par un truc (de_session) qui y cherche ses réglages, alors que ce fichier (et donc la classe Navigateur) n'a pas encore été chargée, ce qui vautre PHP. On ne sérialise donc que le contenu du Navigateur, qu'on se fait chier ensuite à remettre en place à la main ici. Mais qu'est-ce qu'ils sont cons, alors!
		$this->navigo->rattacherALaSession($_SESSION['monster']['navigo']);
		$GLOBALS['monster_monstre'] = &$this;
	}
	
	function pondreInterface($champ)
	{
		switch($this->etape)
		{
			/* Les cas sont ordonnés du dernier au premier, pour que l'on
			 * puisse se rabattre sur le précédent si celui voulu ne marche pas
			 * (session expirée, etc.). */
			case 2:
			case 1:
				$params = &$_SESSION['monster'];
				$r = $this->navigo->aller('http://mon.monster.fr/login.asp', array('user' => $params['id'], 'password' => $params['mdp']));
				/* Récupération des liens vers les CV dans la page obtenue. */
				preg_match_all('/<a href="([^"]*resumeid=([0-9]*)[^"]*)"> *([^<]*)<\/a>/', $r, $reponses, 0);
				if(count($reponses[0]) != 0) // Sinon, c'est qu'on a dû se faire expirer la session Monster au nez.
				{
					$_SESSION['monster']['cv'] = array();
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
		<div><input type="checkbox" name="<?php echo($champ); ?>[touteffacer]"/>remplacer (supprime l'existant)</div>
<?php
					for($z = count($reponses[0]); --$z >= 0;)
					{
						$_SESSION['monster']['cv'][$reponses[2][$z]] = $reponses[1][$z]; // Le numéro du CV sert d'indice, son URL est la donnée enregistrée.
						echo '<a href="#" id="'.$reponses[2][$z].'">'.htmlspecialchars($reponses[3][$z], ENT_NOQUOTES).'</a>';
					}
					echo '</div>';
					break;
				}
			case -1:
				if($this->etape < 0) // On n'est pas passé par la ponte pour obtenir cette interface: c'est donc le premier écran qui présente les interfaces, on part avec une session propre.
				{
					$this->preparerSession();
					$this->navigo->effacerCookies();
					unset($_SESSION['monster']['num']);
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
		}
	}
	
	function verifPresence($nomParam)
	{
		if(array_key_exists($nomParam, $this->nouvelles))
			$_SESSION['monster'][$nomParam] = $this->nouvelles[$nomParam];
		if(!array_key_exists($nomParam, $_SESSION['monster'])) return false;
		return true;
	}
		
	function decomposer($derniersParams, $donnees)
	{
		$this->preparerSession();
		$this->nouvelles = &$derniersParams;
		$params = &$_SESSION['monster'];
		$this->etape = 2;
		if(!$this->verifPresence('num') || !array_key_exists($params['num'], $params['cv'])) $this->etape = 1;
		else $this->verifPresence('touteffacer'); // On ne fait que le mettre en mémoire de session.
		if(!$this->verifPresence('id') || !$this->verifPresence('mdp')) $this->etape = 0;
		
		/* Peut-être arrive-t-on au bout. */
		
		if($this->etape == 2)
		{
			$r = $this->navigo->aller($params['cv'][$params['num']].'&mode=edit');
			preg_match('/<a href="([^"]*experience.asp[^"]*)">/', $r, $reponses, 0);
			if(count($reponses[0]) > 0)
			{
				$this->explo = clone $this->navigo; // Celui-ci s'aventure un peu plus loin que le navigo (qui s'arrête à la page du CV, commune à toutes les sections): il entre dans la section spécifique à remplir (ici, expérience).
				$r = $this->explo->aller(strtr($reponses[1], array('&amp;' => '&')));
				if($params['touteffacer'])
				{
					preg_match_all('/<a href="([^"]*&action=delete[^"]*)"/', $r, $reponses, 0);
					for($z = count($reponses[0]); --$z >= 0;)
						$this->explo->aller($reponses[1][$z]);
				}
				$this->pondreProjets($donnees, $r);
				return;
			}
		}
		
		/* Encore quelques réglages nécessaires. */
		
		pasTeX_debutInterface('monster: derniers réglages');
		$prefixe = pasTeX_debutFormu('monster', array('id' => 'decompo', 'champs' => array('compo[session]', 1)));
		$this->pondreInterface($prefixe);
		echo '</form>';
		pasTeX_finInterface();
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
	
	function pondreProjets($donnees, $page)
	{
		if(!array_key_exists('expérience', $donnees)) return;
		
		foreach($donnees->expérience->projet as $francheRigolade)
		{
			$champs = array();
			
			/* Récup de la page, et préparation des input type="hidden". */
			
			preg_match_all('/<input type="hidden" name="([^"]*)" value="([^"]*)">/', $page, $reponses, 0);
			for($z = count($reponses[0]); --$z >= 0;)
				$champs[$reponses[1][$z]] = $reponses[2][$z];
			
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
			
			$this->explo->obtenir('/experience.asp', $champs); // Bon, je code en dur, pour une fois.
		}
	}
}

?>