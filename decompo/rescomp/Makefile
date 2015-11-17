monde: Makefile.deps.cible
	@make -f Makefile.tout

Makefile.deps.cible:
	( echo patrons: ; find modele -name \*.pat | sed -e 's/\.pat$$/.php/' ) | tr '\012' ' ' > Makefile.deps

tout: patrons

modele/%.php: modele/%.pat ../../commun/pat.php
	php ../../commun/pat.php < $< > $@
