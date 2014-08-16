<p>You also can use shortcode <code>[youtube_channel]</code> with options listed below (all options are optional):</p>
<h3>General Settings</h3>
<ul>
	<li><code>channel</code> <em>(string)</em> ID of preferred YouTube channel. Do not set full URL to channel, but just last part from URL - ID (name)</li>
	<li><code>playlist</code> <em>(string)</em> ID of preferred YouTube playlist.</li>
    <li><code>res</code> <em>(int)</em> Resource to use for feed:
    <ul>
        <li>&bullet; <code>0</code> Channel</li>
        <li>&bullet; <code>1</code> Favorites (for defined channel)</li>
        <li>&bullet; <code>2</code> Playlist</li>
    </ul></li>
    <li><code>only_pl</code> <em>(bool)</em> If you set to use Playlist as resource, you can embed youtube playlist block instead single video from playlist. Simply set this option to true (<code>1</code> or <code>true</code>)</li>
    <li><code>cache</code> <em>(int)</em> Period in seconds for caching feed. You can disable caching by setting this option to <code>0</code>, but if you have a lot of visits, consider at least short caching (couple minutes).</li>

    <li><code>fetch</code> <em>(int)</em> Number of videos that will be used as stack for random pick (min 2, max 50)</li>
    <li><code>num</code> <em>(int)</em> Number of videos to display per YTC block.</li>

    <li><code>fix</code> <em>(bool)</em> Option to fix <em>No Items</em> error, and also to respect order of videos in feed or playlist.</li>
    <li><code>random</code> <em>(bool)</em> Option to randomize videos on every page load.</li>
</ul>
<h3>Video Settings</h3>
<ul>
    <li><code>ratio</code> <em>(int)</em> Set preferred aspect ratio for thumbnail and video. You can use:
    <ul>
        <li>&bullet; <code>3</code> 16:9 (widescreen)</li>
        <li>&bullet; <code>2</code> 16:10 (computer screen)</li>
        <li>&bullet; <code>1</code> 4:3</li>
    </ul></li>
    <li><code>width</code> <em>(int)</em> Width of thumbnail and video in pixels.</li>
    <li><code>show</code> <em>(string)</em> Object that will be used to represent video. We have couple predefined options:
    <ul>
        <li>&bullet; <code>thumbnail</code> Thumbnail will be used and video will be loaded in lightbox.</li>
        <li>&bullet; <code>iframe</code> HTML5 (iframe)</li>
        <li>&bullet; <code>iframe2</code> HTML5 (iframe) with asynchronous loading - recommended</li>
        <li>&bullet; <code>object</code> Flash object (not so good for Apple devices)</li>
        <li>&bullet; <code>chromeless</code> Chromeless solution (also not good for Apple devices)</li>
    </ul></li>

    <li><code>themelight</code> <em>(bool)</em> By default YouTube have dark play controls theme. By setting this option to <code>1</code> or <code>true</code> you can get light theme in player (HTML5 and Flash)</li>
    <li><code>controls</code> <em>(bool)</em> Set this option to <code>1</code> or <code>true</code> to hide playback controls.</li>
    <li><code>fix_h</code> <em>(bool)</em> If you did not set to hide player controls, you can set this option to <code>1</code> or <code>true</code> to fix video height taken by controls</li>
    <li><code>autoplay</code> <em>(bool)</em> Enable autoplay of first video in YTC video stack by setting this option to <code>1</code> or <code>true</code></li>
    <li><code>mute</code> <em>(bool)</em> Set this option to <code>1</code> or <code>true</code> to mute videos set to autoplay on load</li>
    <li><code>norel</code> <em>(bool)</em> Set this option to <code>1</code> or <code>true</code> to hire related videos after finished playbak</li>
</ul>
<h3>Content Layout</h3>
<ul>
    <li><code>showtitle</code> <em>(bool)</em> </li>
    <li><code>showdesc</code> <em>(bool)</em> </li>
    <li><code>desclen</code> <em>(int)</em> </li>
    <li><code>noinfo</code> <em>(bool)</em> </li>
    <li><code>noanno</code> <em>(bool)</em> </li>
</ul>
<h3>Link to Channel</h3>
<ul>
    <li><code>goto</code> <em>(bool)</em> </li>
    <li><code>goto_txt</code> <em>(string)</em></li>
    <li><code>popup</code> <em>(int)</em></li>
    <li><code>userchan</code> <em>(bool)</em> </li>
</ul>

<p>Please note, you can exclude all options listed above, and then we'll use default options from Settings page.</p>
<p>YTC blocks inserted through widget have own settings.</p>
