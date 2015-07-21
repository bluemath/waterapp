
var GRABABLE_MASK_BIT = 1<<31;
var NOT_GRABABLE_MASK = ~GRABABLE_MASK_BIT;

cp.PolyShape.prototype.draw = function(point2canvas) {
	return;
};

cp.SegmentShape.prototype.draw = function(point2canvas) {
	return;
};

cp.CircleShape.prototype.draw = function(flipPoint) {
	if(this.paper) { // Paper isn't set up until the image is loaded
		
		this.paper.position = flipPoint(this.tc);
	}
};

//console.clear();
var debugging = true;

function debug(message) {
	debugging && console.log(message);
}

var DotUI = function(canvas) {

	// Useful for anonymous functions / callbacks
	var that = this;


	/* !Init */
	
	// paper
	this.canvas = canvas;
	paper.install(window);
	paper.setup(this.canvas);
	paper.view.viewSize = [window.innerWidth, window.innerHeight];
	
	// chipmunk
	this.space = new cp.Space();
	this.space.iterations = 50;
	this.space.gravity = cp.v(0, 0);
	this.space.sleepTimeThreshold = 0.5;
	this.space.collisionSlop = 0.7;
	
	/* !Properties */
	this._dots = [];
	this._bubbles = [];
	this.pointers = {};
	
	this._popDot = function() {
		dot = that._bubbles.shift();
		dot.shape.body.setPos(that.offscreenRandom());
		that._bubbles.push(dot);
	}
	
	/* !Events */
	
	// Window resize
	paper.view.onResize = function(event) {
		debug("onResize");
		that._refreshDots();
	}

	// Step Simulation
	paper.view.onFrame = function (event) {
		that.space.step(event.delta);
		
		// Iterate through pointers and update shape positions 
		for (var id in that.pointers) {
			that.pointers[id].shape.body.setPos(that.flipPoint(that.pointers[id].position));
			that.pointers[id].shape.body.setVel(cp.v(0,0));
		}
		
		// Only redraw if the simulation isn't asleep.
		if (that.space.activeShapes.count > 0) {
			that.space.eachShape(function(shape) {
				shape.draw(that.flipPoint);
			});
		}
		
		//console.log(event.time);
	};
	
	/* !Methods */

	this.flipPoint = function(point) {
		return new Point(point.x, paper.view.viewSize.height - point.y);
	};
	
	this.addBubble = function(size) {
		
		var dot = new Dot(size, null);
		
		this._bubbles.push(dot);
		that._scaleDot(dot);
		
		var position = paper.view.center;
		
		dot.circle = new Path.Circle(position, dot.radius);
		dot.circle.fillColor = '#2d7baa';
		dot.position = this.offscreenRandom();
		
		dot.poppable = true;
		
		that._showDot(dot, 50, 600);

	}
	
	// Add a dot to the UI
	this.addDot = function(dot) {
		// Save the dot
		that._dots.push(dot);
		that._scaleDot(dot);
		var position = paper.view.center;
		dot.position = paper.view.center;
		
		dot.poppable = false;
		
		if (dot.imageURL != null) {
			image = new Raster(dot.imageURL, position);
		    image.onLoad = function() {
			    this.setSize(new Size(dot.radius*2, dot.radius*2));
			    var circle = new Path.Circle(position, dot.radius);
				var circleClipped = new Group(circle, this);
				circleClipped.clipped = true;
				dot.circle = circleClipped;
				that._showDot(dot, 400, 400);
		    }
		} else {
			dot.circle = new Path.Circle(position, dot.radius);
			dot.circle.fillColor = '#42a4c4';
			that._showDot(dot, 1000, 400);
		}

	}
	
	this._showDot = function(dot, spring, damp) {
		    
		var position = paper.view.center;
		
	    var label = this.label = new PointText({
		    point: position,
		    content: dot.text,
		    fillColor: 'white',
		    fontFamily: 'reykjavikone',
		    fontSize: Math.floor(dot.radius / 5),
			fontWeight: 'bold',
			justification: 'center',
			shadowColor: new Color(.2,.2,.2),
			shadowBlur: 3,
			shadowOffset: new Point(1,1)
		});
	    
	    group = new Group(dot.circle, label);
						    
	    body = that.space.addBody(new cp.Body(10, cp.momentForCircle(20, 0, dot.radius, cp.v(0,0))));
		body.setPos(that.flipPoint(dot.position));
	
		shape = that.space.addShape(new cp.CircleShape(body, dot.radius + 5, cp.v(0, 10)));
		shape.setElasticity(.5);
		shape.setFriction(1);
		shape.paper = group;
		shape.poppable = dot.poppable;
		
		// Joint to anchor
		shape.anchorPoint = that.space.staticBody;
		shape.anchorPoint.p = that.flipPoint(position);
		shape.anchorJoint = new cp.DampedSpring(shape.anchorPoint, body, cp.v(0, 0), cp.v(0,0), 0, spring, damp);
		that.space.addConstraint(shape.anchorJoint);
		
		dot.shape = shape;
    }
	
	// Scale a dot based on a width
	this._scaleDot = function(dot) {
		// Update the radius
		var newRadius = paper.view.viewSize.width * dot.scale / 2;
		if (dot.shape) {
			dot.shape.r = newRadius;
			if(dot.shape.paper) {
				dot.shape.paper.scale(newRadius / dot.radius);		
			}
			
		}
		dot.radius = newRadius;
	}
	
	// Refresh all dots in the UI
	this._refreshDots = function() {
		debug("Refreshing Dots");
		for (var dotIndex in that._dots) {
			that._scaleDot(that._dots[dotIndex]);
		}
	}
	
	canvas.addEventListener('pointerdown', function(event) {

		// Create and assign a body to the input
		var input = that.flipPoint({x: event.clientX, y: event.clientY});
		var shape = that.space.pointQueryFirst(input, GRABABLE_MASK_BIT, cp.NO_GROUP);
		
		// Only track finger moves if a shape was touched
		if(shape){
			// Remember pointer
			that.pointers[event.pointerId] = {
				position: new Point(event.clientX, event.clientY),
				type: event.pointerType,
				id: event.pointerId,
				shape: shape
			}
		}
	});
	
	canvas.addEventListener('pointermove', function(event) {
		var pointer = that.pointers[event.pointerId];
		if(pointer) {
			pointer.position = new Point(event.clientX, event.clientY);
		}
	});
	
	document.addEventListener('pointerup', function(event) {

		var pointer = that.pointers[event.pointerId];
		if(pointer) {
			pointer.shape.body.setVel(cp.v(0,0));
			if(pointer.shape.poppable) {
				pointer.shape.body.setPos(that.offscreenRandom());
			}
			delete that.pointers[event.pointerId];
		}
	});
	
	canvas.addEventListener('pointercancel', function(event) {
		// Do the same as pointerup?
	});
	
	this.offscreenRandom = function() {
		var w = paper.view.size.width;
		var h = paper.view.size.height;
		
		var x = Math.round(Math.random()) * (2 * w) - Math.random() * w;
		var y = Math.round(Math.random()) * (2 * h) - Math.random() * h;
		
		return cp.v(x, y);
	}
	
	for (var i = 0; i < 200; i++) {
		var size = Math.random() * .07 + .03;
		this.addBubble(size);
	}
	
	setInterval(this._popDot, 1000);

};


var Dot = function(scale, imageURL, text) {

	var that = this;
	
	// Save
	this.scale = scale;
	this.imageURL = imageURL;
	this.text = text;
	
	// Defaults
	this.radius = 0;
	
}

// Taken from http://davidwalsh.name/fullscreen
// Use this to make fullscreen:
// launchIntoFullscreen(document.documentElement);
function launchIntoFullscreen(element) {
  if(element.requestFullscreen) {
    element.requestFullscreen();
  } else if(element.mozRequestFullScreen) {
    element.mozRequestFullScreen();
  } else if(element.webkitRequestFullscreen) {
    element.webkitRequestFullscreen();
  } else if(element.msRequestFullscreen) {
    element.msRequestFullscreen();
  }
}