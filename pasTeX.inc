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

?>