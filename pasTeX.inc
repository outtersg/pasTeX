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

require_once('util/html.inc');
require_once('util/periode.inc');

function pasTeX_chargerCompo($compo)
{
	$compo = 'de_'.$compo; // Bravo, encore un langage qui ne supporte pas les espaces de nommage et oblige donc à, par exemple, préfixer tous les compositeurs de la même manière afin de s'assurer qu'ils n'entrent pas en conflit avec les décompositeurs!
	require_once('compo/'.$compo.'.php');
	$compo = new $compo();
	return $compo;
}

function pasTeX_chargerDecompo($decompo)
{
	require_once('decompo/'.$decompo.'/'.$decompo.'.php');
	$decompo = new $decompo();
	return $decompo;
}

function pasTeX_debutInterface($titre, $dansLEnTete = null, $onload = null)
{
	html_enTete();
	html_meta('meta http-equiv="Content-Type" content="text/html; charset=UTF-8"'); // IE
?>
	<title>Génération de CV</title>
<?php
	if($dansLEnTete !== null) print($dansLEnTete);
	html_corps('style="background: #7F7FFF;"'.($onload !== null ? 'onload="'.$onload.'"' : ''));
?>
	<div style="border: 1px blue solid; margin: 8px; padding: 8px; background: #BFBFFF;">
<?php
}

/* Prépare un formulaire HTML pour un module.
 * Paramètres:
 *  $moduleDestinataireDuFormulaire: nom du module qui devra être appelé en
 *    réponse au formulaire.
 *  $params: options du formulaire.
 *  $supplément: attributs du <form>.
 * Renvoie le préfixe que devra avoir chaque input destiné au module.
 */
function pasTeX_debutFormu($moduleDestinataireDuFormulaire, $params, $supplément = null)
{
	$prefixe = $params['id'].'['.$moduleDestinataireDuFormulaire.']';
?>
	<form action="poule.php" method="post"<?php if($supplément !== null) echo ' '.$supplément;?> enctype="multipart/form-data">
	<input type="hidden" name="<?php echo $prefixe ?>[]" value="1"></input>
<?php
	if(array_key_exists('champs', $params))
	{
		$champ = null;
		foreach($params['champs'] as $bidule)
		{
			if($champ)
				echo('<input type="hidden" name="'.$champ.'[]" value="'.$bidule.'"></input>');
			else
				$champ = $bidule;
		}
	}
	
	return $prefixe;
}

function pasTeX_finInterface()
{
	print('</div>');
	html_fin();
}

function pasTeX_interfaceModules($params)
{
	$modules = $params['modules'];
	pasTeX_debutInterface('Génération de CV', <<<TERMINE
	<script type="text/javascript" src="js/html.js"></script>
	<script language="javascript">
		g_compoAffiche = null;
		function choisir(chose)
		{
			if(g_compoAffiche) html_planquer(g_compoAffiche);
			g_compoAffiche = document.getElementById(chose);
			html_montrer(g_compoAffiche);
		}
	</script>
TERMINE
	, 'choisir(\''.$modules[0].'\')');
?>
		<form action="bidon" method="post">
			<?php echo $params['aff'] ?>: 
			<select name="choix" onchange="choisir(this.value);">
<?php
	foreach($modules as $module)
	{
?>
				<option value="<?php echo($module) ?>"><?php echo($module) ?></option>
<?php
	}
?>
			</select>
		</form>
<?php
	foreach($modules as $module)
	{
		$momo = $params['chargeur']($module);
		$prefixe = pasTeX_debutFormu($module, $params, 'style="display: none;" id="'.$module.'"');
		echo '<div>';
		$momo->pondreInterface($prefixe);
?>
					<input type="submit" value="<?php echo $params['bouton'] ?>"></input>
				</div>
			</form>
<?php
	}
	
	pasTeX_finInterface();
}

function pasTeX_unionPeriodes($dates)
{
	$moments = array();
	foreach($dates as $moment)
		$moments[] = array($moment->d, $moment->f);
	return periode_union($moments);
}

function pasTeX_descriptionPeriode($d, $f)
{
	if($d === null)
		if($f === null)
			return null;
		else
			return 'jusqu\'à'; /* À FAIRE: jusqu'au éventuellement */
	if($f === null)
		return 'depuis '.periode_affDate($d);
	return periode_aff($d, $f);
}

/* Eh oui, messieurs-dames, un langage des années 2000 qui ne gère pas en natif
 * l'UTF-8! */
/* À FAIRE: savoir se servir des spécificités de l'environnement (utf-8-mac,
 * mb_string, etc., s'ils sont présents). */
//function pasTeX_maj($chaine) { return mb_strtoupper($chaine{0}).substr($chaine, 1); }
//function pasTeX_maj($chaine) { $r = iconv('UTF-8', 'UTF-8-MAC', $chaine); return iconv('UTF-8-MAC', 'UTF-8', strtoupper($r{0}).substr($r, 1)); }
function pasTeX_maj($chaine) { return strtoupper($chaine{0}).substr($chaine, 1); }

?>