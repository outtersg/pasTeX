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
require_once('util/module.inc');

class De_Liste
{
	function De_Liste() {}
	
	function analyserParams($argv, &$position)
	{
		$modules = module_liste(dirname(__FILE__), 0, '.php');
		echo 'Modules de chargement installés:'."\n";
		foreach($modules as $module)
			if($module != 'de_liste' && substr($module, 0, 3) == 'de_')
				echo '  '.substr($module, 3)."\n";
		return null;
	}
	
	function analyserChamps($params)
	{
		html_enTete();
?>
	<title>Génération de CV</title>
<?php
		html_corps();
		html_fin();
	}
	
	function composer($params) { return $this; }
}

?>
