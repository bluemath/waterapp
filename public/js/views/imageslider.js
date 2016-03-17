$.fn.imageSlider = function(options) {

  var settings = $.extend(true, {
    // Defaults
    left: {
      bg: "#222",
      label: "before"
    },
    right: {
      bg: "#CCC",
      label: "after"
    },
    percent: 50
  }, options)

  console.log(settings);
  
  this.empty().addClass("jqis");

  // For PEP compatability on iOS devices
  this.attr("touch-action", "none");

  images = $("<div>").addClass("jqisimages");

  right = $("<div>").addClass("jqisright");
  if (typeof settings.right.url != 'undefined') {
    right.css('background-image', "url(" + settings.right.url + ")")
  } else {
    right.css('background-color', settings.right.bg);
  }
  images.append(right);

  left = $("<div>").addClass("jqisleft");
  if (typeof settings.left.url != 'undefined') {
    left.css('background-image', "url(" + settings.left.url + ")")
  } else {
    left.css('background-color', settings.left.bg);  
  }
  images.append(left);

  labels = $("<div>").addClass("jqislabels");
  rightlabel = $("<div>").addClass("jqisright").html(settings.right.label);
  labels.append(rightlabel);
  leftlabel = $("<div>").addClass("jqisleft").html(settings.left.label);
  labels.append(leftlabel);

  this.append(images);
  this.append(labels);

  slider = this;

  moveSlider = function(x, y) {
    leftw = Math.min(Math.max(x, leftlabel.outerWidth(true)) + 10, slider.width() - rightlabel.outerWidth(true)) - 5;
    rightw = slider.width() - leftw;

    left.stop().width(leftw);
    leftlabel.stop().css("right", rightw).css("top", y - 70);
    rightlabel.stop().css("left", leftw).css("top", y - 70);
  }

  this.on("pointerdown pointermove", function(e) {
    moveSlider(e.clientX, e.clientY);
  });

  $(window).on("resize", function() {
    moveSlider(right.width() * settings.percent / 100, right.height() * 2 / 3);
  });

  $(window).trigger("resize");
  
  return this;
}