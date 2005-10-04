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

function pasTeX_interfaceModules($params)
{
	html_enTete();
?>
	<title>Génération de CV</title>
	<script language="javascript">
		g_compoAffiche = null;
		function choisir(chose)
		{
			if(g_compoAffiche) g_compoAffiche.style.setProperty('display', 'none', null);
			g_compoAffiche = document.getElementById(chose);
			g_compoAffiche.style.removeProperty('display');
		}
	</script>
<?php
	$modules = $params['modules'];
	html_corps('style="background: #7F7FFF;" onload="choisir(\''.$modules[0].'\')"');
?>
	<div style="border: 1px blue solid; margin: 8px; padding: 8px; background: #BFBFFF;">
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
?>
			<form action="poule.php" method="post" style="display: none;" id="<?php echo($module); ?>" enctype="multipart/form-data">
				<div>
<?php
		if(array_key_exists('champs', $params))
		{
			$champ = null;
			foreach($params['champs'] as $bidule)
			{
				if($champ)
				{
?>
					<input type="hidden" name="<?php echo $champ ?>[]" value="<?php echo $bidule ?>"></input>
<?php
				}
				else
					$champ = $bidule;
			}
		}
		
		$prefixe = $params['id'].'['.$module.']';
?>
					<input type="hidden" name="<?php echo $prefixe ?>[]" value="1"></input>
<?php
		$momo->pondreInterface($prefixe);
?>
					<input type="submit" value="<?php echo $params['bouton'] ?>"></input>
				</div>
			</form>
<?php
	}
?>
	</div>
<?php
	html_fin();
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

?>