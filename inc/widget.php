<?php

/* youtube widget */
class WPAU_YOUTUBE_CHANNEL_Widget extends WP_Widget {

	public function __construct() {
		global $WPAU_YOUTUBE_CHANNEL;
		// Initialize Widget
		parent::__construct(
			$WPAU_YOUTUBE_CHANNEL->plugin_slug,
			__( 'Youtube Channel' , 'youtube-channel' ),
			array(
				'description' => __(
					'Show YouTube video thumbnails from a channel or playlist',
					'youtube-channel'
				)
			)
		);
	}

	public function widget( $args, $instance ) {
		global $WPAU_YOUTUBE_CHANNEL;
		// outputs the content of the widget
		extract( $args );

		$title = apply_filters( 'widget_title' , $instance['title'] );

		$output = array();
		$output[] = $before_widget;
		if ( $title ) {
			$output[] = $before_title . $title . $after_title;
		}
		$output[] = implode($WPAU_YOUTUBE_CHANNEL->output($instance));
		$output[] = $after_widget;

		echo implode('',array_values($output));
	}

	public function form( $instance ) {
		global $WPAU_YOUTUBE_CHANNEL;
		// outputs the options form for widget settings
		// General Options
		$title         = (!empty($instance['title'])) ? esc_attr($instance['title']) : '';
		$class         = (!empty($instance['class'])) ? esc_attr($instance['class']) : '';
		$channel       = (!empty($instance['channel'])) ? esc_attr($instance['channel']) : '';
		$playlist      = (!empty($instance['playlist'])) ? esc_attr($instance['playlist']) : '';

		$use_res       = (!empty($instance['use_res'])) ? esc_attr($instance['use_res']) : 0; // resource to use: channel, favorites, playlist

		$cache_time    = (!empty($instance['cache_time'])) ? esc_attr($instance['cache_time']) : '';

		$maxrnd        = (!empty($instance['maxrnd'])) ? esc_attr($instance['maxrnd']) : 25; // items to fetch
		$vidqty        = (!empty($instance['vidqty'])) ? esc_attr($instance['vidqty']) : 1; // number of items to show

		$fixnoitem     = (!empty($instance['fixnoitem'])) ? esc_attr($instance['fixnoitem']) : '';
		$getrnd        = (!empty($instance['getrnd'])) ? esc_attr($instance['getrnd']) : '';

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
			<label for="<?php echo $this->get_field_id('use_res');	?>"><?php _e('Resource to use', 'youtube-channel'); ?>:</label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'use_res' ); ?>" name="<?php echo $this->get_field_name( 'use_res' ); ?>">
				<option value="0"<?php selected( $use_res, 0 ); ?>><?php _e('Channel', 'youtube-channel'); ?></option>
				<option value="1"<?php selected( $use_res, 1 ); ?>><?php _e('Favorites', 'youtube-channel'); ?></option>
				<option value="2"<?php selected( $use_res, 2 ); ?>><?php _e('Playlist', 'youtube-channel'); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('cache_time');	?>"><?php _e('Cache feed', 'youtube-channel'); ?>:</label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'cache_time' ); ?>" name="<?php echo $this->get_field_name( 'cache_time' ); ?>">
				<option value="0"<?php selected( $cache_time, 0 ); ?>><?php _e('Do not cache', 'youtube-channel'); ?></option>
				<?php echo $WPAU_YOUTUBE_CHANNEL->cache_time($cache_time); ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('maxrnd'); ?>"><?php _e('Fetch', 'youtube-channel'); ?>: <input class="small-text" id="<?php echo $this->get_field_id('maxrnd'); ?>" name="<?php echo $this->get_field_name('maxrnd'); ?>" type="number" min="2" value="<?php echo $maxrnd; ?>" title="<?php _e('Number of videos that will be used for random pick (min 2, max 50, default 25)', 'youtube-channel'); ?>" /> <?php _e('video(s)', 'youtube-channel'); ?></label>
			<br />
			<label for="<?php echo $this->get_field_id('vidqty'); ?>"><?php _e('Show', 'youtube-channel'); ?>:</label> <input class="small-text" id="<?php echo $this->get_field_id('vidqty'); ?>" name="<?php echo $this->get_field_name('vidqty'); ?>" type="number" min="1" value="<?php echo ( $vidqty ) ? $vidqty : '1'; ?>" title="<?php _e('Number of videos to display', 'youtube-channel'); ?>" /> <?php _e('video(s)', 'youtube-channel'); ?>
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( (bool) $fixnoitem, true ); ?> id="<?php echo $this->get_field_id( 'fixnoitem' ); ?>" name="<?php echo $this->get_field_name( 'fixnoitem' ); ?>" title="<?php _e('Enable this option if you get error No Item', 'youtube-channel'); ?>" /> <label for="<?php echo $this->get_field_id( 'fixnoitem' ); ?>"><?php _e('Fix <em>No items</em> error/Respect playlist order', 'youtube-channel'); ?></label>
			<br />
			<input class="checkbox" type="checkbox" <?php checked( (bool) $getrnd, true ); ?> id="<?php echo $this->get_field_id( 'getrnd' ); ?>" name="<?php echo $this->get_field_name( 'getrnd' ); ?>" title="<?php _e('Get random videos of all fetched from channel or playlist', 'youtube-channel'); ?>" /> <label for="<?php echo $this->get_field_id( 'getrnd' ); ?>"><?php _e('Show random video', 'youtube-channel'); ?></label>
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
		$instance['responsive']    = (isset($new_instance['responsive'])) ? $new_instance['responsive'] : '';

		$instance['fixnoitem']     = (isset($new_instance['fixnoitem'])) ? $new_instance['fixnoitem'] : false;
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

} // end class WPAU_YOUTUBE_CHANNEL_Widget()


// register Foo_Widget widget
function wpau_register_youtube_channel_widget() {
    register_widget( 'WPAU_YOUTUBE_CHANNEL_Widget' );
}
add_action( 'widgets_init', 'wpau_register_youtube_channel_widget' );
