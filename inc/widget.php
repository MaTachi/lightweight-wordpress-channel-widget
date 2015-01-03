<?php
/**
 * The widget that WordPress will register.
 */
class LYCW_Widget extends WP_Widget {

	public function __construct() {
		global $LYCW;
		// Initialize Widget
		parent::__construct(
			$LYCW->plugin_slug,
			__( 'Youtube Channel', 'youtube-channel' ),
			array(
				'description' => __(
					'Show YouTube video thumbnails from a channel or playlist',
					'youtube-channel'
				)
			)
		);
	}

	public function widget( $args, $instance ) {
		global $LYCW;
		// outputs the content of the widget
		extract( $args );

		$title = apply_filters( 'widget_title', $instance['title'] );

		$output = array();
		$output[] = $before_widget;
		if ( $title ) {
			$output[] = $before_title . $title . $after_title;
		}
		$output[] = implode($LYCW->output( $instance ));
		$output[] = $after_widget;

		echo implode( '', array_values( $output ) );
	}

	public function form( $instance ) {
		global $LYCW;
		// outputs the options form for widget settings
		// General Options
		$title         = (!empty($instance['title'])) ? esc_attr($instance['title']) : '';
		$class         = (!empty($instance['class'])) ? esc_attr($instance['class']) : '';
		$channel       = (!empty($instance['channel'])) ? esc_attr($instance['channel']) : '';
		$playlist      = (!empty($instance['playlist'])) ? esc_attr($instance['playlist']) : '';

		$type_of_resource = (!empty($instance['type_of_resource'])) ? esc_attr($instance['type_of_resource']) : 'channel'; // resource to use: channel, favorites, playlist

		$cache_time    = (!empty($instance['cache_time'])) ? esc_attr($instance['cache_time']) : '';

		$fetch_videos  = (!empty($instance['fetch_videos'])) ? esc_attr($instance['fetch_videos']) : 5; // items to fetch
		$show_videos   = (!empty($instance['show_videos'])) ? esc_attr($instance['show_videos']) : 1; // number of items to show

		$fix_no_items  = (!empty($instance['fix_no_items'])) ? esc_attr($instance['fix_no_items']) : '';
		$randomize_videos = (!empty($instance['randomize_videos'])) ? esc_attr($instance['randomize_videos']) : '';

		// Video Settings
		$ratio         = (!empty($instance['ratio'])) ? esc_attr($instance['ratio']) : 3;
		$width         = (!empty($instance['width'])) ? esc_attr($instance['width']) : 306;
		$responsive    = (!empty($instance['responsive'])) ? esc_attr($instance['responsive']) : 0;

		// Content Layout
		$showtitle     = (!empty($instance['showtitle'])) ? esc_attr($instance['showtitle']) : '';
		$showvidesc    = (!empty($instance['showvidesc'])) ? esc_attr($instance['showvidesc']) : '';
		$videsclen     = (!empty($instance['videsclen'])) ? esc_attr($instance['videsclen']) : 0;
		$descappend    = (!empty($instance['descappend'])) ? esc_attr($instance['descappend']) : '&hellip;';
		?>

		<p>
			<label for="<?php echo $this->get_field_id('title');	?>"><?php _e('Widget Title', 'youtube-channel');	?>:<input type="text" class="widefat" id="<?php echo $this->get_field_id('title');		?>" name="<?php echo $this->get_field_name('title');	?>" value="<?php echo $title;		?>" title="<?php _e('Title for widget', 'youtube-channel'); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('class');	?>"><?php _e('Custom CSS Class', 'youtube-channel'); ?>:<input type="text" class="widefat" id="<?php echo $this->get_field_id('class');		?>" name="<?php echo $this->get_field_name('class');	?>" value="<?php echo $class;		?>" title="<?php _e('Enter custom class for YTC block, if you wish to target block styling', 'youtube-channel'); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('channel');	?>"><?php _e('Channel ID', 'youtube-channel'); ?>:<input type="text" class="widefat" id="<?php echo $this->get_field_id('channel');		?>" name="<?php echo $this->get_field_name('channel');	?>" value="<?php echo $channel;		?>" title="<?php _e('YouTube Channel name (not URL to channel)', 'youtube-channel'); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('playlist');	?>"><?php _e('Playlist ID', 'youtube-channel'); ?>:<input type="text" class="widefat" id="<?php echo $this->get_field_id('playlist');	?>" name="<?php echo $this->get_field_name('playlist'); ?>" value="<?php echo $playlist;	?>" title="<?php _e('YouTube Playlist ID (not playlist name)', 'youtube-channel'); ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('type_of_resource'); ?>"><?php _e('Resource to use', 'youtube-channel'); ?>:</label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'type_of_resource' ); ?>" name="<?php echo $this->get_field_name( 'type_of_resource' ); ?>">
				<option value="channel"<?php selected( $type_of_resource, 'channel' ); ?>><?php _e('Channel', 'youtube-channel'); ?></option>
				<option value="favorites"<?php selected( $type_of_resource, 'favorites' ); ?>><?php _e('Favorites', 'youtube-channel'); ?></option>
				<option value="playlist"<?php selected( $type_of_resource, 'playlist' ); ?>><?php _e('Playlist', 'youtube-channel'); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('cache_time');	?>"><?php _e('Cache feed', 'youtube-channel'); ?>:</label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'cache_time' ); ?>" name="<?php echo $this->get_field_name( 'cache_time' ); ?>">
				<option value="0"<?php selected( $cache_time, 0 ); ?>><?php _e('Do not cache', 'youtube-channel'); ?></option>
				<?php echo $this->cache_time( $cache_time ); ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('fetch_videos'); ?>"><?php _e('Fetch', 'youtube-channel'); ?>: <input class="small-text" id="<?php echo $this->get_field_id('fetch_videos'); ?>" name="<?php echo $this->get_field_name('fetch_videos'); ?>" type="number" min="2" value="<?php echo $fetch_videos; ?>" title="<?php _e('Number of videos that will be used for random pick (min 2, max 50, default 25)', 'youtube-channel'); ?>" /> <?php _e('video(s)', 'youtube-channel'); ?></label>
			<br />
			<label for="<?php echo $this->get_field_id('show_videos'); ?>"><?php _e('Show', 'youtube-channel'); ?>:</label> <input class="small-text" id="<?php echo $this->get_field_id('show_videos'); ?>" name="<?php echo $this->get_field_name('show_videos'); ?>" type="number" min="1" value="<?php echo ( $show_videos ) ? $show_videos : '1'; ?>" title="<?php _e('Number of videos to display', 'youtube-channel'); ?>" /> <?php _e('video(s)', 'youtube-channel'); ?>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( (bool) $fix_no_items, true ); ?> id="<?php echo $this->get_field_id( 'fix_no_items' ); ?>" name="<?php echo $this->get_field_name( 'fix_no_items' ); ?>" title="<?php _e('Enable this option if you get error No Item', 'youtube-channel'); ?>" /> <label for="<?php echo $this->get_field_id( 'fix_no_items' ); ?>"><?php _e('Fix <em>No items</em> error/Respect playlist order', 'youtube-channel'); ?></label>
			<br />
			<input class="checkbox" type="checkbox" <?php checked( (bool) $randomize_videos, true ); ?> id="<?php echo $this->get_field_id( 'randomize_videos' ); ?>" name="<?php echo $this->get_field_name( 'randomize_videos' ); ?>" title="<?php _e('Get random videos of all fetched from channel or playlist', 'youtube-channel'); ?>" /> <label for="<?php echo $this->get_field_id( 'randomize_videos' ); ?>"><?php _e('Show random video', 'youtube-channel'); ?></label>
		</p>
		
		<h4><?php _e('Video Settings', 'youtube-channel'); ?></h4>
		<p><label for="<?php echo $this->get_field_id('ratio'); ?>"><?php _e('Aspect ratio', 'youtube-channel'); ?>:</label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'ratio' ); ?>" name="<?php echo $this->get_field_name( 'ratio' ); ?>">
				<option value="3"<?php selected( $ratio, 3 ); ?>>16:9</option>
				<option value="2"<?php selected( $ratio, 2 ); ?>>16:10</option>
				<option value="1"<?php selected( $ratio, 1 ); ?>>4:3</option>
			</select><br />
			<input class="checkbox" type="checkbox" <?php checked( (bool) $responsive, true ); ?> id="<?php echo $this->get_field_id( 'responsive' ); ?>" name="<?php echo $this->get_field_name( 'responsive' ); ?>" /> <label for="<?php echo $this->get_field_id( 'responsive' ); ?>"><?php _e('Responsive video (distribute one full width video per row)', 'youtube-channel'); ?></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width', 'youtube-channel'); ?>:</label> <input class="small-text" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="number" min="32" value="<?php echo $width; ?>" title="<?php _e('Set video width in pixels', 'youtube-channel'); ?>" /> px (<?php _e('default', 'youtube-channel'); ?> 306)
		</p>

		<h4><?php _e('Content Layout', 'youtube-channel'); ?></h4>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( (bool) $showtitle, true ); ?> id="<?php echo $this->get_field_id( 'showtitle' ); ?>" name="<?php echo $this->get_field_name( 'showtitle' ); ?>" /> <label for="<?php echo $this->get_field_id( 'showtitle' ); ?>"><?php _e('Show video title', 'youtube-channel'); ?></label><br />
			<input class="checkbox" type="checkbox" <?php checked( (bool) $showvidesc, true ); ?> id="<?php echo $this->get_field_id( 'showvidesc' ); ?>" name="<?php echo $this->get_field_name( 'showvidesc' ); ?>" /> <label for="<?php echo $this->get_field_id( 'showvidesc' ); ?>"><?php _e('Show video description', 'youtube-channel'); ?></label><br />
			<label for="<?php echo $this->get_field_id('videsclen'); ?>"><?php _e('Description length', 'youtube-channel'); ?>: <input class="small-text" id="<?php echo $this->get_field_id('videsclen'); ?>" name="<?php echo $this->get_field_name('videsclen'); ?>" type="number" value="<?php echo $videsclen; ?>" title="<?php _e('Set number of characters to cut down video description to (0 means full length)', 'youtube-channel');?>" /> (0 = full)</label><br />
			<label for="<?php echo $this->get_field_id('descappend'); ?>"><?php _e('Et cetera string', 'youtube-channel'); ?> <input class="small-text" id="<?php echo $this->get_field_id('descappend'); ?>" name="<?php echo $this->get_field_name('descappend'); ?>" type="text" value="<?php echo $descappend; ?>" title="<?php _e('Default: &amp;hellip;', 'youtube-channel'); ?>"/></label><br />
		</p>

<?php
	}

	public function update($new_instance, $old_instance) {
		// processes widget options to be saved
		$instance                  = $old_instance;
		$instance['title']         = strip_tags($new_instance['title']);
		$instance['class']         = strip_tags($new_instance['class']);
		$instance['channel']       = strip_tags($new_instance['channel']);
		$instance['show_videos']   = $new_instance['show_videos'];
		$instance['playlist']      = strip_tags($new_instance['playlist']);
		$instance['type_of_resource'] = $new_instance['type_of_resource'];
		$instance['cache_time']    = $new_instance['cache_time'];
		$instance['randomize_videos'] = (isset($new_instance['randomize_videos'])) ? $new_instance['randomize_videos'] : false;
		if (
			isset( $new_instance['fetch_videos'] ) &&
			is_numeric( $new_instance['fetch_videos'] )
		) {
			if ( $new_instance['fetch_videos'] > 50 ) {
				$instance['fetch_videos'] = 50;
			} else if ( $new_instance['fetch_videos'] < 1 ) {
				$instance['fetch_videos'] = 1;
			} else {
				$instance['fetch_videos'] = $new_instance['fetch_videos'];
			}
		} else {
			$instance['fetch_videos'] = 5;
		}
		
		$instance['showtitle']     = (isset($new_instance['showtitle'])) ? $new_instance['showtitle'] : false;
		$instance['showvidesc']    = (isset($new_instance['showvidesc'])) ? $new_instance['showvidesc'] : false;
		$instance['descappend']    = strip_tags($new_instance['descappend']);
		$instance['videsclen']     = strip_tags($new_instance['videsclen']);
		$instance['width']         = strip_tags($new_instance['width']);
		$instance['responsive']    = (isset($new_instance['responsive'])) ? $new_instance['responsive'] : '';

		$instance['fix_no_items']  = (isset($new_instance['fix_no_items'])) ? $new_instance['fix_no_items'] : false;
		$instance['ratio']         = strip_tags($new_instance['ratio']);

		return $instance;
	}

	function debug_string($arr) {
		$out = '';
		foreach ( $arr as $key => $val ) {
			if ( empty($val) ) { $val = 'null'; }
			$out .= $key . ': ' . $val . chr(13);
		}
		return $out;
	}

	/**
	 * Get string of cache time dropdown menu alternatives.
	 *
	 * @param int $cache_time The selected cache length in seconds.
	 * @return string HTML string with all selectable cache length options.
	 */
	private function cache_time( $cache_time ) {
		$times = array(
			'minute' => array(
				1  => __('1 minute', 'youtube-channel'),
				5  => __('5 minutes', 'youtube-channel'),
				15 => __('15 minutes', 'youtube-channel'),
				30 => __('30 minutes', 'youtube-channel')
			),
			'hour' => array(
				1  => __('1 hour', 'youtube-channel'),
				2  => __('2 hours', 'youtube-channel'),
				5  => __('5 hours', 'youtube-channel'),
				10 => __('10 hours', 'youtube-channel'),
				12 => __('12 hours', 'youtube-channel'),
				18 => __('18 hours', 'youtube-channel')
			),
			'day' => array(
				1 => __('1 day', 'youtube-channel'),
				2 => __('2 days', 'youtube-channel'),
				3 => __('3 days', 'youtube-channel'),
				4 => __('4 days', 'youtube-channel'),
				5 => __('5 days', 'youtube-channel'),
				6 => __('6 days', 'youtube-channel')
			),
			'week' => array(
				1 => __('1 week', 'youtube-channel'),
				2 => __('2 weeks', 'youtube-channel'),
				3 => __('3 weeks', 'youtube-channel'),
				4 => __('1 month', 'youtube-channel')
			)
		);

		$out = '';
		foreach ( $times as $period => $timeset ) {
			switch ($period) {
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
			}

			foreach ( $timeset as $n => $s ) {
				$sec = $sc * $n;
				$out .= sprintf(
					'<option value="%d" %s>%s</option>',
					$sec,
					selected( $cache_time, $sec, 0 ),
					__( $s, $this->plugin_slug )
				);
				unset($sec);
			}
		}
		return $out;
	}

}

// Register widget.
function lycw_register_widget() {
    register_widget( 'LYCW_Widget' );
}
add_action( 'widgets_init', 'lycw_register_widget' );
