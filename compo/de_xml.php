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

require_once('util/date.inc');
require_once('util/xml/chargeur.php');
require_once('util/xml/compo.php');
require_once('util/xml/composimple.php');

class CompoAProprietes extends Compo
{
	/* Prend en paramètre ses deux listes de propriétés (cf. la variable
	 * membre associée pour une description); les tableaux sont de la forme
	 * propriété => nom de classe, pour que la propriété soit gérée par la
	 * classe ainsi nommée, propriété => 1 pour qu'elle soit gérée par une
	 * classe du même nom qu'elle (la propriété XML), ou n'importe quoi d'autre
	 * pour qu'elle soit gérée comme une Donnee. */
	function CompoAProprietes($proprietesNormales, $proprietesEnTableau)
	{
		$this->données = new Donnee();
		$this->enTableau = $proprietesEnTableau;
		$this->normal = $proprietesNormales;
		$this->classes = array();
		foreach(array_merge($proprietesNormales, $proprietesEnTableau) as $cle => $valeur)
			if(is_string($valeur))
				$this->classes[$cle] = $valeur;
			else if($valeur === 1)
				$this->classes[$cle] = $cle;
	}

	function &entrerDans(&$depuis, $nom, $attributs)
	{
		if(is_string($depuis)) $depuis = new Donnee(); // Tant pis pour ceux qui font du HTML (mélange de balises et texte)! Ici, si on avait une feuille (string) à laquelle est demandée l'adjonction d'un nœud, on détruit le texte pour préparer le terrain au nœud.
		if(array_key_exists($nom, $this->classes))
		{
			$nouveau = $this->classes[$nom];
			$nouveau = new $nouveau();
			$donnee = &$nouveau->données;
		}
		else
		{
			$nouveau = null;
			$donnee = &$nouveau;
		}
		if(array_key_exists($nom, $this->enTableau)) // On est en train de nous constituer un tableau de ces propriétés (propriété multi-valuée).
			$depuis->données->{$nom}[] = &$donnee;
		else // Sinon c'est une propriété unique de l'objet.
			$depuis->données->{$nom} = &$donnee;
		return $nouveau;
	}
	
	function contenuPour(&$objet, $contenu)
	{
		if(is_a($objet, Donnee))
		{
			if(count(get_object_vars($objet)) > 0) return; // Une fois qu'il est devenu un object complet, on ne change pas sa nature. Tant pis pour les données!
			$objet = null;
		}
		$objet = $objet === null ? $contenu : $objet.$contenu;
	}
	
	protected $enTableau; // Liste des sous-éléments XML agrégeables en tableau dans cet objet.
	protected $normal; // Liste des sous-éléments XML qui doivent donner une propriété unique de cet objet.
	protected $classes; // Association d'une classe de Compo à un élément XML.
	protected $données;
}

/* CompoAProprietes connaissant les propriétés particulières 'date' et 'période' */
class CompoADates extends CompoAProprietes
{
	function CompoADates($proprietesNormales = null, $proprietesEnTableau = null)
	{
		$monTableau = array('date' => 'MaDate', 'période' => 1);
		$this->CompoAProprietes($proprietesNormales === null ? $monTableau : array_merge($proprietesNormales, $monTableau), $proprietesEnTableau === null ? array() : $proprietesEnTableau);
	}

	function &entrerDans(&$depuis, $nom, $attributs)
	{
		/* On ruse pour que la période soit acceptée comme date */
		if($nom === 'période')
		{
			$autre = $this->classes['date'];
			$this->classes['date'] = 'période';
			return parent::entrerDans(&$depuis, 'date', $attributs);
			$this->classes['date'] = $autre;
		}
		else
			return parent::entrerDans(&$depuis, $nom, $attributs);
	}
}

/* Comme un CompoADates, sauf qu'il peut avoir plusieurs périodes de suite */
class CompoADatesRepetees extends CompoADates
{
	function CompoADatesRepetees($proprietesNormales = null, $proprietesEnTableau = null)
	{
		$monTableau = array('date' => 'MaDate', 'période' => 1);
		$this->CompoAProprietes($proprietesNormales === null ? array() : $proprietesNormales, $proprietesEnTableau === null ? $monTableau : array_merge($proprietesEnTableau, $monTableau));
	}
}

class De_Xml extends CompoAProprietes
{
	function De_Xml()
	{
		$this->CompoAProprietes(array('perso' => 1, 'titre' => 0, 'formation' => 1, 'expérience' => 1, 'langues' => 1, 'connaissances' => 1, 'intérêts' => 1, 'loisirs' => 1), array());
		$this->chargeur = new Chargeur();
	}
	
	function analyserParams($argv, &$position)
	{
		if(count($argv) <= $position) { pondreAide(); return null; }
		++$position; // Pour le bien-être de notre appelant.
		return array('chemin' => $argv[$position - 1]);
	}
	
	function analyserChamps($champs) { return $champs; }
	
	function pondreAide()
	{
		fprintf(STDERR, <<<TERMINE
Utilisation: xml <fichier source>

TERMINE
		);
	}
	
	function pondreInterface($champ)
	{
?>
	Fichier: <input type="text" name="<?php echo($champ); ?>[chemin]"></input>
<?php
	}
	
	function composer($params)
	{
		if(!array_key_exists('chemin', $params)) { /* À FAIRE: au secours, au secours, qu'est-ce que je fais, là? */ die; }
		$this->chargeur->charger($params['chemin'], 'cv', &$this);
		return $this->données;
	}
	
	protected $chargeur;
}

/* À FAIRE: un constructeur qui prenne tout ce bazar en un seul tableau avec des
 * sous-tableaux de sous-tableaux, mais s'il-vous-plaît, pas tant de copier-
 * coller à la fois, c'est mauvais pour la crâne! */
class Perso extends CompoAProprietes { function Perso() { $this->CompoAProprietes(array(), array()); } }
class Intérêts extends CompoAProprietes { function Intérêts() { $this->CompoAProprietes(array(), array('domaine' => 1)); } }
class Domaine extends CompoAProprietes { function Domaine() { $this->CompoAProprietes(array(), array('techno' => 0)); } }
class Loisirs extends CompoAProprietes { function Loisirs() { $this->CompoAProprietes(array(), array('activité' => 0)); } }
class Formation extends CompoAProprietes { function Formation() { $this->CompoAProprietes(array(), array('études' => 'CompoADates')); } }
class Expérience extends CompoAProprietes { function Expérience() { $this->CompoAProprietes(array(), array('projet' => 1)); } }
class Projet extends CompoADatesRepetees { function Projet() { $this->CompoADatesRepetees(array(), array('techno' => 0, 'société' => 0, 'tâche' => 0)); } }
class Langues extends CompoAProprietes { function Langues() { $this->CompoAProprietes(array(), array('langue' => 1)); } }
class Langue extends CompoAProprietes { function Langue() { $this->CompoAProprietes(array(), array('certificat' => 0)); } }
class Connaissances extends CompoAProprietes { function Connaissances() { $this->CompoAProprietes(array(), array('catégorie' => 1)); } }
class Catégorie extends CompoAProprietes
{
	function Catégorie() { $this->CompoAProprietes(array(), array('catégorie' => 1)); }
	
	function &entrerDans(&$depuis, $nom, $attributs)
	{
		if($nom == 'connaissance')
		{
			$this->courant = null;
			$this->niveauCourant = $attributs['niveau'];
			return $this->courant;
		}
		else
			return parent::entrerDans(&$depuis, $nom, $attributs);
	}
	
	function sortirDe(&$objet)
	{
		if($objet === $this->courant)
			$this->données->connaissances[$this->courant] = hexdec($this->niveauCourant);
	}
	
	protected $courant;
	protected $niveauCourant;
}

class CompoDate extends Compo
{
	function CompoDate() {}
	
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
	function MaDate()  { $this->CompoDate(); }
	
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
	function Période() { $this->données = new Donnee(); $this->CompoSimple(array('entre' => &$this->données->d, 'et' => &$this->données->f)); }
	
	function contenuPour(&$objet, $contenu)
	{
		$objet = $objet === null ? $contenu : $objet.$contenu;
	}
	
	function sortir()
	{
		$this->données->d = decouper_datation($this->données->d);
		$this->données->f = decouper_datation($this->données->f);
	}
}

?>
