<?php
/**
 * @package Fieldmanager
 */

/**
 * Custom Side Bars for Individual Posts
 * @package Fieldmanager
 */
class Fieldmanager_Sidebar extends Fieldmanager_Options {

	/**
	 * @var string
	 * Override $field_class
	 */
	public $field_class = 'sidebar';

	/**
	 * @var boolean
	 * Send an empty element first with a taxonomy select
	 */
	public $first_empty = False;

	private $select_classes = array( 'fm-element','fm-post-custom-sidebar' );

	/**
	 * @var callable
	 * What function to call to match terms. Initialized to Null here because it will be
	 * written in __construct to an internal function that calls get_terms, so only
	 * overwrite it if you do /not/ want to use get_terms.
	 *
	 * The function signature should be query_callback( $match, $args );
	 */
	public $query_callback = null;

	/**
	 * Override constructor to add chosen.js maybe
	 * @param array $options
	 */
	public function __construct( $options = array() ) {
		global $fm_sidebar_custom_widget_counter;
		$fm_sidebar_custom_widget_counter = -1; //offset for proto element

		$this->attributes = array(
			'size' => '1'
		);

		if ( empty( $this->data ) ) {
			$this->data = $this->get_widgets();
		}

		// Add the Fieldmanager Sidebar javascript library
		fm_add_script( 'fm_sidebar_js', fieldmanager_sidebar_get_baseurl().'js/fieldmanager-sidebar.js' );

		parent::__construct( $options );
	}

	/**
	 * Form element
	 * @param array $value
	 * @return string HTML
	 */
	public function form_element( $value = array() ) {
		 global $wp_registered_sidebars,$fm_parent_group_element_rendered,$fm_parent_group_name;

		// If this is a multiple select, need to handle differently
		$do_multiple = '';
		if ( array_key_exists( 'multiple', $this->attributes ) ) $do_multiple = "[]";

		$opts = '';
		if ( $this->first_empty ) {
			$opts .= '<option value="0">&nbsp;</option>';
		}
		$opts .= $this->form_data_elements( $value );
		$form = sprintf(
			'<select class="' . implode( " ", $this->select_classes ) . ' fm-sidebar-select" name="%s[widget_id]" id="%s-widget_id" %s data-value=\'%s\' />%s</select>',
			$this->get_form_name( $do_multiple ),
			$this->get_element_id(),
			$this->get_element_attributes(),
			( $value == null ) ? "" : json_encode( $value ), // For applications where options may be dynamically provided. This way we can still provide the previously stored value to a Javascript.
			$opts
		);
		if( !$this->is_proto && !$fm_parent_group_element_rendered ){
			$form .= sprintf(
				'<input type="hidden" name="fieldmanager-parent-group" id="fm-parent-group" value="%s">',
				$this->parent->name
			);
			$fm_parent_group_name = $this->parent->name;
			$fm_parent_group_element_rendered = true;
		}

		$form .= $this->widget_control();
		return $form;

	}

	/**
	 * Widget Selection data (<option>)
	 * @param array $data_row
	 * @param array $value
	 * @return string HTML
	 */
	public function form_data_element( $data_row, $value = array() ) {

		// For taxonomy-based selects, only return selected options if taxonomy preload is disabled
		// Additional terms will be provided by AJAX for typeahead to avoid overpopulating the select for large taxonomies
		$option_selected = $this->option_selected( $data_row['value'], $value, "selected" );
		if ( $this->taxonomy != null && $this->taxonomy_preload == false && $option_selected != "selected" ) return "";

		return sprintf(
			'<option value="%s" %s>%s</option>',
			$data_row['value'],
			$option_selected,
			$data_row['name']
		);
	}

	/**
	 * Return a list of widgets to use in Fieldmanager Select
	 *
	 * @return array
	 */
	public static function get_widgets( ) {
		global $wp_registered_widgets, $sidebars_widgets, $wp_registered_widget_controls;

		$sort = $wp_registered_widgets;
		usort( $sort, '_sort_name_callback' );
		$done = array();
		$widget_array = array();
		foreach ( $sort as $widget ) {
			if ( in_array( $widget['callback'], $done, true ) ) // We already showed this multi-widget
				continue;

			$sidebar = is_active_widget( $widget['callback'], $widget['id'], false, false );
			$done[] = $widget['callback'];

			$args = array( 'widget_id' => $widget['id'], 'widget_name' => $widget['name'], '_display' => 'template' );

			if ( isset($wp_registered_widget_controls[$widget['id']]['id_base']) && isset($widget['params'][0]['number']) ) {
				$id_base = $wp_registered_widget_controls[$widget['id']]['id_base'];
				$args['_temp_id'] = "$id_base-".next_widget_id_number($id_base);
				$args['_multi_num'] = next_widget_id_number($id_base);
				$args['_add'] = 'multi';
			} else {
				$args['_add'] = 'single';
				if ( $sidebar )
					$args['_hide'] = '1';
			}

			$widget_array[$widget['callback'][0]->id_base] = array('name' => $widget['callback'][0]->name, 'value'=>$widget['callback'][0]->id_base, 'args' => $args, 'callback' => $widget['callback'] );
		}
		return $widget_array;
	}

	/**
	 * Return widget control html
	 *
	 * @return string
	 */
	public function widget_control() {
		global $wp_registered_widgets, $sidebars_widgets, $wp_registered_widget_controls,$fm_sidebar_custom_widget_counter;
		$widgets = $this->get_widgets();
		$widget_control_form = '';
		$do_multiple = '';
		$widget_num =  $fm_sidebar_custom_widget_counter;

		foreach ( $widgets as $base_id => $widget_info ){

			$widget_id =  $widget_info['args']['widget_id'];
			ob_start();
			if ( isset($wp_registered_widget_controls[$widget_id]) ) {
				$control = $wp_registered_widget_controls[$widget_id];
				$control_callback = $control['callback'];
			} elseif ( !isset($wp_registered_widget_controls[$widget_id]) && isset($wp_registered_widgets[$widget_id]) ) {
				$name = esc_html( strip_tags($wp_registered_widgets[$widget_id]['name']) );
			}

			//Set the items to have unique ids since we don't need the ids to create the widgets
			$control_callback[0]->number = $widget_num;
			$control_callback[0]->id = $widget_info['value'].'-'.$widget_num;
			$control['id'] = $widget_info['value'].'-'.$widget_num;
			$control['params'][0]['number'] = $widget_num;

			$name = esc_html( strip_tags($control['name']) );

			$multi_number = isset($control['params'][0]['number']) ? $control['params'][0]['number'] : '';

			$id_base = isset($control['id_base']) ? $control['id_base'] : $control['id'];

			// show the widget form
			$width = ' style="width:240px"';
			$widget_control_form .= sprintf( '<div class="wrap fm-sidebar-widget %s-wrap">',
				esc_attr($id_base)
			);


				$widget_control_form .= '<div class="editwidget fm-wrapper"'.$width.'>
					<div class="widget-inside fm-item">';
						if ( is_callable( $control_callback ) ) {
							call_user_func_array( $control_callback, $control['params'] );
							$widget_controls = ob_get_contents();
							ob_clean();
							//Setting unique ids.  There is probably a better way to do this
							$widget_controls = preg_replace('/__i__/', $widget_num, $widget_controls);
							$widget_id_pattern = '/widget-'.$control['id'].'/';
							$widget_name_pattern = '/widget-'.$control['id_base'].'\['.$control['params'][0]['number'].'\]/';
							$widget_controls = preg_replace($widget_id_pattern,$this->get_element_id(), $widget_controls );
							$widget_controls = preg_replace($widget_name_pattern,$this->get_form_name( $do_multiple ), $widget_controls );
							$widget_control_form .= $widget_controls;
						} else {
							$widget_control_form .= '<p>' . __('There are no options for this widget.') . "</p>\n";
						}
					$widget_control_form .= '</div>';

				$widget_control_form .= '</div>
			</div>';
			ob_end_clean();
		}
		$fm_sidebar_custom_widget_counter++;
		return $widget_control_form;
	}

	/**
	 * Remove empty widgets
	 * @param array $values new post values
	 * @param array $current_values existing post values
	 */
	public function presave_alter_values( $values, $current_values = array() ) {
		global $fm_parent_group_name;
		$fm_group_parent_name = $_POST['fieldmanager-parent-group'];
		foreach ( $_POST[$fm_group_parent_name] as $key => $value ){
			if ( $value[$this->name]['widget_id'] == '0' ){
				unset($_POST[$fm_group_parent_name][$key]);
			}
		}
		if ( $values[0]['widget_id'] == '0' ){
			return array();
		}

		return $values;
	}
}

