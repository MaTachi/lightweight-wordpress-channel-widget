jQuery(document).ready(function($) {
	$('.ytc-lightbox')
		.magnificPopupAU({
			disableOn: 700,
			type: 'iframe',
			mainClass: 'mfp-fade',
			removalDelay: 160,
			preloader: false,
			fixedContentPos: false
		});
	$(window).on('load', function() {
		$(".youtube_channel.responsive .ytc_video_container").fitVids();
	});
});
