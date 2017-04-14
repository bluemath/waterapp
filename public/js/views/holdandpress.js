$.fn.pressAndHold = function(options) {

  var settings = $.extend({
    size: 300,
    thickness: 40,
    color: "#fff",
    duration: 500,
    callback: function() {}
  }, options)

  circle = $("<div>").css({
    "position": "absolute",
    "border-radius": "50%"
  });
  //circle.hide();
  this.append(circle);
  
  this.on("pointerdown", function(e) {
    circle.css({
      border: settings.thickness + "px solid " + settings.color,
      left: e.clientX - settings.thickness,
      top: e.clientY - settings.thickness,
      width: settings.size + "px",
      height: settings.size + "px",
      margin: -settings.size/2 + "px",
      opacity: .1,
    }).stop().animate({
      width: "0px",
      height: "0px",
      margin: 0,
      opacity: .5
    }, settings.duration, "swing", function(e) {
      circle.hide();
      settings.callback();
    }).show();
  });
  
  this.on("pointermove", function(e) {
    console.log();
    circle.css({
      left: e.clientX-settings.thickness,
      top: e.clientY-settings.thickness,
    });
  });

  this.on("pointerup", function() {
    circle.stop().hide();
  });

  return this;
  
}