<?php

$t = file_get_contents('php://stdin');

$t = preg_replace_callback
(
		'#{{ *([^}]*(}[^}][^}]*)*) *}}#',
	'raff',
	$t
);

$t = preg_replace_callback
(
	'#{% *([^%]*(%[^}][^%]*)*) *%}#',
	'rstruct',
	$t
);

function raff($r)
{
	$découpage = array();
	découpe($r[1], /* & */$découpage);
	$compil = compile($découpage);
	
	$dernier = $compil[count($compil) - 1];
	$compil[count($compil) - 1] = array('f', 'aff', array($dernier));
	
	$r = '<?php '.implode('; ', array_map('rends', $compil)).';'.' ?>';
	
	return $r;
}

function rstruct($r)
{
	$découpage = array();
	découpe($r[1], /* & */$découpage);
	$compil = compile($découpage);
	
	$r = '<?php '.implode('; ', array_map('rends', $compil)).' ?>';
	
	return $r;
}

function découpe($c, & $découpage)
{
	$exprGénérale = '#[.()", ]#';
	$exprChaîne = '#"#';
	$expr = $exprGénérale;
	
	$chaîne = false;
	
	while(preg_match($expr, $c, $r, PREG_OFFSET_CAPTURE))
	{
		if($r[0][1] > 0)
			if($chaîne === false)
			{
				if(strlen($sousC = trim(substr($c, 0, $r[0][1]))))
					$découpage[] = array('id', $sousC);
			}
			else
				$chaîne .= substr($c, 0, $r[0][1]);
		$c = substr($c, $r[0][1] + strlen($r[0][0]));
		
		switch($bout = $r[0][0])
		{
			case '"':
				if($chaîne !== false)
				{
					$découpage[] = array('"', $chaîne);
					$expr = $exprGénérale;
					$chaîne = false;
				}
				else
				{
					$expr = $exprChaîne;
					$chaîne = '';
				}
				break;
			case ' ':
				break;
			default:
				$découpage[] = array($bout);
				break;
		}
	}
	if($chaîne !== false)
		throw new Exception('Chaîne non terminée');
	if(strlen(trim($c)))
		$découpage[] = array('id', trim($c));
}

function compile($découpage)
{
	list($i, $r) = _compile($découpage, 0);
	return $r;
}

function _compile($découpage, $i)
{
	$compil = array();
	$courant = null;
	$courantProfond = & $courant;
	for(; $i < count($découpage); ++$i)
	{
		$bloc = $découpage[$i];
		switch($bloc[0])
		{
			case 'id':
				$courant = $bloc;
				$compil[] = $courant;
				$courantProfond = & $compil[count($compil) - 1];
				
				// Mots-clés.
				
				switch($courant[1])
				{
					case 'in':
					case 'for':
					case 'endfor':
						$courantProfond[0] = 'struct';
						//if(isset($compil[0][2])) // À faire après.
						//	throw new Exception('Le mot-clé '.$compil[0][1].' ne peut être utilisé comme identifiant.');
						break;
				}
				break;
			case '.':
				if(!$courant || $courant[0] != 'id')
					throw new Exception('Impossible de caser un . après un '.($courantProfond ? serialize($courantProfond) : $rien));
				++$i;
				if(!isset($découpage[$i]) || $découpage[$i][0] != 'id')
					throw new Exception('Impossible de caser un '.serialize(isset($découpage[$i]) ? $découpage[$i] : null).' après un '.serialize($découpage[$i - 1]));
				$courantProfond[2] = $découpage[$i];
				$courantProfond = & $courantProfond[2];
				break;
			case ',':
			case '"':
				$courant = $bloc;
				$compil[] = $courant;
				break;
			case '(':
				if(!$courantProfond || $courantProfond[0] != 'id')
					throw new Exception("Impossible de caser une ( après un ".($courantProfond ? serialize($courantProfond) : null));
				$courantProfond[0] = 'f';
				list($i, $compilFonction) = _compile($découpage, $i + 1);
				$courantProfond[2] = array();
				foreach($compilFonction as $sousBloc)
					if($sousBloc[0] != ',') // En théorie on a pile un bloc sur deux qui est une virgule.
						$courantProfond[2][] = $sousBloc;
				break;
			case ')':
				break 2;
		}
	}
	$r = array();
	for($j = 0; $j < count($compil); $j = $k)
	{
		for($k = $j; $k < count($compil) && in_array($compil[$k][0], array('id', 'f', '"')); ++$k) {}
		if($k <= $j + 1)
		{
			$r[] = $compil[$j];
			$k = $j + 1; // Avançons de toute manière.
		}
		else
			$r[] = array('concat', array_slice($compil, $j, $k - $j));
	}
	
	// Structures: mise en forme.
	
	for($j = 0; $j < count($r); ++$j)
		if($r[$j][0] == 'struct')
			list($j, $r) = _compileStruct($r, $j);
	
	// Retour.
	
	return array($i, $r);
}

function _compileStruct($compil, $i)
{
	$motClé = $compil[$i][1];
	switch($motClé)
	{
		case 'for':
			if(!isset($compil[$i + 3]) || $compil[$i + 2][1] != 'in' || $compil[$i + 1][0] != 'id' || isset($compil[$i + 1][2]))
				throw new Exception('for <var> in <tableau>');
			$compil[$i][2] = array($compil[$i + 1], $compil[$i + 3]);
			array_splice($compil, $i + 1, 3);
			break;
		case 'endfor':
			break;
	}
	return array($i, $compil);
}

function rends($bloc, $racine = true)
{
	$r = '';
	switch($bloc[0])
	{
		case 'f':
			$r .= ($racine ? '$rf' : '').'->'.$bloc[1].'(';
			$r .= implode(', ', array_map('rends', $bloc[2]));
			$r .= ')';
			break;
		case 'id':
			$r .= ($racine ? '$rv' : '').'->'.$bloc[1];
			if(isset($bloc[2]))
				$r .= rends($bloc[2], false);
			break;
		case '"':
			$r .= "'".strtr($bloc[1], array("'" => "\\'"))."'";
			break;
		case 'concat':
			$r .= implode('.', array_map('rends', $bloc[1]));
			break;
		case 'struct':
			switch($bloc[1])
			{
				case 'for':
					$r .= 'foreach('.rends($bloc[2][1]).' as $'.$bloc[2][0][1].') {';
					break;
				case 'endfor':
					$r .= '}';
					break;
			}
			break;
	}
	return $r;
}

echo $t;

?>
