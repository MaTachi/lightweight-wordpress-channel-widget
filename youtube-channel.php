<?php
/*
Plugin Name: YouTube Channel
Plugin URI: http://urosevic.net/wordpress/plugins/youtube-channel/
Description: <a href="widgets.php">Widget</a> that display latest video thumbnail, iframe (HTML5 video), object (Flash video) or chromeless video from YouTube Channel or Playlist.
Author: Aleksandar Urošević
Version: 2.4.1.3
Author URI: http://urosevic.net/
*/
// @TODO make FitViedo optional

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists('WPAU_YOUTUBE_CHANNEL') )
{
	class WPAU_YOUTUBE_CHANNEL
	{

		public $plugin_version = "2.4.1.3";
		public $plugin_name    = "YouTube Channel";
		public $plugin_slug    = "youtube-channel";
		public $plugin_option  = "youtube_channel_defaults";
		public $channel_id     = "urkekg";
		public $playlist_id    = "PLEC850BE962234400";
		public $plugin_url;

		function __construct()
		{

			// debug JSON
			if (!empty($_GET['ytc_debug_json_for']))
				$this->generate_debug_json();

			$this->plugin_url = plugin_dir_url(__FILE__);
			load_plugin_textdomain( $this->plugin_slug, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

			// Installation and uninstallation hooks
			register_activation_hook(__FILE__, array($this, 'activate'));

			// Initialize Plugin Settings Magic
			if ( is_admin() )
				add_action('init', array($this, 'settings_init'), 900);

			// Load widget definition
			require_once('inc/widget.php');

			// Add youtube_channel shortcode
			add_shortcode( 'youtube_channel', array($this, 'shortcode') );
			add_shortcode( 'ytc', array($this, 'shortcode') );

			// Update YTC version in database on request
			if ( !empty($_GET['ytc_dismiss_update_notice']) )
				update_option( 'ytc_version', $this->plugin_version );

			// Update Redux notice
			if ( !empty($_GET['ytc_ignore_redux']) )
				update_option( 'ytc_no_redux_notice', true);

			// add dashboard notice if version changed
			$version = get_option('ytc_version','0');
			if ( version_compare($version, $this->plugin_version, "<") )
				add_action( 'admin_notices', array($this, 'admin_notices') );

			add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts') );
			add_action( 'wp_footer', array($this, 'footer_scripts') );

		} // end __construct

		function settings_init()
		{

			// Load Redux Framework
			if ( class_exists( "ReduxFramework" ) )
			{
				// Add Settings link on Plugins page if Redux is installed
				add_filter('plugin_action_links_'.plugin_basename(__FILE__), array($this, 'add_settings_link') );

				// Load Settings Page configuration
				if ( file_exists(dirname(__FILE__).'/inc/config.php') ){
					require_once( dirname( __FILE__ ) . '/inc/config.php' );
				}
			} else {
				// Add admin notice for Redux Framework
				if ( !get_option('ytc_no_redux_notice', 0) )
					add_action( 'admin_notices', array($this,'admin_notice_redux') );
			}

		} // settings_init()

		function admin_notices()
		{
			$previous_version = get_option('ytc_version','0');

			$settings_page = "options-general.php?page=youtube-channel";
			$msg = "";
			switch ($previous_version)
			{
				case "0":
					$msg = sprintf(__('Please review <a href="%s">global settings</a>, YTC widgets and shortcodes.', 'youtube-channel'), $settings_page);
					break;
				case "2.2.2":
					$msg = sprintf(__('If you use caching for any YTC widget or shortcode, please <strong>ReCache</strong> feeds in <strong>Tools</strong> section of <a href="%s">plugin settings</a> page.', 'youtube-channel'), $settings_page);
					break;
				case "2.2.3":
					if (class_exists( "ReduxFramework" )){
						$msg = sprintf(__('We switched to <em>Redux Framework</em> so please review global plugin <a href="%s">settings page</a>.', 'youtube-channel'), $settings_page);
					} else {
						$msg = __('We switched to <em>Redux Framework</em> so please install and activate dependency and then review global YouTube Channel plugin settings.', 'youtube-channel');
					}
					break;
			}
			if ( !empty($msg) && class_exists( "ReduxFramework" ) )
			printf(
				'<div class="update-nag"><p><strong>%s</strong> ' . __("updated to version", 'youtube-channel') . ' <strong>%s</strong>. '.$msg.'&nbsp;&nbsp;<a href="?ytc_dismiss_update_notice=1" class="button button-secondary">' . __("I did this already, dismiss notice!", 'youtube-channel') . '</a></p></div>',
				$this->plugin_name,
				$this->plugin_version);
		} // end admin_notices

		function admin_notice_redux()
		{
			$ignoreredux = ' <a href="?ytc_ignore_redux=1" class="button primary">'.__("Dismiss this notice", 'youtube-channel') . '</a>';
			echo '<div class="error"><p>'.sprintf(__("To configure global <strong>%s</strong> options, you need to install and activate <strong>%s</strong>.", 'youtube-channel'), $this->plugin_name, "Redux Framework Plugin") . $ignoreredux . '</p></div>';
		} // admin_notice()

		function add_settings_link($links)
		{
			$settings_link = '<a href="options-general.php?page='.$this->plugin_slug.'">'.__('Settings').'</a>';
			array_unshift( $links, $settings_link );
			return $links;
		} // add_settings_link()

		public function defaults()
		{

			$init = array(
				'channel'        => $this->channel_id,
				'playlist'       => $this->playlist_id,
				'use_res'        => false,
				'only_pl'        => false,
				'cache_time'     => 300, // 5 minutes
				'maxrnd'         => 25,
				'vidqty'         => 1,
				'enhprivacy'     => false,
				'fixnoitem'      => false,
				'getrnd'         => false,
				'ratio'          => 3, // 3 - 16:9, 2 - 16:10, 1 - 4:3
				'width'          => 306,
				'responsive'     => true,
				'to_show'        => 'thumbnail', // thumbnail, iframe, iframe2, chromeless, object
				'themelight'     => false,
				'controls'       => false,
				'fixyt'          => false,
				'autoplay'       => false,
				'autoplay_mute'  => false,
				'norel'          => false,

				'showtitle'      => false,
				'showvidesc'     => false,
				'videsclen'      => 0,
				'descappend'     => '&hellip;',
				'modestbranding' => false,
				'hideanno'       => false,
				'hideinfo'       => false,

				'goto_txt'       => 'Visit our channel',
				'showgoto'       => false,
				'popup_goto'     => 3, // 3 same window, 2 new window JS, 1 new window target
				'userchan'       => false
			);
			$defaults = get_option($this->plugin_option, $init);

			// $options = wp_parse_args(get_option('youtube_channel_defaults'), $defaults);
			// return $options;
			return $defaults;
		}

		/**
		 * Activate the plugin
		 */
		public static function activate()
		{
			// Transit old settings to new format
			// get pre-2.0.0 YTC widgets, and if exist, convert to 2.0.0+ version
			if ( $old = get_option('widget_youtube_channel_widget') ) {
				// if we have pre-2.0.0 YTC widgets, merge them to new version

				// get new YTC widgets
				$new = get_option('widget_youtube-channel');

				// get all widget areas
				$widget_areas = get_option('sidebars_widgets');

				// update options to 2.0.0+ version
				foreach ($old as $k=>$v) {

					if ( $k !== "_multiwidget" ){
						// option for resource
						$v['use_res'] = 0;
						if ( $v['usepl'] == "on" ) {
							$v['use_res'] = 2;
						}

						$v['popup_goto'] = 0;
						if ( $v['popupgoto'] == "on" ) {
							$v['popup_goto'] = 1;
						} else if ($v['target'] == "on") {
							$v['popup_goto'] = 2;
						}
						unset($v['usepl'], $v['popupgoto'], $v['target']);

						$v['cache_time']    = 0;
						$v['userchan']      = 0;
						$v['enhprivacy']    = 0;
						$v['autoplay_mute'] = 0;

						// add old YTC widget to new set
						// but append at the end if YTC widget with same ID already exist
						// in new set (created in version 2.0.0)
						if ( is_array($new[$k]) ) {
							// populate at the end
							array_push($new, $v);
							$ytc_widget_id = "youtube-channel-".end(array_keys($new));
						} else {
							// set as current widget ID
							$new[$k] = $v;
							$ytc_widget_id = "youtube-channel-$k";
						}

						$ytc_widget_added = 0;
						foreach ( $widget_areas as $wak => $wav ) {
							// check if here we have this widget
							if ( is_array($wav) && in_array($ytc_widget_id,$wav) )
								$ytc_widget_added++;
						}
						// if YTC widget has not present in any widget area, add it to inactive widgets ;)
						if ( $ytc_widget_added == 0 )
							array_push($widget_areas['wp_inactive_widgets'], $ytc_widget_id);

					}
					// add to inactive widgets if don't belong to any widget area

				} // foreach widget option

				// update widget areas set
				update_option('sidebars_widgets',$widget_areas);

				// update new YTC widgets
				update_option('widget_youtube-channel',$new);

				// remove old YTC widgets entry
				delete_option('widget_youtube_channel_widget');

				// clear temporary vars
				unset ($old,$new);

			} // if we have old YTC widgets

		} // end function activate

		function enqueue_scripts() {
			wp_enqueue_style( 'youtube-channel', plugins_url('assets/css/youtube-channel.min.css', __FILE__), array(), $this->plugin_version );

			// enqueue fitVids
			wp_enqueue_script( 'fitvids', plugins_url('assets/js/jquery.fitvids.min.js', __FILE__), array('jquery'), $this->plugin_version, true );

			// enqueue magnific-popup
			wp_enqueue_script( 'magnific-popup-au', plugins_url('assets/lib/magnific-popup/jquery.magnific-popup.min.js', __FILE__), array('jquery'), $this->plugin_version, true );
			wp_enqueue_style( 'magnific-popup-au', plugins_url('assets/lib/magnific-popup/magnific-popup.min.css', __FILE__), array(), $this->plugin_version );
			wp_enqueue_script( 'youtube-channel', plugins_url('assets/js/youtube-channel.min.js', __FILE__), array(), $this->plugin_version, true );
		} // end function enqueue_scripts

		function footer_scripts() {
			// Print JS only if we have set YTC array
			if ( !empty($_SESSION['ytc_html5_js']) )
			{
?>
<!-- YouTube Channel v<?php echo $this->plugin_version; ?> -->
<script type="text/javascript">
var tag = document.createElement('script');
tag.src = "//www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
function onYouTubeIframeAPIReady() {
<?php echo $_SESSION['ytc_html5_js']; ?>
}
function ytc_mute(event){
	event.target.mute();
}
</script>
<?php
			}

		} // eof footer_scripts

		public function shortcode($atts)
		{
			// get general default settings
			$instance = $this->defaults();

			// extract shortcode parameters
			extract(
				shortcode_atts(
					array(
						'channel'    => $instance['channel'],
						'playlist'   => $instance['playlist'],
						'res'        => $instance['use_res'],
						'only_pl'    => $instance['only_pl'],
						'cache'      => $instance['cache_time'],

						'fetch'      => $instance['maxrnd'],
						'num'        => $instance['vidqty'],

						'fix'        => $instance['fixnoitem'],
						'random'     => $instance['getrnd'],

						'ratio'      => $instance['ratio'],
						'width'      => $instance['width'],
						'responsive' => (!empty($instance['responsive'])) ? $instance['responsive'] : '0',

						'show'       => $instance['to_show'],

						'themelight' => $instance['themelight'],
						'controls'   => $instance['controls'],
						'fix_h'      => $instance['fixyt'],
						'autoplay'   => $instance['autoplay'],
						'mute'       => $instance['autoplay_mute'],
						'norel'      => $instance['norel'],

						'showtitle'  => $instance['showtitle'],
						'showdesc'   => $instance['showvidesc'],
						'nobrand'    => (!empty($instance['modestbranding'])) ? $instance['modestbranding'] : '0',
						'desclen'    => $instance['videsclen'],
						'noinfo'     => $instance['hideinfo'],
						'noanno'     => $instance['hideanno'],

						'goto'       => $instance['showgoto'],
						'goto_txt'   => $instance['goto_txt'],
						'popup'      => $instance['popup_goto'],
						'userchan'   => $instance['userchan'],

						'class'      => (!empty($instance['class'])) ? $instance['class'] : ''
		    		),
					$atts
				)
			);

			// prepare instance for output
			$instance['channel']       = $channel;
			$instance['playlist']      = $playlist;
			$instance['use_res']       = $res; // resource: 0 channel, 1 favorites, 2 playlist
			$instance['only_pl']       = $only_pl; // use embedded playlist - false by default
			$instance['cache_time']    = $cache; // in seconds, def 5min - settings?

			$instance['maxrnd']        = $fetch;
			$instance['vidqty']        = $num; // num: 1

			$instance['fixnoitem']     = $fix; // fix noitem
			$instance['getrnd']        = $random; // use embedded playlist - false by default

			// Video Settings
			$instance['ratio']         = $ratio; // aspect ratio: 3 - 16:9, 2 - 16:10, 1 - 4:3
			$instance['width']         = $width; // 306
			$instance['responsive']    = $responsive; // enable responsivenes?
			$instance['to_show']       = $show; // thumbnail, iframe, iframe2, object, chromeless

			$instance['themelight']    = $themelight; // use light theme, dark by default
			$instance['controls']      = $controls; // hide controls, false by default
			$instance['fixyt']         = $fix_h; // fix youtube height, disabled by default
			$instance['autoplay']      = $autoplay; // autoplay disabled by default
			$instance['autoplay_mute'] = $mute; // mute sound on autoplay - disabled by default
			$instance['norel']         = $norel; // hide related videos

			// Content Layout
			$instance['showtitle']     = $showtitle; // show video title, disabled by default
			$instance['showvidesc']    = $showdesc; // show video description, disabled by default
			$instance['modestbranding']= $nobrand; // hide YT logo
			$instance['videsclen']     = $desclen; // cut video description, number of characters
			$instance['hideinfo']      = $noinfo; // hide info by default
			$instance['hideanno']      = $noanno; // hide annotations, false by default

			// Link to Channel
			$instance['showgoto']      = $goto; // show goto link, disabled by default
			$instance['goto_txt']      = $goto_txt; // text for goto link - use settings
			$instance['popup_goto']    = $popup; // open channel in: 0 same window, 1 javascript new, 2 target new
			$instance['userchan']      = $userchan; // link to user channel instaled page

			// Customization
			$instance['class']         = $class; // custom additional class for container

			return implode(array_values($this->output($instance)));
		}

		// print out widget
		public function output($instance)
		{

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

			if ( $instance['only_pl'] && $use_res == 2 ) { // print standard playlist
				$output = array_merge($output, self::ytc_only_pl($instance));
			} else { // channel or playlist single videos

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
			} // single playlist or ytc way

			$output = array_merge( $output, $this->ytc_channel_link($instance) ); // insert link to channel on bootom of widget

			$output[] = '</div><!-- .youtube_channel -->';

			return $output;
		}

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

		// function to insert link to channel
		function ytc_channel_link($instance) {
			// initialize array
			$output = array();
			// do we need to show goto link?
			if ( $instance['showgoto'] ) {
				$channel = $instance['channel'];
				if ( !$channel )
					$channel = 'urkekg';
				$goto_txt = $instance['goto_txt'];
				if ( $goto_txt == "" )
					$goto_txt = sprintf( __('Visit channel %1$s', 'youtube-channel'), $channel );
				else
					$goto_txt = str_replace('%channel%', $channel, $goto_txt);

				$output[] = '<div class="ytc_link">';
				$userchan = ( $instance['userchan'] ) ? 'channel' : 'user';
				$goto_url = '//www.youtube.com/'.$userchan.'/'.$channel.'/';
				$newtab = __("in new window/tab", 'youtube-channel');
				$output[] = '<p>';
				switch ( $instance['popup_goto'] ) {
					case 1:
						$output[] = '<a href="javascript: window.open(\''.$goto_url.'\'); void 0;" title="'.$goto_txt.' '.$newtab.'">'.$goto_txt.'</a>';
						break;
					case 2:
						$output[] = '<a href="'.$goto_url.'" target="_blank" title="'.$goto_txt.' '.$newtab.'">'.$goto_txt.'</a>';
						break;
					default:
						$output[] = '<a href="'.$goto_url.'" title="'.$goto_txt.'">'.$goto_txt.'</a>';
				} // switch popup_goto
				$output[] = '</p>';
				$output[] = '</div>';

			} // showgoto

			return $output;
		} // end function ytc_channel_link

		/* function to print video block */
		function ytc_print_video($item, $instance, $y) {

			// get hideinfo, autoplay and controls settings
			// where this is used?
			$hideinfo      = $instance['hideinfo'];
			$autoplay      = $instance['autoplay'];
			$autoplay_mute = $instance['autoplay_mute'];
			$controls      = $instance['controls'];
			$norel         = $instance['norel'];
			$class         = $instance['class'];
			$modestbranding = $instance['modestbranding'];

			// set width and height
			$width  = ( empty($instance['width']) ) ? 306 : $instance['width'];
			$height = $this->height_ratio($width, $instance['ratio']);

			// calculate image height based on width for 4:3 thumbnail
			$imgfixedheight = $width / 4 * 3;

			// which type to show
			$to_show = (empty($instance['to_show'])) ? 'object' : $instance['to_show'];

			// if not thumbnail, increase video height for 25px taken by video controls
			if ( $to_show != 'thumbnail' && !$controls && $instance['fixyt'] )
				$height += 25;

			$hideanno   = $instance['hideanno'];
			$themelight = $instance['themelight'];
			/* end of video settings */

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
					$autoplay = false;
					$vnumclass = 'last';
					break;
				default:
					$vnumclass = 'mid';
					$autoplay = false;
					break;
			}

			$output[] = '<div class="ytc_video_container ytc_video_'.$y.' ytc_video_'.$vnumclass.'" style="width:'.$width.'px">';

			// show video title?
			if ( $instance['showtitle'] )
				$output[] = '<h3 class="ytc_title">'.$yt_title.'</h3>';

			// define object ID
			$ytc_vid = 'ytc_' . $yt_id;

			// enhanced privacy
			$youtube_domain = $this->youtube_domain($instance);

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
				$p = '';
				if ( $norel ) $p .= '&rel=0';
				if ( $modestbranding ) $p .= "&modestbranding=1";
				if ( $controls ) $p .= "&controls=0";
				// if ( $themelight ) $p .= "&theme=light";
				$output[] = '<a href="'.$yt_video.$p.'" title="'.$yt_title.'" class="ytc_thumb ytc-lightbox '.$arclass.'"><span style="background-image: url('.$yt_thumb.');" title="'.$title.'" id="'.$ytc_vid.'"></span></a>';
			} else if ( $to_show == "chromeless" ) {
				ob_start();
		?>
			<object type="application/x-shockwave-flash" data="<?php echo $this->plugin_url . 'inc/chromeless.swf'; ?>" width="<?php echo $width; ?>" height="<?php echo $height; ?>" id="<?php echo $ytc_vid; ?>">
				<param name="flashVars" value="video_source=<?php echo $yt_id; ?>&video_width=<?php echo $width; ?>&video_height=<?php
				 echo $height;
				 if ( $autoplay ) echo "&autoplay=Yes";
				 if ( !$controls ) echo "&youtube_controls=Yes";
				 if ( $hideanno ) echo "&iv_load_policy=3";
				 if ( $themelight ) echo "&theme=light";
				 if ( $modestbranding ) echo "&modestbranding=1";
				 if ( $norel ) echo "&rel=0";
				 ?>" />
				<param name="quality" value="high" />
				<param name="wmode" value="opaque" />
				<param name="swfversion" value="6.0.65.0" />
				<param name="movie" value="<?php echo $this->plugin_url . 'chromeless.swf'; ?>" />
			</object>
		<?php
				$output[] = ob_get_contents();
				ob_end_clean();
			} else if ( $to_show == "iframe" ) {
				if ( empty($usepl) ) $yt_url = $yt_id;

				$output[] = '<iframe title="YouTube video player" width="'.$width.'" height="'.$height.'" src="//'.$youtube_domain.'/embed/'.$yt_url.'?wmode=opaque'; //&enablejsapi=1';
				if ( $controls ) $output[] = "&amp;controls=0";
				if ( $hideinfo ) $output[] = "&amp;showinfo=0";
				if ( $autoplay ) $output[] = "&amp;autoplay=1";
				if ( $hideanno ) $output[] = "&amp;iv_load_policy=3";
				if ( $themelight ) $output[] = "&amp;theme=light";
				if ( $modestbranding ) $output[] = "&amp;modestbranding=1";
				// disable related videos
				if ( $norel ) $output[] = "&amp;rel=0";

				$output[] = '" style="border: 0;" allowfullscreen id="'.$ytc_vid.'"></iframe>';
			} else if ( $to_show == "iframe2" ) {
				// youtube API async
				if ( empty($usepl) ) $yt_url = $yt_id;

				$js_rel            = ( $norel ) ? "rel: 0," : '';
				$js_controls       = ( $controls ) ? "controls: 0," : '';
				$js_showinfo       = ( $hideinfo ) ? "showinfo: 0," : '';
				$js_iv_load_policy = ( $hideanno ) ? "iv_load_policy: 3," : '';
				$js_theme          = ( $themelight ) ? "theme: 'light'," : '';
				$js_autoplay       = ( $autoplay ) ? "autoplay: 1," : '';
				$js_modestbranding = ( $modestbranding ) ? "modestbranding: 1," : '';
				$js_autoplay_mute  = ( $autoplay && $autoplay_mute ) ? "events: {'onReady': ytc_mute}" : '';
				$js_player_id      = str_replace('-', '_', $yt_url);

				$output[] = '<div id="ytc_player_'.$js_player_id.'"></div>';
				$site_domain = $_SERVER['HTTP_HOST'];
				$ytc_html5_js = <<<JS
					var ytc_player_$js_player_id;
					ytc_player_$js_player_id = new YT.Player('ytc_player_$js_player_id', {
						height: '$height',
						width: '$width',
						videoId: '$yt_url',
						enablejsapi: 1,
						playerVars: {
							$js_autoplay $js_showinfo $js_controls $js_theme $js_rel $js_modestbranding wmmode: 'opaque'
						},
						origin: '$site_domain',
						$js_iv_load_policy $js_autoplay_mute
					});
JS;

			// prepare JS for footer
			if ( empty($_SESSION['ytc_html5_js']) )
				$_SESSION['ytc_html5_js'] = $ytc_html5_js;
			else
				$_SESSION['ytc_html5_js'] .= $ytc_html5_js;

			} else { // default is object
				$obj_url = '//'.$youtube_domain.'/'.$yt_url.'?version=3';
				$obj_url .= ( $controls ) ? '&amp;controls=0' : '';
				$obj_url .= ( $hideinfo ) ? '&amp;showinfo=0' : '';
				$obj_url .= ( $autoplay ) ? '&amp;autoplay=1' : '';
				$obj_url .= ( $hideanno ) ? '&amp;iv_load_policy=3' : '';
				$obj_url .= ( $themelight ) ? '&amp;theme=light' : '';
				$obj_url .= ( $modestbranding ) ? '&amp;modestbranding=1' : '';
				$obj_url .= ( $norel ) ? '&amp;rel=0' : '';
				ob_start();
		?>
		<object width="<?php echo $width; ?>" height="<?php echo $height; ?>"  type="application/x-shockwave-flash" data="<?php echo $obj_url; ?>">
			<param name="movie" value="<?php echo $obj_url; ?>" />
			<param name="allowFullScreen" value="true" />
			<param name="allowscriptaccess" value="always" />
			<param name="quality" value="high" />
			<param name="wmode" value="opaque" />
			<embed src="<?php echo $obj_url; ?>" type="application/x-shockwave-flash" width="<?php echo $width; ?>" height="<?php echo $height; ?>" allowscriptaccess="always" allowfullscreen="true" />
		</object>

		<?php
				$output[] = ob_get_contents();
				ob_end_clean();
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

		/* function to print standard playlist embed code */
		function ytc_only_pl($instance) {

		$width = $instance['width'];
		if ( empty($width) )
			$width = 306;

		$playlist = (empty($instance['playlist'])) ? $this->playlist_id : $instance['playlist'];

		$height = self::height_ratio($width, $instance['ratio']);

		$height += ($instance['fixyt']) ? 54 : 0;

		$playlist = $this->clean_playlist_id($playlist);

		$autoplay = (empty($instance['autoplay'])) ? '' : '&autoplay=1';

		$theme = (empty($instance['themelight'])) ? '' : '&theme=light';

		$modestbranding = (empty($instance['modestbranding'])) ? '' : '&modestbranding=1';

		$rel = (empty($instance['norel'])) ? '' : '&rel=0';

		// enhanced privacy
		$youtube_domain = $this->youtube_domain($instance);
		$output[] = '<div class="ytc_video_container ytc_video_1 ytc_video_single">
		<iframe src="//'.$youtube_domain.'/embed/videoseries?list=PL'.$playlist.$autoplay.$theme.$modestbranding.$rel.'"
		width="'.$width.'" height="'.$height.'" frameborder="0"></iframe></div>';
			return $output;
		} // end function ytc_only_pl

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

		function generate_debug_json()
		{
			global $wp_version;

			// get Redux Framework version (if active)
			$redux = ( class_exists( "ReduxFramework" ) ) ? ReduxFramework::$_version : 'N/A';

			// get widget ID from parameter
			$for = $_GET['ytc_debug_json_for'];

			// prepare option name and widget ID
			$option_name = "widget_".substr($for,0,strrpos($for,"-"));
			$widget_id = substr($for,strrpos($for,"-")+1);

			// get YTC widgets options
			$widget_options = get_option($option_name);

			if (!is_array($widget_options[$widget_id]))
				return;

			// prepare debug data with settings of current widget
			$data = array_merge(
				array(
					'date'      => date("r"),
					'server'    => $_SERVER["SERVER_SOFTWARE"],
					'php'       => PHP_VERSION,
					'wp'        => $wp_version,
					'ytc'       => $this->plugin_version,
					'redux'     => $redux,
					'url'       => get_site_url(),
					'widget_id' => $for
				),
				$widget_options[$widget_id]
			);

			// return JSON file
			header('Content-disposition: attachment; filename='.$for.'.json');
			header('Content-Type: application/json');
			echo json_encode($data);

			// destroy vars
			unset($data,$widget_options,$widget_id,$option_name,$for,$redux);

			// exit now, because we need only debug data in JSON file, not settings or any other page
			exit;
		}
	} // end class
} // end class check

global $WPAU_YOUTUBE_CHANNEL;
$WPAU_YOUTUBE_CHANNEL = new WPAU_YOUTUBE_CHANNEL();
