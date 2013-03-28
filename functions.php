<?php
/**
 * Hook to swap in custom sidebar widgets if they exist.  Otherwise fall back on the specified sidebar.
 * @return bool/object
 */
function fms_show_sidebar( $instance, $widget_instance, $args ) {
	global $wp_registered_widgets,$fm_custom_sidebar_displayed,$fm_parent_group_name;


	if ( !isset( $fm_custom_sidebar_displayed ) ){
		$post_id = get_the_ID();
		if ( $post_id ){
			//NEED TO FIGURE OUT A WAY TO GRAB GROUP ID & NAME FOR POST META FIELD
			$custom_sidebar_name = $args['id'];
			$custom_sidebar = get_post_meta( $post_id, $custom_sidebar_name, True );
			if ( !empty( $custom_sidebar ) ) {
				$available_widgets = Fieldmanager_Sidebar::get_widgets();

				foreach( $custom_sidebar as $widget ){
					$new_instance = array();
					$new_args = array();
					$new_widget_instance = $widget_instance;
					if ( $widget[$custom_sidebar_name]['widget_id'] != '0' ){
						$current_widget = $available_widgets[$widget[$custom_sidebar_name]['widget_id']];

						$widget_args = array(
							'widget_id'		=>	$current_widget['callback'][0]->id,
							'widget_name'	=>	$current_widget['callback'][0]->name
						);
						$new_args = array_merge((array)$args,(array)$widget_args);

						foreach ( $widget[$custom_sidebar_name] as $field => $value ) {
							if ($field != 'widget_id') {
								$new_instance[$field] = $value;
							}

						}
						$new_widget_instance = $current_widget['callback'][0];
					}
					if ( !empty($new_args) && !empty($new_instance) ) {
						$new_widget_instance->widget($new_args, $new_instance);
					}
				}
				$fm_custom_sidebar_displayed = True;
				return false;
			}
			return $instance;
		}
		return $instance;
	}
	return false;
}
add_filter( 'widget_display_callback', 'fms_show_sidebar', 10, 3 );