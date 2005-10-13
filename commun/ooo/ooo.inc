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

/* NOTE: travailler sur du OOo
 * Le fichier OOo est un simple zip contenant plusieurs fichiers XML. Créer un
 * répertoire, y aller, faire un unzip du fichier à étudier.
 * Le XML est tassé, il ne comporte donc qu'un retour à la ligne après la balise
 * <?xml … ?>. On peut le rendre plus lisible avec mon script xmlpropre.
 * Pour retasser un XML passé par xmlpropre:
bind 'set disable-completion on' # Une tabulation gênante un peu en dessous.
empaqueter()
{
echo '<?xml version="1.0" encoding="UTF-8"?>'
sed -e 's/^	\{1,\}//' -e '/<?xml/d' | tr -d '\012'
}
bind 'set disable-completion off'
 */

function ooo_enPDF($source, $dest = null)
{
	$ooo = '/Apps/Bureautique/NeoOfficeJ.app/Contents/MacOS'; /* À FAIRE: récupérer ça dans un fichier de conf */
	if($dest) $dest = '"'.$dest.'"';
	system('netstat -an | grep -q "\.2002.*LISTEN" || "'.$ooo.'/soffice" -headless "-accept=socket,host=localhost,port=2002;urp;" &');
	system('"'.$ooo.'/python" commun/ooo/ooo.py "'.$source.'"'.$dest);
}

?>
