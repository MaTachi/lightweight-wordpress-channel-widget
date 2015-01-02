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
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'LYCW' ) ):
/**
 * Lightweight YouTube Channel Widget class.
 *
 * Contains all logic relevant to the plugin.
 */
class LYCW {

	public $plugin_slug          = "lightweight-youtube-channel-widget";
	private $plugin_version      = "10.0";
	private $default_channel_id  = "urkekg";
	private $default_playlist_id = "PLEC850BE962234400";

	function __construct() {
		// Load plugin translations.
		load_plugin_textdomain(
			$this->plugin_slug,
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);

		// Load widget definition.
		require_once( 'inc/widget.php' );

		add_action(
			'wp_enqueue_scripts',
			array( $this, 'enqueue_scripts' )
		);
	}

	/**
	 * Load scripts.
	 */
	function enqueue_scripts() {
		wp_enqueue_style(
			$this->plugin_slug,
			plugins_url( 'assets/css/youtube-channel.min.css', __FILE__ ),
			array(),
			$this->plugin_version
		);
	}

	/**
	 * Print list of videos.
	 */
	public function output( $instance ) {
		// Get channel name.
		if ( "" != $instance['channel'] ) {
			$channel = $instance['channel'];
		} else {
			$channel = $this->default_channel_id;
		}

		// Get playlist ID.
		if ( "" != $instance['playlist'] ) {
			$playlist = $instance['playlist'];
		} else {
			$playlist = $this->default_playlist_id;
		}

		// Trim PL in front of the playlist ID.
		$playlist = preg_replace( '/^PL/', '', $playlist );

		// The type of resource to display.
		$use_res = $instance['use_res'];

		// Get additional class names.
		$class = "" != $instance['class'] ? $instance['class'] : 'default';
		if ( !empty( $instance['responsive'] ) ) {
			$class .= ' responsive';
		}

		$output = array();

		$output[] = '<div class="youtube_channel '.$class.'">';




		// Get max items for random video.
		$maxrnd = $instance['maxrnd'];
		if ( $maxrnd < 1 ) {
			$maxrnd = 10; // default 10
		} elseif ( $maxrnd > 50 ) {
			$maxrnd = 50; // max 50
		}

		$feed_attr = '?alt=json';
		// Select fields.
		$feed_attr .= '&fields=entry(published,title,link,content)';

		if ( !$instance['fixnoitem'] && $use_res != 1 ) {
			$feed_attr .= '&orderby=published';
		}

		$getrnd = $instance['getrnd'];
		if ( $getrnd ) {
			$feed_attr .= '&max-results=' . $maxrnd;
		}

		$feed_attr .= '&rel=0';

		switch ($use_res) {
			case 0: // Channel
			default:
				$feed_url = 'http://gdata.youtube.com/feeds/base/users/' .
					$channel . '/uploads';
				break;
			case 1: // Favorites
				$feed_url = 'http://gdata.youtube.com/feeds/base/users/' .
					$channel . '/favorites';
				break;
			case 2: // Playlist
				$playlist = $this->clean_playlist_id($playlist);
				$feed_url = 'http://gdata.youtube.com/feeds/api/playlists/' .
					$playlist;
		}
		$feed_url .= $feed_attr;

		// Do we need cache?
		if ( $instance['cache_time'] > 0 ) {
			// Generate feed cache key for caching time
			$cache_key = 'lycw_' . md5( $feed_url ) . '_' .
				$instance['cache_time'];

			$json = get_transient( $cache_key );

			if ( false === $json ) {
				// No cached JSON, get new.
				$wprga = array(
					'timeout' => 2 // two seconds only
				);
				$response = wp_remote_get( $feed_url, $wprga );
				$json = wp_remote_retrieve_body( $response );

				// set decoded JSON to transient cache_key
				set_transient(
					$cache_key,
					base64_encode( $json ),
					$instance['cache_time']
				);
			} else {
				// we already have cached feed JSON, get it encoded
				$json = base64_decode( $json );
			}
		} else {
			// just get fresh feed if cache disabled
			$wprga = array(
				'timeout' => 2 // two seconds only
			);
			$response = wp_remote_get( $feed_url, $wprga );
			$json = wp_remote_retrieve_body( $response );
		}

		// Decode JSON data.
		$json_output = json_decode($json);

		// Predefine maxitems to prevent undefined notices.
		$maxitems = 0;
		if (
			!is_wp_error( $json_output ) &&
			is_object( $json_output ) &&
			!empty( $json_output->feed->entry )
		) {
			// Sort by date uploaded.
			$json_entry = $json_output->feed->entry;

			$vidqty = $instance['vidqty'];
			if ( $vidqty > $maxrnd ) {
				$maxrnd = $vidqty;
			}

			if ( $maxrnd > sizeof( $json_entry ) ) {
 				$maxitems = sizeof( $json_entry );
			} else {
				$maxitems = $maxrnd;
			}

			if ( $getrnd ) {
				$items = array_slice( $json_entry , 0 , $maxitems );
			} else {
				if ( !$vidqty ) {
					$vidqty = 1;
				}
				$items = array_slice( $json_entry , 0 , $vidqty );
			}
		}

		if ( $maxitems == 0 ) {
			$output[] = __( 'No items', $this->plugin_slug) .
				' [<a href="' .  $feed_url .  '" target="_blank">' .
				__( 'Check here why' , $this->plugin_slug ) . '</a>]';
		} else {
			if ( $getrnd ) {
				$rnd_used = array(); // set array for unique random item
			}

			for ( $y = 1; $y <= $vidqty; ++$y ) {
				if ( $getrnd ) {
					$rnd_item = mt_rand( 0, ( count( $items ) - 1 ) );
					while ( $y > 1 && in_array( $rnd_item, $rnd_used ) ) {
						$rnd_item = mt_rand( 0, ( count( $items ) - 1 ) );
					}
					$rnd_used[] = $rnd_item;
					$item = $items[ $rnd_item ];
				} else {
					$item = $items[ $y - 1 ];
				}

				// Print single video block.
				$output = array_merge(
					$output,
					$this->print_video( $item, $instance, $y )
				);
			}

		}



		$output[] = '</div><!-- .youtube_channel -->';

		return $output;
	}

	/**
	 * Calculate the height when there's a given width and width/height ratio.
	 */
	private function height_ratio( $width = 306, $ratio ) {
		switch ( $ratio ) {
			case 1:
				return round( ( $width / 4 ) * 3 );
			case 2:
				return round( ( $width / 16 ) * 10 );
			case 3:
			default:
				return round( ( $width / 16 ) * 9 );
		}
	}

	/**
	 * Print a single video block.
	 */
	private function print_video( $item, $instance, $y) {

		$class = $instance['class'];

		// Set width and height
		$width  = ( empty($instance['width']) ) ? 306 : $instance['width'];
		$height = $this->height_ratio( $width, $instance['ratio'] );

		// calculate image height based on width for 4:3 thumbnail
		$imgfixedheight = $width / 4 * 3;

		$yt_id    = $item->link[0]->href;
		$yt_id    = preg_replace( '/^.*=(.*)&.*$/', '${1}', $yt_id );
		$yt_url   = "v/$yt_id";

		$yt_thumb = "//img.youtube.com/vi/$yt_id/0.jpg"; // zero for HD thumb
		$yt_video = $item->link[0]->href;
		$yt_video = preg_replace('/\&.*$/','',$yt_video);

		$yt_title = $item->title->{'$t'};
		$yt_date  = $item->published->{'$t'};
		//$yt_date = $item->get_date('j F Y | g:i a');

		switch ( $y ) {
			case 1:
				$vnumclass = 'first';
				break;
			case $instance['vidqty']:
				$vnumclass = 'last';
				break;
			default:
				$vnumclass = 'mid';
		}

		$output[] = sprintf(
			'<div class="ytc_video_container ytc_video_%d ytc_video_%s" style="width: %dpx">',
			$y, $vnumclass, $width
		);

		// Show video title?
		if ( $instance['showtitle'] ) {
			$output[] = sprintf( '<h3 class="ytc_title">%s</h3>', $yt_title );
		}

		// Define object ID.
		$ytc_vid = 'ytc_' . $yt_id;

		// Set proper class for responsive thumbs per selected aspect ratio.
		switch ( $instance['ratio'] ) {
			case 1: $arclass = 'ar4_3'; break;
			case 2: $arclass = 'ar16_10'; break;
			default: $arclass = 'ar16_9';
		}
		$title = sprintf(
			__('Watch video %1$s published on %2$s', 'youtube-channel' ),
			$yt_title, $yt_date
		);
		$output[] = sprintf(
			'<a href="%s" title="%s" class="ytc_thumb ytc-lightbox %s">' .
				'<span style="background-image: url(%s);" title="%s" id="%s">' .
				'</span></a>',
			$yt_video, $yt_title, $arclass, $yt_thumb, $title, $ytc_vid
		);

		// Do we need to show video description?
		if ( $instance['showvidesc'] ) {
			preg_match( '/><span>(.*)<\/span><\/div>/', $item->content->{'$t'},
				$videsc );
			if ( empty($videsc[1]) ) {
				$videsc[1] = $item->content->{'$t'};
			}

			// clean HTML
			$nohtml = explode( '</div>', $videsc[1] );
			if ( sizeof($nohtml) > 1 ) {
				$videsc[1] = strip_tags( $nohtml[2] );
				unset( $nohtml );
			} else {
				$videsc[1] = strip_tags( $videsc[1] );
			}

			if ( $instance['videsclen'] > 0 ) {
				if ( strlen($videsc[1]) > $instance['videsclen'] ) {
					$video_description = substr( $videsc[1], 0,
						$instance['videsclen'] );
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
			if ( !empty( $video_description ) ) {
				$output[] = sprintf(
					'<p class="ytc_description">%s%s</p>',
					$video_description, $etcetera
				);
			}
		}
		$output[] = '</div><!-- .ytc_video_container -->';

		return $output;
	}

	private function youtube_domain($instance) {
		return 'www.youtube.com';
	}

	private function clean_playlist_id($playlist) {
		if ( substr($playlist,0,4) == 'http' ) {
			// If URL provided, extract playlist ID
			$playlist = preg_replace(
				'/.*list=PL([A-Za-z0-9\-\_]*).*/', '$1', $playlist
			);
		} else if ( substr( $playlist, 0, 2 ) == 'PL' ) {
			$playlist = substr( $playlist, 2 );
		}
		return $playlist;
	}

}
endif;

global $LYCW;
$LYCW = new LYCW();
