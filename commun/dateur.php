<?php

namespace eu_outters_guillaume\PasTeX;

require_once dirname(__FILE__).'/../util/periode.inc';

class Dateur extends \DateurMoinsUn
{
	public $langue;
	
	public function affPériode($d, $f, $mode = 0)
	{
		$null = array(-1, -1, -1, -1, -1, -1);
		$d == $null && $d = null;
		$f == $null && $f = null;
		if($d === null)
			if($f === null)
				return null;
			else
				return $this->_demiDroite($f, true, $mode);
		if($f === null)
			return $this->_demiDroite($d, false, $mode);
		return parent::affPériode($d, $f, $mode);
	}
	
	/**
	 * Sort la chaîne décrivant une période fermée à un bout mais pas l'autre.
	 */
	protected function _demiDroite($d, $jusquÀSinonDepuis = false, $mode = 0)
	{
		$affDate = parent::aff($d, $mode);
		$langue = isset($this->langue) && isset(self::$CHAÎNES[$this->langue]) ? $this->langue : 'fr';
		$chaînes = self::$CHAÎNES[$langue];
		
		$pos = $jusquÀSinonDepuis ? 1 : 0;
		// Mois et an sont des plages, mais le jour est précis: en français, on passe de "depuis" à "depuis le".
		if($d[2] > 0)
			$pos += 2;
		if(!isset($chaînes[$pos]))
			$pos %= 2;
		
		return strtr($chaînes[$pos], array('%d' => $affDate));
	}
	
	protected static $CHAÎNES = array
	(
		'fr' => array('depuis %d', 'jusqu\'à %d', 'depuis le %d', 'jusqu\'au %d'),
		'en' => array('since %d', 'until %d'),
	);
}

?>
