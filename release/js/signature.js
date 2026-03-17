function getOffset( el ) {
	var _x = 0;
	var _y = 0;
	var browser = navigator.userAgent;
	
	if ( 1 || browser.toLowerCase().indexOf('chromium') > 0 ) {
		// chrome, but *should* work on everything
		rec=el.getBoundingClientRect();
		_y=_y+self.pageYOffset+rec.top;
		_x=_x+self.pageXOffset+rec.left;
	} else	if ( browser.toLowerCase().indexOf('safari') > 0 ) {
		// safari
		_y=_y+self.pageYOffset;
		_x=_x+self.pageXOffset;
	} else {
		// everyone else
		while( el && !isNaN( el.offsetLeft ) && !isNaN( el.offsetTop ) ) {
			_x += el.offsetLeft - el.scrollLeft;
			_y += el.offsetTop - el.scrollTop;
			el = el.offsetParent;
		}
	}

	return { y: _y, x: _x };
}

function initialize_signature(id) {
         // get references to the canvas element as well as the 2D drawing context
  	 var sigCanvas = document.getElementById(id);
         var context = sigCanvas.getContext("2d");
///         context.strokeStyle = '#006';
 
         // This will be defined on a TOUCH device such as iPad or Android, etc.
         var is_touch_device = 'ontouchstart' in document.documentElement;
 
	var count=0;

         if (is_touch_device) {
     	 // TOUCH INPUT	
	    // create a drawer which tracks touch movements
            var drawer = {
               isDrawing: false,
               touchstart: function (coors) {
                  event.preventDefault();
                  context.beginPath();
                  context.moveTo(coors.x, coors.y);
                  this.isDrawing = true;
               },
               touchmove: function (coors) {
                  event.preventDefault();
                  if (this.isDrawing) {
			  count=count+1;

			  if(count>10) {
                     context.stroke();
				count=0;
			  }
                     context.lineTo(coors.x, coors.y);
		     canvas_status(id, true);
                  }
               },
               touchend: function (coors) {
                     //context.stroke();
                  event.preventDefault();
                  if (this.isDrawing) {
                     this.touchmove(coors);
                     this.isDrawing = false;
                  }
               }
            };
 
            // create a function to pass touch events and coordinates to drawer
            function draw(event) {
		if(this.disabled)
			return false;

               // get the touch coordinates.  Using the first touch in case of multi-touch
               var coors = {
                  x: event.targetTouches[0].pageX,
                  y: event.targetTouches[0].pageY
               };
              
		
		//coors.y=coors.y_y-
		
		if(navigator.userAgent.toLowerCase().indexOf('opera') >= 0) {		// opera
			coors.y=coors.y-self.pageYOffset;
		}

	
	       var obj = sigCanvas;
 
               if (obj.offsetParent) {
                  do {
                     coors.x -= obj.offsetLeft;
                     coors.y -= obj.offsetTop;
                  }
                  while ((obj = obj.offsetParent) != null);
               }
               // pass the coordinates to the appropriate handler
               drawer[event.type](coors);
            }

            // attach the touchstart, touchmove, touchend event listeners.
            sigCanvas.addEventListener('touchstart', draw, false);
            sigCanvas.addEventListener('touchmove', draw, false);
            sigCanvas.addEventListener('touchend', draw, false);
 
            // prevent elastic scrolling
            sigCanvas.addEventListener('touchmove', function (event) {
               //event.preventDefault();
            }, false); 
         } else {
	 	// MOUSE INPUT	
		var drawer2 = {
                isDrawing: false,
		mouseIsDown: false,

		mouseout: function(coors, context) {
                    if (navigator.userAgent.indexOf("Firefox")==-1)
                        event.preventDefault();
                    if(!this.mouseIsDown) {
                    	this.isDrawing = false;
		    }
		},
                mousedown: function(coors,context){
                    if (navigator.userAgent.indexOf("Firefox")==-1)
                        event.preventDefault();
		    context.beginPath();
                    context.moveTo(coors.x, coors.y);
                    this.isDrawing = true;
                    this.mouseIsDown = true;
                },
                mousemove: function(coors,context){
                    if (navigator.userAgent.indexOf("Firefox")==-1)
                        event.preventDefault();
                    if (this.isDrawing && this.mouseIsDown) {
			//alert(coors.x+"  "+coors.y);	
			context.lineTo(coors.x, coors.y);
                        context.stroke();
			canvas_status(id, true);
                    }
                },
                mouseup: function(coors,context){
                    if (navigator.userAgent.indexOf("Firefox")==-1)
                        event.preventDefault();
                    this.isDrawing = false;
                    this.mouseIsDown = false;
                }
            };

	    function draw2(event,obj){
		if(this.disabled)
			return false;
		
                var x, y;
                if (event.offsetX !== undefined) { // Modern browsers (Chrome, Firefox, Safari, Edge)
                        x = event.offsetX;
                        y = event.offsetY;
                } else if (event.layerX || event.layerX == 0) { // Legacy fallback
                        var offset = getOffset(sigCanvas);
                        x = event.layerX - offset.x;
                        y = event.layerY - offset.y;
                } else {
                        return;
                }

                var coors = { x: x, y: y };
                drawer2[event.type](coors, context);
          }

            sigCanvas.addEventListener('mousedown',draw2, false);
            sigCanvas.addEventListener('mousemove',draw2, false);
            sigCanvas.addEventListener('mouseup',draw2, false);
            sigCanvas.addEventListener('mouseout',draw2, false);

            // annoying bug fix when using a mouse and signing out of the scope
            document.body.addEventListener('mouseup',draw2, false);
         }
}

function clearCanvas(canvas_id)
{
	canvas=document.getElementById(canvas_id);
	if(canvas.disabled)
		return false;
	
	var ctx = canvas.getContext('2d');

	// clear
	ctx.clearRect (0, 0, canvas.width, canvas.height);

	// big white box
	ctx.fillStyle = "#FFF";
	ctx.fillRect(0, 0, canvas.width, canvas.height);

	// little line to 'draw on'
	//ctx.fillStyle = "#ccc";
	//ctx.fillRect(0+20, 120, canvas.width-40, 1);

	// dashed line is cooler
	ctx.fillStyle = "#ccc";
	var length=20;
	var padding=20;
	var gap=5;
	for(x=padding; x<canvas.width-padding; x++) {
		ctx.fillRect(x, 120, length, 1);
		x=x+length+gap;
	}

	// draw a cute X
	ctx.fillStyle = "#ff7676";
	ctx.font = "40px sans-serif";
	ctx.fillText("x", 20, 118);

	// stroke color
	ctx.strokeStyle = '#1c0093';
	
	// make sure we know its blank
	canvas_status(canvas_id, false);

	ctx.lineWidth = 2; /*Math.ceil(parseInt(0, 10))*/
	ctx.lineCap = ctx.lineJoin = "round";
}

function draw_init() {

	var all=document.getElementsByTagName('canvas');

	for(i=0; i<all.length; i++) {
		// get the canvas element and its context
		//var canvas = document.getElementById(all[i]);

		if(all[i].className == "signature") {
			initialize_signature(all[i].id);
			
			var canvas = all[i];
			canvas.width=472;
			canvas.height=150;
			clearCanvas(canvas.id)
			var context = canvas.getContext('2d');
		}
	}
}

// ── typeToCanvas: renders typed text into a signature canvas ──────────────
function typeToCanvas(text, canvasId) {
    var c = document.getElementById(canvasId);
    if (!c) return;
    var ctx = c.getContext('2d');
    ctx.clearRect(0, 0, c.width, c.height);
    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, c.width, c.height);
    if (!text) {
        canvas_status(canvasId, false);
        return;
    }
    // Baseline rule
    ctx.strokeStyle = '#bbb';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(20, c.height - 25);
    ctx.lineTo(c.width - 20, c.height - 25);
    ctx.stroke();
    // Cursive-style name
    ctx.fillStyle = '#222';
    ctx.font = 'italic 26px Georgia, "Times New Roman", serif';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(text, c.width / 2, c.height / 2);
    canvas_status(canvasId, true);
}