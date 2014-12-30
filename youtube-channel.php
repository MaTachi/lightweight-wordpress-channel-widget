<?php
/*
Plugin Name: Lightweight YouTube Channel Widget
Plugin URI: https://github.com/MaTachi/lightweight-wordpress-channel-widget
Description: <a href="widgets.php">Widget</a> that displays video thumbnails
from a YouTube channel or playlist.
Author: Daniel Jonsson
Version: 10.0
Author URI: https://github.com/MaTachi/lightweight-wordpress-channel-widget
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('WPAU_YOUTUBE_CHANNEL') )
{
	class WPAU_YOUTUBE_CHANNEL
	{

		public $plugin_slug    = "youtube-channel";
		private $plugin_version = "10.0";
		private $plugin_option  = "youtube_channel_defaults";
		private $channel_id     = "urkekg";
		private $playlist_id    = "PLEC850BE962234400";

		/**
		 * Constructor
		 */
		function __construct() {
			load_plugin_textdomain(
				$this->plugin_slug,
				false,
				dirname( plugin_basename( __FILE__ ) ) . '/languages'
			);

			// Installation and uninstallation hooks
			register_activation_hook( __FILE__, array( $this, 'activate' ) );

			// Load widget definition
			require_once( 'inc/widget.php' );

			add_action(
				'wp_enqueue_scripts',
				array( $this, 'enqueue_scripts')
			);

		} // end __construct

		/**
		 * Activate the plugin
		 */
		public static function activate() {
		} // end function activate

		/**
		 * Load scripts
		 */
		function enqueue_scripts() {
			wp_enqueue_style(
				'youtube-channel',
				plugins_url( 'assets/css/youtube-channel.min.css', __FILE__ ),
				array(),
				$this->plugin_version
			);
		} // end function enqueue_scripts

		/**
		 * Print out widget
		 */
		public function output( $instance ) {

			// set default channel if nothing predefined
			$channel = $instance['channel'];
			if ( $channel == "" ) $channel = $this->channel_id;

			// set playlist id
			$playlist = $instance['playlist'];
			if ( $playlist == "" ) $playlist = $this->playlist_id;

			// trim PL in front of playlist ID
			$playlist = preg_replace('/^PL/', '', $playlist);
			$use_res = $instance['use_res'];

			$class = $instance['class'] ? $instance['class'] : 'default';

			if ( !empty($instance['responsive']) ) $class .= ' responsive';

			$output = array();

			$output[] = '<div class="youtube_channel '.$class.'">';




			// get max items for random video
			$maxrnd = $instance['maxrnd'];
			if ( $maxrnd < 1 ) { $maxrnd = 10; } // default 10
			elseif ( $maxrnd > 50 ) { $maxrnd = 50; } // max 50

			$feed_attr = '?alt=json';
			// select fields
			$feed_attr .= "&fields=entry(published,title,link,content)";

			if ( !$instance['fixnoitem'] && $use_res != 1 )
				$feed_attr .= '&orderby=published';

			$getrnd = $instance['getrnd'];
			if ( $getrnd ) $feed_attr .= '&max-results='.$maxrnd;

			$feed_attr .= '&rel=0';
			switch ($use_res) {
				case 1: // favorites
					$feed_url = 'http://gdata.youtube.com/feeds/base/users/'.$channel.'/favorites'.$feed_attr;
					break;
				case 2: // playlist
					$playlist = $this->clean_playlist_id($playlist);
					$feed_url = 'http://gdata.youtube.com/feeds/api/playlists/'.$playlist.$feed_attr;
					break;
				default:
					$feed_url = 'http://gdata.youtube.com/feeds/base/users/'.$channel.'/uploads'.$feed_attr;
			}

			// do we need cache?
			if ($instance['cache_time'] > 0 ) {
				// generate feed cache key for caching time
				$cache_key = 'ytc_'.md5($feed_url).'_'.$instance['cache_time'];

				if (!empty($_GET['ytc_force_recache']))
					delete_transient($cache_key);

				// get/set transient cache
				if ( false === ($json = get_transient($cache_key)) ) {
					// no cached JSON, get new
					$wprga = array(
						'timeout' => 2 // two seconds only
					);
					$response = wp_remote_get($feed_url, $wprga);
					$json = wp_remote_retrieve_body( $response );

					// set decoded JSON to transient cache_key
					set_transient($cache_key, base64_encode($json), $instance['cache_time']);
				} else {
					// we already have cached feed JSON, get it encoded
					$json = base64_decode($json);
				}
			} else {
				// just get fresh feed if cache disabled
				$wprga = array(
					'timeout' => 2 // two seconds only
				);
				$response = wp_remote_get($feed_url, $wprga);
				$json = wp_remote_retrieve_body( $response );
			}

			// decode JSON data
			$json_output = json_decode($json);

			// predefine maxitems to prevent undefined notices
			$maxitems = 0;
			if ( !is_wp_error($json_output) && is_object($json_output) && !empty($json_output->feed->entry) ) {
				// sort by date uploaded
				$json_entry = $json_output->feed->entry;

				$vidqty = $instance['vidqty'];
				if ( $vidqty > $maxrnd ) { $maxrnd = $vidqty; }
				$maxitems = ( $maxrnd > sizeof($json_entry) ) ? sizeof($json_entry) : $maxrnd;

				if ( $getrnd ) {
					$items =  array_slice($json_entry,0,$maxitems);
				} else {
					if ( !$vidqty ) $vidqty = 1;
					$items =  array_slice($json_entry,0,$vidqty);
				}
			}

			if ($maxitems == 0) {
				$output[] = __("No items", $this->plugin_slug).' [<a href="'.$feed_url.'" target="_blank">'.__("Check here why",$this->plugin_slug).'</a>]';
			} else {

				if ( $getrnd ) $rnd_used = array(); // set array for unique random item

				for ($y = 1; $y <= $vidqty; $y++) {
					if ( $getrnd ) {
						$rnd_item = mt_rand(0, (count($items)-1));
						while ( $y > 1 && in_array($rnd_item, $rnd_used) ) {
							$rnd_item = mt_rand(0, (count($items)-1));
						}
						$rnd_used[] = $rnd_item;
						$item = $items[$rnd_item];
					} else {
						$item = $items[$y-1];
					}

					// print single video block
					$output = array_merge( $output, $this->ytc_print_video($item, $instance, $y) );
				}

			}



			$output[] = '</div><!-- .youtube_channel -->';

			return $output;
		} // end function ouptup

		// --- HELPER FUNCTIONS ---

		// function to calculate height by width and ratio
		function height_ratio($width=306, $ratio) {
			switch ($ratio)
			{
				case 1:
					$height = round(($width / 4 ) * 3);
					break;
				case 2:
					$height = round(($width / 16 ) * 10);
					break;
				case 3:
				default:
					$height = round(($width / 16 ) * 9);
			}
			return $height;
		} // end function height_ratio

		/* function to print video block */
		function ytc_print_video($item, $instance, $y) {

			$class = $instance['class'];

			// set width and height
			$width  = ( empty($instance['width']) ) ? 306 : $instance['width'];
			$height = $this->height_ratio($width, $instance['ratio']);

			// calculate image height based on width for 4:3 thumbnail
			$imgfixedheight = $width / 4 * 3;

			$yt_id     = $item->link[0]->href;
			$yt_id     = preg_replace('/^.*=(.*)&.*$/', '${1}', $yt_id);
			$yt_url    = "v/$yt_id";

			$yt_thumb  = "//img.youtube.com/vi/$yt_id/0.jpg"; // zero for HD thumb
			$yt_video  = $item->link[0]->href;
			$yt_video  = preg_replace('/\&.*$/','',$yt_video);

			$yt_title  = $item->title->{'$t'};
			$yt_date   = $item->published->{'$t'};
			//$yt_date = $item->get_date('j F Y | g:i a');

			switch ($y) {
				case 1:
					$vnumclass = 'first';
					break;
				case $instance['vidqty']:
					$vnumclass = 'last';
					break;
				default:
					$vnumclass = 'mid';
					break;
			}

			$output[] = '<div class="ytc_video_container ytc_video_'.$y.' ytc_video_'.$vnumclass.'" style="width:'.$width.'px">';

			// show video title?
			if ( $instance['showtitle'] )
				$output[] = '<h3 class="ytc_title">'.$yt_title.'</h3>';

			// define object ID
			$ytc_vid = 'ytc_' . $yt_id;


			// which type to show
			$to_show = 'thumbnail';

			// print out video
			if ( $to_show == "thumbnail" ) {
				// set proper class for responsive thumbs per selected aspect ratio
				switch ($instance['ratio'])
				{
					case 1: $arclass = 'ar4_3'; break;
					case 2: $arclass = 'ar16_10'; break;
					default: $arclass = 'ar16_9';
				}
				$title = sprintf( __('Watch video %1$s published on %2$s', 'youtube-channel' ), $yt_title, $yt_date );
				$output[] = '<a href="'.$yt_video.'" title="'.$yt_title.'" class="ytc_thumb ytc-lightbox '.$arclass.'"><span style="background-image: url('.$yt_thumb.');" title="'.$title.'" id="'.$ytc_vid.'"></span></a>';
			}

			// do we need to show video description?
			if ( $instance['showvidesc'] ) {

				preg_match('/><span>(.*)<\/span><\/div>/', $item->content->{'$t'}, $videsc);
				if ( empty($videsc[1]) ) {
					$videsc[1] = $item->content->{'$t'};
				}

				// clean HTML
				$nohtml = explode("</div>",$videsc[1]);
				if ( sizeof($nohtml) > 1 ) {
					$videsc[1] = strip_tags($nohtml[2]);
					unset($nohtml);
				} else {
					$videsc[1] = strip_tags($videsc[1]);
				}

				if ( $instance['videsclen'] > 0 ) {
					if ( strlen($videsc[1]) > $instance['videsclen'] ) {
						$video_description = substr($videsc[1], 0, $instance['videsclen']);
						if ( $instance['descappend'] ) {
							$etcetera = $instance['descappend'];
						} else {
							$etcetera = '&hellip;';
						}
					}
				} else {
					$video_description = $videsc[1];
					$etcetera = '';
				}
				if (!empty($video_description))
					$output[] = '<p class="ytc_description">' .$video_description.$etcetera. '</p>';
			}
			$output[] = '</div><!-- .ytc_video_container -->';

			return $output;
		} // end function ytc_print_video

		// Helper function cache_time()
		function cache_time($cache_time)
		{
			$times = array(
				'minute' => array(
					1  => __("1 minute", 'youtube-channel'),
					5  => __("5 minutes", 'youtube-channel'),
					15 => __("15 minutes", 'youtube-channel'),
					30 => __("30 minutes", 'youtube-channel')
				),
				'hour' => array(
					1  => __("1 hour", 'youtube-channel'),
					2  => __("2 hours", 'youtube-channel'),
					5  => __("5 hours", 'youtube-channel'),
					10 => __("10 hours", 'youtube-channel'),
					12 => __("12 hours", 'youtube-channel'),
					18 => __("18 hours", 'youtube-channel')
				),
				'day' => array(
					1 => __("1 day", 'youtube-channel'),
					2 => __("2 days", 'youtube-channel'),
					3 => __("3 days", 'youtube-channel'),
					4 => __("4 days", 'youtube-channel'),
					5 => __("5 days", 'youtube-channel'),
					6 => __("6 days", 'youtube-channel')
				),
				'week' => array(
					1 => __("1 week", 'youtube-channel'),
					2 => __("2 weeks", 'youtube-channel'),
					3 => __("3 weeks", 'youtube-channel'),
					4 => __("1 month", 'youtube-channel')
				)
			);

			$out = "";
			foreach ($times as $period => $timeset)
			{
				switch ($period)
				{
					case 'minute':
						$sc = MINUTE_IN_SECONDS;
						break;
					case 'hour':
						$sc = HOUR_IN_SECONDS;
						break;
					case 'day':
						$sc = DAY_IN_SECONDS;
						break;
					case 'week':
						$sc = WEEK_IN_SECONDS;
						break;
				}

				foreach ($timeset as $n => $s)
				{
					$sec = $sc * $n;
					$out .='<option value="'.$sec.'" '. selected( $cache_time, $sec, 0 ).'>'.__($s, $this->plugin_slug).'</option>';
					unset($sec);
				}
			}
			return $out;
		} // end function cache_time

		function youtube_domain($instance) {
			$youtube_domain = ( !empty($instance['enhprivacy']) ) ? 'www.youtube-nocookie.com' : 'www.youtube.com';
			return $youtube_domain;
		} // end function youtube_domain

		function clean_playlist_id($playlist) {
			if ( substr($playlist,0,4) == "http" ) {
				// if URL provided, extract playlist ID
				$playlist = preg_replace('/.*list=PL([A-Za-z0-9\-\_]*).*/','$1', $playlist);
			} else if ( substr($playlist,0,2) == 'PL' ) {
				$playlist = substr($playlist,2);
			}
			return $playlist;
		} // end function clean_playlist_id

	} // end class
} // end class check

global $WPAU_YOUTUBE_CHANNEL;
$WPAU_YOUTUBE_CHANNEL = new WPAU_YOUTUBE_CHANNEL();
