/*
	jQuery Coda-Slider v2.0 - http://www.ndoherty.biz/coda-slider
	Copyright (c) 2009 Niall Doherty
	This plugin available for use in all personal or commercial projects under both MIT and GPL licenses.

  heavily simplified by manfred@monochrom.at 2010-07-19
*/


$.fn.codaSlider = function(settings) {

	settings = $.extend({
		autoHeightEaseDuration: 1000,
		autoHeightEaseFunction: "easeInOutExpo",
		autoSlide: true,
		autoSlideInterval: 7000,
		autoSlideStopWhenClicked: true,
		firstPanelToLoad: 1,
		slideEaseDuration: 1000,
		slideEaseFunction: "easeInOutExpo"
	}, settings);
	
	return this.each(function(){
		
		
		var slider = $(this);
		var panelWidth = slider.find(".panel").width();
		var panelCount = slider.find(".panel").size();
		var panelContainerWidth = panelWidth*panelCount;
		var navClicks = 0; // Used if autoSlideStopWhenClicked = true
		
		// Surround the collection of panel divs with a container div (wide enough for all panels to be lined up end-to-end)
		$('.panel', slider).wrapAll('<div class="panel-container"></div>');
		// Specify the width of the container div (wide enough for all panels to be lined up end-to-end)
		$(".panel-container", slider).css({ width: panelContainerWidth });
		
		var currentPanel = 1;
			
		// Left arrow click
		$(".coda-nav-left").click(function(){
			navClicks++;
			if (currentPanel == 1) {
				offset = - (panelWidth*(panelCount - 1));
				currentPanel = panelCount;
			} else {
				currentPanel -= 1;
				offset = - (panelWidth*(currentPanel - 1));
			};
			$('.panel-container', slider).animate({ marginLeft: offset }, settings.slideEaseDuration, settings.slideEaseFunction);
			$('.HomePanels .textContentBox ').hide();
			$('#panel-'+currentPanel).fadeIn(2000);
			return false;
		});
			
		// Right arrow click
		$('.coda-nav-right').click(function(){
			navClicks++;
			if (currentPanel == panelCount) {
				offset = 0;
				currentPanel = 1;
			} else {
				offset = - (panelWidth*currentPanel);
				currentPanel += 1;
			};
			$('.panel-container', slider).animate({ marginLeft: offset }, settings.slideEaseDuration, settings.slideEaseFunction);
			$('.HomePanels .textContentBox ').hide();
			$('#panel-'+currentPanel).fadeIn(2000);
			
			return false;
		});
		
		
		// Trigger autoSlide
		if (settings.autoSlide) {
			slider.ready(function() {
				setTimeout(autoSlide,settings.autoSlideInterval);
			});
		};
		
		function autoSlide() {
			if (navClicks == 0 || !settings.autoSlideStopWhenClicked) {
				if (currentPanel == panelCount) {
					var offset = 0;
					currentPanel = 1;
				} else {
					var offset = - (panelWidth*currentPanel);
					currentPanel += 1;
				};
				// Switch the current tab:
				slider.siblings('.coda-nav').find('a').removeClass('current').parents('ul').find('li:eq(' + (currentPanel - 1) + ') a').addClass('current');
				// Slide:
				$('.panel-container', slider).animate({ marginLeft: offset }, settings.slideEaseDuration, settings.slideEaseFunction);
				setTimeout(autoSlide,settings.autoSlideInterval);
			};
		};
		
		
	});
};