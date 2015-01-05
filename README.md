# Lightweight YouTube Channel Widget

Author: Daniel Jonsson  
License: GPL version 3  
Download URL:
<https://wordpress.org/plugins/lightweight-youtube-channel-widget/>

This is a slimmed down fork of the WordPress plugin
[youtube-channel](http://wordpress.org/plugins/youtube-channel/) which is
written by [Aleksandar Urošević](http://urosevic.net/). See
[readme.txt](readme.txt) for the WordPress metadata README file.

## License

As the original codebase ([YouTube Channel version
2.4.1.3](https://wordpress.org/plugins/youtube-channel/developers/)) is
licensed under GPLv3, this project as a whole needs to retain that license.
However, my contributions are licensed under the MIT License, though not very
useful as long as the there still exists GPL licensed code in the project.

## Codebase comparison

|     | Lightweight YouTube Channel Widget 10.0  | YouTube Channel 2.4.1.3 |
| --- | ---------------------------------------- | ----------------------- |
| PHP SLOC * | 438 | 1452 |
| PHP LOC * | 623 | 1853 |
| SLOC/LOC | 0.70 | 0.78 |
| WordPress plugin dependencies | - | [Redux Framework](http://reduxframework.com/) |
| JavaScript dependencies | - | [jQuery](http://jquery.com/), [FitVids.JS](http://fitvidsjs.com/), [Magnific Popup](http://dimsemenov.com/plugins/magnific-popup/) |
| JavaScript w/ jQuery, minimized † | 0 B | 119.8 kB ‡ |
| JavaScript w/o jQuery, minimized | 0 B | 24.0 kB § |
| CSS, minimized | 1.1 kB ‖ | 7.1 kB ¶ |

\* Calculated by [CLOC](http://cloc.sourceforge.net/).  
† jQuery version 1.11.1 bundled with WordPress 4.1.  
‡ 95807 + 22012 + 1746 + 270 = 119835  
§ 22012 + 1746 + 270 = 24028  
‖ 1139 = 1139  
¶ 5998 + 1139 = 7137

Note, SI unit prefixes are used, where k = kilo = 1000.

## Local development and testing

The plugin can easily be tested locally during development with Docker.

The following two steps will create a Docker container with a complete LAMP
stack and a WordPress blog running within it:

    $ sudo docker build -t wordpress .
    $ sudo docker run -i -t -p 80:80 -v `pwd`:/var/www/html/wordpress/wp-content/plugins/lightweight-youtube-channel-widget wordpress

The blog is then accessible at <http://localhost/wordpress>.

## Packaging

Build a zip file containing the plugin with all its necessary files by running
the following command:

    $ ./make.sh

## Wordpress.org assets

The assets (banners, screenshots and icon) being used on
[wordpress.org/plugins/lightweight-youtube-channel-widget/](https://wordpress.org/plugins/lightweight-youtube-channel-widget/)
are found in the directory [wp-assets/](wp-assets/).

The [icon](wp-assets/icon.svg) is available on [Wikimedia
Commons](http://commons.wikimedia.org/wiki/File:Anime_eye.svg) under [CC BY-SA
2.5](http://creativecommons.org/licenses/by-sa/2.5/deed.en).

## Generate POT file

Attach to the Docker container and run the following command inside it:

    $ php /var/www/html/wordpress/tools/i18n/makepot.php wp-plugin /var/www/html/wordpress/wp-content/plugins/lightweight-youtube-channel-widget/ /var/www/html/wordpress/wp-content/plugins/lightweight-youtube-channel-widget/languages/lightweight-youtube-channel-widget-xx_XX.pot

The POT file is accessible outside the Docker container inside the `languages`
directory.

## Compile PO file to MO

Use the program `msgfmt` to compile a .po file to a binary .mo file. This
program is available on most Linux distributions in the package `gettext`.
[Click here](http://codex.wordpress.org/I18n_for_WordPress_Developers#MO_files)
for more information.
