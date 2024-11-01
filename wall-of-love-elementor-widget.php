<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Ensure Elementor is loaded
if ( ! did_action( 'elementor/loaded' ) ) {
    return;
}

class Share_One_Wall_of_Love_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'share_one_wall_of_love';
    }

    public function get_title() {
        return __( 'Share.one - Wall of Love', 'share-one' );
    }

    public function get_icon() {
        return 'fa fa-heart';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    protected function render() {
        // Get the UUID from the plugin's settings
        $uuid = esc_attr(get_option(SHARE_ONE_OPTION_UUID));
        if ($uuid) {
            echo "<div class='wall-of-love'>";
            echo do_shortcode('[share_one_wall_of_love]');
            echo "</div>";
        } else {
            echo '<div class="wall-of-love"><p><em>No UUID defined in settings.</em></p></div>';
        }
    }
}
