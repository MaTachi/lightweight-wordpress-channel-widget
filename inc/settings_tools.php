<p>To avoid page load slowdown, we provide internal YouTube Channel caching functionality. You can enable caching per widget or shortcode, and set how long cached feed for particular widget or shortcode will live.</p>
<p>If you wish to force clear cache before it expires (for example, you have set cache timeout to 2 days, but wish to force loading of fresh channel or playlist feed), you can use form below instead to manually remove transients from database.</p>

<form name="ytc_recache">
    <label for="slug">Enter relative URL to page where you display target YTC block (include starting and ending slash): <input name="recache_slug" id="recache_slug" value="" /></label>
    <input type="submit" name="do_recache" id="do_recache" value="ReCache" class="button button-secondary" /> <span id="recache_message"></span>
</form>
<script type="text/javascript">
jQuery(document).ready(function($){
    $('#do_recache').on('click', function(ev){
        ev.preventDefault();
        if ( $('#recache_slug').val() == '' ) {
            $('#recache_message').html("Please type relative path and try again!").show().delay(3000).fadeOut(700);
        } else {
            window.open( $('#recache_slug').val()+'?ytc_force_recache=1' );            
        }
    });
});
</script>