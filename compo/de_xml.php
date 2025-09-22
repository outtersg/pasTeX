<?php
/*
 * Copyright (c) 2005,2007,2009,2014-2015 Guillaume Outters
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

require_once('util/date.inc');
require_once('util/xml/chargeur.php');
require_once('util/xml/compo.php');
require_once('util/xml/composimple.php');

require_once 'util/htopus.php';

const M_ID    = 0;
const M_DÉBUT = 1;
const M_FIN   = 2;
const M_URL   = 3;

#[AllowDynamicProperties]
class Donnee extends Compo
{
	
}

/**
 * Texte enrichi.
 * L'enrichissement est au gré des bourreau de l'objet: chacun décide les attributs dont il l'alourdira.
 */
class Texte
{
	public static $Html = false;
	
	public $texte;
	public $marqueurs;
	public $poids;
	public $signe;
	public $réf;
	public $comme;
	
	public function __construct($chaîne = '')
	{
		$this->texte = $chaîne;
	}
	
	public function __toString()
	{
		if(self::$Html)
		{
			$marqueurs = array();
			if(isset($this->marqueurs))
			{
				// On va mettre sur pied d'égalité débuts et fins de marqueurs, en faisant figurer ces dernières comme marqueurs false: on transforme le tableau [ [ id, début, fin ] ] en [ [ id, début, fin ], [ false, fin ] ].
				$marqueurs = $this->marqueurs;
				foreach($this->marqueurs as $marqueur)
					$marqueurs[] = array(false, $marqueur[M_FIN], $marqueur[M_DÉBUT], $marqueur[M_URL]);
				usort($marqueurs, array($this, 'comparePosMarqueurs'));
			}
			$html = '';
			$pos = strlen($this->texte);
			foreach($marqueurs as $marqueur)
			{
				if($marqueur[M_ID] === false)
					$balise = $marqueur[M_URL] ? '</a>' : '</span>';
				else
				{
					$balise = $marqueur[M_URL] ? '<a href="'.$marqueur[M_URL].'"' : '<span';
					if(isset($marqueur[M_ID])) $balise .= ' class="marque marque-'.$marqueur[0].'"';
					$balise .= '>';
				}
				$html = $balise.htmlspecialchars(substr($this->texte, $marqueur[M_DÉBUT], $pos - $marqueur[M_DÉBUT]), ENT_NOQUOTES).$html;
				$pos = $marqueur[1];
			}
			$html = htmlspecialchars(substr($this->texte, 0, $pos), ENT_NOQUOTES).$html;
			return $html;
		}
		return $this->texte;
	}
	
	public function comparePosMarqueurs($a, $b)
	{
		// Notez qu'on renverra un résultat inversé par rapport à l'usage (a - b), afin d'obtenir un tri inverse (on partira de la fin).
		// Si les début sont clairement différents, le cas est simple.
		if(($diff = $b[1] - $a[1]))
			return $diff;
		
		// Sinon, égalité de position. Une fin est toujours avant un début (pour éviter les chevauchements), sauf si c'est la fin d'un bloc de longueur 0 (qui est logiquement après son début). Pour deux fins, on s'en fiche, vu que ça donnera lieu à deux </span> indifférenciables.
		if($a[0] === false) return $a[2] == $a[1] ? -1 : 1;
		if($b[0] === false) return $b[2] == $b[1] ? 1 : -1;
		
		// Sinon, le plus petit est englobé dans le plus grand, donc commence après.
		return $a[2] - $b[2];
	}
}

class Connaissance
{
	public $niveau;
	public $poids;
	
	public function __construct($niveau)
	{
		$this->niveau = $niveau;
	}
}

class CompoAProprietes extends Compo
{
	protected function _filsDodo($nom, $doc)
	{
		if(isset($doc['attrs']['class']))
			$classe = $doc['attrs']['class'];
		else if(isset($doc['fils']))
		{
			$classe =
				function_exists('mb_strtoupper')
				? mb_strtoupper(mb_substr($nom, 0, 1)).mb_substr($nom, 1)
				: ucfirst($nom);
			if(!class_exists($classe))
				$classe = 'CompoAProprietes';
		}
		else
			$classe = null;
		if($classe)
		{
			$doc['_dodo'] = true;
			$this->classes[$nom] = $classe;
			$this->args[$nom] = $doc;
		}
		if(isset($doc['attrs']['maxOccurs']) && $doc['attrs']['maxOccurs'] == 'unbounded')
			$this->enTableau[$nom] = true;
		else
			$this->normal[$nom] = true;
		if(isset($doc['attrs']['pre']) && $doc['attrs']['pre'])
			$this->_preservatifsEspaces[$nom] = true;
	}
	
	protected function _configurerDodo($doc)
	{
		$this->enTableau = array();
		if(isset($doc['fils']))
			foreach($doc['fils'] as $nom => $fils)
				$this->_filsDodo($nom, $fils);
				
	}
	
	protected function _configurer($conf)
	{
		$d = new Dodo;
		$this->_configurerDodo($d->lire($conf));
	}
	
	/* Prend en paramètre ses deux listes de propriétés (cf. la variable
	 * membre associée pour une description); les tableaux sont de la forme
	 * propriété => nom de classe, pour que la propriété soit gérée par la
	 * classe ainsi nommée, propriété => 1 pour qu'elle soit gérée par une
	 * classe du même nom qu'elle (la propriété XML), ou n'importe quoi d'autre
	 * pour qu'elle soit gérée comme une Donnee. Le CompoAProprietes se charge
	 * lui-même du texte hors propriété. */
	public function __construct($proprietesNormales, $proprietesEnTableau = array())
	{
		$this->données = new Donnee();
		$this->classes = array();
		$this->_preservatifsEspaces = array();
		
		if(is_string($proprietesNormales))
			$this->_configurer($proprietesNormales);
		else if(isset($proprietesNormales['_dodo']))
			$this->_configurerDodo($proprietesNormales);
		else
		{
			$this->enTableau = $proprietesEnTableau;
			$this->normal = $proprietesNormales;
		foreach(array_merge($proprietesNormales, $proprietesEnTableau) as $cle => $valeur)
			if(is_string($valeur))
				$this->classes[$cle] = $valeur;
			else if($valeur === 1)
				$this->classes[$cle] = $cle;
			else if($valeur == -1)
				$this->_preservatifsEspaces[$cle] = 1;
		}
	}

	function &entrerDans(&$depuis, $nom, $attributs)
	{
		if($nom == 'm') // Marqueur / Méta: cas particulier qui peut être embarqué dans du texte, et auquel ne s'applique donc pas la règle qui suivra (« interdit d'avoir à la fois des sous-élements et du contenu textuel »).
		{
			if(!isset($this->marqueurs))
				$this->marqueurs = array();
			$urls = array_intersect_key($attributs, array('href' => 1, 'url' => 1, 'u' => 1)); // Par cohérence avec le HTML (href) mais aussi nos liens (u comme URL).
			$marqueur = array
			(
				M_ID => isset($attributs['id']) ? $attributs['id'] : null,
				M_DÉBUT => (is_string($depuis) || $depuis instanceof Texte) ? strlen($depuis) : 0,
				M_FIN => null,
				M_URL => array_shift($urls),
			);
			$this->marqueurs[] = $marqueur;
			return $depuis;
		}
		if(is_string($depuis)) $depuis = new Donnee(); // Tant pis pour ceux qui font du HTML (mélange de balises et texte)! Ici, si on avait une feuille (string) à laquelle est demandée l'adjonction d'un nœud, on détruit le texte pour préparer le terrain au nœud.
		if(array_key_exists($nom, $this->classes))
		{
			$nouveau = $this->classes[$nom];
			if(isset($this->args[$nom]))
				$nouveau = new $nouveau($this->args[$nom]);
			else
			$nouveau = new $nouveau();
			$donnee = &$nouveau->données;
			if(isset($attributs['id']))
				$nouveau->id = $attributs['id'];
			if(isset($attributs['p']))
				$nouveau->poids = $attributs['p'];
		}
		else if(count($attributs))
		{
			$nouveau = new Texte;
			$donnee = & $nouveau;
			if(isset($attributs['p']))
			$nouveau->poids = $attributs['p'];
			if(isset($attributs['ref']))
				$nouveau->réf = $attributs['ref'];
		}
		else
		{
			$nouveau = null;
			$donnee = &$nouveau;
		}
		if(array_key_exists($nom, $this->enTableau)) // On est en train de nous constituer un tableau de ces propriétés (propriété multi-valuée).
			$depuis->données->{$nom}[] = &$donnee;
		else // Sinon c'est une propriété unique de l'objet.
		{
			if(!isset($depuis)) $depuis = new stdClass;
			if(!isset($depuis->données)) $depuis->données = new stdClass;
			$depuis->données->{$nom} = &$donnee;
		}
		if(isset($this->preserveEspaces))
			++$this->preserveEspaces;
		else if(isset($this->_preservatifsEspaces[$nom]))
			$this->preserveEspaces = 1;
		return $nouveau;
	}
	
	function contenuPour(&$objet, $contenu)
	{
		if($objet instanceof Donnee)
		{
			if(count(get_object_vars($objet)) > 0) return; // Une fois qu'il est devenu un object complet, on ne change pas sa nature. Tant pis pour les données!
			$objet = null;
		}
		else if($objet instanceof Texte)
		{
			$objet->texte .= $contenu;
			return;
		}
		$objet = $objet === null ? $contenu : $objet.$contenu;
	}
	
	public function sortirDe(&$objet, $nom)
	{
		if($nom == 'm')
		{
			// Recherche du marqueur ouvert.
			
			if(isset($this->marqueurs))
				for($numMarqueur = count($this->marqueurs); --$numMarqueur >= 0;)
					if(!isset($this->marqueurs[$numMarqueur][2])) // Ah, en voilà un qui n'a pas de fin.
					{
						$this->marqueurs[$numMarqueur][2] = (is_string($objet) || $objet instanceof Texte) ? strlen($objet) : 0;
						return;
					}
			throw new Exception('Fermeture d\'un marqueur jamais ouvert');
		}
		// Sinon une de nos sous-propriétés. Si l'on avait accumulé des marqueurs, on les y attache.
		else if(isset($this->marqueurs))
		{
			if(is_string($objet))
				$objet = new Texte($objet);
			if(is_object($objet))
			{
				$objet->marqueurs = $this->marqueurs;
				unset($this->marqueurs);
			}
			else
				throw new Exception('Application de marqueurs à un truc inconnu');
		}
		if(isset($this->preserveEspaces))
			if(--$this->preserveEspaces <= 0)
				unset($this->preserveEspaces);
		if(is_object($objet))
			if(isset($objet->données) && is_object($objet->données))
			{
				if(isset($objet->poids)) $objet->données->poids = $objet->poids;
				if(isset($objet->id))    $objet->données->id = $objet->id;
			}
	}
	
	protected $id;
	protected $poids;
	protected $marqueurs;
	protected $args;
	protected $enTableau; // Liste des sous-éléments XML agrégeables en tableau dans cet objet.
	protected $normal; // Liste des sous-éléments XML qui doivent donner une propriété unique de cet objet.
	protected $classes; // Association d'une classe de Compo à un élément XML.
	protected $données;
	public $preserveEspaces; // public pour le Chargeur puisse consulter.
	protected $_preservatifsEspaces;
}

/* CompoAProprietes connaissant les propriétés particulières 'date' et 'période' */
class CompoADates extends CompoAProprietes
{
	public $poids;
	
	public function __construct($proprietesNormales = null, $proprietesEnTableau = null)
	{
		$monTableau = array('date' => 'MaDate', 'période' => 1);
		parent::__construct($proprietesNormales === null ? $monTableau : array_merge($proprietesNormales, $monTableau), $proprietesEnTableau === null ? array() : $proprietesEnTableau);
	}

	function &entrerDans(&$depuis, $nom, $attributs)
	{
		/* On ruse pour que la période soit acceptée comme date */
		if($nom === 'période')
		{
			$autre = $this->classes['date'];
			$this->classes['date'] = 'période';
			$r = &parent::entrerDans($depuis, 'date', $attributs);
			$this->classes['date'] = $autre;
			return $r;
		}
		else
			return parent::entrerDans($depuis, $nom, $attributs);
	}
}

/* Comme un CompoADates, sauf qu'il peut avoir plusieurs périodes de suite */
class CompoADatesRepetees extends CompoADates
{
	public function __construct($proprietesNormales = null, $proprietesEnTableau = null)
	{
		$monTableau = array('date' => 'MaDate', 'période' => 1);
		parent::__construct($proprietesNormales === null ? array() : $proprietesNormales, $proprietesEnTableau === null ? $monTableau : array_merge($proprietesEnTableau, $monTableau));
	}
}

class De_Xml extends CompoAProprietes
{
	public function __construct()
	{
		parent::__construct
		(
			<<<TERMINE
				perso:
				titre maxOccurs=unbounded
				intro maxOccurs=unbounded
				formation:
				expérience:
				langues:
				connaissances:
				intérêts:
				loisirs:
				référence maxOccurs=unbounded:
					prénom
					nom
					rôle
					société
					mél
					tél
					recommandation
				salaire maxOccurs=unbounded attrs=[type]:
					brut
					mois
					variable
					participation
					avantages
				contraintes:
					nonConcurrence
					préavis
					mobilité:
						régions
						pays
				motivation maxOccurs=unbounded:
					conservation
					évolution
					poste
					métier
					entreprise
					lacunes
					pourquoi
				personnalité:
					carrière:
						dominante
						morale maxOccurs=unbounded
						marquant maxOccurs=unbounded
					qualité maxOccurs=unbounded
					défaut maxOccurs=unbounded:
						défaut
						palliatif
					métaphore class=Métaphore maxOccurs=unbounded
TERMINE
		);

		$this->chargeur = new Chargeur();
	}
	
	function analyserParams($argv, &$position)
	{
		$r = array();
		while(true)
		{
			if(count($argv) <= $position && !isset($r['chemin'])) { $this->pondreAide(); return null; }
			switch($argv[$position])
			{
				// trad pourrait être géré comme un filtre, après le chargeur. Sauf que sur du XML, il est plus simple d'appliquer la traduction en amont (texte pur) plutôt que sur la structure en mémoire (objets, sous-objets, attributs, etc.).
				case 'trad':
					if($position >= count($argv) - 1) { $this->pondreAide(); return null; }
					$r['trad'] = $argv[++$position];
					break;
				default:
					// Le premier argument que l'on ne reconnaît pas, après avoir déjà trouvé un chemin, est signe qu'il faut laisser la main.
					if(isset($r['chemin']))
						return $r;
					$r['chemin'] = $argv[$position];
					break;
			}
		++$position; // Pour le bien-être de notre appelant.
		}
	}
	
	function analyserChamps($champs)
	{
		$machin = $_FILES;
		/* Alors voilà, j'avais quelque chose de propre où la poule se chargeait
		 * de ne refiler de $_POST que ce qui pouvait intéresser notre module,
		 * dans $params, mais cet abruti de PHP décide, pour les fichiers
		 * balancés au serveur, de passer par un $_FILES. Donc on fait un
		 * ignoble codage en dur pour récupérer nos données. */
		foreach(array('compo', 'tmp_name', 'xml', 'fichier') as $bidule) // On rentre dans _FILES à la recherche de notre truc.
			if(array_key_exists($bidule, $machin))
				$machin = $machin[$bidule];
			else
			{
				$machin = null;
				break;
			}
		if($machin) // Un fichier arrivé par HTTP.
		{
			$champs['chemin'] = $_FILES['compo']['tmp_name']['xml']['fichier']; // Mais que ce langage est con! Avec un peu de logique ils auraient fait un compo.xml.fichier contenant un tmp_name, un name, un type, mais non, ils me donnent un compo avec son tmp_name, son name, son type, chacun d'eux ayant un xml.fichier.
			move_uploaded_file($champs['chemin'], $champs['chemin'].'.1'); // Qu'il ne nous l'efface pas en sortant du script!
			$champs['chemin'] = $champs['chemin'].'.1'; /* À FAIRE: il faudra l'effacer quand on aura fini avec. */
		}
		return $champs;
	}
	
	function pondreAide()
	{
		fprintf(STDERR, <<<TERMINE
Utilisation: xml <fichier source> [trad <langue>]

TERMINE
		);
	}
	
	function pondreInterface($champ)
	{
?>
	Fichier: <input type="file" name="<?php echo($champ); ?>[fichier]"></input>
<?php
	}
	
	function composer($params)
	{
		if(!array_key_exists('chemin', $params)) { /* À FAIRE: au secours, au secours, qu'est-ce que je fais, là? */ die; }
		$contenu = null;
		if(isset($params['trad']))
		{
			require_once dirname(__FILE__).'/../trad.php';
			$t = new Trad;
			$contenu = file_get_contents($params['chemin']);
			$cheminTrad = preg_replace('#\.[^.]*$#', '.'.$params['trad'].'.trad', $params['chemin']);
			if(!file_exists($cheminTrad))
				throw new Exception($cheminTrad.' introuvable');
			$contenu = $t->traduis($params['chemin'], $cheminTrad);
			$langue = $params['trad'];
		}
		$this->chargeur->charger($params['chemin'].(isset($langue) ? '(traduit)' : ''), 'cv', $this, false, $contenu);
		if(isset($langue))
			$this->données->langue = $langue;
		return $this->données;
	}
	
	protected $chargeur;
}

/* À FAIRE: un constructeur qui prenne tout ce bazar en un seul tableau avec des
 * sous-tableaux de sous-tableaux, mais s'il-vous-plaît, pas tant de copier-
 * coller à la fois, c'est mauvais pour la crâne! */
class Perso extends CompoAProprietes { public function __construct() { parent::__construct(array('naissance' => 'CompoDate', 'photo' => 'CompoImage'), array()); } }
class Intérêts extends CompoAProprietes { public function __construct() { parent::__construct(array(), array('domaine' => 1)); } }
class Domaine extends CompoAProprietes { public function __construct() { parent::__construct(array(), array('techno' => -1)); } }
class Loisirs extends CompoAProprietes { public function __construct() { parent::__construct(array(), array('activité' => 0)); } }
class Formation extends CompoAProprietes { public function __construct() { parent::__construct(array(), array('études' => 'CompoADates')); } }
class Expérience extends CompoAProprietes { public function __construct() { parent::__construct(array(), array('projet' => 1)); } }
class Projet extends CompoADatesRepetees { public function __construct() { parent::__construct(array('cadre' => 0, 'typologie' => 0, 'départ' => 0), array('rôle' => 0, 'lieu' => 0, 'techno' => -1, 'société' => 0, 'tâche' => -1, 'morale' => 0, 'marquant' => 0, 'bilan' => 1, 'réal' => 0, 'référence' => 1)); } }
class Bilan extends CompoAProprietes
{
	public function __construct() { parent::__construct(array()); }

	public function entrerAvecAttrs($attributs)
	{
		$this->données = new Texte;
		if(isset($attributs['s']))
			$this->données->signe = $attributs['s'];
		return $this->données;
	}

	public function contenuPour(& $objet, $contenu)
	{
		$this->données->texte .= $contenu;
	}
}
class Métaphore extends CompoAProprietes
{
	public function __construct() { parent::__construct(array()); }

	public function entrerAvecAttrs($attributs)
	{
		$this->données = new Texte;
		if(isset($attributs['comme']))
			$this->données->comme = $attributs['comme'];
		return $this->données;
	}

	public function contenuPour(& $objet, $contenu)
	{
		$this->données->texte .= $contenu;
	}
}
class Référence extends CompoAProprietes
{
	public function __construct()
	{
		parent::__construct
		(
			<<<TERMINE
			prénom
			nom
			rôle
			société
			mél
			tél
			recommandation
TERMINE
		);
	}
}
class Langues extends CompoAProprietes { public function __construct() { parent::__construct(array(), array('langue' => 1)); } }
class Langue extends CompoAProprietes { public function __construct() { parent::__construct(array(), array('certificat' => 0)); } }
class Connaissances extends CompoAProprietes { public function __construct() { parent::__construct(array(), array('catégorie' => 1)); } }
class Catégorie extends CompoAProprietes
{
	public function __construct() { parent::__construct(array(), array('catégorie' => 1)); }
	
	function &entrerDans(&$depuis, $nom, $attributs)
	{
		if($nom == 'connaissance')
		{
			$this->courant = new Texte;
			if(isset($attributs['p']))
				$this->courant->poids = $attributs['p'];
			$this->niveauCourant = $attributs['niveau'];
			return $this->courant;
		}
		else
			return parent::entrerDans($depuis, $nom, $attributs);
	}
	
	function sortirDe(&$objet, $nom)
	{
		if($objet === $this->courant)
		{
			$connaissance = new Connaissance(hexdec($this->niveauCourant));
			if(isset($objet->poids))
				$connaissance->poids = $objet->poids;
			$this->données->maîtrise[$this->courant->texte] = $connaissance;
			$this->données->connaissances[$this->courant->texte] = hexdec($this->niveauCourant);
		}
	}
	
	protected $courant;
	protected $niveauCourant;
}

class CompoImage extends Compo
{
	public $données;
	
	public function notifChargeur($chargeur)
	{
		require_once dirname(__FILE__).'/../util/chemin.inc';
		$this->_cheminXml = new Chemin($chargeur->chemin);
	}
	
	public function contenuPour(& $objet, $contenu)
	{
		$this->données = isset($this->données) ? $this->données.$contenu : $contenu;
	}
	
	public function sortir()
	{
		$chemin = $this->_cheminXml->et($this->données)->cheminComplet();
		if(!file_exists($chemin))
			echo "# $chemin inexistant.\n";
		$this->données = $chemin;
	}
	
	protected $_cheminXml;
}

class CompoDate extends Compo
{
	function contenuPour(&$objet, $contenu)
	{
		$this->données = $this->données === null ? $contenu : $this->données.$contenu;
	}
	
	function sortir()
	{
		$this->données = decouper_datation($this->données);
	}
	
	public $données;
}

class MaDate extends CompoDate
{
	function sortir()
	{
		parent::sortir();
		$données = $this->données;
		$this->données = new Donnee();
		$this->données->d = $données;
		$this->données->f = $données;
	}
}

class Période extends CompoSimple
{
	public $données;
	
	public function __construct()
	{
		$this->données = new Donnee();
		parent::__construct(array('entre' => &$this->données->d, 'et' => &$this->données->f));
	}
	
	function contenuPour(&$objet, $contenu)
	{
		$objet = $objet === $this ? $contenu : $objet.$contenu;
	}
	
	function sortir()
	{
		$this->données->d = decouper_datation($this->données->d);
		$this->données->f = decouper_datation($this->données->f);
	}
}

?>
