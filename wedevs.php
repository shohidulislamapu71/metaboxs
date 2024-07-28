<?php
/**
 * Plugin Name: WeDevs 
 * Plugin URI: http://wedevs.com
 * Description: A plugin for WeDevs
 * Author: WeDevs
 * Author URI: http://wedevs.com
 * Version: 1.0.0
 */

class WeDevs {
    public function __construct() {
        add_action( 'init', array( $this, 'init' ));
    }

    public function init() {
        add_action( 'add_meta_boxes', array( $this, 'adding_custom_meta_boxes' ), 10, 2 );
        add_action( 'save_post', array( $this, 'save_meta_box_data' ) );
        add_filter( 'the_title', array( $this, 'add_title' ), 10, 2 );
    }

    public function adding_custom_meta_boxes( $post_type, $post ) {
        add_meta_box( 
            'my-meta-box',
            __( 'My Meta Box', 'wedevs' ),
            array( $this, 'render_my_meta_box' ),
            'post',
            'normal',
            'default'
        );
    }

    public function render_my_meta_box( $post ) {
        // Add a nonce field for security
        wp_nonce_field( 'wedevs_save_meta_box_data', 'wedevs_meta_box_nonce' );

        // Fetch all pages
        $get_posts = get_posts([
            'numberposts' => -1,
            'post_type'   => 'page',
        ]);

        // Get the saved meta value if it exists
        $saved_post_id = get_post_meta( $post->ID, '_related_post_id', true );

        ?>
        <label for="related_post_id"><?php _e( 'Select a related post:', 'wedevs' ); ?></label>
        <select name="related_post_id" id="related_post_id">
            <option value=""><?php _e( 'Select a post', 'wedevs' ); ?></option>
            <?php foreach ( $get_posts as $single_post ) : ?>
                <option value="<?php echo esc_attr( $single_post->ID ); ?>" <?php selected( $saved_post_id, $single_post->ID ); ?>>
                    <?php echo esc_html( $single_post->post_title ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    public function save_meta_box_data( $post_id ) {
        // Check if our nonce is set and verify it
        if ( !isset( $_POST['wedevs_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['wedevs_meta_box_nonce'], 'wedevs_save_meta_box_data' ) ) {
            return;
        }

        // Check the user's permissions
        if ( !current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Save the related post ID
        if ( isset( $_POST['related_post_id'] ) ) {
            update_post_meta( $post_id, '_related_post_id', sanitize_text_field( $_POST['related_post_id'] ) );
        }
    }

    public function add_title( $title, $post_id ) {
        // Get the related post ID from meta
        $related_post_id = get_post_meta( $post_id, '_related_post_id', true );

        // Modify the title if a related post ID is set
        if ( !empty( $related_post_id ) ) {
            $related_post = get_post( $related_post_id );
            if ( $related_post ) {
                $title .= ' - Related: ' . esc_html( $related_post->post_title );
            }
        }

        return $title;
    }
}

new WeDevs();
?>
