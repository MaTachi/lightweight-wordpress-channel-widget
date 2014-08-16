<?php
/*
Plugin Name: YouTube Channel
Plugin URI: http://urosevic.net/wordpress/plugins/youtube-channel/
Description: <a href="widgets.php">Widget</a> that display latest video thumbnail, iframe (HTML5 video), object (Flash video) or chromeless video from YouTube Channel or Playlist.
Author: Aleksandar Urošević
Version: 2.2.3
Author URI: http://urosevic.net/
*/
define( 'YTCVER', '2.2.3' );
define( 'YOUTUBE_CHANNEL_URL', plugin_dir_url(__FILE__) );
define( 'YTCPLID', 'PLEC850BE962234400' );
define( 'YTCUID', 'urkekg' );
define( 'YTCTDOM', 'youtube-channel' );
define( 'YTCNAME', 'YouTube Channel' );

/* youtube widget */
class WPAU_YOUTUBE_CHANNEL extends WP_Widget {

	function __construct() {

		add_shortcode( 'youtube_channel', array($this, 'youtube_channel_shortcode') );
		// Initialize Widget
		parent::__construct(
			YTCTDOM,
			__( 'Youtube Channel' , YTCTDOM ),
			array( 'description' => __( 'Serve YouTube videos from channel or playlist right to widget area', YTCTDOM ) )
		);

		// update YTC version in database on request
		if ( !empty($_GET['ytc_dismiss_update_notice']) )
			update_option( 'ytc_version', YTCVER );

		// add dashboard notice if version changed
		$version = get_option('ytc_version','0');
		if ( version_compare($version, YTCVER, "<") )
			add_action( 'admin_notices', array($this, 'admin_notices') );

		// Initialize Settings
		require_once(sprintf("%s/assets/settings.php", dirname(__FILE__)));

		$WPAU_YOUTUBE_CHANNEL_SETTINGS = new WPAU_YOUTUBE_CHANNEL_SETTINGS();

	}

	function admin_notices()
	{
?>
	<div class="update-nag">
		<p>
		<strong><?php echo YTCNAME; ?></strong> is updated to version <strong><?php echo YTCVER; ?></strong>.
		If you enabled caching for any YTC widget or shortcode, please <strong>ReCache</strong> feeds at <a href="options-general.php?page=youtube-channel&tab=ytc_tools">Tools</a> tab of settings page.
		&nbsp;&nbsp;<a href="?ytc_dismiss_update_notice=1" class="button button-secondary">I did this already, dismiss notice!</a>
		</p>
	</div>
<?php
	}
	public static function defaults()
	{
		$defaults = array(
			'channel'       => YTCUID,
			'playlist'      => YTCPLID,
			'use_res'       => false,
			'cache_time'    => 300, // 5 minutes
			'maxrnd'        => 25,
			'vidqty'        => 1,
			'enhprivacy'    => false,
			'fixnoitem'     => false,
			'getrnd'        => false,
			'ratio'         => 3, // 3 - 16:9, 2 - 16:10, 1 - 4:3
			'width'         => 220,
			
			'showtitle'     => false,
			'showvidesc'    => false,
			'videsclen'     => 0,
			'descappend'    => '&hellip;',
			'hideanno'      => false,
			'hideinfo'      => false,
			
			'goto_txt'      => 'Visit our channel',
			'showgoto'      => false,
			'popup_goto'    => 3, // 3 same window, 2 new window JS, 1 new window target
			'userchan'      => false
		);

		$options = wp_parse_args(get_option('youtube_channel_defaults'), $defaults);

		return $options;
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

	} // END public static function activate

	// Helper function cache_time()
	function cache_time($cache_time)
	{
		$times = array(
			'minute' => array(
				1  => "1 minute",
				5  => "5 minutes",
				15 => "15 minutes",
				30 => "30 minutes"
			),
			'hour' => array(
				1  => "1 hour",
				2  => "2 hours",
				5  => "5 hours",
				10 => "10 hours",
				12 => "12 hours",
				18 => "18 hours"
			),
			'day' => array(
				1 => "1 day",
				2 => "2 days",
				3 => "3 days",
				4 => "4 days",
				5 => "5 days",
				6 => "6 days"
			),
			'week' => array(
				1 => "1 week",
				2 => "2 weeks",
				3 => "3 weeks",
				4 => "1 month"
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
				$out .='<option value="'.$sec.'" '. selected( $cache_time, $sec, 0 ).'>'.__($s, YTCTDOM).'</option>';
				unset($sec);
			}
		}
		return $out;
	}

	// TODO: Form code
	public function form($instance) {
		// outputs the options form for widget settings
		// General Options
		$title         = (!empty($instance['title'])) ? esc_attr($instance['title']) : '';
		$channel       = (!empty($instance['channel'])) ? esc_attr($instance['channel']) : '';
		$playlist      = (!empty($instance['playlist'])) ? esc_attr($instance['playlist']) : '';

		$use_res       = (!empty($instance['use_res'])) ? esc_attr($instance['use_res']) : 0; // resource to use: channel, favorites, playlis : ''t

		$cache_time    = (!empty($instance['cache_time'])) ? esc_attr($instance['cache_time']) : '';

		$maxrnd        = (!empty($instance['maxrnd'])) ? esc_attr($instance['maxrnd']) : 25; // items to fetch
		$vidqty        = (!empty($instance['vidqty'])) ? esc_attr($instance['vidqty']) : 1; // number of items to show

		$enhprivacy    = (!empty($instance['enhprivacy'])) ? esc_attr($instance['enhprivacy']) : '';
		$fixnoitem     = (!empty($instance['fixnoitem'])) ? esc_attr($instance['fixnoitem']) : '';
		$getrnd        = (!empty($instance['getrnd'])) ? esc_attr($instance['getrnd']) : '';

		// Video Settings
		$ratio         = (!empty($instance['ratio'])) ? esc_attr($instance['ratio']) : 3;
		$width         = (!empty($instance['width'])) ? esc_attr($instance['width']) : 220;
		// $height        = (!empty($instance['height'])) ? esc_attr($instance['height']) : '';

		// Content Layout
		$showtitle     = (!empty($instance['showtitle'])) ? esc_attr($instance['showtitle']) : '';
		$showvidesc    = (!empty($instance['showvidesc'])) ? esc_attr($instance['showvidesc']) : '';
		$videsclen     = (!empty($instance['videsclen'])) ? esc_attr($instance['videsclen']) : 0;
		$descappend    = (!empty($instance['descappend'])) ? esc_attr($instance['descappend']) : '&hellip;';

		$hideanno      = (!empty($instance['hideanno'])) ? esc_attr($instance['hideanno']) : '';
		$hideinfo      = (!empty($instance['hideinfo'])) ? esc_attr($instance['hideinfo']) : '';

		// Link to Channel
		$goto_txt      = (!empty($instance['goto_txt'])) ? esc_attr($instance['goto_txt']) : '';
		$showgoto      = (!empty($instance['showgoto'])) ? esc_attr($instance['showgoto']) : '';
		$popup_goto    = (!empty($instance['popup_goto'])) ? esc_attr($instance['popup_goto']) : '';
		$userchan      = (!empty($instance['userchan'])) ? esc_attr($instance['userchan']) : '';
		
		// Debug YTC
		$debugon       = (!empty($instance['debugon'])) ? esc_attr($instance['debugon']) : '';
		?>

		<p>
			<label for="<?php echo $this->get_field_id('title');	?>"><?php _e('Widget Title:', YTCTDOM);	?><input type="text" class="widefat" id="<?php echo $this->get_field_id('title');		?>" name="<?php echo $this->get_field_name('title');	?>" value="<?php echo $title;		?>" title="<?php _e('Title for widget', YTCTDOM); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('channel');	?>"><?php _e('Channel ID:', YTCTDOM); ?><input type="text" class="widefat" id="<?php echo $this->get_field_id('channel');		?>" name="<?php echo $this->get_field_name('channel');	?>" value="<?php echo $channel;		?>" title="<?php _e('YouTube Channel name (not URL to channel)', YTCTDOM); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('playlist');	?>"><?php _e('Playlist ID:', YTCTDOM); ?><input type="text" class="widefat" id="<?php echo $this->get_field_id('playlist');	?>" name="<?php echo $this->get_field_name('playlist'); ?>" value="<?php echo $playlist;	?>" title="<?php _e('YouTube Playlist ID (not playlist name)', YTCTDOM); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('use_res');	?>"><?php _e('Resource to use:', YTCTDOM); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'use_res' ); ?>" name="<?php echo $this->get_field_name( 'use_res' ); ?>">
				<option value="0"<?php selected( $use_res, 0 ); ?>><?php _e('Channel', YTCTDOM); ?></option>
				<option value="1"<?php selected( $use_res, 1 ); ?>><?php _e('Favorites', YTCTDOM); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('cache_time');	?>"><?php _e('Cache feed:', YTCTDOM); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'cache_time' ); ?>" name="<?php echo $this->get_field_name( 'cache_time' ); ?>">
				<option value="0"<?php selected( $cache_time, 0 ); ?>><?php _e('Do not chache', YTCTDOM); ?></option>
				<?php echo self::cache_time($cache_time); ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('maxrnd'); ?>"><?php _e('Fetch:', YTCTDOM); ?> <input class="small-text" id="<?php echo $this->get_field_id('maxrnd'); ?>" name="<?php echo $this->get_field_name('maxrnd'); ?>" type="number" min="2" value="<?php echo $maxrnd; ?>" title="<?php _e('Number of videos that will be used for random pick (min 2, max 50, default 25)', YTCTDOM); ?>" /> video(s)</label>
			<br />
			<label for="<?php echo $this->get_field_id('vidqty'); ?>"><?php _e('Show:', YTCTDOM); ?></label> <input class="small-text" id="<?php echo $this->get_field_id('vidqty'); ?>" name="<?php echo $this->get_field_name('vidqty'); ?>" type="number" min="1" value="<?php echo ( $vidqty ) ? $vidqty : '1'; ?>" title="<?php _e('Number of videos to display', YTCTDOM); ?>" /> video(s)
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( (bool) $enhprivacy, true ); ?> id="<?php echo $this->get_field_id( 'enhprivacy' ); ?>" name="<?php echo $this->get_field_name( 'enhprivacy' ); ?>" title="<?php _e('Enable this option to protect your visitors privacy', YTCTDOM); ?>" /> <label for="<?php echo $this->get_field_id( 'enhprivacy' ); ?>"><?php printf(__('Use <a href="%s" target="_blank">Enhanced Privacy</a>', YTCTDOM), 'http://support.google.com/youtube/bin/answer.py?hl=en-GB&answer=171780'); ?></label>
			<br />
			<input class="checkbox" type="checkbox" <?php checked( (bool) $fixnoitem, true ); ?> id="<?php echo $this->get_field_id( 'fixnoitem' ); ?>" name="<?php echo $this->get_field_name( 'fixnoitem' ); ?>" title="<?php _e('Enable this option if you get error No Item', YTCTDOM); ?>" /> <label for="<?php echo $this->get_field_id( 'fixnoitem' ); ?>"><?php _e('Fix <em>No items</em> error/Respect playlist order', YTCTDOM); ?></label>
			<br />
			<input class="checkbox" type="checkbox" <?php checked( (bool) $getrnd, true ); ?> id="<?php echo $this->get_field_id( 'getrnd' ); ?>" name="<?php echo $this->get_field_name( 'getrnd' ); ?>" title="<?php _e('Get random videos of all fetched from channel or playlist', YTCTDOM); ?>" /> <label for="<?php echo $this->get_field_id( 'getrnd' ); ?>"><?php _e('Show random video', YTCTDOM); ?></label>
		</p>
		
		<h4><?php _e('Video Settings', YTCTDOM); ?></h4>
		<p><label for="<?php echo $this->get_field_id('ratio'); ?>"><?php _e('Aspect ratio', YTCTDOM); ?>:</label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'ratio' ); ?>" name="<?php echo $this->get_field_name( 'ratio' ); ?>">
				<?php /* <option value="0"<?php selected( $ratio, 0 ); ?>><?php _e('Custom (as set above)', YTCTDOM); ?></option> */ ?>
				<option value="3"<?php selected( $ratio, 3 ); ?>>16:9</option>
				<option value="2"<?php selected( $ratio, 2 ); ?>>16:10</option>
				<option value="1"<?php selected( $ratio, 1 ); ?>>4:3</option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width', YTCTDOM); ?>:</label> <input class="small-text" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="number" min="32" value="<?php echo $width; ?>" title="<?php _e('Set video width in pixels', YTCTDOM); ?>" /> px (<?php _e('default', YTCTDOM); ?> 220)
		</p>

		<h4><?php _e('Content Layout', YTCTDOM); ?></h4>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( (bool) $showtitle, true ); ?> id="<?php echo $this->get_field_id( 'showtitle' ); ?>" name="<?php echo $this->get_field_name( 'showtitle' ); ?>" /> <label for="<?php echo $this->get_field_id( 'showtitle' ); ?>"><?php _e('Show video title', YTCTDOM); ?></label><br />
			<input class="checkbox" type="checkbox" <?php checked( (bool) $showvidesc, true ); ?> id="<?php echo $this->get_field_id( 'showvidesc' ); ?>" name="<?php echo $this->get_field_name( 'showvidesc' ); ?>" /> <label for="<?php echo $this->get_field_id( 'showvidesc' ); ?>"><?php _e('Show video description', YTCTDOM); ?></label><br />
			<label for="<?php echo $this->get_field_id('videsclen'); ?>"><?php _e('Description length', YTCTDOM); ?>: <input class="small-text" id="<?php echo $this->get_field_id('videsclen'); ?>" name="<?php echo $this->get_field_name('videsclen'); ?>" type="number" value="<?php echo $videsclen; ?>" title="<?php _e('Set number of characters to cut down video description to (0 means full length)', YTCTDOM);?>" /> (0 = full)</label><br />
			<label for="<?php echo $this->get_field_id('descappend'); ?>"><?php _e('Et cetera string', YTCTDOM); ?> <input class="small-text" id="<?php echo $this->get_field_id('descappend'); ?>" name="<?php echo $this->get_field_name('descappend'); ?>" type="text" value="<?php echo $descappend; ?>" title="<?php _e('Default: &amp;hellip;', YTCTDOM); ?>"/></label><br />
			<input class="checkbox" type="checkbox" <?php checked( (bool) $hideanno, true ); ?> id="<?php echo $this->get_field_id( 'hideanno' ); ?>" name="<?php echo $this->get_field_name( 'hideanno' ); ?>" /> <label for="<?php echo $this->get_field_id( 'hideanno' ); ?>"><?php _e('Hide annotations from video', YTCTDOM); ?></label><br />
			<input class="checkbox" type="checkbox" <?php checked( (bool) $hideinfo, true ); ?> id="<?php echo $this->get_field_id( 'hideinfo' ); ?>" name="<?php echo $this->get_field_name( 'hideinfo' ); ?>" /> <label for="<?php echo $this->get_field_id( 'hideinfo' ); ?>"><?php _e('Hide video info', YTCTDOM); ?></label>
		</p>

		<h4><?php _e('Link to Channel', YTCTDOM); ?></h4>
		<p>
			<label for="<?php echo $this->get_field_id('goto_txt'); ?>"><?php _e('Visit YouTube Channel text:', YTCTDOM); ?> <input class="widefat" id="<?php echo $this->get_field_id('goto_txt'); ?>" name="<?php echo $this->get_field_name('goto_txt'); ?>" type="text" value="<?php echo $goto_txt; ?>" title="<?php _e('Default: Visit channel %channel%. Use placeholder %channel% to insert channel name.', YTCTDOM); ?>" /></label>
			<input class="checkbox" type="checkbox" <?php checked( (bool) $showgoto, true ); ?> id="<?php echo $this->get_field_id( 'showgoto' ); ?>" name="<?php echo $this->get_field_name( 'showgoto' ); ?>" /> <label for="<?php echo $this->get_field_id( 'showgoto' ); ?>"><?php _e('Show link to channel', YTCTDOM); ?></label><br />

			<select class="widefat" id="<?php echo $this->get_field_id( 'popup_goto' ); ?>" name="<?php echo $this->get_field_name( 'popup_goto' ); ?>">
				<option value="0"<?php selected( $popup_goto, 0 ); ?>><?php _e('in same window', YTCTDOM); ?></option>
				<option value="1"<?php selected( $popup_goto, 1 ); ?>><?php _e('in new window (JavaScript)', YTCTDOM); ?></option>
				<option value="2"<?php selected( $popup_goto, 2 ); ?>><?php _e('in new window (Target)', YTCTDOM); ?></option>
			</select>

			<input class="checkbox" type="checkbox" <?php checked( (bool) $userchan, true ); ?> id="<?php echo $this->get_field_id( 'userchan' ); ?>" name="<?php echo $this->get_field_name( 'userchan' ); ?>" /> <label for="<?php echo $this->get_field_id( 'userchan' ); ?>"><?php _e('Link to channel instead to user', YTCTDOM); ?></label><br />
		</p>

		<h4><?php _e('Debug YTC', YTCTDOM); ?></h4>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( (bool) $debugon, true ); ?> id="<?php echo $this->get_field_id( 'debugon' ); ?>" name="<?php echo $this->get_field_name( 'debugon' ); ?>" /> <label for="<?php echo $this->get_field_id( 'debugon' ); ?>">Enable debugging</label><br />

<?php
if ( $debugon == 'on' ) {
	global $wp_version;
	$debug_arr = array_merge(
		array(
			'server' => $_SERVER["SERVER_SOFTWARE"],
			'php'    => PHP_VERSION, //phpversion(),
			'wp'     => $wp_version, // get_bloginfo('version'),
			'ytc'    => YTCVER,
			'url'    => get_site_url()
		),
		$instance);
?>
			<textarea name="debug" class="widefat" style="height: 100px;"><?php echo au_ytc_dbg($debug_arr); ?></textarea><br />
			<small>Insert debug data to <a href="http://wordpress.org/support/plugin/youtube-channel" target="_support">support forum</a>.<br />Please do not remove channel and playlist ID's. If you are concerned about privacy, send this debug log to email <a href="mailto:urke.kg@gmail.com?subject=YTC%20debug%20log">urke.kg@gmail.com</a></small>
<?php } ?>
		</p>

		<p>
			<input type="button" value="Support YTC / Donate via PayPal" onclick="window.location='https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=Q6Q762MQ97XJ6'" class="button-secondary">
		</p>

		<?php
	}

	public function update($new_instance, $old_instance) {
		// processes widget options to be saved
		$instance                  = $old_instance;
		$instance['title']         = strip_tags($new_instance['title']);
		$instance['channel']       = strip_tags($new_instance['channel']);
		$instance['vidqty']        = $new_instance['vidqty'];
		$instance['playlist']      = strip_tags($new_instance['playlist']);
		$instance['use_res']       = $new_instance['use_res'];
		$instance['cache_time']    = $new_instance['cache_time'];
		$instance['getrnd']        = (isset($new_instance['getrnd'])) ? $new_instance['getrnd'] : false;
		$instance['maxrnd']        = $new_instance['maxrnd'];
		
		$instance['goto_txt']      = strip_tags($new_instance['goto_txt']);
		$instance['showgoto']      = (isset($new_instance['showgoto'])) ? $new_instance['showgoto'] : false;
		$instance['popup_goto']    = $new_instance['popup_goto'];
		
		$instance['showtitle']     = (isset($new_instance['showtitle'])) ? $new_instance['showtitle'] : false;
		$instance['showvidesc']    = (isset($new_instance['showvidesc'])) ? $new_instance['showvidesc'] : false;
		$instance['descappend']    = strip_tags($new_instance['descappend']);
		$instance['videsclen']     = strip_tags($new_instance['videsclen']);
		$instance['width']         = strip_tags($new_instance['width']);
		// $instance['height']        = strip_tags($new_instance['height']);
		$instance['to_show']       = strip_tags($new_instance['to_show']);

		$instance['fixnoitem']     = (isset($new_instance['fixnoitem'])) ? $new_instance['fixnoitem'] : false;
		$instance['ratio']         = strip_tags($new_instance['ratio']);
		$instance['hideinfo']      = (isset($new_instance['hideinfo'])) ? $new_instance['hideinfo'] : '';
		$instance['hideanno']      = (isset($new_instance['hideanno'])) ? $new_instance['hideanno'] : '';
		$instance['debugon']       = (isset($new_instance['debugon'])) ? $new_instance['debugon'] : '';
		$instance['userchan']      = (isset($new_instance['userchan'])) ? $new_instance['userchan'] : '';
		$instance['enhprivacy']    = (isset($new_instance['enhprivacy'])) ? $new_instance['enhprivacy'] : '';

		return $instance;
	}


	public static function youtube_channel_shortcode($attr)
	{
		$defaults = WPAU_YOUTUBE_CHANNEL::defaults();
		if (!empty($attr)) extract( $attr );
		$instance                  = array();
		$instance['channel']       = (empty($channel)) ? $defaults['channel'] : $channel;
		$instance['playlist']      = (empty($playlist)) ? $defaults['playlist'] : $playlist;
		$instance['use_res']       = (empty($res)) ? $defaults['use_res'] : $res; // resource: 0 channel, 1 favorites, 2 playlist
		$instance['cache_time']    = (empty($cache)) ? $defaults['cache_time'] : $cache; // in seconds, def 5min - settings?

		$instance['maxrnd']        = (empty($fetch)) ? $defaults['maxrnd'] : $fetch;
		$instance['vidqty']        = (empty($num)) ? $defaults['vidqty'] : $num; // num: 1

		$instance['fixnoitem']     = (empty($fix)) ? $defaults['fixnoitem'] : $fix; // fix noitem
		$instance['getrnd']        = (empty($random)) ? $defaults['getrnd'] : $random; // use embedded playlist - false by default

		// Video Settings
		$instance['ratio']         = (empty($ratio)) ? $defaults['ratio'] : $ratio; // aspect ratio: 3 - 16:9, 2 - 16:10, 1 - 4:3
		$instance['width']         = (empty($width)) ? $defaults['width'] : $width; // 220
		$instance['to_show']       = (empty($show)) ? $defaults['to_show'] : $show; // thumbnail, iframe, iframe2, object, chromeless
		
		// Content Layout
		$instance['showtitle']     = (empty($showtitle)) ? $defaults['showtitle'] : $showtitle; // show video title, disabled by default
		$instance['showvidesc']    = (empty($showdesc)) ? $defaults['showvidesc'] : $showdesc; // show video description, disabled by default
		$instance['videsclen']     = (empty($desclen)) ? $defaults['videsclen'] : $desclen; // cut video description, number of characters
		$instance['hideinfo']      = (empty($noinfo)) ? $defaults['hideinfo'] : $noinfo; // hide info by default
		$instance['hideanno']      = (empty($noanno)) ? $defaults['hideanno'] : $noanno; // hide annotations, false by default

		// Link to Channel
		$instance['showgoto']      = (empty($goto)) ? $defaults['showgoto'] : $goto; // show goto link, disabled by default
		$instance['goto_txt']      = (empty($goto_txt)) ? $defaults['goto_txt'] : $goto_txt; // text for goto link - use settings
		$instance['popup_goto']    = (empty($popup)) ? $defaults['popup_goto'] : $popup; // open channel in: 0 same window, 1 javascript new, 2 target new
		$instance['userchan']      = (empty($userchan)) ? $defaults['userchan'] : $userchan; // link to user channel instaled page
		
		// return implode(self::print_ytc($instance));
		return implode(array_values(self::print_ytc($instance)));
	}

	// print out widget
	public static function print_ytc($instance)
	{

		// set default channel if nothing predefined
		$channel = $instance['channel'];
		if ( $channel == "" ) $channel = YTCUID;

		// set playlist id
		$playlist = $instance['playlist'];
		if ( $playlist == "" ) $playlist = YTCPLID;

		// trim PL in front of playlist ID
		$playlist = preg_replace('/^PL/', '', $playlist);
		$use_res = $instance['use_res'];

		$output = array();

		$output[] = '<div class="youtube_channel">';

		// channel or playlist single videos

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
				$playlist = ytc_clean_playlist_id($playlist);
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

				// $json = file_get_contents($feed_url,0,null,null);
				// set decoded JSON to transient cache_key
				set_transient($cache_key, base64_encode($json), $instance['cache_time']);
			} else {
				// we already have cached feed JSON, get it encoded
				$json = base64_decode($json);
			}
		} else {
			// just get fresh feed if cache disabled
			// $json = file_get_contents($feed_url,0,null,null);
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
		if ( !is_wp_error($json_output) && is_object($json_output) ) {
			// sort by date uploaded
			$json_entry = $json_output->feed->entry;

			/* AU:20140216 for what we need this at all, when feed already have orderby=published */
			// do we need sort when use playlist?
			// if ( $use_res != 2 ) usort($json_entry, "ytc_json_sort_by_date"); 
			
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
			// $output[] = __( 'No items' , YTCTDOM );
			$output[] = __("No items", YTCTDOM).' [<a href="'.$feed_url.'" target="_blank">'.__("Check here why",YTCTDOM).'</a>]';
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
				$output = array_merge( $output, ytc_print_video($item, $instance, $y) );
			}

		}

		// single playlist or ytc way

		$output = array_merge( $output, ytc_channel_link($instance) ); // insert link to channel on bootom of widget

		$output[] = '</div><!-- .youtube_channel -->';

		return $output;
	}

	// TODO: make shortcode
	public function widget($args, $instance) {
		// outputs the content of the widget
		extract( $args );

		$title   = apply_filters('widget_title', $instance['title']);
		$output = array();
		$output[] = $before_widget;
		if ( $title ) $output[] = $before_title . $title . $after_title;
		$output[] = implode(self::print_ytc($instance));
		$output[] = $after_widget;

		echo implode('',array_values($output));
	}

} // class WPAU_YOUTUBE_CHANNEL()

if( class_exists('WPAU_YOUTUBE_CHANNEL'))
{
	// Installation and uninstallation hooks
	register_activation_hook(__FILE__, array('WPAU_YOUTUBE_CHANNEL', 'activate'));

	/* Load plugin's textdomain */
	add_action( 'init', 'youtube_channel_init' ); /*TODO: move inside class*/
	function youtube_channel_init() {
		load_plugin_textdomain( YTCTDOM, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/* Load YTC player script */
	function ytc_enqueue_scripts() {
		wp_enqueue_style( 'youtube-channel', plugins_url('assets/css/youtube-channel.css', __FILE__), array(), YTCVER );
	}
	add_action( 'wp_enqueue_scripts', 'ytc_enqueue_scripts' );
}

/* function to print video in widget */
function ytc_print_video($item, $instance, $y) {

	// get hideinfo, autoplay and controls settings
	// where this is used?
	$hideinfo      = $instance['hideinfo'];

	// set width and height
	$width  = ( empty($instance['width']) ) ? 220 : $instance['width'];
	$height = height_ratio($width, $instance['ratio']);

	// calculate image height based on width for 4:3 thumbnail
	$imgfixedheight = $width / 4 * 3;

	$hideanno   = $instance['hideanno'];
	/* end of video settings */

	$yt_id     = $item->link[0]->href;
	$yt_id     = preg_replace('/^.*=(.*)&.*$/', '${1}', $yt_id);
	$yt_url    = "v/$yt_id";
	
	$yt_thumb  = "http://img.youtube.com/vi/$yt_id/0.jpg"; // zero for HD thumb
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

	$output[] = '<div class="ytc_video_container ytc_video_'.$y.' ytc_video_'.$vnumclass.'">';

	// show video title?
	if ( $instance['showtitle'] )
		$output[] = '<h3 class="ytc_title">'.$yt_title.'</h3>';
		
	// define object ID
	$ytc_vid = 'ytc_' . $yt_id;

	// enhanced privacy
	$yt_domain = yt_domain($instance);

	// print out video
	// set proper class for responsive thumbs per selected aspect ratio
	switch ($instance['ratio'])
	{
		case 1: $arclass = 'ar4_3'; break;
		case 2: $arclass = 'ar16_10'; break;
		default: $arclass = 'ar16_9';
	}
	$title = sprintf( __( 'Watch video %1$s published on %2$s' , YTCTDOM ), $yt_title, $yt_date );
	$output[] = '<a href="'.$yt_video.'" title="'.$yt_title.'" class="ytc_thumb ytc-lightbox '.$arclass.'"><span style="background-image: url('.$yt_thumb.');" title="'.$title.'" id="'.$ytc_vid.'"></span></a>';

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
}

// function to calculate height by width and ratio
function height_ratio($width=220, $ratio) {
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
}

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
			$goto_txt = sprintf( __( 'Visit channel %1$s' , YTCTDOM ), $channel );
		else
			$goto_txt = str_replace('%channel%', $channel, $goto_txt);

		$output[] = '<div class="ytc_link">';
		$userchan = ( $instance['userchan'] ) ? 'channel' : 'user';
		$goto_url = 'http://www.youtube.com/'.$userchan.'/'.$channel.'/';
		$newtab = __("in new window/tab", "youtube-channel");
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
}

function ytc_clean_playlist_id($playlist) {
	if ( substr($playlist,0,4) == "http" ) {
		// if URL provided, extract playlist ID
		$playlist = preg_replace('/.*list=PL([A-Za-z0-9\-\_]*).*/','$1', $playlist);
	} else if ( substr($playlist,0,2) == 'PL' ) {
		$playlist = substr($playlist,2);
	}
	return $playlist;
}

/* Register plugin's widget */
function youtube_channel_register_widget() {
	register_widget( 'WPAU_YOUTUBE_CHANNEL' );
}
add_action( 'widgets_init', 'youtube_channel_register_widget' );

function au_ytc_dbg($arr) {
	$out = '';
	foreach ( $arr as $key => $val ) {
		if ( empty($val) ) { $val = 'null'; }
		$out .= $key . ': ' . $val . chr(13);
	}
	return $out;
}
// do we still need this?
function ytc_json_sort_by_date($a, $b) {
	$ap = $a->published;
	$bp = $b->published;
	return strnatcmp($bp, $ap);
}
function yt_domain($instance) {
	$yt_domain = ( !empty($instance['enhprivacy']) ) ? 'www.youtube-nocookie.com' : 'www.youtube.com';
	return $yt_domain;
}
