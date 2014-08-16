=== YouTube Channel ===
Contributors: urkekg
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=Q6Q762MQ97XJ6
Tags: youtube, channel, playlist, single, widget, widgets, youtube player, flash player, rss, feed, video, thumbnail, embed, sidebar, chromeless, iframe, html5
Requires at least: 3.8.0
Tested up to: 4.0
Stable tag: 2.2.2
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Show video thumbnails or playable blocks of recent videos from YouTube Channel or Playlist.

== Description ==

When you need to display sidebar widget with latest video from some YouTube channel or playlist, you can use customisable `YouTube Channel` plugin.

Simply insert widget to sidebar, set channel name and if you wish leave all other options on default. You will get latest video from chosen YouTube channel embedded in sidebar widget, with link to channel on the bottom of the widget. If you wish to use playlist instead of channel, just set playlist ID and injoy!

If you like this extension and you find it useful, please rate it on the right side.

= Features =
* Display latest video from YouTube channel, favorites or playlist
* Option to get random video from resources mentioned above
* Set custom widget title
* Enhanced Privacy
* Preferred aspect ratio relative to width (16:9, 16:10 and 4:3)
* Custom width for video embeded object (default is 220px)
* Choose to display video as thumbnail, HTML5 (iframe), HTML5 Asynchronous (iframe2), Flash (object) or chromeless video
* Thumbnail mode is responsive and opens video in lightbox
* Custom caching timeout
* Optimized gdata feeds
* Option to enable autoplay video
* Option to hide video controls
* Option to hide video info
* Option to show video title on top of the video
* Option to show video description below video
* Option to hide annotations from video
* Option to use light controls theme
* Set custom text for link to channel
* Option to show link to channel
* Option to use target="_blank" instead of javascript window.open() for chanel link in new tab/window

= Styling =
You can use `style.css` from theme to style `YouTube Video` widget content.

* `.youtube_channel` - widget wrapper class
* `.ytc_title` - class of video title abowe thumbnail/video object
* `.ytc_video_container` - class of container for single item
* `.ytc_video_1`, `.ytc_video_2`, ... - class of container for single item with ordering number of item in widget
* `.ytc_video_first` - class of first container for single item
* `.ytc_video_last` - class of last container for single item
* `.ytc_video_mid` - class of all other containers for single item 
* `.ytc_description` - class for video description text
* `.ytc_link` - class of container for link to channel

= Issues =
Controls light theme and hidden annotations does not work for chromeless object.
Video description for videos from playlist does nt work.

If WordFence or other malware scan tool detect YouTube Channel fule youtube-channel.php as potential risk because `base64_encode()` and `base64_decode()` functions, remember that we use this two functions to store and restore JSON feeds to transient cache, so potential detection is false positive.

= Credits =
Chromeless option borrowed from [Chromeless YouTube](http://wordpress.org/extend/plugins/chromeless-youtube/) extension.
For playing videos in lightbox we use [Magnific Popup](http://dimsemenov.com/plugins/magnific-popup/).
Code improvements and textdomain adds done by [dimadin](http://wordpress.org/extend/plugins/profile/dimadin).
[Federico Bozo](http://corchoweb.com/) reminded me to fix z-index problem

= How To Use =
**Add New Widget**
[youtube http://www.youtube.com/watch?v=Sj84cja7ieg]

**Use Playlist**
[youtube http://www.youtube.com/watch?v=y9zoi_Pk2kY]

**How To Get Debug Info**
[youtube http://www.youtube.com/watch?v=6jIu2OeKB24]

== Installation ==

You can use the built in installer and upgrader, or you can install the plugin manually.

1. You can either use the automatic plugin installer or your FTP program to upload unziped `youtube-channel` directory to your `wp-content/plugins` directory.
2. Activate the plugin through the `Plugins` menu in WordPress
3. Add YouTube Channel widget to sidebar
4. Set channel name and save changes

If you have to upgrade manually simply repeat the installation steps and re-enable the plugin.

== Frequently Asked Questions ==

= I set more than one items to fetch, but only one video is displayed. How to fix this? =

Below option `Fetch ... video(s)` you can find `Show ... video(s)` since version 2.0.0. Here you can set number of videos to display.

= How to get playlist ID? =

Playlist ID can be manualy extracted from YouTube playlist URL. Part of strings after `&list=` that begins with uppercase letters `PL` represent playlist ID. You can paste ID with or without leading `PL` string.

Since version 1.3.1 you can also paste full YouTube playlist URL and ID will be automaticaly extracted.

= How to force embeding 320p video with better audio quality? =

YouTube provide 320p videos if height of embeded video is set to 320 or more. If you use small YTC video size, 240p will be loaded instead. So, you could not force 720p in tiny YTC.

= What is a difference between `Fetch latest` and `Show ... videos`? =

Value for `Fetch latest` says how many items will containt videos feed for choosing random video.
Value for `Show ... videos` says how many videos will be displayed in widget.

= In wich way does the plugin order the videos? Not by the time they were uploaded... =

By default, YTC sort videos by publishing date/time, not by uploaded date/time.

If you have enabled option `Fix No items error/Respect playlist order`, then videos are not sorted by publishing date, but by YouTube default order.

= When I upload a new video at youtube, it is not in the list at my site =

Video feed for YTC has been retreived with standard youtube feed [uploads by specified user](https://developers.google.com/youtube/2.0/developers_guide_protocol?hl=en#User_Uploaded_Videos "User Uploaded Videos"), and as Google say: [uploaded videos will be included in a user's public uploaded videos feed a few minutes after the upload completes and YouTube finishes processing the video](https://developers.google.com/youtube/2.0/reference#Latency_Information).

If you does not see your latest video in your uplaods feed (which you can access at https://gdata.youtube.com/feeds/api/users/YOUR_YT_USERID/uploads by replacing YOUR_YT_USERID with your real youtube user ID), then YTC will not see it too.

== Changelog ==
= 2.2.3 (2014-07-27) =
* Add: option to disable related videos (not supported by chromeless player)
* Enhance: added support for YouTube `rel` parameter in Magnific PopUp IFRAME module 

= 2.2.2 (2014-07-25) =
* Add: admin notices after upgrade to prevent errors and avare users to do ReCache and prevent mixed json_decode / base64_encode strings for cached feeds
* Change: moved ReCache part to Tools tab on settings page

= 2.2.1 (2014-07-13) =
* Fix: to prevent broken JSON feed, transient caching changed from plain serialized string to base64_encode
* Add: URL parameter `ytc_force_recache` to force recache, also included on Help tab in plugin settings

= 2.2.0 =
* Add: open thumbnails in lightbox and stay on site, instead opening YouTube page (Magnific Popup jQuery library)
* Add: make thumbnail responsive
* Add: play indicator for thumbnails
* Add: shortcode [youtube_channel]
* Add: tabbed settings page for default options for shortcodes
* Add: Help tab for shortcode parameters
* Change: moved parts of code to helper functions

= 2.1.0.2 =
* Fix: remove embed object below old IFRAME implementation

= 2.1.0.1 =
* Change: add back old iframe functionality, second iframe option is async loading

= 2.1.0 =
* Change: iframe/HTML5 player inject with IFrame Player API https://developers.google.com/youtube/player_parameters#IFrame_Player_API
* Change: use native WP remote file download function wp_remote_get to fetch feeds (prevent some permission errors on some hosts)
* Change: removed height parameter so height is calculated by aspect ratio selection - 16:9 as default
* Add: mute audio on autoplay if iframe/HTML5 is selected
* Add: converter that will port pre-2.0.0 YTC widgets to 2.0.0+ version
* Fix: playlist parser (now allowed dash and underscore in playlist ID)

= 2.0.0 =
* Fix: undefined vars notices
* Fix: embedding default plugin playlist instead custom set for "Embed standard playlist"
* Add: caching system
* Add: option to link to channel instead to user
* Add: support for enhanced privacy by YouTube
* Enhance: RSS feed replaced with JSON
* Enhance: better formatted debug info with site URL
* Enhance: re-group widget options
* Enhance: updated wording
* Enhance: added tooltips for options
* Enhance: playlist ID detection from URL
* Remove: modified error_reporting

= 1.5.1 =
* Fix issue in widget settings when no apache_get_version() support on server
* Fix validation errors for widget settings
* Fix broken sidebar issue introduced in 1.5.0 release

= 1.5.0 =
* Add inline debug tracker
* Fix deprecated functions - changed rss.php by feed.php and split() by extract()
* Fix video description parser for new YouTube feed format
* Fix autoplay for single video bug
* Fix autoplay for multiple videos to play only first video
* Code cleanup
* Update compatibility to WordPress 3.5.1

= 1.4.0 =
* Added option to show preferred quantity of videos
* Added option to embed classic playlist
* Added class to video container: universal .ytc_video_container and selective (.ytc_video_first, .ytc_video_mid, .ytc_video_last)
* Added class to video title .ytc_title
* Added class to video description text .ytc_description
* Added class to container for link to channel .ytc_link
* Added routine to unique random video from channel if displayed more than one video
* Added option to set `et cetera` string for shortened video description
* Changed option for random video to use channel or playlist
* Fields for width and height converted to number with spinner
* Improved playlist ID handler

= 1.3.3 =
* Added Czech translation by Ladislav Drábek

= 1.3.2 =
* Add option to show video description below video
* Add option to shorten video description to N characters
* Add option to use light theme for controls instead of default dark theme (HTML5 iframe and flash object)
* Add option to hide annotations from video

= 1.3.1 =
* Add support for playlist full URL
* Fixed no random video for playlist

= 1.3 =
* Fixed z-index problem with flash and iframe object
* Add option to try to fix 'No items' error
* Add donate button in options dialog

= 1.2 =
* Fixed number of items for random video (min 1, max 50)
* Fixed no-controls for HTML5 iframe

= 1.1 =
* Added option to use the playlist instead of channel (suggested by Stacy)

= 1.0 =
* Ported to WordPress 3.2.1
* Added option to set maximum number of items for random video
* Version changed from major.minor.micro to to major.minor format
* Adds Spanish translation

= 0.1.3 =
* Uses selected() instead of if [dimadin]
* Uses sprintf for better i18n [dimadin]
* Wraps some strings in gettext [dimadin]
* Adds textdomain and loads it [dimadin]
* Adds target="_blank" for channel link [dimadin]
* Adds option to open channel link in popup
* Uses plugin_dir_url() instead of guessing of location [dimadin]
* Loads widget in its own function [dimadin]
* Adds Serbian translation

= 0.1.2 =
* Option to display random video from channel

= 0.1.1 =
* Fixed option to toggle video title visibility
* Added option to hide controls for iframe and object videos
* Added option to hide video info
* Enabled autostart for iframe and object videos

= 0.1.0 =
* Initial release

== Upgrade Notice ==

Better handling of cached UNICODE content, ReCache tool to force reloading cached feeds.

== Screenshots ==

1. YouTube Channel plugin default settings
2. YouTube Channel customized widget settings
3. YouTube Channel in WP Customizer
4. YouTube Channel preview
