<?php
/*
 * Copyright (c) 2004 Guillaume Outters
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

/* La Poule pond un CV comme elle pondrait un œuf. La ponte est sa raison de vivre,
 * ce n'est pas une telle bassesse du destin qui la détournerait de sa vocation. Et
 * puis, pour ce qu'elle a de cerveau, le temps qu'elle se rende compte du
 * remplacement… */

/* La ponte est découpée en trois étapes:
 * - récupération des données du CV et mise en mémoire
 * - filtrage des données (toutes ne figureront peut-être pas sur le CV final)
 * - génération du fichier final
 * Ce qui nous fait bien trois étapes, et non pas quatre comme ma pauvre tête le
 * croyait au départ (car, étant adepte du saucissonnage à outrance, catégorie
 * tranchage de saucisson, elle imaginait une étape intermédiaire d'un méta-
 * modèle assez confus avec des tableaux, des paragraphes et d'autres notions
 * générales qui planeraient un peu, avant de redescendre sur terre via un
 * transformateur de ce format universel en XHTML, OpenOffice, PDF, texte brut
 * encodé UTF-8, et tout ce votre fertile imagination aura cru bon d'ajouter).
 */

/* La première version est bien entendu minimaliste, prototype et codée en dur. */

require_once('compo/xml.php');
require_once('decompo/openoffice/openoffice.php');

$donnees = new Xml($argv[1]);
#print_r($donnees);
#return;
new OpenOffice($donnees);
#new OpenOffice(new Xml($argv[1]));

?>
