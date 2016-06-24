<?php 
/*
* @package wsf
* Flicker Widget
*/

class wsf_flicker_widget extends WP_Widget {

function __construct() {
parent::__construct(
// Base ID of your widget
'wsf_flicker_widget', 

// Widget name will appear in UI
__('Flicker', 'wsf'), 

// Widget description
array( 'description' => __( 'latest flickr Photos', 'wsf' ), ) 
);
}

// Creating widget front-end
// This is where the action happens
public function widget( $args, $instance ) {
$title = apply_filters( 'widget_title', $instance['title'] );
$api_key = apply_filters( 'widget_title', $instance['api_key'] );
$user_id = apply_filters( 'widget_title', $instance['user_id'] );
$post_num = apply_filters( 'widget_title', $instance['post_num'] );
// before and after widget arguments are defined by themes
echo $args['before_widget'];
if ( ! empty( $title ) )
echo $args['before_title'] . $title . $args['after_title'];
// flickr display the output

	$perPage = $post_num;
	$url = 'https://api.flickr.com/services/rest/?method=flickr.photos.search';
	$url.= '&api_key='.$api_key;
	$url.= '&user_id='.$user_id;
	$url.= '&per_page='.$perPage;
	$url.= '&format=json';
	$url.= '&nojsoncallback=1';
	
	$response = json_decode( file_get_contents( $url ) );
	$photo_array = $response->photos->photo;
	if( $photo_array ){
		foreach( $photo_array as $single_photo ){
			$farm_id = $single_photo->farm;
			$server_id = $single_photo->server;
			$photo_id = $single_photo->id;
			$secret_id = $single_photo->secret;
			$size = 'm';
			 
			$title = $single_photo->title;
			 
			$photo_url = 'https://farm'.$farm_id.'.staticflickr.com/'.$server_id.'/'.$photo_id.'_'.$secret_id.'_'.$size.'.'.'jpg';
			$photo_link = 'https://www.flickr.com/photos/'.$user_id.'/'.$photo_id.'/';
			 
			echo '<div class="flickrs">';
				echo '<div class="FlickrImages">';
					echo '<ul>';
						echo '<li>';
							echo '<a href="'.$photo_link.'">';
								echo "<img title='".$title."' src='".$photo_url."' />";
							echo '</a>';
						echo '</li>';
					echo '</ul>';
				echo '</div>';
			echo '</div>';
		}
	}
	
echo $args['after_widget'];
}
		
// Widget Backend 
public function form( $instance ) {
if ( isset( $instance[ 'title' ] ) ) {
$title = $instance[ 'title' ];
}
else {
$title = __( 'Latest Photos', 'wsf' );
}

$api_key = ( isset( $instance[ 'api_key' ] ) ) ? $instance['api_key'] : '';
$user_id = ( isset( $instance[ 'user_id' ] ) ) ? $instance['user_id'] : '';
$post_num 	= ( isset( $instance[ 'post_num' ] ) ) ? $instance['post_num'] : '2';
// Widget admin form
?>
<p>
<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ,'wsf'); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>

<p>
	<label for="<?php echo $this->get_field_id( 'api_key' ); ?>"><?php _e( 'Set api key:','wsf' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'api_key' ); ?>" name="<?php echo $this->get_field_name( 'api_key' ); ?>" type="text" value="<?php echo esc_attr((!$api_key)? '':$api_key); ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'user_id' ); ?>"><?php _e( 'Set user id:','wsf' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'user_id' ); ?>" name="<?php echo $this->get_field_name( 'user_id' ); ?>" type="text" value="<?php echo esc_attr((!$user_id)? '':$user_id); ?>" />
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
	$instance['api_key'] = ( ! empty( $new_instance['api_key'] ) ) ? strip_tags( $new_instance['api_key'] ) : '';
	$instance['user_id'] = ( ! empty( $new_instance['user_id'] ) ) ? strip_tags( $new_instance['user_id'] ) : '';
	$instance['post_num'] = ( ! empty( $new_instance['post_num'] ) ) ? strip_tags( $new_instance['post_num'] ) : '';
	return $instance;
}
} // Class wpb_widget ends here

// Register and load the widget
function wsf_load_flicker_widget() {
	register_widget( 'wsf_flicker_widget' );
}
add_action( 'widgets_init', 'wsf_load_flicker_widget' );
