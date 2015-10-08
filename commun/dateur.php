<?php

namespace eu_outters_guillaume\PasTeX;

require_once dirname(__FILE__).'/../util/periode.inc';

class Dateur extends \DateurMoinsUn
{
	public function affPériode($d, $f, $mode = 0)
	{
		$null = array(-1, -1, -1, -1, -1, -1);
		$d == $null && $d = null;
		$f == $null && $f = null;
		if($d === null)
			if($f === null)
				return null;
			else
				return 'jusqu\'à'; /* À FAIRE: jusqu'au éventuellement */
		if($f === null)
			return 'depuis '.parent::aff($d, $mode);
		return parent::affPériode($d, $f, $mode);
	}
}

?>
