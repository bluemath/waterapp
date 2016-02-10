
var GRABABLE_MASK_BIT = 1<<31;
var NOT_GRABABLE_MASK = ~GRABABLE_MASK_BIT;

cp.PolyShape.prototype.draw = function(point2canvas) {
	return;
};

cp.SegmentShape.prototype.draw = function(point2canvas) {
	return;
};

cp.CircleShape.prototype.draw = function(flipPoint, awayFromMiddle) {
	if(this.paperCircle) { // Paper isn't set up until the image is loaded
		
		this.paperCircle.position = flipPoint(this.tc);

	}
	if(this.paperTag) { // Paper isn't set up until the image is loaded
		
		this.paperTag.position = awayFromMiddle(flipPoint(this.tc));

	}
			
};

//console.clear();
var debugging = true;

function debug(message) {
	debugging && console.log(message);
}

var Splash = function(container) {

	// Useful for anonymous functions / callbacks
	var that = this;
	
	this.paused = false;
	
	// paper
	this.$container = container;
	this.canvas = container.find("canvas")[0];
	paper.install(window);
	paper.setup(this.canvas);
	//paper.view.viewSize = [window.innerWidth, window.innerHeight];
	paper.view.viewSize = [container.width(), container.height()];
	
	// chipmunk
	this.space = new cp.Space();
	this.space.iterations = 50;
	this.space.gravity = cp.v(0, 0);
	this.space.sleepTimeThreshold = 0.5;
	this.space.collisionSlop = 0.7;
	this.space.sleepTimeThreshold = 0.5;

	this._bubbles = [];
	this.pointers = {};
	
	this.hide = function() {
		that.$container.fadeOut(400, function() {
			that.paused = true;
		});
	}
	
	this.show = function() {
		that.paused = false;
		that.$container.fadeIn(400);
	}
	
	// Resize the canvas if the window size changes
	$(window).resize(function() {
		paper.view.viewSize = [container.width(), container.height()];
	});

	// Step Simulation
	paper.view.onFrame = function (event) {
		if(!that.paused) {
			that.space.step(1/30);
		
			// Iterate through pointers and update shape positions 
			for (var id in that.pointers) {
				that.pointers[id].shape.body.setPos(that.flipPoint(that.pointers[id].position));
				that.pointers[id].shape.body.setVel(cp.v(0,0));
						// Tag
				if (typeof shape.tag != 'undefined') {
					
				}
			}
			
			// Only redraw if the simulation isn't asleep.
			if (that.space.activeShapes.count > 0) {
				that.space.eachShape(function(shape) {
					shape.draw(that.flipPoint, that.awayFromMiddle);
				});
			}
		}
	};
	
	/* !Methods */

	this.flipPoint = function(point) {
		return new Point(point.x, paper.view.viewSize.height - point.y);
	};
	
	this.awayFromMiddle = function(point) {
		y = paper.view.viewSize.height/2;
		x = paper.view.viewSize.width/2;
		newx = point.x + (point.x - x)*.55;
		newy = point.y + (point.y - y)*.25;
		return new Point(newx, newy);
	};
	
	this.addBubble = function(scale) {
		var bubble = new Bubble(scale);
		that._bubbles.push(bubble);
		
		that._scale(bubble);
		bubble.position = this.offscreenRandom();
		bubble.poppable = true;
		
		bubble.circle = new Path.Circle(bubble.position, bubble.radius);
		bubble.circle.fillColor = '#6a94b0'; //#2d7baa';
		
		that._addWithPhysics(bubble, 50, 500);
	}
	
	this.addImageBubble = function(scale, imageURL, text, callback) {
		var bubble = new Bubble(scale);
		bubble.callback = callback;
		
		that._scale(bubble);
		bubble.position = paper.view.center;
		bubble.poppable = false;
		
		image = new Raster(imageURL, bubble.position);
		image.onLoad = function() {
		    this.setSize(new Size(bubble.radius*2, bubble.radius*2));
		    
		    var circle = new Path.Circle(bubble.position, bubble.radius);
			var circleClipped = new Group(circle, this);
			circleClipped.clipped = true;
			

			var label = this.label = new PointText({
			    point: bubble.position,
			    content: text,
			    fillColor: 'white',
			    fontFamily: 'reykjavikone',
			    fontSize: 50,
				fontWeight: 'normal',
				justification: 'center',
/*
				shadowColor: new Color(.2,.2,.2),
				shadowBlur: 3,
				shadowOffset: new Point(1,1)
*/
			});
			
			var labelback = new Path.Rectangle({
					point: bubble.position,
					rectangle: label.bounds.expand(60,10),
					fillColor: new Color(67/255,109/255,134/255,.80),//'#436d86',
/*
					shadowColor: new Color(0,0,0,.5),
					shadowBlur: 5,
					shadowOffset: new Point(1,1)
*/
				});
	    
			bubble.circle = circleClipped;
			bubble.tag = new Group(labelback, label);
			
			that._addWithPhysics(bubble, 400, 400);
		}
	}
	
	this._addWithPhysics = function(bubble, spring, damp) {
		
		body = that.space.addBody(new cp.Body(10, cp.momentForCircle(20, 0, bubble.radius, cp.v(0,0))));
		body.setPos(that.flipPoint(bubble.position));
	
		shape = that.space.addShape(new cp.CircleShape(body, bubble.radius + 5, cp.v(10, 10)));
		shape.setElasticity(.3);
		shape.setFriction(.8);
		shape.paperCircle = bubble.circle;
		shape.paperTag = bubble.tag;
		shape.poppable = bubble.poppable;
		shape.callback = bubble.callback;
		
		// Joint to anchor
		shape.anchorPoint = that.space.staticBody;
		shape.anchorPoint.p = that.flipPoint(paper.view.center);
		shape.anchorJoint = new cp.DampedSpring(shape.anchorPoint, body, cp.v(0, 0), cp.v(0,0), 0, spring, damp);
		that.space.addConstraint(shape.anchorJoint);
		
		bubble.shape = shape;
	}
	
	this.offscreenRandom = function() {
		var w = paper.view.size.width;
		var h = paper.view.size.height;
		
		var xtra = .5;
		var x = (Math.random() * (1 + 2 * xtra) * w) - (xtra * w);
		if(x < w && x > 0) {
			// Above and below
			var y = ( Math.round(Math.random()) * (1 + xtra) * h ) - ( Math.random() * xtra * h );
		} else {
			// Left and right
			var y = (Math.random() * (1 + 2 * xtra) * h) - (xtra * h);
		}
		
		
		return cp.v(x, y);
	}
	
	// Scale a sot based on a width
	this._scale = function(bubble) {
		// Update the radius
		var newRadius = paper.view.viewSize.width * bubble.scale / 2;
		if (bubble.shape) {
			bubble.shape.r = newRadius;
			if(bubble.shape.paperCircle) {
				bubble.shape.paperCircle.scale(newRadius / bubble.radius);		
			}
			
		}
		bubble.radius = newRadius;
	}
	
	////////////////////
	// Events
	////////////////////
	
	this.code = "";
	bubblecode = function(c) {
		that.code = that.code.concat(c);
		that.code = that.code.slice(-8);
		switch(that.code) {
			case 'pdpdppdd':
				if(document.location.href.indexOf("/app") > -1) {
					//document.location.href = '/';
				} else {
					//document.location.href = '/app';
				}
			break;
			default:
				// Ignore
			break;
		}
		debug(that.code);
	}
	
	select = function(event) {
		// Create and assign a body to the input
		var input = that.flipPoint({x: event.clientX, y: event.clientY});
		var shape = that.space.pointQueryFirst(input, GRABABLE_MASK_BIT, cp.NO_GROUP);
		
		// Only track finger moves if a shape was touched
		if(shape){
			
			if (shape.callback != undefined) {
				shape.callback();
				return;
			}
			
			// Remember pointer
			that.pointers[event.pointerId] = {
				start: new Point(event.clientX, event.clientY),
				position: new Point(event.clientX, event.clientY),
				type: event.pointerType,
				id: event.pointerId,
				shape: shape
			}
		}
	}
	
	move = function(event) {
		var pointer = that.pointers[event.pointerId];
		if(pointer) {
			pointer.position = new Point(event.clientX, event.clientY);
		}
	}
	
	this.distance = function(start, end) {
		var x = Math.abs(start.x - end.x);
		var y = Math.abs(start.y - end.y);
		return  Math.sqrt(x*x + y*y);
	}
	
	pop = function(event) {
		var pointer = that.pointers[event.pointerId];
		if(pointer) {
			var end = new Point(event.clientX, event.clientY);
			var distance = that.distance(pointer.start, end);
			if(pointer.shape.poppable && distance < 10) {
				pointer.shape.body.setPos(that.offscreenRandom());
				bubblecode('p');
			} else {
				bubblecode('d');
			}
			delete that.pointers[event.pointerId];
		}
	}
	
	canvas.addEventListener('pointerdown', select);
	canvas.addEventListener('pointermove', move);
	document.addEventListener('pointerup', pop);
	canvas.addEventListener('pointercancel', pop);
	
	
	///////////////////
	// Build scene
	///////////////////
	
	for (var i = 0; i <200; i++) {
		var size = Math.random() * .06 + .02;
		this.addBubble(size);
	}

	this.popDot = function() {
		dot = that._bubbles.shift();
		dot.shape.body.setPos(that.offscreenRandom());
		that._bubbles.push(dot);
		setTimeout(function() {
			that.popDot();
			}, 100 + Math.random()*1000);
	}
	
	this.popDot();

};

var Bubble = function(scale) {

	var that = this;
	
	// Save
	this.scale = scale;
	
	// Defaults
	this.radius = 0;
	
}