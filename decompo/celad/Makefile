all: modele/content.xml.php modele/styles.xml.php

modele/%.php: modele/%.pat
	php pat.php < $< > $@
