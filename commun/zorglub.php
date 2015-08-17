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
}

?>
