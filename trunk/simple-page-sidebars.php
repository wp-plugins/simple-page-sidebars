<?php
/*
Plugin Name: Simple Page Sidebars
Version: 0.1
Plugin URI: http://wordpress.org/extend/plugins/simple-page-sidebars/
Description: Assign custom, widget-enabled sidebars to any page with ease.
Author: Blazer Six, Inc.
Author URI: http://www.blazersix.com/
*/


load_plugin_textdomain( 'simple-page-sidebars', false, 'simple-page-sidebars/languages' );

require_once( plugin_dir_path( __FILE__ ) .'/includes/widget-area.php' );

// Lower priority registers sidebars below those typically added in themes
add_action( 'widgets_init', 'simpsid_register_sidebars', 20 );

if ( is_admin() ) {
	require_once( plugin_dir_path( __FILE__ ) .'/admin/admin.php' );
}


function simpsid_get_page_sidebars() {
	global $wpdb;
	
	$sql = "SELECT meta_value
		FROM $wpdb->posts p, $wpdb->postmeta pm
		WHERE p.post_type='page' AND p.post_status!='auto-draft' AND p.ID=pm.post_id
			AND pm.meta_key='_sidebar_name'
		GROUP BY pm.meta_value
		ORDER BY pm.meta_value ASC";
	
	$sidebars = array();
	$sidebar_names = $wpdb->get_results($sql);
	if ( count( $sidebar_names ) ) {
		foreach ( $sidebar_names as $meta ) {
			$sidebars[] = $meta->meta_value;
		}
	}
	
	return $sidebars;
}


/**
 * Add widget areas and automatically register page sidebars
 */
function simpsid_register_sidebars() {
	global $wpdb;
	
	$widget_areas = array();
	
	// Add widget areas using this filter
	$widget_areas = apply_filters( 'simpsid_widget_areas', $widget_areas );
	
	// Verify id's exist, otherwise create them
	// Helps ensure widgets don't get mixed up if widget areas are added or removed
	if ( is_array( $widget_areas ) ) {
		foreach ( $widget_areas as $key => $area ) {
			if ( is_numeric( $key ) ) {
				$widget_areas[ 'widget-area-' . sanitize_key( $area['name'] ) ] = $area;
				unset( $widget_areas[ $key ] );
			}
		}
	}
	
	// Override the default widget properties
	$widget_area_defaults = array(
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="title">',
		'after_title' => '</h4>'
	);
	$widget_area_defaults = apply_filters( 'simpsid_widget_area_defaults', $widget_area_defaults );
	
	// If any custom sidebars have been assigned to pages, merge them with widget areas defined above
	$sidebars = simpsid_get_page_sidebars();
	if ( count( $sidebars ) ) {
		foreach ( $sidebars as $sidebar ) {
			$page_sidebars[ 'page-sidebar-' . sanitize_key( $sidebar ) ] = array(
				'name' => $sidebar,
				'description' => NULL
			);
		}
		
		ksort($page_sidebars);
		$widget_areas = array_merge_recursive($widget_areas, $page_sidebars);
	}
	
	if ( is_array( $widget_areas ) ) {
		// Register the widget areas
		foreach ( $widget_areas as $key => $area ) {
			register_sidebar(array(
				'id' => $key,
				'name' => $area['name'],
				'description' => $area['description'],
				'before_widget' => ( ! isset( $area['before_widget'] ) ) ? $widget_area_defaults['before_widget'] : $area['before_widget'],
				'after_widget' => ( ! isset( $area['after_widget'] ) ) ? $widget_area_defaults['after_widget'] : $area['after_widget'],
				'before_title' => ( ! isset( $area['before_title'] ) ) ? $widget_area_defaults['before_title'] : $area['before_title'],
				'after_title' => ( ! isset( $area['after_title'] ) ) ? $widget_area_defaults['after_title'] : $area['after_title']
			));
		}
	}
}



/*
 * Sidebar display template tag
 *
 * Call this function in the template where custom sidebars should be displayed.
 * If a custom sidebar hasn't been defined, the sidebar name passed as the parameter
 * will be served as a fallback.
 *
 * @param string $default_sidebar
 */
function simple_sidebar( $default_sidebar ) {
	global $post, $wp_registered_sidebars;
	
	$sidebar_name = get_post_meta( $post->ID, '_sidebar_name', true );
	
	// Last chance to override which sidebar is displayed
	$sidebar_name = apply_filters( 'simpsid_sidebar_name', $sidebar_name );
	
	if ( is_page() && ! empty( $sidebar_name ) ) {
		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( count( $sidebars_widgets ) ) {
			foreach ( $wp_registered_sidebars as $id => $sidebar ) {
				if ( $sidebar['name'] == $sidebar_name ) {
					if ( count( $sidebars_widgets[$id] ) ) {
						dynamic_sidebar( $sidebar_name );
					} elseif ( ! empty( $default_sidebar ) ) {
						dynamic_sidebar( $default_sidebar );
					}
				}
			}
		}
	} elseif ( ! empty( $default_sidebar ) ) {
		dynamic_sidebar( $default_sidebar );
	}
}
?>