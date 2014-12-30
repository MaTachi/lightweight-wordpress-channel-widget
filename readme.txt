=== Lightweight YouTube Channel Widget ===
Contributors: MaTachi
Donate Link: https://github.com/404
Tags: youtube, channel, playlist, favorites, widget, video, thumbnail, sidebar
Requires at least: 3.9.0
Tested up to: 4.1
Stable tag: 10.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Widget showing video thumbnails of recent videos from a YouTube channel or
playlist.

== Description ==

Use this plugin when you want to display a list of recent YouTube videos from a
channel or playlist in your site's sidebar.

This plugin is a fork of Aleksandar Urošević's [YouTube
Channel](https://wordpress.org/plugins/youtube-channel/). This plugin is much
more lightweight and does only have a subset of YouTube Channel's features.

= Features =
* Display latest videos from YouTube channel, favorites or playlist.
* Option to get random videos from resources mentioned above.
* The videos are displayed with a thumbnail.
* Clicking a thumbnail takes the user to the video's page on YouTube.
* Custom caching timeout.

= Styling =
You can use `style.css` from theme to style `YouTube Video` widget content.

* `.youtube_channel` - main widget wrapper class (non-responsive block have additional class `default`, responsive block have additional class `responsive`)
* `.ytc_title` - class of video title abowe thumbnail/video object
* `.ytc_video_container` - class of container for single item
* `.ytc_video_1`, `.ytc_video_2`, ... - class of container for single item with ordering number of item in widget
* `.ytc_video_first` - class of first container for single item
* `.ytc_video_last` - class of last container for single item
* `.ytc_video_mid` - class of all other containers for single item
* `.ytc_description` - class for video description text
* `.ytc_link` - class of container for link to channel

= Known Issues =

None!

= Credits =

* Original codebase written by [Aleksandar Urošević](http://urosevic.net/).

== Installation ==

1. Upload the plugin's directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Add the widget to one of your theme's widget areas.

== Frequently Asked Questions ==

= Who should I direct support questions to? =

Daniel Jonsson. Aleksandar Urošević has not been involved in this fork.

== Changelog ==

= 10.0 (2015-xx-yy) =
* Fix: previous release broke opening lightbox for thumbnails and load YouTube
  website.

