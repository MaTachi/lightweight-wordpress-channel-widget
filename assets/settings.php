<?php
if(class_exists('WPAU_YOUTUBE_CHANNEL') && !class_exists('WPAU_YOUTUBE_CHANNEL_SETTINGS'))
{
    
    class WPAU_YOUTUBE_CHANNEL_SETTINGS extends WPAU_YOUTUBE_CHANNEL
    {
        private $general_settings_key = 'ytc_general';
        private $video_settings_key   = 'ytc_video';
        private $content_settings_key = 'ytc_content';
        private $link_settings_key    = 'ytc_link';
        private $tools_settings_key   = 'ytc_tools';
        private $help_settings_key    = 'ytc_help';
        private $plugin_options_key   = 'youtube_channel_defaults';
        private $plugin_settings_page = YTCTDOM;
        private $plugin_settings_tabs = array();

        /**
         * Construct the plugin object
		 */
		public function __construct()
		{
			// register actions
            add_action('init', array( &$this, 'load_settings' ) );

            add_action('admin_init', array( &$this, 'register_general_settings' ) );
            add_action('admin_init', array( &$this, 'register_video_settings' ) );
            add_action('admin_init', array( &$this, 'register_content_settings' ) );
            add_action('admin_init', array( &$this, 'register_link_settings' ) );
            add_action('admin_init', array( &$this, 'register_tools_settings' ) );
            add_action('admin_init', array( &$this, 'register_help_settings' ) );

        	add_action('admin_menu', array( &$this, 'add_menu' ) );
		} // END public function __construct
		
        function load_settings() {
            $this->defaults = $this->defaults();
        }

        // validate our options
        function plugin_options_validate($options) {
            $options = wp_parse_args($options, get_option('youtube_channel_defaults'));
            return wp_parse_args($options, $this->defaults);
        }
        function register_general_settings()
        {
            $this->plugin_settings_tabs[$this->general_settings_key] = 'General';
            register_setting(
                $this->general_settings_key, // option_group
                $this->plugin_options_key, // option_name
                array(&$this, 'plugin_options_validate') // callback
            );

            // add general settings section
            add_settings_section(
                'general_settings', // id
                __('General Settings',YTCTDOM),  // title
                array(&$this, 'general_settings_section_description'), // callback
                $this->general_settings_key // page
            );
            
            // add setting's fields
            add_settings_field(
                'wpau_youtube_channel-channel', // id
                __('YouTube Channel ID',YTCTDOM), // title
                array(&$this, 'settings_field_input_text'), // callback
                $this->general_settings_key, // page
                'general_settings', // section
                array(
                    'field'       => "youtube_channel_defaults[channel]",
                    'description' => __('Enter your YouTube channel ID (channel name, not full URL to channel)',YTCTDOM),
                    'class'       => 'regular-text',
                    'value'       => $this->defaults['channel'],
                )
            );
            add_settings_field(
                'wpau_youtube_channel-playlist', 
                __('Default Playlist ID',YTCTDOM), 
                array(&$this, 'settings_field_input_text'), 
                $this->general_settings_key,
                'general_settings',
                array(
                    'field'       => "youtube_channel_defaults[playlist]",
                    'description' => __('Enter default playlist ID (not playlist name)',YTCTDOM),
                    'class'       => 'regular-text',
                    'value'       => $this->defaults['playlist'],
                )
            );
            add_settings_field(
                'wpau_youtube_channel-use_res', 
                __('Resource to use',YTCTDOM), 
                array(&$this, 'settings_field_select'), 
                $this->general_settings_key,
                'general_settings',
                array(
                    'field'       => "youtube_channel_defaults[use_res]",
                    'description' => __('What to use as resource for feeds',YTCTDOM),
                    'class'       => 'regular-text',
                    'items'       => array(
                        "0" => __("Channel",YTCTDOM),
                        "1" => __("Favorites",YTCTDOM),
                        "2" => __("Playlist",YTCTDOM)
                    ),
                    'value' => $this->defaults['use_res'],
                )
            );
            add_settings_field(
                'wpau_youtube_channel-only_pl', 
                __('Embed standard playlist',YTCTDOM), 
                array(&$this, 'settings_field_checkbox'), 
                $this->general_settings_key,
                'general_settings',
                array(
                    'field'       => "youtube_channel_defaults[only_pl]",
                    'description' => __('Enable this option to embed whole playlist instead single video from playlist when you chose playlist as resource',YTCTDOM),
                    'class'       => 'checkbox',
                    'value'       => $this->defaults['only_pl'],
                )
            );
            // caching timeout field
            add_settings_field(
                'wpau_youtube_channel-cache_time', 
                __('Cache Timeout',YTCTDOM), 
                array(&$this, 'settings_field_input_number'), 
                $this->general_settings_key,
                'general_settings',
                array(
                    'field'       => "youtube_channel_defaults[cache_time]",
                    'description' => __('Define caching timeout for YouTube feeds, in seconds',YTCTDOM),
                    'class'       => 'number',
                    'value'       => $this->defaults['cache_time'],
                    'min'         => 0,
                    'max'         => 2419200,
                    'step'        => 60
                )
            );
            add_settings_field(
                'wpau_youtube_channel-maxrnd', 
                __('Fetch',YTCTDOM), 
                array(&$this, 'settings_field_input_number'), 
                $this->general_settings_key,
                'general_settings',
                array(
                    'field'       => "youtube_channel_defaults[maxrnd]",
                    'description' => __('Number of videos that will be used for random pick (min 2, max 50, default 25)',YTCTDOM),
                    'class'       => 'num',
                    'value'       => $this->defaults['maxrnd'],
                    'min'         => 2,
                    'max'         => 50,
                    'step'        => 1
                )
            );
            add_settings_field(
                'wpau_youtube_channel-vidqty', 
                __('Show',YTCTDOM), 
                array(&$this, 'settings_field_input_number'), 
                $this->general_settings_key,
                'general_settings',
                array(
                    'field'       => "youtube_channel_defaults[vidqty]",
                    'description' => __('Number of videos to display',YTCTDOM),
                    'class'       => 'number',
                    'value'       => $this->defaults['vidqty'],
                    'min'         => 1,
                    'max'         => 50,
                    'step'        => 1
                )
            );
            add_settings_field(
                'wpau_youtube_channel-enhprivacy', 
                __('Use Enhanced privacy',YTCTDOM), 
                array(&$this, 'settings_field_checkbox'), 
                $this->general_settings_key,
                'general_settings',
                array(
                    'field'       => "youtube_channel_defaults[enhprivacy]",
                    'description' => __(sprintf('Enable this option to protect your visitors <a href="%s" target="_blank">privacy</a>.','http://support.google.com/youtube/bin/answer.py?hl=en-GB&answer=171780'),YTCTDOM),
                    'class'       => 'number',
                    'value'       => $this->defaults['enhprivacy'],
                )
            );
            add_settings_field(
                'wpau_youtube_channel-fixnoitem', 
                __('Fix <em>No items</em> error/Respect playlist order',YTCTDOM), 
                array(&$this, 'settings_field_checkbox'), 
                $this->general_settings_key,
                'general_settings',
                array(
                    'field'       => "youtube_channel_defaults[fixnoitem]",
                    'description' => __('Enable this option if you get error No Item',YTCTDOM),
                    'class'       => 'widefat',
                    'value'       => $this->defaults['fixnoitem'],
                )
            );
            add_settings_field(
                'wpau_youtube_channel-getrnd', 
                __('Show random video',YTCTDOM), 
                array(&$this, 'settings_field_checkbox'), 
                $this->general_settings_key,
                'general_settings',
                array(
                    'field'       => "youtube_channel_defaults[getrnd]",
                    'description' => __('Get random videos of all fetched from channel or playlist',YTCTDOM),
                    'class'       => 'widefat',
                    'value'       => $this->defaults['getrnd'],
                )
            );
        } // END register_general_settings()

        function register_video_settings()
        {
            $this->plugin_settings_tabs[$this->video_settings_key] = 'Video';
            register_setting(
                $this->video_settings_key, 
                $this->plugin_options_key, 
                array(&$this, 'plugin_options_validate')
            );

            // add video settings section
            add_settings_section(
                'video_settings', 
                __('Video Settings',YTCTDOM), 
                array(&$this, 'video_settings_section_description'), 
                $this->video_settings_key
            );
            add_settings_field(
                'wpau_youtube_channel-ratio', 
                __('Aspect Ratio',YTCTDOM), 
                array(&$this, 'settings_field_select'), 
                $this->video_settings_key, 
                'video_settings',
                array(
                    'field'       => "youtube_channel_defaults[ratio]",
                    'description' => __('Select aspect ratio for displayed video',YTCTDOM),
                    'class'       => 'regular-text',
                    'items'       => array(
                        "3" => __("16:9",YTCTDOM),
                        "2" => __("16:10",YTCTDOM),
                        "1" => __("4:3",YTCTDOM)
                    ),
                    'value' => $this->defaults['ratio'],
                )
            );
            add_settings_field(
                'wpau_youtube_channel-width', 
                __('Video Width','wpaust'), 
                array(&$this, 'settings_field_input_number'), 
                $this->video_settings_key, 
                'video_settings',
                array(
                    'field'       => "youtube_channel_defaults[width]",
                    'description' => __('Set default width for displayed video, in pixels',YTCTDOM),
                    'class'       => 'number',
                    'value'       => $this->defaults['width'],
                    'min'         => 160,
                    'max'         => 1920,
                    'step'        => 1
                )
            );
            add_settings_field(
                'wpau_youtube_channel-to_show', 
                __('What to show?',YTCTDOM), 
                array(&$this, 'settings_field_select'), 
                $this->video_settings_key, 
                'video_settings',
                array(
                    'field'       => "youtube_channel_defaults[to_show]",
                    'description' => __('Set what will be shown by default',YTCTDOM),
                    'class'       => 'regular-text',
                    'items'       => array(
                        "thumbnail"  => __("Thumbnail",YTCTDOM),
                        "object"     => __("Flash (object)",YTCTDOM),
                        "iframe"     => __("HTML5 (iframe)",YTCTDOM),
                        "iframe2"    => __("HTML5 (iframe) Async",YTCTDOM),
                        "chromeless" => __("Chromeless",YTCTDOM)
                    ),
                    'value' => $this->defaults['to_show'],
                )
            );
            add_settings_field(
                'wpau_youtube_channel-themelight', 
                __('Use light theme',YTCTDOM), 
                array(&$this, 'settings_field_checkbox'), 
                $this->video_settings_key, 
                'video_settings',
                array(
                    'field'       => "youtube_channel_defaults[themelight]",
                    'description' => __('Enable this option to use light theme for playback controls instead dark',YTCTDOM),
                    'class'       => 'checkbox',
                    'value'       => $this->defaults['themelight'],
                )
            );
            add_settings_field(
                'wpau_youtube_channel-controls', 
                __('Hide player controls',YTCTDOM), 
                array(&$this, 'settings_field_checkbox'), 
                $this->video_settings_key, 
                'video_settings',
                array(
                    'field'       => "youtube_channel_defaults[controls]",
                    'description' => __('Enable this option to hide playback controls',YTCTDOM),
                    'class'       => 'checkbox',
                    'value'       => $this->defaults['controls'],
                )
            );
            add_settings_field(
                'wpau_youtube_channel-fixyt', 
                __('Fix video height',YTCTDOM), 
                array(&$this, 'settings_field_checkbox'), 
                $this->video_settings_key, 
                'video_settings',
                array(
                    'field'       => "youtube_channel_defaults[fixyt]",
                    'description' => __('Enable this option to fix video height when playback controls are not hidden',YTCTDOM),
                    'class'       => 'checkbox',
                    'value'       => $this->defaults['fixyt'],
                )
            );
            add_settings_field(
                'wpau_youtube_channel-autoplay', 
                __('Autoplay video or playlist',YTCTDOM), 
                array(&$this, 'settings_field_checkbox'), 
                $this->video_settings_key, 
                'video_settings',
                array(
                    'field'       => "youtube_channel_defaults[autoplay]",
                    'description' => __('Enable this option to start video playback right after block is rendered',YTCTDOM),
                    'class'       => 'checkbox',
                    'value'       => $this->defaults['autoplay'],
                )
            );
            add_settings_field(
                'wpau_youtube_channel-autoplay_mute', 
                __('Mute video on autoplay',YTCTDOM), 
                array(&$this, 'settings_field_checkbox'), 
                $this->video_settings_key, 
                'video_settings',
                array(
                    'field'       => "youtube_channel_defaults[autoplay_mute]",
                    'description' => __('Enable this option to mute video when start autoplay',YTCTDOM),
                    'class'       => 'checkbox',
                    'value'       => $this->defaults['autoplay_mute'],
                )
            );
            add_settings_field(
                'wpau_youtube_channel-norel', 
                __('Hide related videos',YTCTDOM), 
                array(&$this, 'settings_field_checkbox'), 
                $this->video_settings_key, 
                'video_settings',
                array(
                    'field'       => "youtube_channel_defaults[norel]",
                    'description' => __('Enable this option to hide related videos after finished playback',YTCTDOM),
                    'class'       => 'checkbox',
                    'value'       => $this->defaults['norel'],
                )
            );
        } // END register_video_settings()

        function register_content_settings()
        {
            $this->plugin_settings_tabs[$this->content_settings_key] = 'Content';
            register_setting(
                $this->content_settings_key, 
                $this->plugin_options_key,
                array(&$this, 'plugin_options_validate')
            );

            // add content settings section
            add_settings_section(
                'content_settings', 
                __('Content Layout',YTCTDOM), 
                array(&$this, 'content_settings_section_description'), 
                $this->content_settings_key
            );
            add_settings_field(
                'wpau_youtube_channel-showtitle', 
                __('Show video title',YTCTDOM), 
                array(&$this, 'settings_field_checkbox'), 
                $this->content_settings_key, 
                'content_settings',
                array(
                    'field'       => "youtube_channel_defaults[showtitle]",
                    'description' => __('Enable this option to display title of video',YTCTDOM),
                    'class'       => 'checkbox',
                    'value'       => $this->defaults['showtitle']
                )
            );
            add_settings_field(
                'wpau_youtube_channel-showvidesc', 
                __('Show video description',YTCTDOM), 
                array(&$this, 'settings_field_checkbox'), 
                $this->content_settings_key, 
                'content_settings',
                array(
                    'field'       => "youtube_channel_defaults[showvidesc]",
                    'description' => __('Enable this option to display description for video',YTCTDOM),
                    'class'       => 'checkbox',
                    'value'       => $this->defaults['showvidesc']
                )
            );
            add_settings_field(
                'wpau_youtube_channel-videsclen', 
                __('Description length',YTCTDOM), 
                array(&$this, 'settings_field_input_number'), 
                $this->content_settings_key, 
                'content_settings',
                array(
                    'field'       => "youtube_channel_defaults[videsclen]",
                    'description' => __('Enter length for video description in characters (0 for full length)',YTCTDOM),
                    'class'       => 'number',
                    'value'       => $this->defaults['videsclen'],
                    'min'         => 0,
                    'max'         => 2000,
                    'step'        => 1,
                )
            );
            add_settings_field(
                'wpau_youtube_channel-descappend', 
                __('Et cetera string',YTCTDOM), 
                array(&$this, 'settings_field_input_text'), 
                $this->content_settings_key, 
                'content_settings',
                array(
                    'field'       => "youtube_channel_defaults[descappend]",
                    'description' => __(sprintf('Indicator for shortened video description (default: %s)',$this->defaults['descappend']),YTCTDOM),
                    'class'       => 'small-text',
                    'value'       => $this->defaults['descappend'],
                )
            );
            add_settings_field(
                'wpau_youtube_channel-hideanno', 
                __('Hide annotations from video',YTCTDOM), 
                array(&$this, 'settings_field_checkbox'), 
                $this->content_settings_key, 
                'content_settings',
                array(
                    'field'       => "youtube_channel_defaults[hideanno]",
                    'description' => __('Enable this option to hide video annotations (custom text set by uploader over video during playback)',YTCTDOM),
                    'class'       => 'checkbox',
                    'value'       => $this->defaults['hideanno']
                )
            );
            add_settings_field(
                'wpau_youtube_channel-hideinfo', 
                __('Hide video info',YTCTDOM), 
                array(&$this, 'settings_field_checkbox'), 
                $this->content_settings_key, 
                'content_settings',
                array(
                    'field'       => "youtube_channel_defaults[hideinfo]",
                    'description' => __('Enable this option to hide informations about video before play start (video title and uploader in overlay)',YTCTDOM),
                    'class'       => 'checkbox',
                    'value'       => $this->defaults['hideinfo']
                )
            );
        } // END register_content_settings

        function register_link_settings()
        {
            $this->plugin_settings_tabs[$this->link_settings_key] = 'Link to Channel';
            register_setting(
                $this->link_settings_key, 
                $this->plugin_options_key,
                array(&$this, 'plugin_options_validate')
            );

            // add content settings section
            add_settings_section(
                'link_settings', 
                __('Link to Channel',YTCTDOM), 
                array(&$this, 'link_settings_section_description'), 
                $this->link_settings_key
            );
            add_settings_field(
                'wpau_youtube_channel-goto_txt', 
                __('Visit YouTube Channel text',YTCTDOM), 
                array(&$this, 'settings_field_input_text'), 
                $this->link_settings_key, 
                'link_settings',
                array(
                    'field'       => "youtube_channel_defaults[goto_txt]",
                    'description' => __('Use placeholder %channel% to insert channel name',YTCTDOM),
                    'class'       => 'regular-text',
                    'value'       => $this->defaults['goto_txt'],
                )
            );
            add_settings_field(
                'wpau_youtube_channel-showgoto', 
                __('Show link to channel',YTCTDOM), 
                array(&$this, 'settings_field_checkbox'), 
                $this->link_settings_key, 
                'link_settings',
                array(
                    'field'       => "youtube_channel_defaults[showgoto]",
                    'description' => __('Enable this option to show customized link to channel at the bottom of YTC block',YTCTDOM),
                    'class'       => 'checkbox',
                    'value'       => $this->defaults['showgoto']
                )
            );
            add_settings_field(
                'wpau_youtube_channel-popup_goto', 
                __('Open YouTube channel page',YTCTDOM), 
                array(&$this, 'settings_field_select'), 
                $this->link_settings_key, 
                'link_settings',
                array(
					'field'       => "youtube_channel_defaults[popup_goto]",
					'description' => __('Set what will be shown by default',YTCTDOM),
					'class'       => 'regular-text',
					'items'       => array(
						"0"  => __("in same window",YTCTDOM),
						"1"     => __("in new window (JavaScript)",YTCTDOM),
						"2"     => __("in new window (Target)",YTCTDOM),
                    ),
                    'value' => $this->defaults['popup_goto'],
                )
            );
            add_settings_field(
                'wpau_youtube_channel-userchan', 
                __('Link to channel instead to user',YTCTDOM), 
                array(&$this, 'settings_field_checkbox'), 
                $this->link_settings_key, 
                'link_settings',
                array(
                    'field'       => "youtube_channel_defaults[userchan]",
                    'description' => __('Enable this option if link to your channel have <code>/channel/</code> instead <code>/user/</code> part in URL',YTCTDOM),
                    'class'       => 'checkbox',
                    'value'       => $this->defaults['userchan']
                )
            );
        } // END register_link_settings()

        function register_tools_settings()
        {
            $this->plugin_settings_tabs[$this->tools_settings_key] = 'Tools';
            register_setting($this->help_settings_key, $this->help_settings_key);

            add_settings_section(
                'tools_settings', 
                __('Tools',YTCTDOM), 
                array(&$this, 'tools_settings_section'), 
                $this->tools_settings_key
            );

        } // END register_help_settings()

        function register_help_settings()
        {
            $this->plugin_settings_tabs[$this->help_settings_key] = 'Help';
            register_setting($this->help_settings_key, $this->help_settings_key);

            add_settings_section(
                'help_settings', 
                __('Help',YTCTDOM), 
                array(&$this, 'help_settings_section'), 
                $this->help_settings_key
            );

        } // END register_help_settings()

        function options_tabs() {
            $current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->general_settings_key;
            echo '<h2 class="nav-tab-wrapper">';
            foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
                $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
                echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->plugin_settings_page . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
            }
            echo '</h2>';
        }

        public function general_settings_section_description()
        {
            // Think of this as help text for the section.
            _e('<p>Default settings for YouTuber Channel. This will be used as default values for shortcode.</p>',YTCTDOM);
        }
        public function video_settings_section_description()
        {
            // Think of this as help text for the section.
            _e('<p>Default settings for video section. This will be used as default values for shortcode.</p>',YTCTDOM);
        }
        public function content_settings_section_description()
        {
            // Think of this as help text for the section.
            _e('<p>Default settings for content layout section. This will be used as default values for shortcode.</p>',YTCTDOM);
        }
        public function link_settings_section_description()
        {
            // Think of this as help text for the section.
            _e('<p>Default settings for channel link at the bottom of video block. This will be used as default values for shortcode.</p>',YTCTDOM);
        }
        public function tools_settings_section()
        {
            include('settings_tools.php');
        }
        public function help_settings_section()
        {
            include('settings_help.php');
        }

        /**
         * This function provides text inputs for settings fields
         */
        public function settings_field_input_text($args)
        {
            extract( $args );
            echo sprintf('<input type="text" name="%s" id="%s" value="%s" class="%s" /><p class="description">%s</p>', $field, $field, $value, $class, $description);
        } // END public function settings_field_input_text($args)
        
        /**
         * This function provides number inputs for settings fields
         */
        public function settings_field_input_number($args)
        {
            extract( $args );
            echo sprintf('<input type="number" name="%s" id="%s" value="%s" class="%s" min="%s" max="%s" step="%s" /><p class="description">%s</p>', $field, $field, $value, $class, $min, $max, $step, $description);
        } // END public function settings_field_input_number($args)

        /**
         * This function provides checkbox for settings fields
         */
        public function settings_field_checkbox($args)
        {
            extract( $args );
            $checked = ( !empty($args['value']) ) ? 'checked="checked"' : '';
            echo sprintf('<label for="%s"><input type="checkbox" name="%s" id="%s" value="1" class="%s" %s />%s</label>', $field, $field, $field, $class, $checked, $description);
        } // END public function settings_field_checkbox($args)

        /**
         * This function provides textarea for settings fields
         */
        public function settings_field_textarea($args)
        {
            extract( $args );
            if (empty($rows)) $rows = 7;
            echo sprintf('<textarea name="%s" id="%s" rows="%s" class="%s">%s</textarea><p class="description">%s</p>', $field, $field, $rows, $class, $value, $description);
        } // END public function settings_field_textarea($args)

        /**
         * This function provides select for settings fields
         */
        public function settings_field_select($args)
        {
            extract( $args );
            $html = sprintf('<select id="%s" name="%s">',$field,$field);
            foreach ($items as $key=>$val)
            {
                $selected = ($value==$key) ? 'selected="selected"' : '';
                $html .= sprintf('<option %s value="%s">%s</option>',$selected,$key,$val);
            }
            $html .= sprintf('</select><p class="description">%s</p>',$description);
            echo $html;
        } // END public function settings_field_select($args)

        public function settings_field_colour_picker($args)
        {
            extract( $args );
            $html = sprintf('<input type="text" name="%s" id="%s" value="%s" class="wpau-color-field" />',$field, $field, $value);
            $html .= (!empty($description)) ? ' <p class="description">'.$description.'</p>' : '';
            echo $html;
        } // END public function settings_field_colour_picker($args)

        /**
         * add a menu
         */		
        function add_menu()
        {
            // Add a page to manage this plugin's settings
        	add_options_page(
        	    __(sprintf('%s Settings',YTCNAME),YTCTDOM), 
        	    __(YTCNAME,YTCTDOM), 
        	    'manage_options', 
        	    $this->plugin_settings_page, 
        	    array(&$this, 'plugin_settings_page')
        	);
        } // END function add_menu()
    
        /**
         * Menu Callback
         */		
        public function plugin_settings_page()
        {
        	if(!current_user_can('manage_options'))
        		wp_die(__('You do not have sufficient permissions to access this page.'));
	
        	// Render the settings template
        	include(sprintf("%s/settings_template.php", dirname(__FILE__)));
        } // END public function plugin_settings_page()
    } // END class WPAU_YOUTUBE_CHANNEL_SETTINGS
} // END if(!class_exists('WPAU_YOUTUBE_CHANNEL_SETTINGS'))

add_action( 'plugins_loaded', create_function( '', '$ytc_settings = new WPAU_YOUTUBE_CHANNEL_SETTINGS;' ) );