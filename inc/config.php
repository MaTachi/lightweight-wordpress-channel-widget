<?php
/**
	ReduxFramework YouTube Channel File
**/

if ( !class_exists( "ReduxFramework" ) ) {
	return;
} 

if ( !class_exists( "Redux_Framework_YouTube_Channel" ) ) {
	class Redux_Framework_YouTube_Channel {

		public $args = array();
		public $sections = array();
		public $ReduxFramework;

		public function __construct( ) {

			// Set the default arguments
			$this->setArguments();

			// Create the sections and fields
			$this->setSections();

			if ( !isset( $this->args['opt_name'] ) ) { // No errors please
				return;
			}

			// If Redux is running as a plugin, this will remove the demo notice and links
			add_action( 'redux/plugin/hooks', array( $this, 'remove_demo' ) );
			
			$this->ReduxFramework = new ReduxFramework($this->sections, $this->args);

		}			
		
		// Remove the demo link and the notice of integrated demo from the redux-framework plugin
		function remove_demo() {
			
			// Used to hide the demo mode link from the plugin page. Only used when Redux is a plugin.
			if ( class_exists('ReduxFrameworkPlugin') ) {
				remove_filter( 'plugin_row_meta', array( ReduxFrameworkPlugin::get_instance(), 'plugin_meta_demo_mode_link'), null, 2 );
			}

			// Used to hide the activation notice informing users of the demo panel. Only used when Redux is a plugin.
			remove_action('admin_notices', array( ReduxFrameworkPlugin::get_instance(), 'admin_notices' ) );	

		}

		public function setSections() {
			global $WPAU_YOUTUBE_CHANNEL;
			// ACTUAL DECLARATION OF SECTIONS

			$this->sections[] = array(
				'title'  => __('General', 'youtube-channel'),
				'icon'   => 'el-icon-home',
				'fields' => array(
					array(
						'id'      => 'channel',
						'type'    => 'text',
						'title'   => __('YouTube Channel ID', 'youtube-channel'),
						'desc'    => __('Enter your YouTube channel ID (channel name, not full URL to channel)', 'youtube-channel'),
						'default' => $WPAU_YOUTUBE_CHANNEL->channel_id
					),
					array(
						'id'      => 'playlist',
						'type'    => 'text',
						'title'   => __('Default Playlist ID', 'youtube-channel'),
						'desc'    => __('Enter default playlist ID (not playlist name)', 'youtube-channel'),
						'default' => $WPAU_YOUTUBE_CHANNEL->playlist_id
					),
					array(
						'id'      => 'use_res',
						'type'    => 'select',
						'title'   => __('Resource to use', 'youtube-channel'),
						'desc'    => __('What to use as resource for feeds', 'youtube-channel'),
						'options' => array(
							'0' => __('Channel', 'youtube-channel'),
							'1' => __('Favorites', 'youtube-channel'),
							'2' => __('Playlist', 'youtube-channel'),
						),
						'default' => '0'
					),
                    array(
                        'id'        => 'only_pl',
                        'type'      => 'checkbox',
                        'title'     => __('Embed standard playlist', 'youtube-channel'),
                        'desc'      => __('Enable this option to embed whole playlist instead single video from playlist when you chose playlist as resource', 'youtube-channel'),
                        'default'   => '0'// 1 = on | 0 = off
                    ),
					array(
						'id'      => 'cache_time',
						'type'    => 'select',
						'title'   => __('Cache Timeout', 'youtube-channel'),
						'desc'    => __('Define caching timeout for YouTube feeds, in seconds', 'youtube-channel'),

						'options' => array(
							'0'       => __('Do not chache', 'youtube-channel'),
							'60'      => __('1 minute', 'youtube-channel'),
							'300'     => __('5 minutes', 'youtube-channel'),
							'900'     => __('15 minutes', 'youtube-channel'),
							'1800'    => __('30 minutes', 'youtube-channel'),
							'3600'    => __('1 hour', 'youtube-channel'),
							'7200'    => __('2 hours', 'youtube-channel'),
							'18000'   => __('5 hours', 'youtube-channel'),
							'36000'   => __('10 hours', 'youtube-channel'),
							'43200'   => __('12 hours', 'youtube-channel'),
							'64800'   => __('18 hours', 'youtube-channel'),
							'86400'   => __('1 day', 'youtube-channel'),
							'172800'  => __('2 days', 'youtube-channel'),
							'259200'  => __('3 days', 'youtube-channel'),
							'345600'  => __('4 days', 'youtube-channel'),
							'432000'  => __('5 days', 'youtube-channel'),
							'518400'  => __('6 days', 'youtube-channel'),
							'604800'  => __('1 week', 'youtube-channel'),
							'1209600' => __('2 weeks', 'youtube-channel'),
							'1814400' => __('3 weeks', 'youtube-channel'),
							'2419200' => __('1 month', 'youtube-channel')
						),
						'default' => '0'
					),
					array(
						'id'      =>'maxrnd',
						'type'    => 'spinner',
						'title'   => __('Fetch', 'youtube-channel'), 
						'desc'    => __('Number of videos that will be used for random pick (min 2, max 50, default 25)', 'youtube-channel'),
						'default' => 25,
						'min'     => 2,
						'max'     => 50,
						'step'    => 1
					),
					array(
						'id'      =>'vidqty',
						'type'    => 'spinner',
						'title'   => __('Show', 'youtube-channel'), 
						'desc'    => __('Number of videos to display', 'youtube-channel'),
						'default' => 1,
						'min'     => 1,
						'max'     => 50,
						'step'    => 1
					),
					array(
						'id'       => 'enhprivacy',
						'type'     => 'checkbox',
						'title'    => __('Use Enhanced privacy', 'youtube-channel'), 
						'desc'     => sprintf(__('Enable this option to protect your visitors <a href="%s" target="_blank">privacy</a>.', 'youtube-channel'), 'http://support.google.com/youtube/bin/answer.py?hl=en-GB&answer=171780'),
						'default'  => 0
					),
					array(
						'id'       => 'fixnoitem',
						'type'     => 'checkbox',
						'title'    => __('Fix <em>No items</em> error/Respect playlist order', 'youtube-channel'), 
						'desc'     => __('Enable this option if you get error No Item', 'youtube-channel'),
						'default'  => 0
					),
					array(
						'id'       => 'getrnd',
						'type'     => 'checkbox',
						'title'    => __('Show random video', 'youtube-channel'), 
						'desc'     => __('Get random videos of all fetched from channel or playlist', 'youtube-channel'),
						'default'  => 0
					),

				)
			);

			$this->sections[] = array(
				'icon'   => 'el-icon-youtube',
				'title'  => __('Video', 'youtube-channel'),
				'fields' => array(

					array(
						'id'      => 'ratio',
						'type'    => 'select',
						'title'   => __('Aspect Ratio', 'youtube-channel'),
						'desc'    => __('Select aspect ratio for displayed video', 'youtube-channel'),
						
						'options' => array(
							'3' => __('16:9', 'youtube-channel'),
							'2' => __('16:10', 'youtube-channel'),
							'1' => __('4:3', 'youtube-channel')
						),
						'default' => '3'
					),
					array(
						'id'       => 'responsive',
						'type'     => 'checkbox',
						'title'    => __('Make responsive', 'youtube-channel'), 
						'desc'     => __('Enable this option to make all YTC videos responsive. Please note, this option will set all videos full width relative to parent container, and disable more than one video per row.', 'youtube-channel'),
						'default'  => 0
					),
					array(
						'id'      => 'width',
						'type'    => 'spinner',
						'title'   => __('Video Width', 'youtube-channel'), 
						'desc'    => __('Set default width for displayed video, in pixels', 'youtube-channel'), 
						'default' => 306,
						'min'     => 50,
						'max'     => 1980,
						'step'    => 1
					),
					array(
						'id'      => 'to_show',
						'type'    => 'select',
						'title'   => __('What to show?', 'youtube-channel'),
						'desc'    => __('Set what will be shown by default', 'youtube-channel'),
						
						'options' => array(
							'thumbnail'  => __('Thumbnail', 'youtube-channel'),
							'object'     => __('Flash (object)', 'youtube-channel'),
							'iframe'     => __('HTML5 (iframe)', 'youtube-channel'),
							'iframe2'    => __('HTML5 (iframe) Async', 'youtube-channel'),
							'chromeless' => __('Chromeless', 'youtube-channel')
						),
						'default' => 'thumbnail'
					),
					array(
						'id'       => 'themelight',
						'type'     => 'checkbox',
						'title'    => __('Use light theme', 'youtube-channel'), 
						'desc'     => __('Enable this option to use light theme for playback controls instead dark', 'youtube-channel'),
						'default'  => 0
					),
					array(
						'id'       => 'controls',
						'type'     => 'checkbox',
						'title'    => __('Hide player controls', 'youtube-channel'), 
						'desc'     => __('Enable this option to hide playback controls', 'youtube-channel'),
						'default'  => 0
					),
					array(
						'id'       => 'fixyt',
						'type'     => 'checkbox',
						'title'    => __('Fix video height', 'youtube-channel'), 
						'desc'     => __('Enable this option to fix video height when playback controls are not hidden', 'youtube-channel'),
						'default'  => 0
					),
					array(
						'id'       => 'autoplay',
						'type'     => 'checkbox',
						'title'    => __('Autoplay video or playlist', 'youtube-channel'), 
						'desc'     => __('Enable this option to start video playback right after block is rendered', 'youtube-channel'),
						'default'  => 0
					),
					array(
						'id'       => 'autoplay_mute',
						'type'     => 'checkbox',
						'title'    => __('Mute video on autoplay', 'youtube-channel'), 
						'desc'     => __('Enable this option to mute video when start autoplay', 'youtube-channel'),
						'default'  => 0
					),
					array(
						'id'       => 'norel',
						'type'     => 'checkbox',
						'title'    => __('Hide related videos', 'youtube-channel'), 
						'desc'     => __('Enable this option to hide related videos after finished playback', 'youtube-channel'),
						'default'  => 0
					),
					array(
						'id'       => 'modestbranding',
						'type'     => 'checkbox',
						'title'    => __('Hide YT Logo', 'youtube-channel'), 
						'desc'     => __('Enable this option to hide YouTube logo from playback control bar. Does not work for all videos.', 'youtube-channel'),
						'default'  => 0
					),

				)
			);

			$this->sections[] = array(
				'icon'   => 'el-icon-view-mode',
				'title'  => __('Content', 'youtube-channel'),
				'fields' => array(

					array(
						'id'       => 'showtitle',
						'type'     => 'checkbox',
						'title'    => __('Show video title', 'youtube-channel'), 
						'desc'     => __('Enable this option to display title of video', 'youtube-channel'),
						'default'  => 0
					),
					array(
						'id'       => 'showvidesc',
						'type'     => 'checkbox',
						'title'    => __('Show video description', 'youtube-channel'), 
						'desc'     => __('Enable this option to display description for video', 'youtube-channel'),
						'default'  => 0
					),
					array(
						'id'      =>'videsclen',
						'type'    => 'spinner',
						'title'   => __('Description length', 'youtube-channel'), 
						'desc'    => __('Enter length for video description in characters (0 for full length)', 'youtube-channel'), 
						'default' => 0,
						'min'     => 0,
						'max'     => 2500,
						'step'    => 1
					),
					array(
						'id'      => 'descappend',
						'type'    => 'text',
						'title'   => __('Et cetera string', 'youtube-channel'), 
						'desc'    => __('Indicator for shortened video description (default: …)', 'youtube-channel'),
						'default' => __('...', 'youtube-channel')
					),
					array(
						'id'       => 'hideanno',
						'type'     => 'checkbox',
						'title'    => __('Hide annotations from video', 'youtube-channel'), 
						'desc'     => __('Enable this option to hide video annotations (custom text set by uploader over video during playback)', 'youtube-channel'),
						'default'  => 0
					),
					array(
						'id'       => 'hideinfo',
						'type'     => 'checkbox',
						'title'    => __('Hide video info', 'youtube-channel'), 
						'desc'     => __('Enable this option to hide informations about video before play start (video title and uploader in overlay)', 'youtube-channel'),
						'default'  => 0
					),

				)
			);

			$this->sections[] = array(
				'icon'   => 'el-icon-link',
				'title'  => __('Link to Channel', 'youtube-channel'),
				'fields' => array(

					array(
						'id'      => 'goto_txt',
						'type'    => 'text',
						'title'   => __('Visit YouTube Channel text', 'youtube-channel'), 
						'desc'    => __('Use placeholder %channel% to insert channel name', 'youtube-channel'),
						'default' => __('Visit my channel %channel%', 'youtube-channel')
					),
					array(
						'id'       => 'showgoto',
						'type'     => 'checkbox',
						'title'    => __('Show link to channel', 'youtube-channel'), 
						'desc'     => __('Enable this option to show customized link to channel at the bottom of YTC block', 'youtube-channel'),
						'default'  => 0
					),
					array(
						'id'      => 'popup_goto',
						'type'    => 'select',
						'title'   => __('Open YouTube channel page', 'youtube-channel'),
						'desc'    => __('Set what will be shown by default', 'youtube-channel'),
						
						'options' => array(
							'0' => __('in same window', 'youtube-channel'),
							'1' => __('in new window (JavaScript)', 'youtube-channel'),
							'2' => __('in new window (Target)', 'youtube-channel')
						),
						'default' => '0'
					),
					array(
						'id'       => 'userchan',
						'type'     => 'checkbox',
						'title'    => __('Link to channel instead to user', 'youtube-channel'), 
						'desc'     => __('Enable this option if link to your channel have <code>/channel/</code> instead <code>/user/</code> part in URL', 'youtube-channel'),
						'default'  => 0
					),

				)
			);

			$this->sections[] = array(
				'icon'   => 'el-icon-magic',
				'title'  => __('Tools', 'youtube-channel'),
				'fields' => array(
                    array(
                        'id'        => 'recache',
                        'type'      => 'raw',
                        'markdown'  => false,
                        'content'   => file_get_contents(dirname(__FILE__) . '/settings_tools.php')
                    ),
				)
			);

			$this->sections[] = array(
				'type' => 'divide',
			);

			$this->sections[] = array(
				'icon'   => 'el-icon-question-sign',
				'title'  => __('Usage', 'youtube-channel'),
				'fields' => array(
					array(
						'id'      => 'implementation',
						'title'   => 'How to use '.$WPAU_YOUTUBE_CHANNEL->plugin_name,
						'type'    => 'raw',
						'content' => file_get_contents(dirname(__FILE__) . '/settings_usage.php')
					),
					array(
						'id'          => 'shortcode',
						'title'       => 'How to use shortcode',
						'subtitle'    => 'Shortcode parameters with default values',
						'type'        => 'raw',
						// 'raw_html' => true,
						'content'     => file_get_contents(dirname(__FILE__) . '/settings_usage_shortcode.php')
					)
				)
			);

			$this->sections[] = array(
				'icon'   => 'el-icon-group',
				'title'  => __('Support', 'youtube-channel'),
				'fields' => array(
					array(
						'id' => 'support',
						'type' => 'raw',
						// 'raw_html' => true,
						'content' => file_get_contents(dirname(__FILE__) . '/settings_support.php')
					),
				)
			);

		}	

		/**
			All the possible arguments for Redux.
			For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments

		 **/
		public function setArguments() {
			global $WPAU_YOUTUBE_CHANNEL;
			$theme = wp_get_theme(); // For use with some settings. Not necessary.

			$this->args = array(
	            
	            // TYPICAL -> Change these values as you need/desire
				'opt_name'          	=> $WPAU_YOUTUBE_CHANNEL->plugin_option, // This is where your data is stored in the database and also becomes your global variable name.
				'display_name'			=> $WPAU_YOUTUBE_CHANNEL->plugin_name, // Name that appears at the top of your panel
				'display_version'		=> $WPAU_YOUTUBE_CHANNEL->plugin_version, // Version that appears at the top of your panel
				'menu_type'          	=> 'submenu', //Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
				'allow_sub_menu'     	=> true, // Show the sections below the admin menu item or not
				'menu_title'			=> __( $WPAU_YOUTUBE_CHANNEL->plugin_name, $WPAU_YOUTUBE_CHANNEL->plugin_slug ),
	            'page'		 	 		=> __( $WPAU_YOUTUBE_CHANNEL->plugin_name.' Options', $WPAU_YOUTUBE_CHANNEL->plugin_slug ),
	            'google_api_key'   	 	=> '', // Must be defined to add google fonts to the typography module
	            'global_variable'    	=> '', // Set a different name for your global variable other than the opt_name
	            'dev_mode'           	=> false, // Show the time the page took to load, etc
	            'customizer'         	=> true, // Enable basic customizer support

	            // OPTIONAL -> Give you extra features
	            'page_priority'      	=> null, // Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
	            'page_parent'        	=> 'options-general.php', // For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
	            'page_permissions'   	=> 'manage_options', // Permissions needed to access the options panel.
	            'menu_icon'          	=> '', // Specify a custom URL to an icon
	            'last_tab'           	=> '', // Force your panel to always open to a specific tab (by id)
	            'page_icon'          	=> 'icon-settings', // Icon displayed in the admin panel next to your menu_title
	            'page_slug'          	=> $WPAU_YOUTUBE_CHANNEL->plugin_slug, // Page slug used to denote the panel
	            'save_defaults'      	=> true, // On load save the defaults to DB before user clicks save or not
	            'default_show'       	=> false, // If true, shows the default value next to each field that is not the default value.
	            'default_mark'       	=> '', // What to print by the field's title if the value shown is default. Suggested: *


	            // CAREFUL -> These options are for advanced use only
	            'transient_time' 	 	=> 60 * MINUTE_IN_SECONDS,
	            'output'            	=> true, // Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
	            'output_tag'            => true, // Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
	            //'domain'             	=> 'redux-framework', // Translation domain key. Don't change this unless you want to retranslate all of Redux.
	            //'footer_credit'      	=> '', // Disable the footer credit of Redux. Please leave if you can help it.
	            

	            // FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
	            'database'           	=> '', // possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
	            
	        
	            'show_import_export' 	=> true, // REMOVE
	            'system_info'        	=> false, // REMOVE
	            
	            'help_tabs'          	=> array(),
	            'help_sidebar'       	=> '', // __( '', $this->args['domain'] );            
				);


			// SOCIAL ICONS -> Setup custom links in the footer for quick links in your panel footer icons.		
			$this->args['share_icons'][] = array(
			    'url' => 'https://www.facebook.com/urosevic',
			    'title' => 'Author on Facebook', 
			    'icon' => 'el-icon-facebook'
			);
			$this->args['share_icons'][] = array(
			    'url' => 'http://twitter.com/urosevic',
			    'title' => 'Add me on Twitter', 
			    'icon' => 'el-icon-twitter'
			);
			$this->args['share_icons'][] = array(
			    'url' => 'http://rs.linkedin.com/in/aurosevic',
			    'title' => 'Find me on LinkedIn', 
			    'icon' => 'el-icon-linkedin'
			);
			$this->args['share_icons'][] = array(
			    'url' => 'http://youtube.com/user/urkekg',
			    'title' => 'Subscribe to my YouTube', 
			    'icon' => 'el-icon-youtube'
			);
			$this->args['share_icons'][] = array(
			    'url' => 'http://urosevic.net/wordpress/plugins/youtube-channel/',
			    'title' => 'Visit official plugin site', 
			    'icon' => 'el-icon-home-alt'
			);

			// Panel Intro text -> before the form
			if (!isset($this->args['global_variable']) || $this->args['global_variable'] !== false ) {
				if (!empty($this->args['global_variable'])) {
					$v = $this->args['global_variable'];
				} else {
					$v = str_replace("-", "_", $this->args['opt_name']);
				}
				$this->args['intro_text'] = '<p style="float:right;"><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=Q6Q762MQ97XJ6" target="_blank" title="Donate via PayPal - The safer, easier way to pay online!"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" style="width:147px;height:47px;border:0" alt="PayPal - The safer, easier way to pay online!"></a></p>';
				$this->args['intro_text'] .= __('<p>Easy embed playable videos from YouTube.</p><p>Here you can set default options that will be used as defaults for new widgets, and for shortcode.</p>', 'youtube-channel' );
				$this->args['intro_text'] .='<p>&nbsp;</p>';
			}

		}
	}
    global $reduxConfig;
    $reduxConfig = new Redux_Framework_YouTube_Channel();
}
