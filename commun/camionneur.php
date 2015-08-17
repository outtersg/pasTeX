<?php
/*
 * Copyright (c) 2015 Guillaume Outters
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

/**
 * Le camionneur fait ce que le patron lui demande de faire. On a ici un certain nombre de méthodes que la patron pourra exploiter comme fonctions intégrées.
 */
class Camionneur
{
	public function __construct($zorglub)
	{
		$this->_zorglub = $zorglub;
	}
	
	public function aff($x)
	{
		echo htmlspecialchars($x);
	}
	
	public function maj($x)
	{
		return mb_strtoupper($x);
	}
	
	public function minus($x)
	{
		return mb_strtolower($x);
	}
	
	public function cap($x)
	{
		return mb_strtoupper(mb_substr($x, 0, 1)).mb_strtolower(mb_substr($x, 1));
	}
	
	public function substr($x, $d, $f)
	{
		return substr($x, $d, $f);
	}
	
	public function implode($jointure, $contenu)
	{
		if(!is_array($contenu))
			return $contenu;
		foreach($contenu as $n => $élément)
			if(!isset($élément))
				unset($contenu[$n]);
		return implode($jointure, $contenu);
	}
	
	public function opand($x, $y)
	{
		return $x && $y;
	}
	
	public function opor($x, $y)
	{
		return $x ? $x : $y; // Et non pas $x || $y, qui force le résultat en booléen.
	}
	
	public function opdiff($x, $y)
	{
		return $x != $y;
	}
	
	public function last($x)
	{
		return is_array($x) && count($x) ? end($x) : null;
	}
	
	/*- Propre à Pasτεχ -*/
	
	public function date($date)
	{
		return Date::aff(Date::mef($date));
	}
	
	public function durée($projet)
	{
		return $this->_zorglub->durée($projet);
	}
	
	public function année($projet)
	{
		return $this->_zorglub->année($projet);
	}
}
?>
