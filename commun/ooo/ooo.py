# Utilisation:
# netstat -an | grep -q '\.2002.*LISTEN' || $OOO/soffice -headless '-accept=socket,host=localhost,port=8100;urp;' &
# $OOO/python cettechose.py fichier.sxw sortie.pdf
# Pas de copyright là-dessus, la majeure partie est repompée de:
# la doc de PyUNO sur <http://udk.openoffice.org/python/python-bridge.html>, ainsi
# que <http://www.oooforum.org/forum/viewtopic.phtml?t=3772> pour la conversion
# elle-même, et <http://udk.openoffice.org/python/samples/ooextract.py> pour la
# sortie en stdout.

import sys
import os
import uno
from unohelper import Base
from com.sun.star.beans import PropertyValue
from com.sun.star.io import XOutputStream

def urlPour(fichier):
	if fichier[0] == '/':
		return 'file://'+fichier
	else:
		return 'file://'+os.getcwd()+'/'+fichier

def prop(nom, contenu):
	return PropertyValue(nom, 0, contenu, 0)

class OutputStream( Base, XOutputStream ):
	def __init__( self ):
		self.closed = 0
	def closeOutput(self):
		self.closed = 1
	def writeBytes( self, seq ):
		sys.stdout.write( seq.value )
	def flush( self ):
		pass

url = urlPour(sys.argv[1])
acces = uno.getComponentContext()
connecteur = acces.ServiceManager.createInstanceWithContext("com.sun.star.bridge.UnoUrlResolver", acces)
chargeur = connecteur.resolve("uno:socket,host=localhost,port=2002;urp;StarOffice.ComponentContext").ServiceManager.createInstance("com.sun.star.frame.Desktop")
docu = chargeur.loadComponentFromURL(url, "_blank", 0, (prop("Hidden", True),))
if len(sys.argv) > 2:
	url = urlPour(sys.argv[2])
else:
	url = 'private:stream';
docu.storeToURL(url, (prop("FilterName", "writer_pdf_Export"),prop("OutputStream", OutputStream())))
docu.close(True)
