<?php

require_once dirname(__FILE__).'/../compo/de_xml.php';

class DeXmlTest extends PHPUnit_Framework_TestCase
{
	public function testImbricationTexte()
	{
		$t = new Texte('Ceci est un texte accentué à outrance');
		$t->marqueurs = array
		(
			array('debut', 0, 8),
			array('verbe', 5, 8),
			array('vide', 8, 8),
			array('a', 28, 30),
		);
		Texte::$Html = true;
		$c = $t->__toString();
		Texte::$Html = false;
		$this->assertEquals('<span class="_m_debut">Ceci <span class="_m_verbe">est</span></span><span class="_m_vide"></span> un texte accentué <span class="_m_a">à</span> outrance', $c);
	}
}
