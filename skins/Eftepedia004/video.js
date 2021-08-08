// Replace IFrames by links that open the video in a popup or a new window.
$(function(){
	$videos = $('iframe.video-player.youtube-player');
	$videos.each(function() {
		var $iframe = $(this)

		// Get relevant properties of the video.
		var video = this.src.split('?')[0].split('/').pop(); // Video code is last part of url
		var width = this.width;
		var height = this.height;
		var parent = this.parentNode;
		var playing = false;
		var loaded = false;
		
		var isNoAutoPlayBrowser = function()
		{
			// Some browsers don't support YouTube auto-play api.
			var ua = navigator.userAgent.toLowerCase();
			var isAndroid = ua.indexOf("android") > -1;
			
			return isAndroid;
		}
		
		// Function for updating the playing state of the iframe player.
		var updatePlay = function() {
			if (!loaded) return;
			var contentWindow = $iframe[0].contentWindow;
			var func = playing ? 'playVideo' : 'pauseVideo';
			contentWindow.postMessage('{"event":"command","func":"' + func + '","args":""}', '*');
		}
		
		// Function to play and pause the video when the lightbox is opened and closed.
		var playVideo = function(play)
		{
			playing = play;
			updatePlay();
		};
		
		// Start playing the video as soon as possible if someone opened a video before it was loaded.
		$iframe.load(function(){
			loaded = true;
			playing && updatePlay();
		});
		
		// Create a new placeholder for the video.
		var $placeholder = $(document.createElement('a'))
			.prependTo($(parent))
			.attr('href', this.src)
			.addClass('video youtube')
			.css({
				'width': width + 'px',
				'height': height + 'px',
				'display': 'inline-block',
				'background-image': 'url(https://i1.ytimg.com/vi/' + video + '/hqdefault.jpg)',
				'background-size': 'cover',
				'position': 'relative'
			})
			.on('click', function(event) {
				if (isNoAutoPlayBrowser()) 
					return;
				event.preventDefault();
				$lightbox.css('display', 'block');
				playVideo(true);
			});
			

		// Create a popup container for the video
		var $lightbox  = $(document.createElement('div'))
			.appendTo($('body'))
			.addClass('video-lightbox')
			.css({
				'top': '0',
				'left': '0', 
				'display': 'none',
				'position': 'fixed',
				'width': '100%',
				'height': '100%'
			})
			.on('click', function(){
				playVideo(false);
				$lightbox.css('display', 'none');
			});

		// Create a play button
		var $button = $(document.createElement('span'))
			.appendTo($placeholder)
			.addClass('video-play-button')
			.css({
				'left': ((width - 60) / 2).toString() + 'px',
				'top': ((height - 40) / 2).toString() + 'px'
			})
			//.html('\u25B6') // Webdings arrow has size issues on mobile.
			;
		$arrow = $(document.createElement('span'))
			.appendTo($button)
			.addClass('video-play-button-icon');

		// Move the video to the lightbox and position it.
		var ratio = (height / width);
		var scale = 0.9; // Possible to do this in CSS?
		
		// Use a separate wrapper element. Full screen mode doesn't work right if this is
		// applied to the iframe directly.
		var $videowrapper = $(document.createElement('div'))
			.css({
				'margin': 'auto', 
				'position': 'absolute',
				'top': '0', 'left': '0', 'right': '0', 'bottom': '0',
				'width': (100 * scale).toString() + 'vw',
				'height': (100 * scale * ratio).toString() + 'vw',
				'max-height': (100 * scale).toString() + 'vh',
				'max-width': (100 * scale / ratio).toString() + 'vh',
				'box-shadow': '10px 10px 5px rgba(0,0,0,0.7)'
			})
			.appendTo($lightbox);
		
		// Enable the Javascript api, so the video can be paused.
		$iframe.attr('src', $iframe.attr('src') + '?enablejsapi=1');

		// Move the iframe to the wrapper
		$iframe
			.removeAttr('width')
			.removeAttr('height')
			.css({
				'width': '100%',
				'height': '100%'
			})
			.appendTo($videowrapper);
	});
});
