<?php echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n"; ?>
<w:document xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:w10="urn:schemas-microsoft-com:office:word" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml" xmlns:wpg="http://schemas.microsoft.com/office/word/2010/wordprocessingGroup" xmlns:wpi="http://schemas.microsoft.com/office/word/2010/wordprocessingInk" xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml" xmlns:wps="http://schemas.microsoft.com/office/word/2010/wordprocessingShape" mc:Ignorable="w14 wp14">
	<w:body>
		<w:p w:rsidR="00974A7A" w:rsidRPr="00B223F1" w:rsidRDefault="00974A7A" w:rsidP="00B223F1">
			<w:pPr>
				<w:pStyle w:val="Titre1"/>
			</w:pPr>
			<w:r w:rsidRPr="00B223F1">
				<w:t>DOSSIER DE COMPÉTENCES</w:t>
			</w:r>
		</w:p>
		<w:p w:rsidR="00DA6837" w:rsidRDefault="00DA6837">
			<w:pPr>
				<w:spacing w:after="200" w:line="276" w:lineRule="auto"/>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
		</w:p>
		<w:p w:rsidR="00DA6837" w:rsidRDefault="00DA6837">
			<w:pPr>
				<w:spacing w:after="200" w:line="276" w:lineRule="auto"/>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
		</w:p>
		<w:p w:rsidR="00DA6837" w:rsidRDefault="00DA6837">
			<w:pPr>
				<w:spacing w:after="200" w:line="276" w:lineRule="auto"/>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
			<w:r>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t>Nom : {{ perso.nom }}</w:t>
			</w:r>
		</w:p>
		<w:p w:rsidR="00DA6837" w:rsidRDefault="00DA6837">
			<w:pPr>
				<w:spacing w:after="200" w:line="276" w:lineRule="auto"/>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
			<w:r>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t>Prénom : {{ perso.prénom }}</w:t>
			</w:r>
		</w:p>
		<w:p w:rsidR="00DA6837" w:rsidRDefault="00DA6837">
			<w:pPr>
				<w:spacing w:after="200" w:line="276" w:lineRule="auto"/>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
			<w:r>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t><!-- dur -->Adresse actuelle : {{ implode(" • ", perso.adresse) }}</w:t>
			</w:r>
		</w:p>
		<w:p w:rsidR="00DA6837" w:rsidRDefault="00DA6837">
			<w:pPr>
				<w:spacing w:after="200" w:line="276" w:lineRule="auto"/>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
			<w:r>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t>Numéro de portable : {{ perso.tél }}</w:t>
			</w:r>
		</w:p>
		<w:p w:rsidR="00DA6837" w:rsidRDefault="00DA6837">
			<w:pPr>
				<w:spacing w:after="200" w:line="276" w:lineRule="auto"/>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
			<w:r>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t>Email : {{ perso.mél }}</w:t>
			</w:r>
		</w:p>
		<w:p w:rsidR="00DA6837" w:rsidRDefault="00DA6837">
			<w:pPr>
				<w:spacing w:after="200" w:line="276" w:lineRule="auto"/>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
			<w:r>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t>Date et lieu de naissance : {{ perso.ddn }}, Saint-Cloud (92)</w:t>
			</w:r>
		</w:p>
		<w:p w:rsidR="00DA6837" w:rsidRDefault="00DA6837">
			<w:pPr>
				<w:spacing w:after="200" w:line="276" w:lineRule="auto"/>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
			<w:r>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t>Numéro de Sécurité Sociale : 179109206404436</w:t>
			</w:r>
		</w:p>
		<w:p w:rsidR="00DA6837" w:rsidRDefault="00DA6837">
			<w:pPr>
				<w:spacing w:after="200" w:line="276" w:lineRule="auto"/>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
			<w:r>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t><!-- dur -->Prise de référence : Claire Robieux / AccorHotels / Architecte SI transverse / Claire.ROBIEUX@accor.com - 06 60 31 50 19</w:t>
			</w:r>
		</w:p>
		<w:p w:rsidR="00DA6837" w:rsidRDefault="00DA6837">
			<w:pPr>
				<w:spacing w:after="200" w:line="276" w:lineRule="auto"/>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
			<w:r>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t><!-- dur -->Salaire actuel : 52 000 € (Île-de-France)</w:t>
			</w:r>
		</w:p>
		<w:p w:rsidR="00DA6837" w:rsidRDefault="00DA6837">
			<w:pPr>
				<w:spacing w:after="200" w:line="276" w:lineRule="auto"/>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
			<w:r>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t><!-- dur -->Salaire souhaité : 52 000 € (province)</w:t>
			</w:r>
		</w:p>
		<w:p w:rsidR="00DA6837" w:rsidRDefault="00DA6837">
			<w:pPr>
				<w:spacing w:after="200" w:line="276" w:lineRule="auto"/>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
			<w:r>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t><!-- dur -->Souhaits de métiers ou projets : projets de grande ampleur à forte composante technique, requérant des interactions avec l'ensemble des interlocuteurs (des ingénieurs réseau à l'utilisateur final), dans le domaine du service</w:t>
			</w:r>
		</w:p>
		<w:p w:rsidR="00DA6837" w:rsidRDefault="00DA6837">
			<w:pPr>
				<w:spacing w:after="200" w:line="276" w:lineRule="auto"/>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
			<w:r>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:br w:type="page"/>
			</w:r>
		</w:p>
		<w:p w:rsidR="00DA6837" w:rsidRDefault="00DA6837" w:rsidP="00DA6837">
			<w:pPr>
				<w:spacing w:after="200" w:line="276" w:lineRule="auto"/>
				<w:jc w:val="both"/>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
		</w:p>
		<w:p w:rsidR="00DA6837" w:rsidRPr="00B223F1" w:rsidRDefault="00DA6837" w:rsidP="00DA6837">
			<w:pPr>
				<w:pStyle w:val="Titre1"/>
			</w:pPr>
			<w:r w:rsidRPr="00B223F1">
				<w:t>DOSSIER DE COMPÉTENCES</w:t>
			</w:r>
		</w:p>
		<w:p w:rsidR="00974A7A" w:rsidRDefault="00974A7A" w:rsidP="00974A7A">
			<w:pPr>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
		</w:p>
		<w:p w:rsidR="00DA6837" w:rsidRPr="00974A7A" w:rsidRDefault="00DA6837" w:rsidP="00974A7A">
			<w:pPr>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
		</w:p>
		<w:p w:rsidR="00974A7A" w:rsidRPr="00974A7A" w:rsidRDefault="00974A7A" w:rsidP="00974A7A">
			<w:pPr>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
			<w:r w:rsidRPr="00974A7A">
				<w:rPr>
					<w:b/>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t>{{ titre }}</w:t>
			</w:r>
		</w:p>
		<w:p w:rsidR="00974A7A" w:rsidRPr="00974A7A" w:rsidRDefault="00974A7A" w:rsidP="00974A7A">
			<w:pPr>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
			<w:r w:rsidRPr="00974A7A">
				<w:rPr>
					<w:b/>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t><!-- dur -->Architecture • Installation et automatisation des postes dév, environnements de test, plates-formes de prod • Formation développeurs, ingés système • Expertises techniques en phases de conception, développement, ou diagnostic</w:t>
			</w:r>
		</w:p>
		<w:p w:rsidR="00974A7A" w:rsidRPr="00974A7A" w:rsidRDefault="00974A7A" w:rsidP="00974A7A">
			<w:pPr>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
		</w:p>
		<w:p w:rsidR="00974A7A" w:rsidRPr="00974A7A" w:rsidRDefault="00974A7A" w:rsidP="00974A7A">
			<w:pPr>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
		</w:p>
		<w:p w:rsidR="00974A7A" w:rsidRPr="00974A7A" w:rsidRDefault="00974A7A" w:rsidP="00974A7A">
			<w:pPr>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
		</w:p>
		<w:p w:rsidR="00974A7A" w:rsidRPr="00B223F1" w:rsidRDefault="00974A7A" w:rsidP="00B223F1">
			<w:pPr>
				<w:pStyle w:val="Titre2"/>
			</w:pPr>
			<w:r w:rsidRPr="00B223F1">
				<w:t>Domaines de compétences</w:t>
			</w:r>
		</w:p>
		<!-- dur -->
		{% for domaine in [ "DevOps", "automatisation", "performances", "architecture logicielle" ] %}
		<w:p w:rsidR="00974A7A" w:rsidRPr="00974A7A" w:rsidRDefault="00974A7A" w:rsidP="00974A7A">
			<w:pPr>
				<w:pStyle w:val="Paragraphedeliste"/>
				<w:numPr>
					<w:ilvl w:val="0"/>
					<w:numId w:val="2"/>
				</w:numPr>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
			<w:r w:rsidRPr="00974A7A">
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t>{{ domaine }}</w:t>
			</w:r>
		</w:p>
		{% endfor %}
		<w:p w:rsidR="00974A7A" w:rsidRPr="00974A7A" w:rsidRDefault="00974A7A" w:rsidP="00974A7A">
			<w:pPr>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
		</w:p>
		<w:p w:rsidR="00974A7A" w:rsidRPr="00974A7A" w:rsidRDefault="00974A7A" w:rsidP="00B223F1">
			<w:pPr>
				<w:pStyle w:val="Titre2"/>
			</w:pPr>
			<w:r w:rsidRPr="00974A7A">
				<w:t>Environnements d’interventions</w:t>
			</w:r>
		</w:p>
		<!-- dur -->
		{% for domaine in [ "télécom", "routier", "défense", "institutionnel" ] %}
		<w:p w:rsidR="00974A7A" w:rsidRPr="00974A7A" w:rsidRDefault="00974A7A" w:rsidP="00974A7A">
			<w:pPr>
				<w:pStyle w:val="Paragraphedeliste"/>
				<w:numPr>
					<w:ilvl w:val="0"/>
					<w:numId w:val="2"/>
				</w:numPr>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
			<w:r w:rsidRPr="00974A7A">
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t>{{ domaine }}</w:t>
			</w:r>
		</w:p>
		{% endfor %}
		<w:p w:rsidR="00974A7A" w:rsidRPr="00974A7A" w:rsidRDefault="00974A7A" w:rsidP="00974A7A">
			<w:pPr>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
		</w:p>
		<w:p w:rsidR="00974A7A" w:rsidRPr="00974A7A" w:rsidRDefault="00974A7A" w:rsidP="00B223F1">
			<w:pPr>
				<w:pStyle w:val="Titre2"/>
			</w:pPr>
			<w:r w:rsidRPr="00974A7A">
				<w:t>Environnements techniques</w:t>
			</w:r>
		</w:p>
		<!-- dur -->
		{% for domaine in [ "Linux / BSD", "web (PHP)", "shell", "PostgreSQL", "Jenkins" ] %}
		<w:p w:rsidR="00974A7A" w:rsidRPr="00974A7A" w:rsidRDefault="00974A7A" w:rsidP="00974A7A">
			<w:pPr>
				<w:pStyle w:val="Paragraphedeliste"/>
				<w:numPr>
					<w:ilvl w:val="0"/>
					<w:numId w:val="2"/>
				</w:numPr>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
			<w:r w:rsidRPr="00974A7A">
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t>{{ domaine }}</w:t>
			</w:r>
		</w:p>
		{% endfor %}
		<w:p w:rsidR="00974A7A" w:rsidRPr="00974A7A" w:rsidRDefault="00974A7A" w:rsidP="00974A7A">
			<w:pPr>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
		</w:p>
		<w:p w:rsidR="00974A7A" w:rsidRDefault="00974A7A">
			<w:pPr>
				<w:rPr>
					<w:rFonts w:ascii="Lucida Bright" w:hAnsi="Lucida Bright"/>
					<w:color w:val="5C7F92" w:themeColor="background2"/>
					<w:sz w:val="40"/>
					<w:szCs w:val="40"/>
				</w:rPr>
			</w:pPr>
			<w:r>
				<w:rPr>
					<w:rFonts w:ascii="Lucida Bright" w:hAnsi="Lucida Bright"/>
					<w:color w:val="5C7F92" w:themeColor="background2"/>
					<w:sz w:val="40"/>
					<w:szCs w:val="40"/>
				</w:rPr>
				<w:br w:type="page"/>
			</w:r>
		</w:p>
		<w:p w:rsidR="0080279F" w:rsidRDefault="0080279F" w:rsidP="00B223F1">
			<w:pPr>
				<w:pStyle w:val="Titre1"/>
			</w:pPr>
			<w:r>
				<w:lastRenderedPageBreak/>
				<w:t>Synthèse des expériences</w:t>
			</w:r>
		</w:p>
		<w:p w:rsidR="0080279F" w:rsidRDefault="0080279F" w:rsidP="0080279F"/>
		<w:tbl>
			<w:tblPr>
				<w:tblStyle w:val="Grilledutableau"/>
				<w:tblW w:w="10206" w:type="dxa"/>
				<w:tblInd w:w="392" w:type="dxa"/>
				<w:tblBorders>
					<w:top w:val="none" w:sz="0" w:space="0" w:color="auto"/>
					<w:left w:val="none" w:sz="0" w:space="0" w:color="auto"/>
					<w:bottom w:val="none" w:sz="0" w:space="0" w:color="auto"/>
					<w:right w:val="none" w:sz="0" w:space="0" w:color="auto"/>
					<w:insideH w:val="single" w:sz="12" w:space="0" w:color="5C7F92" w:themeColor="background2"/>
					<w:insideV w:val="single" w:sz="12" w:space="0" w:color="5C7F92" w:themeColor="background2"/>
				</w:tblBorders>
				<w:tblLook w:val="04A0" w:firstRow="1" w:lastRow="0" w:firstColumn="1" w:lastColumn="0" w:noHBand="0" w:noVBand="1"/>
			</w:tblPr>
			<w:tblGrid>
				<w:gridCol w:w="2693"/>
				<w:gridCol w:w="1985"/>
				<w:gridCol w:w="1417"/>
				<w:gridCol w:w="4111"/>
			</w:tblGrid>
			<w:tr w:rsidR="00171EAD" w:rsidTr="00171EAD">
				<w:tc>
					<w:tcPr>
						<w:tcW w:w="2693" w:type="dxa"/>
						<w:tcBorders>
							<w:bottom w:val="single" w:sz="12" w:space="0" w:color="5C7F92" w:themeColor="background2"/>
						</w:tcBorders>
					</w:tcPr>
					<w:p w:rsidR="00171EAD" w:rsidRPr="0080279F" w:rsidRDefault="00171EAD" w:rsidP="0080279F">
						<w:pPr>
							<w:spacing w:before="60" w:after="60"/>
							<w:jc w:val="center"/>
							<w:rPr>
								<w:b/>
								<w:sz w:val="20"/>
							</w:rPr>
						</w:pPr>
						<w:r w:rsidRPr="0080279F">
							<w:rPr>
								<w:b/>
								<w:sz w:val="20"/>
							</w:rPr>
							<w:t>Client / Domaine</w:t>
						</w:r>
					</w:p>
				</w:tc>
				<w:tc>
					<w:tcPr>
						<w:tcW w:w="1985" w:type="dxa"/>
						<w:tcBorders>
							<w:bottom w:val="single" w:sz="12" w:space="0" w:color="5C7F92" w:themeColor="background2"/>
						</w:tcBorders>
					</w:tcPr>
					<w:p w:rsidR="00171EAD" w:rsidRPr="0080279F" w:rsidRDefault="00171EAD" w:rsidP="0080279F">
						<w:pPr>
							<w:spacing w:before="60" w:after="60"/>
							<w:jc w:val="center"/>
							<w:rPr>
								<w:b/>
								<w:sz w:val="20"/>
							</w:rPr>
						</w:pPr>
						<w:r w:rsidRPr="0080279F">
							<w:rPr>
								<w:b/>
								<w:sz w:val="20"/>
							</w:rPr>
							<w:t>Statut</w:t>
						</w:r>
					</w:p>
				</w:tc>
				<w:tc>
					<w:tcPr>
						<w:tcW w:w="5528" w:type="dxa"/>
						<w:gridSpan w:val="2"/>
						<w:tcBorders>
							<w:bottom w:val="single" w:sz="12" w:space="0" w:color="5C7F92" w:themeColor="background2"/>
						</w:tcBorders>
					</w:tcPr>
					<w:p w:rsidR="00171EAD" w:rsidRPr="0080279F" w:rsidRDefault="00171EAD" w:rsidP="0080279F">
						<w:pPr>
							<w:spacing w:before="60" w:after="60"/>
							<w:jc w:val="center"/>
							<w:rPr>
								<w:b/>
								<w:sz w:val="20"/>
							</w:rPr>
						</w:pPr>
						<w:r w:rsidRPr="0080279F">
							<w:rPr>
								<w:b/>
								<w:sz w:val="20"/>
							</w:rPr>
							<w:t>Description</w:t>
						</w:r>
					</w:p>
				</w:tc>
			</w:tr>
			{% for boulot in expérience.projet %}
			<w:tr w:rsidR="00171EAD" w:rsidTr="00171EAD">
				<w:tc>
					<w:tcPr>
						<w:tcW w:w="2693" w:type="dxa"/>
						<w:vMerge w:val="restart"/>
						<w:tcBorders>
							<w:top w:val="single" w:sz="12" w:space="0" w:color="5C7F92" w:themeColor="background2"/>
							<w:bottom w:val="single" w:sz="2" w:space="0" w:color="A59D95" w:themeColor="accent3"/>
						</w:tcBorders>
					</w:tcPr>
					<w:p w:rsidR="00171EAD" w:rsidRPr="00171EAD" w:rsidRDefault="00171EAD" w:rsidP="00171EAD">
						<w:pPr>
							<w:spacing w:before="60" w:after="60"/>
							<w:rPr>
								<w:b/>
								<w:color w:val="595959" w:themeColor="accent1"/>
								<w:sz w:val="20"/>
								<w:szCs w:val="20"/>
							</w:rPr>
						</w:pPr>
						<w:r w:rsidRPr="00171EAD">
							<w:rPr>
								<w:b/>
								<w:color w:val="{{ gris(boulot.couleur) }}"/>
								<w:sz w:val="20"/>
								<w:szCs w:val="20"/>
							</w:rPr>
							<w:t>{{ boulot.société|last or "(indépendant)" }}</w:t>
						</w:r>
					</w:p>
					{% if boulot.domaine %}
					<w:p w:rsidR="00171EAD" w:rsidRPr="00171EAD" w:rsidRDefault="00171EAD" w:rsidP="00171EAD">
						<w:pPr>
							<w:spacing w:before="60" w:after="60"/>
							<w:rPr>
								<w:i/>
								<w:color w:val="595959" w:themeColor="accent1"/>
								<w:sz w:val="20"/>
								<w:szCs w:val="20"/>
							</w:rPr>
						</w:pPr>
						<w:r w:rsidRPr="00171EAD">
							<w:rPr>
								<w:i/>
								<w:color w:val="{{ gris(boulot.couleur) }}"/>
								<w:sz w:val="20"/>
								<w:szCs w:val="20"/>
							</w:rPr>
							<w:t>{{ boulot.domaine|" / " }}</w:t>
						</w:r>
					</w:p>
					{% endif %}
				</w:tc>
				<w:tc>
					<w:tcPr>
						<w:tcW w:w="1985" w:type="dxa"/>
						<w:vMerge w:val="restart"/>
						<w:tcBorders>
							<w:top w:val="single" w:sz="12" w:space="0" w:color="5C7F92" w:themeColor="background2"/>
							<w:bottom w:val="single" w:sz="2" w:space="0" w:color="A59D95" w:themeColor="accent3"/>
						</w:tcBorders>
					</w:tcPr>
					<w:p w:rsidR="00171EAD" w:rsidRPr="00171EAD" w:rsidRDefault="00171EAD" w:rsidP="00171EAD">
						<w:pPr>
							<w:spacing w:before="60" w:after="60"/>
							<w:rPr>
								<w:color w:val="595959" w:themeColor="accent1"/>
								<w:sz w:val="20"/>
								<w:szCs w:val="20"/>
							</w:rPr>
						</w:pPr>
						<w:r w:rsidRPr="00171EAD">
							<w:rPr>
								<w:color w:val="{{ gris(boulot.couleur) }}"/>
								<w:sz w:val="20"/>
								<w:szCs w:val="20"/>
							</w:rPr>
							<w:t>{{ boulot.rôle|", " }}</w:t>
						</w:r>
					</w:p>
				</w:tc>
				<w:tc>
					<w:tcPr>
						<w:tcW w:w="1417" w:type="dxa"/>
						<w:tcBorders>
							<w:top w:val="single" w:sz="12" w:space="0" w:color="5C7F92" w:themeColor="background2"/>
							<w:bottom w:val="single" w:sz="2" w:space="0" w:color="A59D95" w:themeColor="accent3"/>
							<w:right w:val="nil"/>
						</w:tcBorders>
					</w:tcPr>
					<w:p w:rsidR="00171EAD" w:rsidRPr="00171EAD" w:rsidRDefault="00171EAD" w:rsidP="0080279F">
						<w:pPr>
							<w:rPr>
								<w:i/>
								<w:color w:val="5C7F92" w:themeColor="background2"/>
								<w:sz w:val="16"/>
								<w:szCs w:val="16"/>
							</w:rPr>
						</w:pPr>
						<w:r w:rsidRPr="00171EAD">
							<w:rPr>
								<w:i/>
								<w:color w:val="5C7F92" w:themeColor="background2"/>
								<w:sz w:val="16"/>
								<w:szCs w:val="16"/>
							</w:rPr>
							<w:t>Intitulé :</w:t>
						</w:r>
					</w:p>
				</w:tc>
				<w:tc>
					<w:tcPr>
						<w:tcW w:w="4111" w:type="dxa"/>
						<w:tcBorders>
							<w:top w:val="single" w:sz="12" w:space="0" w:color="5C7F92" w:themeColor="background2"/>
							<w:left w:val="nil"/>
							<w:bottom w:val="single" w:sz="2" w:space="0" w:color="A59D95" w:themeColor="accent3"/>
						</w:tcBorders>
					</w:tcPr>
					<w:p w:rsidR="00171EAD" w:rsidRPr="00171EAD" w:rsidRDefault="00171EAD" w:rsidP="0080279F">
						<w:pPr>
							<w:rPr>
								<w:color w:val="595959" w:themeColor="accent1"/>
								<w:sz w:val="20"/>
								<w:szCs w:val="20"/>
							</w:rPr>
						</w:pPr>
						<w:r>
							<w:rPr>
								<w:color w:val="{{ gris(boulot.couleur) }}"/>
								<w:sz w:val="20"/>
								<w:szCs w:val="20"/>
							</w:rPr>
							<w:t>{{ [ boulot.nom, boulot.description ]|" : " }}</w:t>
						</w:r>
					</w:p>
				</w:tc>
			</w:tr>
			<w:tr w:rsidR="00171EAD" w:rsidTr="00171EAD">
				<w:tc>
					<w:tcPr>
						<w:tcW w:w="2693" w:type="dxa"/>
						<w:vMerge/>
						<w:tcBorders>
							<w:top w:val="single" w:sz="2" w:space="0" w:color="A59D95" w:themeColor="accent3"/>
							<w:bottom w:val="single" w:sz="2" w:space="0" w:color="A59D95" w:themeColor="accent3"/>
						</w:tcBorders>
					</w:tcPr>
					<w:p w:rsidR="00171EAD" w:rsidRPr="00171EAD" w:rsidRDefault="00171EAD" w:rsidP="00171EAD">
						<w:pPr>
							<w:spacing w:before="60" w:after="60"/>
							<w:rPr>
								<w:color w:val="595959" w:themeColor="accent1"/>
								<w:sz w:val="20"/>
								<w:szCs w:val="20"/>
							</w:rPr>
						</w:pPr>
					</w:p>
				</w:tc>
				<w:tc>
					<w:tcPr>
						<w:tcW w:w="1985" w:type="dxa"/>
						<w:vMerge/>
						<w:tcBorders>
							<w:top w:val="single" w:sz="2" w:space="0" w:color="A59D95" w:themeColor="accent3"/>
							<w:bottom w:val="single" w:sz="2" w:space="0" w:color="A59D95" w:themeColor="accent3"/>
						</w:tcBorders>
					</w:tcPr>
					<w:p w:rsidR="00171EAD" w:rsidRPr="00171EAD" w:rsidRDefault="00171EAD" w:rsidP="00171EAD">
						<w:pPr>
							<w:spacing w:before="60" w:after="60"/>
							<w:rPr>
								<w:color w:val="595959" w:themeColor="accent1"/>
								<w:sz w:val="20"/>
								<w:szCs w:val="20"/>
							</w:rPr>
						</w:pPr>
					</w:p>
				</w:tc>
				<w:tc>
					<w:tcPr>
						<w:tcW w:w="1417" w:type="dxa"/>
						<w:tcBorders>
							<w:top w:val="single" w:sz="2" w:space="0" w:color="A59D95" w:themeColor="accent3"/>
							<w:bottom w:val="single" w:sz="2" w:space="0" w:color="A59D95" w:themeColor="accent3"/>
							<w:right w:val="nil"/>
						</w:tcBorders>
					</w:tcPr>
					<w:p w:rsidR="00171EAD" w:rsidRPr="00171EAD" w:rsidRDefault="00171EAD" w:rsidP="0080279F">
						<w:pPr>
							<w:rPr>
								<w:i/>
								<w:color w:val="5C7F92" w:themeColor="background2"/>
								<w:sz w:val="16"/>
								<w:szCs w:val="16"/>
							</w:rPr>
						</w:pPr>
						<w:r w:rsidRPr="00171EAD">
							<w:rPr>
								<w:i/>
								<w:color w:val="5C7F92" w:themeColor="background2"/>
								<w:sz w:val="16"/>
								<w:szCs w:val="16"/>
							</w:rPr>
							<w:t>Durée :</w:t>
						</w:r>
					</w:p>
				</w:tc>
				<w:tc>
					<w:tcPr>
						<w:tcW w:w="4111" w:type="dxa"/>
						<w:tcBorders>
							<w:top w:val="single" w:sz="2" w:space="0" w:color="A59D95" w:themeColor="accent3"/>
							<w:left w:val="nil"/>
							<w:bottom w:val="single" w:sz="2" w:space="0" w:color="A59D95" w:themeColor="accent3"/>
						</w:tcBorders>
					</w:tcPr>
					<w:p w:rsidR="00171EAD" w:rsidRPr="00171EAD" w:rsidRDefault="00171EAD" w:rsidP="0080279F">
						<w:pPr>
							<w:rPr>
								<w:color w:val="595959" w:themeColor="accent1"/>
								<w:sz w:val="20"/>
								<w:szCs w:val="20"/>
							</w:rPr>
						</w:pPr>
						<w:r>
							<w:rPr>
								<w:color w:val="{{ gris(boulot.couleur) }}"/>
								<w:sz w:val="20"/>
								<w:szCs w:val="20"/>
							</w:rPr>
							<w:t>{{ durée(boulot) }}</w:t>
						</w:r>
					</w:p>
				</w:tc>
			</w:tr>
			<w:tr w:rsidR="00171EAD" w:rsidTr="00171EAD">
				<w:tc>
					<w:tcPr>
						<w:tcW w:w="2693" w:type="dxa"/>
						<w:vMerge/>
						<w:tcBorders>
							<w:top w:val="single" w:sz="2" w:space="0" w:color="A59D95" w:themeColor="accent3"/>
							<w:bottom w:val="single" w:sz="12" w:space="0" w:color="5C7F92" w:themeColor="background2"/>
						</w:tcBorders>
					</w:tcPr>
					<w:p w:rsidR="00171EAD" w:rsidRPr="00171EAD" w:rsidRDefault="00171EAD" w:rsidP="00171EAD">
						<w:pPr>
							<w:spacing w:before="60" w:after="60"/>
							<w:rPr>
								<w:color w:val="595959" w:themeColor="accent1"/>
								<w:sz w:val="20"/>
								<w:szCs w:val="20"/>
							</w:rPr>
						</w:pPr>
					</w:p>
				</w:tc>
				<w:tc>
					<w:tcPr>
						<w:tcW w:w="1985" w:type="dxa"/>
						<w:vMerge/>
						<w:tcBorders>
							<w:top w:val="single" w:sz="2" w:space="0" w:color="A59D95" w:themeColor="accent3"/>
							<w:bottom w:val="single" w:sz="12" w:space="0" w:color="5C7F92" w:themeColor="background2"/>
						</w:tcBorders>
					</w:tcPr>
					<w:p w:rsidR="00171EAD" w:rsidRPr="00171EAD" w:rsidRDefault="00171EAD" w:rsidP="00171EAD">
						<w:pPr>
							<w:spacing w:before="60" w:after="60"/>
							<w:rPr>
								<w:color w:val="595959" w:themeColor="accent1"/>
								<w:sz w:val="20"/>
								<w:szCs w:val="20"/>
							</w:rPr>
						</w:pPr>
					</w:p>
				</w:tc>
				<w:tc>
					<w:tcPr>
						<w:tcW w:w="1417" w:type="dxa"/>
						<w:tcBorders>
							<w:top w:val="single" w:sz="2" w:space="0" w:color="A59D95" w:themeColor="accent3"/>
							<w:bottom w:val="single" w:sz="12" w:space="0" w:color="5C7F92" w:themeColor="background2"/>
							<w:right w:val="nil"/>
						</w:tcBorders>
					</w:tcPr>
					<w:p w:rsidR="00171EAD" w:rsidRPr="00171EAD" w:rsidRDefault="00171EAD" w:rsidP="0080279F">
						<w:pPr>
							<w:rPr>
								<w:i/>
								<w:color w:val="5C7F92" w:themeColor="background2"/>
								<w:sz w:val="16"/>
								<w:szCs w:val="16"/>
							</w:rPr>
						</w:pPr>
						<w:r w:rsidRPr="00171EAD">
							<w:rPr>
								<w:i/>
								<w:color w:val="5C7F92" w:themeColor="background2"/>
								<w:sz w:val="16"/>
								<w:szCs w:val="16"/>
							</w:rPr>
							<w:t>Compétences :</w:t>
						</w:r>
					</w:p>
				</w:tc>
				<w:tc>
					<w:tcPr>
						<w:tcW w:w="4111" w:type="dxa"/>
						<w:tcBorders>
							<w:top w:val="single" w:sz="2" w:space="0" w:color="A59D95" w:themeColor="accent3"/>
							<w:left w:val="nil"/>
							<w:bottom w:val="single" w:sz="12" w:space="0" w:color="5C7F92" w:themeColor="background2"/>
						</w:tcBorders>
					</w:tcPr>
					<w:p w:rsidR="00171EAD" w:rsidRPr="00171EAD" w:rsidRDefault="00171EAD" w:rsidP="0080279F">
						<w:pPr>
							<w:rPr>
								<w:color w:val="595959" w:themeColor="accent1"/>
								<w:sz w:val="20"/>
								<w:szCs w:val="20"/>
							</w:rPr>
						</w:pPr>
						<w:r>
							<w:rPr>
								<w:color w:val="{{ gris(boulot.couleur) }}"/>
								<w:sz w:val="20"/>
								<w:szCs w:val="20"/>
							</w:rPr>
							<w:t>{{ boulot.techno|", " }}</w:t>
						</w:r>
					</w:p>
				</w:tc>
			</w:tr>
			{% endfor %}
		</w:tbl>
		<w:p w:rsidR="00171EAD" w:rsidRPr="0080279F" w:rsidRDefault="00171EAD" w:rsidP="0080279F"/>
		<w:p w:rsidR="0080279F" w:rsidRDefault="0080279F">
			<w:pPr>
				<w:spacing w:after="200" w:line="276" w:lineRule="auto"/>
				<w:rPr>
					<w:rFonts w:ascii="Lucida Bright" w:hAnsi="Lucida Bright"/>
					<w:color w:val="5C7F92" w:themeColor="background2"/>
					<w:sz w:val="40"/>
					<w:szCs w:val="40"/>
				</w:rPr>
			</w:pPr>
			<w:r>
				<w:br w:type="page"/>
			</w:r>
		</w:p>
		<w:p w:rsidR="00974A7A" w:rsidRPr="00974A7A" w:rsidRDefault="00974A7A" w:rsidP="00B223F1">
			<w:pPr>
				<w:pStyle w:val="Titre1"/>
			</w:pPr>
			<w:r w:rsidRPr="00974A7A">
				<w:lastRenderedPageBreak/>
				<w:t>Expériences</w:t>
			</w:r>
		</w:p>
		{% for boulot in expérience.projet %}
		<w:p w:rsidR="00974A7A" w:rsidRPr="00974A7A" w:rsidRDefault="00974A7A" w:rsidP="00B223F1">
			<w:pPr>
				<w:pStyle w:val="Titre2"/>
			</w:pPr>
			<w:r w:rsidRPr="00974A7A">
				<w:lastRenderedPageBreak/>
				<w:t>{{ [ boulot.société|last, boulot.nom ]|" / " }}</w:t>
			</w:r>
		</w:p>
		<w:p w:rsidR="00974A7A" w:rsidRPr="00B223F1" w:rsidRDefault="00974A7A" w:rsidP="00B223F1">
			<w:pPr>
				<w:jc w:val="right"/>
				<w:rPr>
					<w:i/>
					<w:color w:val="595959" w:themeColor="accent1"/>
					<w:sz w:val="18"/>
					<w:szCs w:val="18"/>
				</w:rPr>
			</w:pPr>
			<w:r w:rsidRPr="00B223F1">
				<w:rPr>
					<w:i/>
					<w:color w:val="595959" w:themeColor="accent1"/>
					<w:sz w:val="18"/>
					<w:szCs w:val="18"/>
				</w:rPr>
				<w:t>{{ durée(boulot)" / "année(boulot) }}</w:t>
			</w:r>
		</w:p>
		{% if boulot.rôle %}
		<w:p w:rsidR="00974A7A" w:rsidRPr="00B223F1" w:rsidRDefault="00B223F1" w:rsidP="00B223F1">
			<w:pPr>
				<w:spacing w:before="120" w:after="120"/>
				<w:ind w:left="3260" w:hanging="3260"/>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
			<w:r>
				<w:rPr>
					<w:b/>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t>Domaine de compétences :</w:t>
			</w:r>
			<w:r>
				<w:rPr>
					<w:b/>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:tab/>
			</w:r>
			<w:r w:rsidR="00974A7A" w:rsidRPr="00B223F1">
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t>{{ boulot.rôle|", " }}</w:t>
			</w:r>
		</w:p>
		{% endif %}
		<w:p w:rsidR="00974A7A" w:rsidRPr="00B223F1" w:rsidRDefault="00B223F1" w:rsidP="00B223F1">
			<w:pPr>
				<w:spacing w:before="120" w:after="120"/>
				<w:ind w:left="3260" w:hanging="3260"/>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
			<w:r>
				<w:rPr>
					<w:b/>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t>Intitulé de l’intervention</w:t>
			</w:r>
			<w:r>
				<w:rPr>
					<w:b/>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:tab/>
			</w:r>
			<w:r w:rsidR="00974A7A" w:rsidRPr="00B223F1">
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t>{{ boulot.description }}</w:t>
			</w:r>
		</w:p>
		<!-- Pas d'objectif chez moi
		<w:p w:rsidR="00974A7A" w:rsidRPr="00974A7A" w:rsidRDefault="00974A7A" w:rsidP="00B223F1">
			<w:pPr>
				<w:pStyle w:val="Titre3"/>
			</w:pPr>
			<w:r w:rsidRPr="00974A7A">
				<w:t>Objectif(s) :</w:t>
			</w:r>
		</w:p>
		<w:p w:rsidR="00974A7A" w:rsidRPr="00B223F1" w:rsidRDefault="00974A7A" w:rsidP="00B223F1">
			<w:pPr>
				<w:pStyle w:val="Paragraphedeliste"/>
				<w:numPr>
					<w:ilvl w:val="0"/>
					<w:numId w:val="4"/>
				</w:numPr>
				<w:ind w:left="1134" w:hanging="283"/>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
					<w:sz w:val="20"/>
					<w:szCs w:val="20"/>
				</w:rPr>
			</w:pPr>
			<w:r w:rsidRPr="00B223F1">
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
					<w:sz w:val="20"/>
					<w:szCs w:val="20"/>
				</w:rPr>
				<w:t>Objectif 1</w:t>
			</w:r>
		</w:p>
		<w:p w:rsidR="00974A7A" w:rsidRPr="00B223F1" w:rsidRDefault="00974A7A" w:rsidP="00B223F1">
			<w:pPr>
				<w:pStyle w:val="Paragraphedeliste"/>
				<w:numPr>
					<w:ilvl w:val="0"/>
					<w:numId w:val="4"/>
				</w:numPr>
				<w:ind w:left="1134" w:hanging="283"/>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
					<w:sz w:val="20"/>
					<w:szCs w:val="20"/>
				</w:rPr>
			</w:pPr>
			<w:r w:rsidRPr="00B223F1">
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
					<w:sz w:val="20"/>
					<w:szCs w:val="20"/>
				</w:rPr>
				<w:t>Objectif xxx</w:t>
			</w:r>
		</w:p>
		<w:p w:rsidR="00974A7A" w:rsidRPr="00B223F1" w:rsidRDefault="00974A7A" w:rsidP="00B223F1">
			<w:pPr>
				<w:pStyle w:val="Paragraphedeliste"/>
				<w:numPr>
					<w:ilvl w:val="0"/>
					<w:numId w:val="4"/>
				</w:numPr>
				<w:ind w:left="1134" w:hanging="283"/>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
					<w:sz w:val="20"/>
					<w:szCs w:val="20"/>
				</w:rPr>
			</w:pPr>
			<w:r w:rsidRPr="00B223F1">
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
					<w:sz w:val="20"/>
					<w:szCs w:val="20"/>
				</w:rPr>
				<w:t>réaliser une soufflerie, standardiser SATCOM, assurer la conformité de modifications de câblage, …</w:t>
			</w:r>
		</w:p>
		-->
		<w:p w:rsidR="00974A7A" w:rsidRPr="00974A7A" w:rsidRDefault="00974A7A" w:rsidP="00B223F1">
			<w:pPr>
				<w:pStyle w:val="Titre3"/>
			</w:pPr>
			<w:r w:rsidRPr="00974A7A">
				<w:t>Réalisation(s) :</w:t>
			</w:r>
		</w:p>
		{% for tâche in boulot.tâche %}
		<w:p w:rsidR="00974A7A" w:rsidRPr="00B223F1" w:rsidRDefault="00974A7A" w:rsidP="00B223F1">
			<w:pPr>
				<w:pStyle w:val="Paragraphedeliste"/>
				<w:numPr>
					<w:ilvl w:val="0"/>
					<w:numId w:val="4"/>
				</w:numPr>
				<w:ind w:left="1134" w:hanging="283"/>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
					<w:sz w:val="20"/>
					<w:szCs w:val="20"/>
				</w:rPr>
			</w:pPr>
			<w:r w:rsidRPr="00B223F1">
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
					<w:sz w:val="20"/>
					<w:szCs w:val="20"/>
				</w:rPr>
				<w:t xml:space="preserve">{{ tâche }}</w:t>
			</w:r>
		</w:p>
		{% endfor %}
		<w:p w:rsidR="00974A7A" w:rsidRPr="00974A7A" w:rsidRDefault="00974A7A" w:rsidP="00B223F1">
			<w:pPr>
				<w:pStyle w:val="Titre3"/>
			</w:pPr>
			<w:r w:rsidRPr="00974A7A">
				<w:t>Environnement(s) technique(s) :</w:t>
			</w:r>
		</w:p>
		{% for techno in boulot.techno %}
		<w:p w:rsidR="00974A7A" w:rsidRPr="00B223F1" w:rsidRDefault="00974A7A" w:rsidP="00B223F1">
			<w:pPr>
				<w:pStyle w:val="Paragraphedeliste"/>
				<w:numPr>
					<w:ilvl w:val="0"/>
					<w:numId w:val="4"/>
				</w:numPr>
				<w:ind w:left="1134" w:hanging="283"/>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
					<w:sz w:val="20"/>
					<w:szCs w:val="20"/>
				</w:rPr>
			</w:pPr>
			<w:r w:rsidRPr="00B223F1">
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
					<w:sz w:val="20"/>
					<w:szCs w:val="20"/>
				</w:rPr>
				<w:t>{{ techno }}</w:t>
			</w:r>
		</w:p>
		{% endfor %}
		<w:p w:rsidR="00DA1D17" w:rsidRDefault="00DA1D17">
			<w:pPr>
				<w:rPr>
					<w:rFonts w:ascii="Lucida Bright" w:hAnsi="Lucida Bright"/>
					<w:color w:val="5C7F92" w:themeColor="background2"/>
					<w:sz w:val="40"/>
					<w:szCs w:val="40"/>
				</w:rPr>
			</w:pPr>
			<w:r>
				<w:br w:type="page"/>
			</w:r>
		</w:p>
		{% endfor %}
		<w:p w:rsidR="00974A7A" w:rsidRPr="00974A7A" w:rsidRDefault="00B223F1" w:rsidP="00B223F1">
			<w:pPr>
				<w:pStyle w:val="Titre1"/>
			</w:pPr>
			<w:r>
				<w:lastRenderedPageBreak/>
				<w:t>Formations</w:t>
			</w:r>
		</w:p>
		{% for diplôme in formation.études %}
		<w:p w:rsidR="00DA1D17" w:rsidRDefault="00DA1D17" w:rsidP="00DA1D17">
			<w:pPr>
				<w:pStyle w:val="Paragraphedeliste"/>
				<w:numPr>
					<w:ilvl w:val="0"/>
					<w:numId w:val="2"/>
				</w:numPr>
				<w:tabs>
					<w:tab w:val="left" w:pos="2410"/>
				</w:tabs>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
			<w:r w:rsidRPr="00DA1D17">
				<w:rPr>
					<w:b/>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t>{{ diplôme.date.f|date }}</w:t>
			</w:r>
			<w:r w:rsidRPr="00974A7A">
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:tab/>
				<w:t>{{ diplôme.diplôme }}</w:t>
			</w:r>
		</w:p>
		<w:p w:rsidR="00DA1D17" w:rsidRPr="00DA1D17" w:rsidRDefault="00DA1D17" w:rsidP="00DA1D17">
			<w:pPr>
				<w:pStyle w:val="Paragraphedeliste"/>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
		</w:p>
		{% endfor %}
		<w:p w:rsidR="00974A7A" w:rsidRPr="00974A7A" w:rsidRDefault="00974A7A" w:rsidP="00974A7A">
			<w:pPr>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
		</w:p>
		<w:p w:rsidR="00974A7A" w:rsidRPr="00974A7A" w:rsidRDefault="00974A7A" w:rsidP="00974A7A">
			<w:pPr>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
		</w:p>
		<w:p w:rsidR="00974A7A" w:rsidRPr="00974A7A" w:rsidRDefault="00974A7A" w:rsidP="00974A7A">
			<w:pPr>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
		</w:p>
		<w:p w:rsidR="00974A7A" w:rsidRPr="00974A7A" w:rsidRDefault="00B223F1" w:rsidP="00B223F1">
			<w:pPr>
				<w:pStyle w:val="Titre1"/>
			</w:pPr>
			<w:r>
				<w:t>Langues étrangères</w:t>
			</w:r>
		</w:p>
		{% for langue in langues.langue %}
		{% if langue.nom != "Français" %}
		<w:p w:rsidR="00DA1D17" w:rsidRPr="00DA1D17" w:rsidRDefault="00974A7A" w:rsidP="00DA1D17">
			<w:pPr>
				<w:pStyle w:val="Paragraphedeliste"/>
				<w:numPr>
					<w:ilvl w:val="0"/>
					<w:numId w:val="2"/>
				</w:numPr>
				<w:tabs>
					<w:tab w:val="left" w:pos="2410"/>
				</w:tabs>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
			<w:r w:rsidRPr="00DA1D17">
				<w:rPr>
					<w:b/>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t>{{ langue.nom|cap }}</w:t>
			</w:r>
			<w:r w:rsidRPr="00DA1D17">
				<w:rPr>
					<w:b/>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:tab/>
			</w:r>
			<w:r w:rsidR="00DA1D17" w:rsidRPr="00DA1D17">
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t>Niveau</w:t>
			</w:r>
			<w:r w:rsidR="00DA1D17">
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
				<w:t> : {{ langue.niveau|minus }}</w:t>
			</w:r>
		</w:p>
		<w:p w:rsidR="00DA1D17" w:rsidRDefault="00DA1D17" w:rsidP="00DA1D17">
			<w:pPr>
				<w:pStyle w:val="Paragraphedeliste"/>
				<w:tabs>
					<w:tab w:val="left" w:pos="2410"/>
				</w:tabs>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
				</w:rPr>
			</w:pPr>
		</w:p>
		{% endif %}
		{% endfor %}
		<w:p w:rsidR="00CE5B4A" w:rsidRPr="00C51EA6" w:rsidRDefault="00CE5B4A">
			<w:pPr>
				<w:spacing w:after="200" w:line="276" w:lineRule="auto"/>
				<w:rPr>
					<w:b/>
					<w:i/>
					<w:color w:val="8B8178" w:themeColor="accent2"/>
				</w:rPr>
			</w:pPr>
			<w:r w:rsidRPr="00C51EA6">
				<w:rPr>
					<w:b/>
					<w:i/>
					<w:color w:val="8B8178" w:themeColor="accent2"/>
				</w:rPr>
				<w:br w:type="page"/>
			</w:r>
		</w:p>
		<w:bookmarkStart w:id="0" w:name="_GoBack"/>
		<w:bookmarkEnd w:id="0"/>
		<w:p w:rsidR="00DB6ECB" w:rsidRPr="00DB6ECB" w:rsidRDefault="0082727B" w:rsidP="00DA1D17">
			<w:pPr>
				<w:pStyle w:val="Paragraphedeliste"/>
				<w:tabs>
					<w:tab w:val="left" w:pos="2410"/>
				</w:tabs>
				<w:rPr>
					<w:color w:val="595959" w:themeColor="accent1"/>
					<w:lang w:val="en-US"/>
				</w:rPr>
			</w:pPr>
			<w:r>
				<w:rPr>
					<w:noProof/>
					<w:color w:val="3095B4" w:themeColor="text2"/>
					<w:lang w:eastAsia="fr-FR"/>
				</w:rPr>
				<w:lastRenderedPageBreak/>
				<mc:AlternateContent>
					<mc:Choice Requires="wps">
						<w:drawing>
							<wp:anchor distT="0" distB="0" distL="114300" distR="114300" simplePos="0" relativeHeight="251672576" behindDoc="0" locked="0" layoutInCell="1" allowOverlap="1">
								<wp:simplePos x="0" y="0"/>
								<wp:positionH relativeFrom="column">
									<wp:posOffset>-185420</wp:posOffset>
								</wp:positionH>
								<wp:positionV relativeFrom="paragraph">
									<wp:posOffset>5394325</wp:posOffset>
								</wp:positionV>
								<wp:extent cx="7162800" cy="2077720"/>
								<wp:effectExtent l="21590" t="17145" r="16510" b="19685"/>
								<wp:wrapNone/>
								<wp:docPr id="1" name="AutoShape 14"/>
								<wp:cNvGraphicFramePr>
									<a:graphicFrameLocks xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"/>
								</wp:cNvGraphicFramePr>
								<a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">
									<a:graphicData uri="http://schemas.microsoft.com/office/word/2010/wordprocessingShape">
										<wps:wsp>
											<wps:cNvSpPr>
												<a:spLocks noChangeArrowheads="1"/>
											</wps:cNvSpPr>
											<wps:spPr bwMode="auto">
												<a:xfrm>
													<a:off x="0" y="0"/>
													<a:ext cx="7162800" cy="2077720"/>
												</a:xfrm>
												<a:prstGeom prst="roundRect">
													<a:avLst>
														<a:gd name="adj" fmla="val 16667"/>
													</a:avLst>
												</a:prstGeom>
												<a:solidFill>
													<a:srgbClr val="FFFFFF"/>
												</a:solidFill>
												<a:ln w="28575">
													<a:solidFill>
														<a:schemeClr val="tx2">
															<a:lumMod val="100000"/>
															<a:lumOff val="0"/>
														</a:schemeClr>
													</a:solidFill>
													<a:round/>
													<a:headEnd/>
													<a:tailEnd/>
												</a:ln>
											</wps:spPr>
											<wps:bodyPr rot="0" vert="horz" wrap="square" lIns="91440" tIns="45720" rIns="91440" bIns="45720" anchor="t" anchorCtr="0" upright="1">
												<a:noAutofit/>
											</wps:bodyPr>
										</wps:wsp>
									</a:graphicData>
								</a:graphic>
								<wp14:sizeRelH relativeFrom="page">
									<wp14:pctWidth>0</wp14:pctWidth>
								</wp14:sizeRelH>
								<wp14:sizeRelV relativeFrom="page">
									<wp14:pctHeight>0</wp14:pctHeight>
								</wp14:sizeRelV>
							</wp:anchor>
						</w:drawing>
					</mc:Choice>
					<mc:Fallback>
						<w:pict>
							<v:roundrect id="AutoShape 14" o:spid="_x0000_s1026" style="position:absolute;margin-left:-14.6pt;margin-top:424.75pt;width:564pt;height:163.6pt;z-index:251672576;visibility:visible;mso-wrap-style:square;mso-width-percent:0;mso-height-percent:0;mso-wrap-distance-left:9pt;mso-wrap-distance-top:0;mso-wrap-distance-right:9pt;mso-wrap-distance-bottom:0;mso-position-horizontal:absolute;mso-position-horizontal-relative:text;mso-position-vertical:absolute;mso-position-vertical-relative:text;mso-width-percent:0;mso-height-percent:0;mso-width-relative:page;mso-height-relative:page;v-text-anchor:top" arcsize="10923f" o:gfxdata="UEsDBBQABgAIAAAAIQC2gziS/gAAAOEBAAATAAAAW0NvbnRlbnRfVHlwZXNdLnhtbJSRQU7DMBBF&#xA;90jcwfIWJU67QAgl6YK0S0CoHGBkTxKLZGx5TGhvj5O2G0SRWNoz/78nu9wcxkFMGNg6quQqL6RA&#xA;0s5Y6ir5vt9lD1JwBDIwOMJKHpHlpr69KfdHjyxSmriSfYz+USnWPY7AufNIadK6MEJMx9ApD/oD&#xA;OlTrorhX2lFEilmcO2RdNtjC5xDF9pCuTyYBB5bi6bQ4syoJ3g9WQ0ymaiLzg5KdCXlKLjvcW893&#xA;SUOqXwnz5DrgnHtJTxOsQfEKIT7DmDSUCaxw7Rqn8787ZsmRM9e2VmPeBN4uqYvTtW7jvijg9N/y&#xA;JsXecLq0q+WD6m8AAAD//wMAUEsDBBQABgAIAAAAIQA4/SH/1gAAAJQBAAALAAAAX3JlbHMvLnJl&#xA;bHOkkMFqwzAMhu+DvYPRfXGawxijTi+j0GvpHsDYimMaW0Yy2fr2M4PBMnrbUb/Q94l/f/hMi1qR&#xA;JVI2sOt6UJgd+ZiDgffL8ekFlFSbvV0oo4EbChzGx4f9GRdb25HMsYhqlCwG5lrLq9biZkxWOiqY&#xA;22YiTra2kYMu1l1tQD30/bPm3wwYN0x18gb45AdQl1tp5j/sFB2T0FQ7R0nTNEV3j6o9feQzro1i&#xA;OWA14Fm+Q8a1a8+Bvu/d/dMb2JY5uiPbhG/ktn4cqGU/er3pcvwCAAD//wMAUEsDBBQABgAIAAAA&#xA;IQBLwMyFTQIAAJoEAAAOAAAAZHJzL2Uyb0RvYy54bWysVF9v0zAQf0fiO1h+Z0mqrh3R0mnaGEIa&#xA;MDH4AK7tNAbHNme36fj0O1/S0sEbIg/Wne/ud39+vlxe7XvLdhqi8a7h1VnJmXbSK+M2Df/29e7N&#xA;BWcxCaeE9U43/ElHfrV6/epyCLWe+c5bpYEhiIv1EBrepRTqooiy072IZz5oh8bWQy8SqrApFIgB&#xA;0XtbzMpyUQweVAAvdYx4ezsa+Yrw21bL9Llto07MNhxrS3QCnet8FqtLUW9AhM7IqQzxD1X0wjhM&#xA;eoS6FUmwLZi/oHojwUffpjPp+8K3rZGaesBuqvKPbh47ETT1gsOJ4Tim+P9g5afdAzCjkDvOnOiR&#xA;outt8pSZVfM8nyHEGt0ewwPkDmO49/JHZM7fdMJt9DWAHzotFFZVZf/iRUBWIoay9fDRK4QXCE+j&#xA;2rfQZ0AcAtsTI09HRvQ+MYmXy2oxuyiROIm2WblcLmfEWSHqQ3iAmN5r37MsNBz81qkvyDvlELv7&#xA;mIgXNXUn1HfO2t4iyzthWbVYLJZUtagnZ8Q+YFK/3hp1Z6wlBTbrGwsMQxt+R98UHE/drGMD1ntx&#xA;vjynMl4Y6W3rI0raz8jHbnsc0IhclfnLyKLGe3zC4/2h9yMEThsZOU1N/VNg5uSdUyQnYewoo791&#xA;E0mZl5HftVdPyBH4cUFwoVHoPPzibMDlaHj8uRWgObMfHPL8tprP8zaRMj/PpDA4taxPLcJJhGp4&#xA;4mwUb9K4gdsAZtNhpoom4Hx+eq1Jh0c0VjUViwtA3U7LmjfsVCev37+U1TMAAAD//wMAUEsDBBQA&#xA;BgAIAAAAIQDE1oPv4wAAAA0BAAAPAAAAZHJzL2Rvd25yZXYueG1sTI/bSsNAEIbvBd9hGcG7dtPU&#xA;NgezKSKIiFjsAa+n2WkSzB7Ibtv17d1e6d0M8/HP91eroAZ2ptH1RguYTRNgpBsje90K2O9eJjkw&#xA;51FLHIwmAT/kYFXf3lRYSnPRGzpvfctiiHYlCui8tyXnrulIoZsaSzrejmZU6OM6tlyOeInhauBp&#xA;kiy5wl7HDx1aeu6o+d6elIBNWHx8zS2+ZvPw1tjj+zp8tmsh7u/C0yMwT8H/wXDVj+pQR6eDOWnp&#xA;2CBgkhZpRAXkD8UC2JVIijy2OcRpli0z4HXF/7eofwEAAP//AwBQSwECLQAUAAYACAAAACEAtoM4&#xA;kv4AAADhAQAAEwAAAAAAAAAAAAAAAAAAAAAAW0NvbnRlbnRfVHlwZXNdLnhtbFBLAQItABQABgAI&#xA;AAAAIQA4/SH/1gAAAJQBAAALAAAAAAAAAAAAAAAAAC8BAABfcmVscy8ucmVsc1BLAQItABQABgAI&#xA;AAAAIQBLwMyFTQIAAJoEAAAOAAAAAAAAAAAAAAAAAC4CAABkcnMvZTJvRG9jLnhtbFBLAQItABQA&#xA;BgAIAAAAIQDE1oPv4wAAAA0BAAAPAAAAAAAAAAAAAAAAAKcEAABkcnMvZG93bnJldi54bWxQSwUG&#xA;AAAAAAQABADzAAAAtwUAAAAA&#xA;" strokecolor="#3095b4 [3215]" strokeweight="2.25pt"/>
						</w:pict>
					</mc:Fallback>
				</mc:AlternateContent>
			</w:r>
		</w:p>
		<w:sectPr w:rsidR="00DB6ECB" w:rsidRPr="00DB6ECB" w:rsidSect="002C39A4">
			<w:headerReference w:type="default" r:id="rId11"/>
			<w:footerReference w:type="default" r:id="rId12"/>
			<w:pgSz w:w="11906" w:h="16838"/>
			<w:pgMar w:top="1417" w:right="707" w:bottom="709" w:left="851" w:header="708" w:footer="356" w:gutter="0"/>
			<w:cols w:space="708"/>
			<w:docGrid w:linePitch="360"/>
		</w:sectPr>
	</w:body>
</w:document>
