<?php 
/*
* @package wsf
* twitter weidget
*/

class wsf_twitter_widget extends WP_Widget {

function __construct() {
parent::__construct(
// Base ID of your widget
'wsf_twitter_widget', 

// Widget name will appear in UI
__('Twitter Feed', 'wsf'), 

// Widget description
array( 'description' => __( 'latest tweets feed', 'wsf' ), ) 
);
}

// Creating widget front-end
// This is where the action happens
public function widget( $args, $instance ) {
$title = apply_filters( 'widget_title', $instance['title'] );
$screen_name_get = apply_filters( 'widget_title', $instance['screen_name_get'] );
$token_get = apply_filters( 'widget_title', $instance['token_get'] );
$token_secret_get = apply_filters( 'widget_title', $instance['token_secret_get'] );
$consumer_key_get = apply_filters( 'widget_title', $instance['consumer_key_get'] );
$consumer_secret_get = apply_filters( 'widget_title', $instance['consumer_secret_get'] );
$post_num = apply_filters( 'widget_title', $instance['post_num'] );
// before and after widget arguments are defined by themes
echo $args['before_widget'];
if ( ! empty( $title ) )
echo $args['before_title'] . $title . $args['after_title'];
// This is where you run the code and display the output
if( !empty($screen_name_get) && !empty($token_get) && !empty($token_secret_get) && !empty($consumer_key_get) && !empty($consumer_secret_get) ):

$token =$token_get;
$token_secret = $token_secret_get;
$consumer_key = $consumer_key_get;
$consumer_secret = $consumer_secret_get;

$host = 'api.twitter.com';
$method = 'GET';
$path = '/1.1/statuses/user_timeline.json'; // api call path

$query = array( // query parameters
    'screen_name' => $screen_name_get,
    'count' 	  => $post_num,
);

$oauth = array(
    'oauth_consumer_key' => $consumer_key,
    'oauth_token' => $token,
    'oauth_nonce' => (string)mt_rand(), // a stronger nonce is recommended
    'oauth_timestamp' => time(),
    'oauth_signature_method' => 'HMAC-SHA1',
    'oauth_version' => '1.0'
);

$oauth = array_map("rawurlencode", $oauth); // must be encoded before sorting
$query = array_map("rawurlencode", $query);

$arr = array_merge($oauth, $query); // combine the values THEN sort

asort($arr); // secondary sort (value)
ksort($arr); // primary sort (key)

// http_build_query automatically encodes, but our parameters
// are already encoded, and must be by this point, so we undo
// the encoding step
$querystring = urldecode(http_build_query($arr, '', '&'));

$url = "https://$host$path";

// mash everything together for the text to hash
$base_string = $method."&".rawurlencode($url)."&".rawurlencode($querystring);

// same with the key
$key = rawurlencode($consumer_secret)."&".rawurlencode($token_secret);

// generate the hash
$signature = rawurlencode(base64_encode(hash_hmac('sha1', $base_string, $key, true)));

// this time we're using a normal GET query, and we're only encoding the query params
// (without the oauth params)
$url .= "?".http_build_query($query);
$url=str_replace("&amp;","&",$url); //Patch by @Frewuill

$oauth['oauth_signature'] = $signature; // don't want to abandon all that work!
ksort($oauth); // probably not necessary, but twitter's demo does it

// also not necessary, but twitter's demo does this too
function add_quotes($str) { return '"'.$str.'"'; }
$oauth = array_map("add_quotes", $oauth);

// this is the full value of the Authorization line
$auth = "OAuth " . urldecode(http_build_query($oauth, '', ', '));

// if you're doing post, you need to skip the GET building above
// and instead supply query parameters to CURLOPT_POSTFIELDS
$options = array( CURLOPT_HTTPHEADER => array("Authorization: $auth"),
//CURLOPT_POSTFIELDS => $postfields,
CURLOPT_HEADER => false,
CURLOPT_URL => $url,
CURLOPT_RETURNTRANSFER => true,
CURLOPT_SSL_VERIFYPEER => false);

// do our business
$feed = curl_init();
curl_setopt_array($feed, $options);
$json = curl_exec($feed);
curl_close($feed);

$twitter_data = json_decode($json);

echo '<div id="teets"><ul>';
if($twitter_data):
	foreach ($twitter_data as $value) {
		echo '<li>';
	   $tweetout = preg_replace("/(http:\/\/|(www\.))(([^\s<]{4,68})[^\s<]*)/", '<a href="http://$2$3" target="_blank">$1$2$4</a>', $value->text);
	   $tweetout = preg_replace("/@(\w+)/", "<a href=\"http://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $tweetout);
	   $tweetout = preg_replace("/#(\w+)/", "<a href=\"http://search.twitter.com/search?q=\\1\" target=\"_blank\">#\\1</a>", $tweetout);
	   
	   echo $tweetout;
	   echo '</li>';
	}
endif;
echo '</ul></div>';

endif;	// value empty check

echo $args['after_widget'];
}
		
// Widget Backend 
public function form( $instance ) {
if ( isset( $instance[ 'title' ] ) ) {
$title = $instance[ 'title' ];
}
else {
$title = __( 'Latest Tweets', 'wsf' );
}
$screen_name_get = ( isset( $instance[ 'screen_name_get' ] ) ) ? $instance['screen_name_get'] : '';
$token_get 	= ( isset( $instance[ 'token_get' ] ) ) ? $instance['token_get'] : '';
$token_secret_get = ( isset( $instance[ 'token_secret_get' ] ) ) ? $instance['token_secret_get'] : '';
$consumer_key_get = ( isset( $instance[ 'consumer_key_get' ] ) ) ? $instance['consumer_key_get'] : '';
$consumer_secret_get = ( isset( $instance[ 'consumer_secret_get' ] ) ) ? $instance['consumer_secret_get'] : '';
$post_num 	= ( isset( $instance[ 'post_num' ] ) ) ? $instance['post_num'] : '2';
// Widget admin form
?>
<p>
<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ,'wsf'); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'screen_name_get' ); ?>"><?php _e( 'Set Screen Name:','wsf' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'screen_name_get' ); ?>" name="<?php echo $this->get_field_name( 'screen_name_get' ); ?>" type="text" value="<?php echo esc_attr((!$screen_name_get)? '':$screen_name_get); ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'token_get' ); ?>"><?php _e( 'Set Token:','wsf' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'token_get' ); ?>" name="<?php echo $this->get_field_name( 'token_get' ); ?>" type="text" value="<?php echo esc_attr((!$token_get)? '':$token_get); ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'token_secret_get' ); ?>"><?php _e( 'Set Token Secret:','wsf' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'token_secret_get' ); ?>" name="<?php echo $this->get_field_name( 'token_secret_get' ); ?>" type="text" value="<?php echo esc_attr((!$token_secret_get)? '':$token_secret_get); ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'consumer_key_get' ); ?>"><?php _e( 'Set Consumer key:','wsf' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'consumer_key_get' ); ?>" name="<?php echo $this->get_field_name( 'consumer_key_get' ); ?>" type="text" value="<?php echo esc_attr((!$consumer_key_get)? '':$consumer_key_get); ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'consumer_secret_get' ); ?>"><?php _e( 'Set Consumer Secret:','wsf' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'consumer_secret_get' ); ?>" name="<?php echo $this->get_field_name( 'consumer_secret_get' ); ?>" type="text" value="<?php echo esc_attr((!$consumer_secret_get)? '':$consumer_secret_get); ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'post_num' ); ?>"><?php _e( 'Number of posts to show:','wsf' ); ?></label>
	<input class="tiny-text" id="<?php echo $this->get_field_id( 'post_num' ); ?>" name="<?php echo $this->get_field_name( 'post_num' ); ?>" type="number" value="<?php echo esc_attr((!$post_num)? '2':$post_num); ?>" />
</p>
<?php 
}
	
// Updating widget replacing old instances with new
public function update( $new_instance, $old_instance ) {
	$instance = array();
	$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
	$instance['screen_name_get'] = ( ! empty( $new_instance['screen_name_get'] ) ) ? strip_tags( $new_instance['screen_name_get'] ) : '';
	$instance['token_get'] = ( ! empty( $new_instance['token_get'] ) ) ? strip_tags( $new_instance['token_get'] ) : '';
	$instance['token_secret_get'] = ( ! empty( $new_instance['token_secret_get'] ) ) ? strip_tags( $new_instance['token_secret_get'] ) : '';
	$instance['consumer_key_get'] = ( ! empty( $new_instance['consumer_key_get'] ) ) ? strip_tags( $new_instance['consumer_key_get'] ) : '';
	$instance['consumer_secret_get'] = ( ! empty( $new_instance['consumer_secret_get'] ) ) ? strip_tags( $new_instance['consumer_secret_get'] ) : '';
	$instance['post_num'] = ( ! empty( $new_instance['post_num'] ) ) ? strip_tags( $new_instance['post_num'] ) : '';
	return $instance;
}
} // Class wpb_widget ends here

// Register and load the widget
function wsf_load_twitter_widget() {
	register_widget( 'wsf_twitter_widget' );
}
add_action( 'widgets_init', 'wsf_load_twitter_widget' );