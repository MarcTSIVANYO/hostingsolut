<?php
if( !function_exists('su_parse_csv') ){
	function su_parse_csv( $file ) {
		$csv_lines = file( $file );
		if ( is_array( $csv_lines ) ) {
			$cnt = count( $csv_lines );
			for ( $i = 0; $i < $cnt; $i++ ) {
				$line = $csv_lines[$i];
				$line = trim( $line );
				$first_char = true;
				$col_num = 0;
				$length = strlen( $line );
				for ( $b = 0; $b < $length; $b++ ) {
					if ( $skip_char != true ) {
						$process = true;
						if ( $first_char == true ) {
							if ( $line[$b] == '"' ) {
								$terminator = '";';
								$process = false;
							}
							else
								$terminator = ';';
							$first_char = false;
						}
						if ( $line[$b] == '"' ) {
							$next_char = $line[$b + 1];
							if ( $next_char == '"' ) $skip_char = true;
							elseif ( $next_char == ';' ) {
								if ( $terminator == '";' ) {
									$first_char = true;
									$process = false;
									$skip_char = true;
								}
							}
						}
						if ( $process == true ) {
							if ( $line[$b] == ';' ) {
								if ( $terminator == ';' ) {
									$first_char = true;
									$process = false;
								}
							}
						}
						if ( $process == true ) $column .= $line[$b];
						if ( $b == ( $length - 1 ) ) $first_char = true;
						if ( $first_char == true ) {
							$values[$i][$col_num] = $column;
							$column = '';
							$col_num++;
						}
					}
					else
						$skip_char = false;
				}
			}
		}
		$return = '<table><tr>';
		foreach ( $values[0] as $value ) $return .= '<th>' . $value . '</th>';
		$return .= '</tr>';
		array_shift( $values );
		foreach ( $values as $rows ) {
			$return .= '<tr>';
			foreach ( $rows as $col ) {
				$return .= '<td>' . $col . '</td>';
			}
			$return .= '</tr>';
		}
		$return .= '</table>';
		return $return;
	}
}


/**
 * Color shift a hex value by a specific percentage factor
 *
 * @param string  $supplied_hex Any valid hex value. Short forms e.g. #333 accepted.
 * @param string  $shift_method How to shift the value e.g( +,up,lighter,>)
 * @param integer $percentage   Percentage in range of [0-100] to shift provided hex value by
 *
 * @return string shifted hex value
 * @version 1.0 2008-03-28
 */
function su_hex_shift( $supplied_hex, $shift_method, $percentage = 50 ) {
	$shifted_hex_value = null;
	$valid_shift_option = false;
	$current_set = 1;
	$RGB_values = array();
	$valid_shift_up_args = array( 'up', '+', 'lighter', '>' );
	$valid_shift_down_args = array( 'down', '-', 'darker', '<' );
	$shift_method = strtolower( trim( $shift_method ) );
	// Check Factor
	if ( !is_numeric( $percentage ) || ( $percentage = ( int ) $percentage ) < 0 || $percentage > 100
	) trigger_error( "Invalid factor", E_USER_NOTICE );
	// Check shift method
	foreach ( array( $valid_shift_down_args, $valid_shift_up_args ) as $options ) {
		foreach ( $options as $method ) {
			if ( $method == $shift_method ) {
				$valid_shift_option = !$valid_shift_option;
				$shift_method = ( $current_set === 1 ) ? '+' : '-';
				break 2;
			}
		}
		++$current_set;
	}
	if ( !$valid_shift_option ) trigger_error( "Invalid shift method", E_USER_NOTICE );
	// Check Hex string
	switch ( strlen( $supplied_hex = ( str_replace( '#', '', trim( $supplied_hex ) ) ) ) ) {
	case 3:
		if ( preg_match( '/^([0-9a-f])([0-9a-f])([0-9a-f])/i', $supplied_hex ) ) {
			$supplied_hex = preg_replace( '/^([0-9a-f])([0-9a-f])([0-9a-f])/i', '\\1\\1\\2\\2\\3\\3',
				$supplied_hex );
		}
		else {
			trigger_error( "Invalid hex color value", E_USER_NOTICE );
		}
		break;
	case 6:
		if ( !preg_match( '/^[0-9a-f]{2}[0-9a-f]{2}[0-9a-f]{2}$/i', $supplied_hex ) ) {
			trigger_error( "Invalid hex color value", E_USER_NOTICE );
		}
		break;
	default:
		trigger_error( "Invalid hex color length", E_USER_NOTICE );
	}
	// Start shifting
	$RGB_values['R'] = hexdec( $supplied_hex{0} . $supplied_hex{1} );
	$RGB_values['G'] = hexdec( $supplied_hex{2} . $supplied_hex{3} );
	$RGB_values['B'] = hexdec( $supplied_hex{4} . $supplied_hex{5} );
	foreach ( $RGB_values as $c => $v ) {
		switch ( $shift_method ) {
		case '-':
			$amount = round( ( ( 255 - $v ) / 100 ) * $percentage ) + $v;
			break;
		case '+':
			$amount = $v - round( ( $v / 100 ) * $percentage );
			break;
		default:
			trigger_error( "Oops. Unexpected shift method", E_USER_NOTICE );
		}
		$shifted_hex_value .= $current_value = ( strlen( $decimal_to_hex = dechex( $amount ) ) < 2 ) ?
			'0' . $decimal_to_hex : $decimal_to_hex;
	}
	return '#' . $shifted_hex_value;
}


/**
 * Apply all custom formatting options of plugin
 */
function su_apply_formatting() {
	// Enable shortcodes in text widgets
	add_filter( 'widget_text', 'do_shortcode' );
	// Enable shortcodes in category descriptions
	add_filter( 'category_description', 'do_shortcode' );
	// Enable custom formatting
}

add_action( 'init', 'su_apply_formatting' );

/**
 * Custom do_shortcode function for nested shortcodes
 *
 * @param string  $content Shortcode content
 * @param string  $pre     First shortcode letter
 *
 * @return string Formatted content
 */
function su_do_shortcode( $content, $pre ) {
	if ( strpos( $content, '[_' ) !== false ) $content = preg_replace( '@(\[_*)_(' . $pre . '|/)@', "$1$2", $content );
	return do_shortcode( $content );
}

/**
 * Shortcode names prefix in compatibility mode
 *
 * @return string Special prefix
 */
function su_compatibility_mode_prefix() {
	return get_option( 'su_option_prefix' );
}

/**
 * Shortcut for su_compatibility_mode_prefix()
 */
function su_cmpt() {
	return su_compatibility_mode_prefix();
}

/**
 * Extra CSS class helper
 *
 * @param array   $atts Shortcode attributes
 *
 * @return string
 */
function su_ecssc( $atts ) {
	return ( $atts['class'] ) ? ' ' . trim( $atts['class'] ) : '';
}

/**
 *  Resizes an image and returns an array containing the resized URL, width, height and file type. Uses native Wordpress functionality.
 *
 *  @author Matthew Ruddy (http://easinglider.com)
 *  @return array   An array containing the resized image URL, width, height and file type.
 */
function su_image_resize( $url, $width = NULL, $height = NULL, $crop = true, $retina = false ) {
	global $wp_version;

	//######################################################################
	//  First implementation
	//######################################################################
	if ( isset( $wp_version ) && version_compare( $wp_version, '3.5' ) >= 0 ) {
		global $wpdb;
		if ( empty( $url ) )
			return new WP_Error( 'no_image_url', 'No image URL has been entered.', $url );
		// Get default size from database
		$width = ( $width ) ? $width : get_option( 'thumbnail_size_w' );
		$height = ( $height ) ? $height : get_option( 'thumbnail_size_h' );
		// Allow for different retina sizes
		$retina = $retina ? ( $retina === true ? 2 : $retina ) : 1;
		// Get the image file path
		$file_path = parse_url( $url );
		$file_path = $_SERVER['DOCUMENT_ROOT'] . $file_path['path'];
		// Check for Multisite
		if ( is_multisite() ) {
			global $blog_id;
			$blog_details = get_blog_details( $blog_id );
			$file_path = str_replace( $blog_details->path . 'files/', '/wp-content/blogs.dir/' . $blog_id . '/files/', $file_path );
		}
		// Destination width and height variables
		$dest_width = $width * $retina;
		$dest_height = $height * $retina;
		// File name suffix (appended to original file name)
		$suffix = "{$dest_width}x{$dest_height}";
		// Some additional info about the image
		$info = pathinfo( $file_path );
		$dir = $info['dirname'];
		$ext = $info['extension'];
		$name = wp_basename( $file_path, ".$ext" );
		// Suffix applied to filename
		$suffix = "{$dest_width}x{$dest_height}";
		// Get the destination file name
		$dest_file_name = "{$dir}/{$name}-{$suffix}.{$ext}";
		if ( !file_exists( $dest_file_name ) ) {
			$query = $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE guid='%s'", $url );
			$get_attachment = $wpdb->get_results( $query );
			if ( !$get_attachment )
				return array( 'url' => $url, 'width' => $width, 'height' => $height );
			// Load Wordpress Image Editor
			$editor = wp_get_image_editor( $file_path );
			if ( is_wp_error( $editor ) )
				return array( 'url' => $url, 'width' => $width, 'height' => $height );
			// Get the original image size
			$size = $editor->get_size();
			$orig_width = $size['width'];
			$orig_height = $size['height'];
			$src_x = $src_y = 0;
			$src_w = $orig_width;
			$src_h = $orig_height;
			if ( $crop ) {

				$cmp_x = $orig_width / $dest_width;
				$cmp_y = $orig_height / $dest_height;

				// Calculate x or y coordinate, and width or height of source
				if ( $cmp_x > $cmp_y ) {
					$src_w = round( $orig_width / $cmp_x * $cmp_y );
					$src_x = round( ( $orig_width - ( $orig_width / $cmp_x * $cmp_y ) ) / 2 );
				}
				else if ( $cmp_y > $cmp_x ) {
						$src_h = round( $orig_height / $cmp_y * $cmp_x );
						$src_y = round( ( $orig_height - ( $orig_height / $cmp_y * $cmp_x ) ) / 2 );
					}
			}

			// Time to crop the image!
			$editor->crop( $src_x, $src_y, $src_w, $src_h, $dest_width, $dest_height );
			// Now let's save the image
			$saved = $editor->save( $dest_file_name );
			// Get resized image information
			$resized_url = str_replace( basename( $url ), basename( $saved['path'] ), $url );
			$resized_width = $saved['width'];
			$resized_height = $saved['height'];
			$resized_type = $saved['mime-type'];
			// Add the resized dimensions to original image metadata (so we can delete our resized images when the original image is delete from the Media Library)
			$metadata = wp_get_attachment_metadata( $get_attachment[0]->ID );
			if ( isset( $metadata['image_meta'] ) ) {
				$metadata['image_meta']['resized_images'][] = $resized_width . 'x' . $resized_height;
				wp_update_attachment_metadata( $get_attachment[0]->ID, $metadata );
			}
			// Create the image array
			$image_array = array(
				'url' => $resized_url,
				'width' => $resized_width,
				'height' => $resized_height,
				'type' => $resized_type
			);
		}
		else {
			$image_array = array(
				'url' => str_replace( basename( $url ), basename( $dest_file_name ), $url ),
				'width' => $dest_width,
				'height' => $dest_height,
				'type' => $ext
			);
		}
		// Return image array
		return $image_array;
	}

	//######################################################################
	//  Second implementation
	//######################################################################
	else {
		global $wpdb;

		if ( empty( $url ) )
			return new WP_Error( 'no_image_url', 'No image URL has been entered.', $url );

		// Bail if GD Library doesn't exist
		if ( !extension_loaded( 'gd' ) || !function_exists( 'gd_info' ) )
			return array( 'url' => $url, 'width' => $width, 'height' => $height );

		// Get default size from database
		$width = ( $width ) ? $width : get_option( 'thumbnail_size_w' );
		$height = ( $height ) ? $height : get_option( 'thumbnail_size_h' );

		// Allow for different retina sizes
		$retina = $retina ? ( $retina === true ? 2 : $retina ) : 1;

		// Destination width and height variables
		$dest_width = $width * $retina;
		$dest_height = $height * $retina;

		// Get image file path
		$file_path = parse_url( $url );
		$file_path = $_SERVER['DOCUMENT_ROOT'] . $file_path['path'];

		// Check for Multisite
		if ( is_multisite() ) {
			global $blog_id;
			$blog_details = get_blog_details( $blog_id );
			$file_path = str_replace( $blog_details->path . 'files/', '/wp-content/blogs.dir/' . $blog_id . '/files/', $file_path );
		}

		// Some additional info about the image
		$info = pathinfo( $file_path );
		$dir = $info['dirname'];
		$ext = $info['extension'];
		$name = wp_basename( $file_path, ".$ext" );

		// Suffix applied to filename
		$suffix = "{$dest_width}x{$dest_height}";

		// Get the destination file name
		$dest_file_name = "{$dir}/{$name}-{$suffix}.{$ext}";

		// No need to resize & create a new image if it already exists!
		if ( !file_exists( $dest_file_name ) ) {

			/*
				 *  Bail if this image isn't in the Media Library either.
				 *  We only want to resize Media Library images, so we can be sure they get deleted correctly when appropriate.
				 */
			$query = $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE guid='%s'", $url );
			$get_attachment = $wpdb->get_results( $query );
			if ( !$get_attachment )
				return array( 'url' => $url, 'width' => $width, 'height' => $height );

			$image = wp_get_image_editor( $file_path );
			if ( !is_resource( $image ) )
				return new WP_Error( 'error_loading_image_as_resource', $image, $file_path );

			// Get the current image dimensions and type
			$size = @getimagesize( $file_path );
			if ( !$size )
				return new WP_Error( 'file_path_getimagesize_failed', 'Failed to get $file_path information using getimagesize.' );
			list( $orig_width, $orig_height, $orig_type ) = $size;

			// Create new image
			$new_image = wp_imagecreatetruecolor( $dest_width, $dest_height );

			// Do some proportional cropping if enabled
			if ( $crop ) {

				$src_x = $src_y = 0;
				$src_w = $orig_width;
				$src_h = $orig_height;

				$cmp_x = $orig_width / $dest_width;
				$cmp_y = $orig_height / $dest_height;

				// Calculate x or y coordinate, and width or height of source
				if ( $cmp_x > $cmp_y ) {
					$src_w = round( $orig_width / $cmp_x * $cmp_y );
					$src_x = round( ( $orig_width - ( $orig_width / $cmp_x * $cmp_y ) ) / 2 );
				}
				else if ( $cmp_y > $cmp_x ) {
						$src_h = round( $orig_height / $cmp_y * $cmp_x );
						$src_y = round( ( $orig_height - ( $orig_height / $cmp_y * $cmp_x ) ) / 2 );
					}

				// Create the resampled image
				imagecopyresampled( $new_image, $image, 0, 0, $src_x, $src_y, $dest_width, $dest_height, $src_w, $src_h );
			}
			else
				imagecopyresampled( $new_image, $image, 0, 0, 0, 0, $dest_width, $dest_height, $orig_width, $orig_height );

			// Convert from full colors to index colors, like original PNG.
			if ( IMAGETYPE_PNG == $orig_type && function_exists( 'imageistruecolor' ) && !imageistruecolor( $image ) )
				imagetruecolortopalette( $new_image, false, imagecolorstotal( $image ) );

			// Remove the original image from memory (no longer needed)
			imagedestroy( $image );

			// Check the image is the correct file type
			if ( IMAGETYPE_GIF == $orig_type ) {
				if ( !imagegif( $new_image, $dest_file_name ) )
					return new WP_Error( 'resize_path_invalid', 'Resize path invalid (GIF)' );
			}
			elseif ( IMAGETYPE_PNG == $orig_type ) {
				if ( !imagepng( $new_image, $dest_file_name ) )
					return new WP_Error( 'resize_path_invalid', 'Resize path invalid (PNG).' );
			}
			else {

				// All other formats are converted to jpg
				if ( 'jpg' != $ext && 'jpeg' != $ext )
					$dest_file_name = "{$dir}/{$name}-{$suffix}.jpg";
				if ( !imagejpeg( $new_image, $dest_file_name, apply_filters( 'resize_jpeg_quality', 90 ) ) )
					return new WP_Error( 'resize_path_invalid', 'Resize path invalid (JPG).' );
			}

			// Remove new image from memory (no longer needed as well)
			imagedestroy( $new_image );

			// Set correct file permissions
			$stat = stat( dirname( $dest_file_name ) );
			$perms = $stat['mode'] & 0000666;
			@chmod( $dest_file_name, $perms );

			// Get some information about the resized image
			$new_size = @getimagesize( $dest_file_name );
			if ( !$new_size )
				return new WP_Error( 'resize_path_getimagesize_failed', 'Failed to get $dest_file_name (resized image) info via @getimagesize', $dest_file_name );
			list( $resized_width, $resized_height, $resized_type ) = $new_size;

			// Get the new image URL
			$resized_url = str_replace( basename( $url ), basename( $dest_file_name ), $url );

			// Add the resized dimensions to original image metadata (so we can delete our resized images when the original image is delete from the Media Library)
			$metadata = wp_get_attachment_metadata( $get_attachment[0]->ID );
			if ( isset( $metadata['image_meta'] ) ) {
				$metadata['image_meta']['resized_images'][] = $resized_width . 'x' . $resized_height;
				wp_update_attachment_metadata( $get_attachment[0]->ID, $metadata );
			}

			// Return array with resized image information
			$image_array = array(
				'url' => $resized_url,
				'width' => $resized_width,
				'height' => $resized_height,
				'type' => $resized_type
			);
		}
		else {
			$image_array = array(
				'url' => str_replace( basename( $url ), basename( $dest_file_name ), $url ),
				'width' => $dest_width,
				'height' => $dest_height,
				'type' => $ext
			);
		}

		return $image_array;
	}
}

/**
 *  Deletes the resized images when the original image is deleted from the Wordpress Media Library.
 *
 *  @author Matthew Ruddy
 */
function su_delete_resized_images( $post_id ) {

	// Get attachment image metadata
	$metadata = wp_get_attachment_metadata( $post_id );
	if ( !$metadata )
		return;

	// Do some bailing if we cannot continue
	if ( !isset( $metadata['file'] ) || !isset( $metadata['image_meta']['resized_images'] ) )
		return;
	$pathinfo = pathinfo( $metadata['file'] );
	$resized_images = $metadata['image_meta']['resized_images'];

	// Get Wordpress uploads directory (and bail if it doesn't exist)
	$wp_upload_dir = wp_upload_dir();
	$upload_dir = $wp_upload_dir['basedir'];
	if ( !is_dir( $upload_dir ) )
		return;

	// Delete the resized images
	foreach ( $resized_images as $dims ) {

		// Get the resized images filename
		$file = $upload_dir . '/' . $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '-' . $dims . '.' . $pathinfo['extension'];

		// Delete the resized image
		@unlink( $file );
	}
}

add_action( 'delete_attachment', 'su_delete_resized_images' );


class Su_Tools {
	function __construct() {
		add_action( 'wp_ajax_su_example_preview', array( __CLASS__, 'example' ) );
		add_action( 'sc/update',                  array( __CLASS__, 'reset_examples' ) );
		add_action( 'sc/activation',              array( __CLASS__, 'reset_examples' ) );
		add_action( 'sunrise/page/before',        array( __CLASS__, 'reset_examples' ) );

		add_action( 'load-users.php',             array( __CLASS__, 'reset_users_cache' ) );
		add_action( 'load-user-edit.php',         array( __CLASS__, 'reset_users_cache' ) );
	}

	public static function get_templates( $dirz = '' ){

		$dir = ABSPATH.'wp-content'.DS.'themes'.DS.THEME_SLUG.DS.'templates'.DS.'shortcode'.DS.$dirz.DS;
		
		global $king;
		$_return = array();
		
		if( is_dir( $dir ) ){
			if ( $handle = opendir( $dir ) ){
				while ( false !== ( $entry = readdir($handle) ) ) {
					if( is_file( $dir.$entry ) && strpos($entry, '.php') !== false  ){
						$key = ucfirst( str_replace( '-', ' ', basename( $entry, '.php' ) ) );
						$_return[ $dirz.'/'.$entry ] = $key;
					}
				}
			}
		}
		
		return $_return;
		
	}
	
	public static function select( $args ) {
		$args = wp_parse_args( $args, array(
				'id'       => '',
				'name'     => '',
				'class'    => '',
				'multiple' => '',
				'size'     => '',
				'disabled' => '',
				'selected' => '',
				'none'     => '',
				'options'  => array(),
				'style' => '',
				'format'   => 'keyval', // keyval/idtext
				'noselect' => '' // return options without <select> tag
			) );
		$options = array();
		if ( !is_array( $args['options'] ) ) $args['options'] = array();
		if ( $args['id'] ) $args['id'] = ' id="' . $args['id'] . '"';
		if ( $args['name'] ) $args['name'] = ' name="' . $args['name'] . '"';
		if ( $args['class'] ) $args['class'] = ' class="' . $args['class'] . '"';
		if ( $args['style'] ) $args['style'] = ' style="' . esc_attr( $args['style'] ) . '"';
		if ( $args['multiple'] ) $args['multiple'] = ' multiple="multiple"';
		if ( $args['disabled'] ) $args['disabled'] = ' disabled="disabled"';
		if ( $args['size'] ) $args['size'] = ' size="' . $args['size'] . '"';
		if ( $args['none'] && $args['format'] === 'keyval' ) $args['options'][0] = $args['none'];
		if ( $args['none'] && $args['format'] === 'idtext' ) array_unshift( $args['options'], array( 'id' => '0', 'text' => $args['none'] ) );
		// keyval loop
		// $args['options'] = array(
		//   id => text,
		//   id => text
		// );
		if ( $args['format'] === 'keyval' ) foreach ( $args['options'] as $id => $text ) {
				$options[] = '<option value="' . (string) $id . '">' . (string) $text . '</option>';
			}
		// idtext loop
		// $args['options'] = array(
		//   array( id => id, text => text ),
		//   array( id => id, text => text )
		// );
		elseif ( $args['format'] === 'idtext' ) foreach ( $args['options'] as $option ) {
				if ( isset( $option['id'] ) && isset( $option['text'] ) )
					$options[] = '<option value="' . (string) $option['id'] . '">' . (string) $option['text'] . '</option>';
			}
		$options = implode( '', $options );
		$options = str_replace( 'value="' . $args['selected'] . '"', 'value="' . $args['selected'] . '" selected="selected"', $options );
		return ( $args['noselect'] ) ? $options : '<select' . $args['id'] . $args['name'] . $args['class'] . $args['multiple'] . $args['size'] . $args['disabled'] . $args['style'] . '>' . $options . '</select>';
	}

	public static function get_categories() {
		$cats = array();
		foreach ( (array) get_terms( 'category', array( 'hide_empty' => false ) ) as $cat ) $cats[$cat->slug] = $cat->name;
		return $cats;
	}

	public static function get_types() {
		$types = array();
		foreach ( (array) get_post_types( '', 'objects' ) as $cpt => $cpt_data ) $types[$cpt] = $cpt_data->label;
		return $types;
	}

	public static function get_users() {
		
		$users = get_users();
		// Cache results
		set_transient( 'sc/users_cache', $users );
		// Prepare data array
		$data = array();
		// Loop through users
		foreach ( $users as $user ) $data[$user->data->ID] = $user->data->display_name;
		// Return data
		return $data;
	}

	public static function reset_users_cache() {
		if ( ( isset( $_GET['update'] ) || isset( $_GET['updated'] ) ) )
			if ( $_GET['update'] === 'del' || $_GET['update'] === 'add' || $_GET['updated'] === '1' ) delete_transient( 'sc/users_cache' );
	}

	public static function get_taxonomies() {
		$taxes = array();
		foreach ( (array) get_taxonomies( '', 'objects' ) as $tax ) $taxes[$tax->name] = $tax->label;
		return $taxes;
	}

	public static function get_terms( $tax = 'category', $key = 'id', $type = '', $default = '' ) {
	
		
		$get_terms = (array) get_terms( $tax, array( 'hide_empty' => false ) );
		
		if( $type != '' ){
			$get_terms = self::get_terms_by_post_type( $get_terms, $type );
		}
		
		$terms = array();
		
		if( $default != '' ){
			$terms[] = $default;
		}
		
		if ( $key == 'id' ){
			foreach ( $get_terms as $term ){
				if( isset( $term->term_id ) && isset( $term->name ) ){
					$terms[$term->term_id] = $term->name;
				}
			}	
		}else if ( $key == 'slug' ){
			foreach ( $get_terms as $term ){
				if( !empty($term->name) ){
					if( isset( $term->slug ) && isset( $term->name ) ){
						$terms[$term->slug] = $term->name;
					}	
				}	
			}	
		}	
		
		return $terms;

	}
	
	public static function get_terms_by_post_type( $terms, $type ){
		
		$_terms = array();
		
		foreach( $terms as $term ){
		
			$args = array(	'category'         => $term->term_id,
							'post_type'        => $type );
			if( count( get_posts( $args ) ) ) array_push( $_terms, $term );
			
		}
		
		return $_terms;
		
	}
		
	public static function get_slides( $args ) {
		$args = wp_parse_args( $args, array(
				'source'  => 'none',
				'limit'   => 20,
				'gallery' => null,
				'type'    => '',
				'link'    => 'none'
			) );
		// Get deprecated galleries if needed
		if ( $args['gallery'] !== null || ( $args['source'] === 'none' && get_option( 'su_option_galleries-432' ) ) ) return self::get_slides_432( $args );
		// Prepare empty array for slides
		$slides = array();
		// Loop through source types
		foreach ( array( 'media', 'posts', 'category', 'taxonomy' ) as $type )
			if ( strpos( trim( $args['source'] ), $type . ':' ) === 0 ) {
				$args['source'] = array(
					'type' => $type,
					'val'  => (string) trim( str_replace( array( $type . ':', ' ' ), '', $args['source'] ), ',' )
				);
				break;
			}
		// Source is not parsed correctly, return empty array
		if ( !is_array( $args['source'] ) ) return $slides;
		// Default posts query
		$query = array( 'posts_per_page' => $args['limit'] );
		// Source: media
		if ( $args['source']['type'] === 'media' ) {
			$query['post_type'] = 'attachment';
			$query['post_status'] = 'any';
			$query['post__in'] = (array) explode( ',', $args['source']['val'] );
			$query['orderby'] = 'post__in';
		}
		// Source: posts
		if ( $args['source']['type'] === 'posts' ) {
			if ( $args['source']['val'] !== 'recent' ) {
				$query['post__in'] = (array) explode( ',', $args['source']['val'] );
				$query['orderby'] = 'post__in';
			}
		}
		// Source: category
		elseif ( $args['source']['type'] === 'category' ) {
			$query['category__in'] = (array) explode( ',', $args['source']['val'] );
		}
		// Source: taxonomy
		elseif ( $args['source']['type'] === 'taxonomy' ) {
			// Parse taxonomy name and terms ids
			$args['source']['val'] = explode( '/', $args['source']['val'] );
			// Taxonomy parsed incorrectly, return empty array
			if ( !is_array( $args['source']['val'] ) || count( $args['source']['val'] ) !== 2 ) return $slides;
			$query['tax_query'] = array(
				array(
					'taxonomy' => $args['source']['val'][0],
					'field' => 'id',
					'terms' => (array) explode( ',', $args['source']['val'][1] )
				)
			);
		}
		// Query posts
		$query = new WP_Query( $query );
		// Loop through posts
		if ( is_array( $query->posts ) ) foreach ( $query->posts as $post ) {
				// Get post thumbnail ID
				$thumb = ( $args['source']['type'] === 'media' ) ? $post->ID : get_post_thumbnail_id( $post->ID );
				// Thumbnail isn't set, go to next post
				if ( !is_numeric( $thumb ) ) continue;
				$slide = array(
					'image' => wp_get_attachment_url( $thumb ),
					'link'  => '',
					'title' => get_the_title( $post->ID )
				);
				if ( $args['link'] === 'image' || $args['link'] === 'lightbox' ) $slide['link'] = $slide['image'];
				elseif ( $args['link'] === 'custom' ) $slide['link'] = get_post_meta( $post->ID, 'su_slide_link', true );
				elseif ( $args['link'] === 'post' ) $slide['link'] = get_permalink( $post->ID );
				elseif ( $args['link'] === 'attachment' ) $slide['link'] = get_attachment_link( $thumb );
				$slides[] = $slide;
			}
		// Return slides
		
		return $slides;
	}

	public static function get_slides_432( $args ) {
		$args = wp_parse_args( $args, array(
				'gallery' => 1
			) );
		$slides = array();
		$args['gallery'] = ( $args['gallery'] === null ) ? 0 : $args['gallery'] - 1;
		$galleries = get_option( 'su_option_galleries-432' );
		// No galleries found
		if ( !is_array( $galleries ) ) return $slides;
		// If galleries found loop through them
		if ( isset( $galleries[$args['gallery']] ) ) $slides = $galleries[$args['gallery']]['items'];
		// Return slides
		return $slides;
	}

	public static function example() {
	
		global $king;
	
		// Check authentication
		self::access();
		// Check incoming data
		if ( !isset( $_REQUEST['code'] ) || !isset( $_REQUEST['id'] ) ) return;
		// Check for cache
		$output = get_transient( 'sc/examples/render/' . sanitize_key( $_REQUEST['id'] ) );
		if ( $output ) print( $output );
		// Cache not found
		else {
			ob_start();
			// Prepare data
			$code = $king->ext['fg']( sanitize_text_field( $_REQUEST['code'] ) );
			// Check for code
			if ( !$code ) die( '<p class="king-examples-error">' . __( 'Example code does not found, please check it later', 'arkahost' ) . '</p>' );
			// Clean-up the code
			$code = str_replace( array( "\t", '%su_' ), array( '  ', su_cmpt() ), $code );
			// Split code
			$chunks = explode( '-----', $code );
			// Show snippets
			do_action( 'sc/examples/preview/before' );
			foreach ( $chunks as $chunk ) {
				// Clean-up new lines
				$chunk = trim( $chunk, "\n\r" );
				// Calc textarea rows
				$rows = substr_count( $chunk, "\n" );
				$rows = ( $rows < 4 ) ? '4' : (string) ( $rows + 1 );
				$rows = ( $rows > 20 ) ? '20' : (string) ( $rows + 1 );
				echo wpautop( do_shortcode( $chunk ) );
				echo '<div style="clear:both"></div>';
				echo '<div class="king-examples-code"><span class="king-examples-get-code button"><i class="fa fa-code"></i>&nbsp;&nbsp;' . __( 'Get the code', 'arkahost' ) . '</span><textarea rows="' . $rows . '">' . esc_textarea( $chunk ) . '</textarea></div>';
			}
			do_action( 'sc/examples/preview/after' );
			$output = ob_get_contents();
			ob_end_clean();
			set_transient( 'sc/examples/render/' . sanitize_key( $_REQUEST['id'] ), $output );
			print( $output );
		}
		die();
	}

	public static function reset_examples() {
		foreach ( (array) king_shortcode_data::examples() as $example ) foreach ( (array) $example['items'] as $item ) delete_transient( 'sc/examples/render/' . $item['id'] );
	}

	public static function do_attr( $value ) {
		return do_shortcode( str_replace( array( '{', '}' ), array( '[', ']' ), $value ) );
	}

	public static function icon( $src = 'file' ) {
		return ( strpos( $src, '/' ) !== false ) ? '<img src="' . $src . '" alt="" />' : '<i class="fa fa-' . $src . '"></i>';
	}

	public static function get_icon( $args ) {
		$args = wp_parse_args( $args, array(
				'icon' => '',
				'size' => '',
				'color' => '',
				'style' => ''
			) );
		// Check for icon param
		if ( !$args['icon'] ) return;
		// Add trailing ; to the style param
		if ( $args['style'] ) $args['style'] = rtrim( $args['style'], ';' ) . ';';
		// Font Awesome icon
		if ( strpos( $args['icon'], 'icon:' ) !== false ) {
			// Add size
			if ( $args['size'] ) $args['style'] .= 'font-size:' . $args['size'] . 'px;';
			// Add color
			if ( $args['color'] ) $args['style'] .= 'color:' . $args['color'] . ';';
			// Query font-awesome stylesheet
			king_su_query_asset( 'css', 'font-awesome' );
			// Return icon
			return '<i class="fa fa-' . trim( str_replace( 'icon:', '', $args['icon'] ) ) . '" style="' . $args['style'] . '"></i>';
		}
		// Image icon
		elseif ( strpos( $args['icon'], '/' ) !== false ) {
			// Add size
			if ( $args['size'] ) $args['style'] .= 'width:' . $args['size'] . 'px;height:' . $args['size'] . 'px;';
			// Return icon
			return '<img src="' . $args['icon'] . '" alt="" style="' . $args['style'] . '" />';
		}
		// Icon is not detected
		return false;
	}

		public static function  awesome(){
		
		return array( 'glass', 'music', 'search', 'envelope-o', 'heart', 'star', 'star-o', 'user', 'film', 'th-large', 'th', 'th-list', 'check', 'times', 'search-plus', 'search-minus', 'power-off', 'signal', 'cog', 'trash-o', 'home', 'file-o', 'clock-o', 'road', 'download', 'arrow-circle-o-down', 'arrow-circle-o-up', 'inbox', 'play-circle-o', 'repeat', 'refresh', 'list-alt', 'lock', 'flag', 'headphones', 'volume-off', 'volume-down', 'volume-up', 'qrcode', 'barcode', 'tag', 'tags', 'book', 'bookmark', 'print', 'camera', 'font', 'bold', 'italic', 'text-height', 'text-width', 'align-left', 'align-center', 'align-right', 'align-justify', 'list', 'outdent', 'indent', 'video-camera', 'picture-o', 'pencil', 'map-marker', 'adjust', 'tint', 'pencil-square-o', 'share-square-o', 'check-square-o', 'arrows', 'step-backward', 'fast-backward', 'backward', 'play', 'pause', 'stop', 'forward', 'fast-forward', 'step-forward', 'eject', 'chevron-left', 'chevron-right', 'plus-circle', 'minus-circle', 'times-circle', 'check-circle', 'question-circle', 'info-circle', 'crosshairs', 'times-circle-o', 'check-circle-o', 'ban', 'arrow-left', 'arrow-right', 'arrow-up', 'arrow-down', 'share', 'expand', 'compress', 'plus', 'minus', 'asterisk', 'exclamation-circle', 'gift', 'leaf', 'fire', 'eye', 'eye-slash', 'exclamation-triangle', 'plane', 'calendar', 'random', 'comment', 'magnet', 'chevron-up', 'chevron-down', 'retweet', 'shopping-cart', 'folder', 'folder-open', 'arrows-v', 'arrows-h', 'bar-chart-o', 'camera-retro', 'key', 'cogs', 'comments', 'thumbs-o-up', 'thumbs-o-down', 'star-half', 'heart-o', 'sign-out', 'thumb-tack', 'external-link', 'sign-in', 'trophy', 'github-square', 'upload', 'lemon-o', 'phone', 'square-o', 'bookmark-o', 'phone-square', 'unlock', 'credit-card', 'rss', 'hdd-o', 'bullhorn', 'bell', 'certificate', 'hand-o-right', 'hand-o-left', 'hand-o-up', 'hand-o-down', 'arrow-circle-left', 'arrow-circle-right', 'arrow-circle-up', 'arrow-circle-down', 'globe', 'wrench', 'tasks', 'filter', 'briefcase', 'arrows-alt', 'users', 'link', 'cloud', 'flask', 'scissors', 'files-o', 'paperclip', 'floppy-o', 'square', 'bars', 'list-ul', 'list-ol', 'strikethrough', 'underline', 'table', 'magic', 'truck', 'money', 'caret-down', 'caret-up', 'caret-left', 'caret-right', 'columns', 'sort', 'sort-asc', 'sort-desc', 'envelope', 'undo', 'gavel', 'tachometer', 'comment-o', 'comments-o', 'bolt', 'sitemap', 'umbrella', 'clipboard', 'lightbulb-o', 'exchange', 'cloud-download', 'cloud-upload', 'user-md', 'stethoscope', 'suitcase', 'bell-o', 'coffee', 'cutlery', 'file-text-o', 'building-o', 'hospital-o', 'ambulance', 'medkit', 'fighter-jet', 'beer', 'h-square', 'plus-square', 'angle-double-left', 'angle-double-right', 'angle-double-up', 'angle-double-down', 'angle-left', 'angle-right', 'angle-up', 'angle-down', 'desktop', 'laptop', 'tablet', 'mobile', 'circle-o', 'quote-left', 'quote-right', 'spinner', 'circle', 'reply', 'github-alt', 'folder-o', 'folder-open-o', 'smile-o', 'frown-o', 'meh-o', 'gamepad', 'keyboard-o', 'flag-o', 'flag-checkered', 'terminal', 'code', 'reply-all', 'mail-reply-all', 'star-half-o', 'location-arrow', 'crop', 'code-fork', 'chain-broken', 'question', 'info', 'exclamation', 'superscript', 'subscript', 'eraser', 'puzzle-piece', 'microphone', 'microphone-slash', 'shield', 'calendar-o', 'fire-extinguisher', 'rocket', 'maxcdn', 'chevron-circle-left', 'chevron-circle-right', 'chevron-circle-up', 'chevron-circle-down', 'html5', 'css3', 'anchor', 'unlock-alt', 'bullseye', 'ellipsis-h', 'ellipsis-v', 'rss-square', 'play-circle', 'ticket', 'minus-square', 'minus-square-o', 'level-up', 'level-down', 'check-square', 'pencil-square', 'external-link-square', 'share-square', 'compass', 'caret-square-o-down', 'caret-square-o-up', 'caret-square-o-right', 'eur', 'gbp', 'usd', 'inr', 'jpy', 'rub', 'krw', 'btc', 'file', 'file-text', 'sort-alpha-asc', 'sort-alpha-desc', 'sort-amount-asc', 'sort-amount-desc', 'sort-numeric-asc', 'sort-numeric-desc', 'thumbs-up', 'thumbs-down', 'youtube-square', 'youtube', 'xing', 'xing-square', 'youtube-play', 'dropbox', 'stack-overflow', 'instagram', 'flickr', 'adn', 'bitbucket', 'bitbucket-square', 'tumblr', 'tumblr-square', 'long-arrow-down', 'long-arrow-up', 'long-arrow-left', 'long-arrow-right', 'apple', 'windows', 'android', 'linux', 'dribbble', 'foursquare', 'trello', 'female', 'male', 'gittip', 'sun-o', 'moon-o', 'archive', 'bug', 'vk', 'weibo', 'renren', 'pagelines', 'stack-exchange', 'arrow-circle-o-right', 'arrow-circle-o-left', 'caret-square-o-left', 'dot-circle-o', 'wheelchair', 'try', 'plus-square-o', 'skype', 'google-plus', 'pinterest', 'linkedin', 'twitter', 'facebook', 'github', 'youtube', 'vimeo-square');
	
	}	

	
	public static function icons() {
	
		$icons = array();
	
		foreach ( self::awesome() as $icon ) {
				$icons[] = '<i class="fa fa-' . $icon . '" title="' . $icon . '"></i>';
			}
		return implode( '', $icons );
	}
	
	public static function simpleline() {
		
		return array( 'user-female', 'user-follow', 'user-following', 'user-unfollow', 'trophy', 'screen-smartphone', 'screen-desktop', 'plane', 'notebook', 'moustache', 'mouse', 'magnet', 'energy', 'disc', 'cursor-move', 'crop', 'credit-card', 'chemistry', 'user', 'speedometer', 'social-youtube', 'social-twitter', 'social-tumblr', 'social-facebook', 'social-dropbox', 'social-dribbble', 'shield', 'screen-tablet', 'magic-wand', 'hourglass', 'graduation', 'ghost', 'game-controller', 'fire', 'eyeglasses', 'envelope-open', 'envelope-letter', 'bell', 'badge', 'anchor', 'wallet', 'vector', 'speech', 'puzzle', 'printer', 'present', 'playlist', 'pin', 'picture', 'map', 'layers', 'handbag', 'globe-alt', 'globe', 'frame', 'folder-alt', 'film', 'feed', 'earphones-alt', 'earphones', 'drop', 'drawer', 'docs', 'directions', 'direction', 'diamond', 'cup', 'compass', 'call-out', 'call-in', 'call-end', 'calculator', 'bubbles', 'briefcase', 'book-open', 'basket-loaded', 'basket', 'bag', 'action-undo', 'action-redo', 'wrench', 'umbrella', 'trash', 'tag', 'support', 'size-fullscreen', 'size-actual', 'shuffle', 'share-alt', 'share', 'rocket', 'question', 'pie-chart', 'pencil', 'note', 'music-tone-alt', 'music-tone', 'microphone', 'loop', 'logout', 'login', 'list', 'like', 'home', 'grid', 'graph', 'equalizer', 'dislike', 'cursor', 'control-start', 'control-rewind', 'control-play', 'control-pause', 'control-forward', 'control-end', 'calendar', 'bulb', 'bar-chart', 'arrow-up', 'arrow-right', 'arrow-left', 'arrow-down', 'ban', 'bubble', 'camcorder', 'camera', 'check', 'clock', 'close', 'cloud-download', 'cloud-upload', 'doc', 'envelope', 'eye', 'flag', 'folder', 'heart', 'info', 'key', 'link', 'lock', 'lock-open', 'magnifier', 'magnifier-add', 'magnifier-remove', 'paper-clip', 'paper-plane', 'plus', 'pointer', 'power', 'refresh', 'reload', 'settings', 'star', 'symbol-female', 'symbol-male', 'target', 'volume-1', 'volume-2', 'volume-off', 'users' );
		
	}
	
	public static function iconsSimple() {
	
		$icons = array();
	
		foreach ( self::simpleline() as $icon ) {
				$icons[] = '<span aria-hidden="true" class="icon-'. $icon . '" title="' . $icon . '"></span>';
			}
			
		return implode( '', $icons );
		
	}
	
	public static function etline() {
		
		return array( 'mobile', 'laptop', 'desktop', 'tablet', 'phone', 'document', 'documents', 'search', 'clipboard', 'newspaper', 'notebook', 'book-open', 'browser', 'calendar', 'presentation', 'picture', 'pictures', 'video', 'camera', 'printer', 'toolbox', 'briefcase', 'wallet', 'gift', 'bargraph', 'grid', 'expand', 'focus', 'edit', 'adjustments', 'ribbon', 'hourglass', 'lock', 'megaphone', 'shield', 'trophy', 'flag', 'map', 'puzzle', 'basket', 'envelope', 'streetsign', 'telescope', 'gears', 'key', 'paperclip', 'attachment', 'pricetags', 'lightbulb', 'layers', 'pencil', 'tools', 'tools-2', 'scissors', 'paintbrush', 'magnifying-glass', 'circle-compass', 'linegraph', 'mic', 'strategy', 'beaker', 'caution', 'recycle', 'anchor', 'profile-male', 'profile-female', 'bike', 'wine', 'hotairballoon', 'globe', 'genius', 'map-pin', 'dial', 'chat', 'heart', 'cloud', 'upload', 'download', 'target', 'hazardous', 'piechart', 'speedometer', 'global', 'compass', 'lifesaver', 'clock', 'aperture', 'quote', 'scope', 'alarmclock', 'refresh', 'happy', 'sad', 'facebook', 'twitter', 'googleplus', 'rss', 'tumblr', 'linkedin', 'dribbble' );
		
	}
	
	public static function iconsEtline() {
	
		$icons = array();
	
		foreach ( self::etline() as $icon ) {
				$icons[] = '<span aria-hidden="true" class="et-'. $icon . '" title="' . $icon . '"></span>';
			}
			
		return implode( '', $icons );
		
	}
	
	public static function tagP($html){

	    $exs = explode('</p>',$html);
	    $_out = '';
		foreach( $exs as $ex ){
			if( strpos( $ex, '<p>' ) === false ){
				$_out .= $ex;
			}else if( trim($ex) != '<p>' ){
				$_out .= $ex.'</p>';
			}
		}
	   
	    return $_out;
	    
	}

	public static function access() {
		if ( !self::access_check() ) wp_die( __( 'Access denied', 'arkahost' ) );
	}

	public static function access_check() {
		return current_user_can( 'edit_posts' );
	}

	public static function slide_link_input( $form_fields, $post ) {
		$form_fields['su_slide_link'] = array(
			'label' => __( 'Slide link', 'arkahost' ),
			'input' => 'text',
			'value' => get_post_meta( $post->ID, 'su_slide_link', true ),
			'helps' => sprintf( '<strong>%s</strong><br>%s', __( 'king-theme.com', 'arkahost' ), __( 'Use this field to add custom links to slides used with Slider, Carousel and Custom Gallery components', 'arkahost' ) )
		);
		
		$form_fields['customSize'] = array(
			'label' => 'Size',
			'helps'	=> '<select><option value="thumbnail">Thumbnail - 150 x 150</option><option value="medium">
								Medium - 300 x 187</option><option value="large">Large - 1024 x 640</option><option value="full" selected="selected">Full Size - 1680 x 1050</option></select>',			
		);
		
		return $form_fields;
	}

	public static function slide_link_save( $post, $attachment ) {
		if ( isset( $attachment['su_slide_link'] ) )
			update_post_meta( $post['ID'], 'su_slide_link', $attachment['su_slide_link'] );
		return $post;
	}
}

new Su_Tools;

/**
 * Shortcut for Su_Tools::decode_shortcode()
 */
function su_scattr( $value ) {
	return Su_Tools::do_attr( $value );
}

/**
 * Shortcut for Su_Tools::get_icon()
 */
function su_get_icon( $args ) {
	return Su_Tools::get_icon( $args );
}

