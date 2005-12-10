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

/* Utilitaires de soumission de formulaires HTTP. */

require_once('util/params.inc');

function formu_tronque($bidule, $limite)
{
	return strlen($bidule) > $limite ? substr($bidule, 0, $limite - 5).'[...]' : $bidule;
}
	
function formu_conc($bidules, $limite, $separateur = ', ', $inverse = false)
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

function formu_paramsVersParamsUrl($params)
{
	$params[session_name()] = session_id();
	return substr(params_decomposer(null, $params, 0), 1);
}

?>