<?php

/**
 * Notre statisticien fou, qui effectue toute sorte de calculs sur les CV.
 */
class Zorglub
{
	public function durée($projet)
	{
		$d = 0;
		if(isset($projet->date))
			foreach($projet->date as $plage)
				$d += pasTeX_durée($plage->d, $plage->f);
		if($d > 0)
			return pasTeX_affDurée($d);
	}
	
	public function année($projet)
	{
		$périodes = array();
		foreach($projet->date as $période)
			$périodes[] = array($période->d, $période->f);
		$période = periode_union($périodes);
		if($période[1][0] == -1)
			$période[1] = Date::obtenir(time());
		$période[0] = array($période[0][0], -1, -1, -1, -1, -1);
		$période[1] = array($période[1][0], -1, -1, -1, -1, -1);
		return Periode::aff(Date::mef($période[0]), Date::mef($période[1]));
	}
}

?>
