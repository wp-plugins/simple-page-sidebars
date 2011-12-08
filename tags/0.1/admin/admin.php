<?php
/*
 * Super function for saving page sidebars
 */
add_action( 'save_post', 'simpsid_update_page_sidebar' );
add_action( 'wp_ajax_simpsid_update_page_sidebar', 'simpsid_update_page_sidebar' );
function simpsid_update_page_sidebar( $post_id = 0 ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return $post_id;
	
	if ( $post_id == 0 ) {
		$post_id = $_POST['post_id'];
	}
	
	// verify either an individual post nonce or the bulk edit nonce
	// requests can come from a page update, ajax from the sidebar meta box, quick edit, or bulk edit
	if ( ! wp_verify_nonce( $_REQUEST['sidebar_name_nonce'], 'update-page-sidebar-name-' . $post_id ) && ! wp_verify_nonce( $_REQUEST['bulk_sidebar_name_nonce'], 'bulk-update-page-sidebar-name' ) ) {
		if ( defined( 'DOING AJAX' ) && DOING_AJAX ) {
			exit;
		} else {
			return;
		}
	}
	
	if ( ! wp_is_post_revision( $post_id ) && 'page' == get_post_type( $post_id ) ) {
		// if 'new_sidebar_name' is set and not empty, it supercedes any 'sidebar_name' setting
		// if 'sidebar_name' is blank or it equals 'default', delete meta
		// if 'sidebar_name' is set and not empty, update to new name
		// if 'sidebar_name' is -1, skip
		
		// bulk edit uses $_GET for some reason, so we use the $_REQUEST global
		if ( isset( $_REQUEST['new_sidebar_name' ] ) && ! empty( $_REQUEST['new_sidebar_name'] ) ) {
			update_post_meta( $post_id, '_sidebar_name', $_REQUEST['new_sidebar_name'] );
		} else {
			// if $_REQUEST['sidebar_name'] isn't set, we don't want to update the sidebar meta value
			$sidebar = ( isset( $_REQUEST['sidebar_name'] ) ) ? $_REQUEST['sidebar_name'] : -1;
			
			if ( empty( $sidebar ) || 'default' == $sidebar ) {
				delete_post_meta( $post_id, '_sidebar_name' );
			} elseif ( -1 != intval( $sidebar ) ) {
				update_post_meta( $post_id, '_sidebar_name', $_REQUEST['sidebar_name'] );
			}
		}
	}
	
	if ( defined( 'DOING AJAX' ) && DOING_AJAX )
		exit;
}


add_action( 'admin_menu', 'simpsid_admin_menu' );
function simpsid_admin_menu() {
	add_action( 'admin_footer-post.php', 'simpsid_page_sidebar_script' );
	
	// wish the Page Attributes meta box had a hook. oh well.
	add_meta_box( 'simplepagesidebarsdiv', 'Sidebar', 'simpsid_page_sidebar_meta_box', 'page', 'side', 'core' );
}


function simpsid_page_sidebar_meta_box( $page ) {
	global $wpdb;
	
	$sidebar = get_post_meta( $page->ID, '_sidebar_name', true );
	$current_sidebars = simpsid_get_page_sidebars();
	
	wp_nonce_field( 'update-page-sidebar-name-' . $page->ID, 'sidebar_name_nonce', false );
	?>
	<p>
		<label for="sidebar-name" class="screen-reader-text"><?php _e( 'Choose an existing sidebar:', 'simple-page-sidebars' ); ?></label>
		<select name="sidebar_name" id="sidebar-name" class="widefat">
			<option value="default"><?php _e( 'Default Sidebar', 'simple-page-sidebars' ); ?></option>
			<?php
			foreach ( $current_sidebars as $sb ) {
				echo '<option value="' . esc_attr( $sb ) . '"';
				selected( $sb, $sidebar );
				echo '>' . esc_html( $sb ) . '</option>';
			}
			?>
		</select>
		<label for="new-sidebar-name" class="screen-reader-text"><?php _e( 'Or create a new sidebar:', 'simple-page-sidebars' ); ?></label>
		<input type="text" name="new_sidebar_name" id="new-sidebar-name" class="widefat hide-if-js" value="" />
		<span id="sidebarnew" class="hide-if-no-js"><?php _e( 'Enter New', 'simple-page-sidebars' ); ?></span>
		<span id="sidebarcancel" class="hidden"><?php _e( 'Cancel', 'simple-page-sidebars' ); ?></span>
	</p>
	
	<p style="margin-top: 10px; margin-bottom: 0; text-align: right">
		<img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" id="sidebar-update-feedback" style="display: none" />
		<button class="button"><?php _e( 'Update', 'simple-page-sidebars' ); ?></button>
	</p>
	
	<style type="text/css">
	#sidebar-update-feedback { display: none; margin: 0 5px 0 0; vertical-align: middle;}
	#sidebarcancel, #sidebarnew { cursor: pointer; float: left; margin: 3px 0 0 3px; color: #21759b; font-size: 12px;}
	#sidebarcancel, #sidebarnew:hover { color: #d54e21;}
	</style>
	
	<script type="text/javascript">
	jQuery(function($) {
		$('#sidebarcancel, #sidebarnew').click(function() {
			$('#new-sidebar-name, #sidebar-name, #sidebarcancel, #sidebarnew').toggle();
			
			// clear the new sidebar name field when cancel is clicked
			if ( 'sidebarcancel' == $(this).attr('id') ) {
				$('#new-sidebar-name').val('');
			}
		});
		
		$('#simplepagesidebarsdiv').find('button').click(function(e) {
			e.preventDefault();
			
			$('#sidebar-update-feedback').show();
			$.post(ajaxurl, {
					action : 'simpsid_update_page_sidebar',
					post_id : $('#post_ID').val(),
					sidebar_name : $('select[name="sidebar_name"]').val(),
					new_sidebar_name : $('input[name="new_sidebar_name"]').val(),
					sidebar_name_nonce : $('input[name="sidebar_name_nonce"]').val()
				},
				function(data){
					new_sidebar_name = $('#new-sidebar-name').val();
					
					if ( '' != new_sidebar_name ) {
						if ( $('#simplepagesidebarsdiv select option[value="' + new_sidebar_name + '"]').length < 1 ) {
							$('#simplepagesidebarsdiv select').append('<option selected="selected">' + new_sidebar_name + '</option>').val(new_sidebar_name);
						} else {
							$('#simplepagesidebarsdiv select option[value="' + new_sidebar_name + '"]').attr('selected','selected');
						}
						
						$('#new-sidebar-name, #sidebar-name, #sidebarcancel, #sidebarnew').toggle().filter('input').val('');
					}
					
					$('#sidebar-update-feedback').hide();
				}
			);
		});
	});
	</script>
	
	<br class="clear" />
	<?php
}


function simpsid_page_sidebar_script() {
	global $current_screen;
	if ( 'page' != $current_screen->id || 'page' != $current_screen->post_type ) { return;  }
	?>
	
	<?php
}


// quick edit ain't so quick to implement
add_filter('manage_pages_columns', 'simpsid_manage_pages_columns');
function simpsid_manage_pages_columns( $columns ) {
	$columns['sidebar'] = __( 'Sidebar', 'simple-page-sidebars' );
	return $columns;
}


add_action( 'manage_pages_custom_column', 'simpsid_manage_pages_custom_column', 10, 2 );
function simpsid_manage_pages_custom_column( $column, $page_id ) {
	if ( 'sidebar' == $column ) {
		$sidebar = get_post_meta( $page_id, '_sidebar_name', true );
		echo ( $sidebar ) ? esc_html( $sidebar ) : '';
		
		// add the nonce here and copy it to the inline editor with javascript
		wp_nonce_field( 'update-page-sidebar-name-' . $page_id, 'sidebar_name_nonce', false );
	}
}


add_action( 'quick_edit_custom_box', 'simpsid_quick_edit_custom_box', 10, 2 );
function simpsid_quick_edit_custom_box( $column, $post_type ) {
	if ( 'page' != $post_type || 'sidebar' != $column ) { return; }
	
	$sidebars = simpsid_get_page_sidebars();
	?>
	<fieldset class="inline-edit-col-left">
		<div class="inline-edit-col">
			<div class="inline-edit-group" id="sidebar-edit-group">
				<label>
					<span class="title"><?php _e( 'Sidebar', 'simple-page-sidebars' ); ?></span>
					<select name="sidebar_name" id="sidebar-name">
						<option value="default"><?php _e( 'Default Sidebar', 'simple-page-sidebars' ); ?></option>
						<?php
						foreach ( $sidebars as $sb ) {
							echo '<option value="' . $sb . '">' . $sb . '</option>';
						}
						?>
					</select>
				</label>
			</div>
		</div>
    </fieldset>
	<?php
}


add_action( 'admin_footer-edit.php', 'simpsid_quick_edit_js' );
function simpsid_quick_edit_js() {
	global $current_screen;
	if ( 'edit-page' != $current_screen->id || 'page' != $current_screen->post_type ) { return;  }
	?>
	<script type="text/javascript">
	jQuery(function($) {
		$('table.pages').delegate('a.editinline', 'click', function(e) {
			inlineEditPost.revert();
			
			var id = inlineEditPost.getId(this);
			var currentSidebar = $('#post-' + id + ' .sidebar').text();
			
			// select the current sidebar option
			$('select#sidebar-name option').attr('selected', false);
			if ( '' != currentSidebar ) {
				$('select#sidebar-name option:contains(' + currentSidebar + ')').attr('selected', true);
			}
			
			// copy the sidebar name nonce
			$('#sidebar-edit-group').find('input[name="sidebar_name_nonce"]').remove().end().append( $('#post-' + id + ' input[name="sidebar_name_nonce"]').clone() );
		});
	});
	</script>
	<style type="text/css">
	.widefat .column-sidebar { width: 15%;}
	</style>
	<?php
}


add_action( 'bulk_edit_custom_box', 'simpsid_bulk_edit_custom_box', 10, 2 );
function simpsid_bulk_edit_custom_box( $column, $post_type ) {
	if ( 'page' != $post_type || 'sidebar' != $column ) { return; }
	
	$sidebars = simpsid_get_page_sidebars();
	?>
	<fieldset class="inline-edit-col-right" style="margin-top: 0">
		<div class="inline-edit-col">
			<label>
				<span class="title"><?php _e( 'Sidebar', 'simple-page-sidebars' ); ?></span>
				<select name="sidebar_name" id="sidebar-name">
					<option value="-1"><?php _e( '&mdash; No Change &mdash;', 'simple-page-sidebars' ); ?></option>
					<option value="default"><?php _e( 'Default Sidebar', 'simple-page-sidebars' ); ?></option>
					<?php
					foreach ( $sidebars as $sb ) {
						echo '<option value="' . $sb . '">' . $sb . '</option>';
					}
					?>
				</select>
			</label>
			<?php wp_nonce_field( 'bulk-update-page-sidebar-name', 'bulk_sidebar_name_nonce', false ); ?>
		</div>
    </fieldset>
	<?php
}
?>