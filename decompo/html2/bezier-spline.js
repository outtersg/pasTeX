/* bezier-spline.js
 *
 * computes cubic bezier coefficients to generate a smooth
 * line through specified points. couples with SVG graphics 
 * for interactive processing.
 *
 * For more info see:
 * http://www.particleincell.com/2012/bezier-splines/ 
 *
 * Lubos Brieda, Particle In Cell Consulting LLC, 2012
 * you may freely use this algorithm in your codes however where feasible
 * please include a link/reference to the source article
 */ 

var PiloteBezier =
{
/*creates formated path string for SVG cubic path element*/
	path: function(x1,y1,px1,py1,px2,py2,x2,y2)
{
	return "M "+x1+" "+y1+" C "+px1+" "+py1+" "+px2+" "+py2+" "+x2+" "+y2;
	},

/*computes control points given knots K, this is the brain of the operation*/
	computeControlPoints: function(K)
{
	p1=new Array();
	p2=new Array();
	n = K.length-1;
	
	/*rhs vector*/
	a=new Array();
	b=new Array();
	c=new Array();
	r=new Array();
	
	/*left most segment*/
	a[0]=0;
	b[0]=2;
	c[0]=1;
	r[0] = K[0]+2*K[1];
	
	/*internal segments*/
	for (i = 1; i < n - 1; i++)
	{
		a[i]=1;
		b[i]=4;
		c[i]=1;
		r[i] = 4 * K[i] + 2 * K[i+1];
	}
			
	/*right segment*/
	a[n-1]=2;
	b[n-1]=7;
	c[n-1]=0;
	r[n-1] = 8*K[n-1]+K[n];
	
	/*solves Ax=b with the Thomas algorithm (from Wikipedia)*/
	for (i = 1; i < n; i++)
	{
		m = a[i]/b[i-1];
		b[i] = b[i] - m * c[i - 1];
		r[i] = r[i] - m*r[i-1];
	}
 
	p1[n-1] = r[n-1]/b[n-1];
	for (i = n - 2; i >= 0; --i)
		p1[i] = (r[i] - c[i] * p1[i+1]) / b[i];
		
	/*we have p1, now compute p2*/
	for (i=0;i<n-1;i++)
		p2[i]=2*K[i+1]-p1[i+1];
	
	p2[n-1]=0.5*(K[n]+p1[n-1]);
	
	return {p1:p1, p2:p2};
	},
	
	calculerCourbe: function(etapes)
	{
		var i;
		
		/* Gestion des cas particuliers. */
		
		switch(etapes.length)
		{
			case 0:
			case 1:
				return [];
			case 2:
				var r = [ etapes[0], etapes[1], etapes[0], etapes[1] ];
				return r;
		}
		
		/* Gros du boulot. */
		
		var tx, ty, dy;
		var p;
		var pc = [];
		
		for(i = 0; ++i < etapes.length - 1;)
		{
			p = etapes[i];
			
			// La tangente en un point doit être parallèle au segment qui joint les deux points l'entourant (pour aller de A à C via B, on veut qu'à son passage par B la courbe soit parallèle à (AB)).
			
			ty = etapes[i + 1].y - etapes[i - 1].y;
			tx = etapes[i + 1].x - etapes[i - 1].x;
			
			// Les points de contrôle partent du point de passage selon cette tangente, sur une hauteur de 0,5 * le dy entre le point et son voisin. Comme on a une présentation verticale (deux points successifs ne peuvent être à la même ordonnée), on est sûrs de n'avoir pas de division par 0.
			
			dy = Math.min(p.y - etapes[i - 1].y, etapes[i + 1].y - p.y);
			
			//dy = p.y - etapes[i - 1].y;
			pc.push
			(
				{
					x: p.x - (tx / ty) * (dy * 0.5),
					y: p.y - (dy * 0.5)
				}
			);
			
			pc.push(p);
			
			//dy = etapes[i + 1].y - p.y;
			pc.push
			(
				{
					x: p.x + (tx / ty) * (dy * 0.5),
					y: p.y + (dy * 0.5)
				}
			);
		}
		
		// Pour les extrémités, on reprend les points de contrôle de leur successeur / prédécesseur.
		
		pc.unshift(pc[0]);
		pc.unshift(etapes[0]);
		
		pc.push(pc[pc.length - 1]);
		pc.push(etapes[etapes.length - 1]);
		
		return pc;
	},
	
	/**
	 * Descente style bougie (descente rapide, puis ralentissement pour bifurquer vers le point suivant, puis redescente rapide en arrivant dessus).
	 */
	calculerBougie: function(etapes)
	{
		var pc = [];
		var i;
		var p;
		
		for(i = 0; i < etapes.length; ++i)
		{
			p = etapes[i];
			
			if(i > 0)
				pc.push({ x: p.x, y: (p.y * 2 + etapes[i - 1].y * 1) / 3 });
			
			pc.push(p);
			
			if(i < etapes.length - 1)
				pc.push({ x: p.x, y: (p.y * 2 + etapes[i + 1].y * 1) / 3 });
		}
		
		return pc;
	},
	
	/**
	 * Descente avec pas mal de verticalités.
	 */
	calculerCascade: function(etapes)
	{
		var pc = [];
		var i;
		var p;
		
		for(i = 0; i < etapes.length; ++i)
		{
			p = etapes[i];
			
			if(i > 0)
			{
				pc.push({ x: p.x, y: (p.y * 2 + etapes[i - 1].y * 1) / 3 });
				pc.push({ x: p.x, y: (p.y * 2 + etapes[i - 1].y * 1) / 3 });
				pc.push({ x: p.x, y: (p.y * 7 + etapes[i - 1].y * 2) / 9 });
			}
			
			pc.push({ x: p.x, y: p.y, ligne: 1 });
			
			if(i < etapes.length - 1)
			{
				pc.push({ x: p.x, y: (p.y * 7 + etapes[i + 1].y * 2) / 9, ligne: 1 });
				pc.push({ x: p.x, y: (p.y * 2 + etapes[i + 1].y * 1) / 3 });
				pc.push({ x: p.x, y: (p.y * 2 + etapes[i + 1].y * 1) / 3 });
				pc.push({ x: (p.x + etapes[i + 1].x) / 2, y: (p.y + etapes[i + 1].y) / 2 });
			}
		}
		
		return pc;
	},

	courber: function(svg, etapes, couleur)
	{
		var ensvg = "http://www.w3.org/2000/svg";
		var courbe;
		
		// Via mon code.
		if(1)
		{
			var pc = PiloteBezier.calculerCascade(etapes);
			if(pc.length > 0)
			{
				i = 0;
				d = 'M '+pc[i].x+','+pc[i].y;
				for(++i; i < pc.length; i += 3)
				{
					if(pc[i].ligne)
					{
						d += ' L '+pc[i].x+','+pc[i].y;
						i -= 2;
					}
					else
					d += ' C '+pc[i].x+','+pc[i].y+' '+pc[i + 1].x+','+pc[i + 1].y+' '+pc[i + 2].x+','+pc[i + 2].y;
				}
			}
			else
				d = '';
		}
		// Via bezier-spline.js.
		else
		{
		var x = [];
		var y = [];
		var i, j;
		var d;
		
		for(i = 0; i < etapes.length; ++i)
		{
			x.push(etapes[i].x);
			y.push(etapes[i].y);
		}
		
		px = PiloteBezier.computeControlPoints(x);
		py = PiloteBezier.computeControlPoints(y);
			
			d = '';
			for(i = 0; i < etapes.length - 1; ++i)
				d += ' '+PiloteBezier.path(x[i],y[i],px.p1[i],py.p1[i],px.p2[i],py.p2[i],x[i+1],y[i+1]);
		}
		
		courbe = document.createElementNS(ensvg, 'path');
		courbe.setAttributeNS(null, 'fill', 'none');
		courbe.setAttributeNS(null, 'stroke', 'rgba('+couleur+')');
		courbe.setAttributeNS(null, 'stroke-width', 5);
		courbe.setAttributeNS(null, 'd', d);
		svg.appendChild(courbe);
	}
};
