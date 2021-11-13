<?php
/*
*	This is private registration with WP
* 	(c) king-theme.com
*	
*/


global $king;

add_action( "wp_head", 'king_meta', 0 ); 
add_action( "get_header", 'king_set_header' ); 
add_action( "wp_head", 'king_custom_header', 99999 );
add_action( "wp_footer", 'king_custom_footer' );


function king_set_header( $name ){
	
	global $king;
	
	if( !empty( $name ) ){
		$file = ( strpos( $name, '.php' ) === false ) ? $name.'.php' : $name;
		if( file_exists( THEME_PATH.DS.'templates/header/'.$file ) ){	
			$king->cfg[ 'header' ] = $file;
			$king->cfg[ 'header_autoLoaded' ] = 1;
		}	
	}

}

/*-----------------------------------------------------------------------------------*/
# Setup custom header from theme panel
/*-----------------------------------------------------------------------------------*/

function king_custom_header(){		
	global $king;
	echo '<script type="text/javascript">var site_uri = "'.SITE_URI.'";var SITE_URI = "'.SITE_URI.'";var theme_uri = "'.THEME_URI.'";</script>';	
	
	$options_css = get_option( 'king_'.strtolower( THEME_NAME ).'_options_css', true ); 
	if( !empty( $options_css ) ){
		echo '<style type="text/css">';
		echo str_replace( array( '%SITE_URI%', '<style', '</style>', '%HOME_URL%' ), array( SITE_URI, '&lt;', '', SITE_URI ), $options_css );
		if( is_admin_bar_showing() ){
			echo '.header{margin-top:32px;}';
		}
		echo '</style>';
	}
	
}

/*-----------------------------------------------------------------------------------*/
# setup footer from theme panel
/*-----------------------------------------------------------------------------------*/


function king_custom_footer( ){
	
	global $king;	
	
	echo '<a href="#" class="scrollup" id="scrollup" style="display: none;">Scroll</a>'."\n";
	
	if( !empty( $king->cfg['GAID'] ) ){
		/*
		*	
		* Add google analytics in footer
		*	
		*/
		echo "<script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');ga('create', '".esc_attr($king->cfg['GAID'])."', 'auto');ga('send', 'pageview');</script>";
		
	}

	$king_sticky = true;
	if(isset($king->cfg[ 'stickymenu' ]) && $king->cfg[ 'stickymenu' ] ==1){
		$king_sticky = false;
	}
		
	echo '<script type="text/javascript">
	jQuery(document).ready(function($) {
		var king_sticky	= '.(($king_sticky)?'true':'false').';
		$(window).scroll(function () {

			if ($(window).scrollTop() > 48 ) {
				$("#scrollup").show();
				if(king_sticky)
					document.mainMenu.addClass("compact");
			} else {
				$("#scrollup").hide();
				if(king_sticky)
					document.mainMenu.removeClass("compact");
			}
		});
	});
	</script>';
	if(is_array($king->carousel) && count($king->carousel) >0){
		echo '<script type="text/javascript">
		jQuery(document).ready(function($) {
		';
		foreach($king->carousel as $car_js){
			echo "\n".$car_js."\n";
		}
		echo '
		});
		</script>';
	}
}


/* Add box select layouts into page|post editting */
add_action( 'save_post', 'king_save_post_process', 10, 2 );
function king_save_post_process( $post_id, $post ) {

	if( $post->post_type != 'page' && !empty( $_POST['king'] ) ){

		if( !empty( $_POST['king']['_type'] ) ){
			if( !add_post_meta( $post->ID , 'king_'.$_POST['king']['_type'] , $_POST['king'], true ) ){
				update_post_meta( $post->ID , 'king_'.$_POST['king']['_type'] , $_POST['king'] );
			}
		}
		
	}	
	
	if( $post->post_type == 'page' && !empty( $_POST['king'] ) ){
		
		if( !empty( $_POST['king'] ) ){
			foreach( $_POST['king'] as $key => $value ){
				if( !empty( $value ) ){
					if( !add_post_meta( $post->ID, '_king_page_'.$key, $value, true ) ){
						update_post_meta( $post->ID, '_king_page_'.$key, $value );
					}
				}else{
					delete_post_meta( $post->ID, '_king_page_'.$key );
				}
			}
		}
		
	}

}

function king_post_save_regexp($m){
		
	return str_replace('"',"'",$m[0]);
	
}

add_action("after_switch_theme", "king_activeTheme", 1000 ,  1);
/*----------------------------------------------------------*/
#	Active theme -> import some data
/*----------------------------------------------------------*/
function king_activeTheme( $oldname, $oldtheme=false ) {
 	global $king;
	#Check to import base of settings
	$opname = strtolower( THEME_NAME) .'_import';
	$king_opimp  = get_option( $opname, true );

	if($king_opimp == 1){
		
		get_template_part( 'core/import' );
	}

	
	# Make sure all images & icons are readable
	king_check_filesReadable( ABSPATH.'wp-content'.DS.'themes'.DS.$king->stylesheet );
	
	if( $king->template == $king->stylesheet ){
		
		?>
		<style type="text/css">
			body{display:none;}
		</style>
		<script type="text/javascript">
			/*Redirect to install required plugins after active theme*/
			window.location = '<?php echo esc_url( 'admin.php?page='.strtolower( THEME_NAME ).'-importer' ); ?>';
		</script>
		
		<?php	
	
	}
}

/*-----------------------------------------------------------------------------------*/
# 	Check un-readable files, and change chmod to readable
/*-----------------------------------------------------------------------------------*/

function king_check_filesReadable( $dir = '' ){

	if( $dir != '' && is_dir( $dir ) ){
		
		if ( $handle = opendir( $dir ) ){
			
			@chmod( $dir, 0755 );
			
			while ( false !== ( $entry = readdir($handle) ) ) {
				if( $entry != '.' && $entry != '..' && strpos($entry, '.php') === false && is_file( $dir.DS.$entry ) ){
					
					$perm = substr(sprintf('%o', fileperms( $dir.DS.$entry )), -1 );

					if( $perm == '0' ){
						@chmod( $dir.DS.$entry, 0644 );
					}	
				}
				if( $entry != '.' && $entry != '..' && is_dir( $dir.DS.$entry ) ){
					king_check_filesReadable( $dir.DS.$entry );
				}
			}
		}
		
	}
}

/*-----------------------------------------------------------------------------------*/
# 	Register Menus in NAV-ADMIN
/*-----------------------------------------------------------------------------------*/


add_action('admin_menu', 'king_settings_menu');

function king_settings_menu() {

	add_theme_page( THEME_NAME.' Panel', THEME_NAME.' - Options', 'edit_theme_options', THEME_SLUG.'-panel', 'king_theme_panel');
	add_theme_page( THEME_NAME.' Import', THEME_NAME.' - Demos', 'edit_theme_options', THEME_SLUG.'-importer', 'king_theme_import');
}

function king_theme_panel(){
	
	global $king, $king_options;

	$king->assets(array(
		array('js' => THEME_URI.'/core/assets/jscolor/jscolor')
	));
	
	$king_options->_options_page_html();
	
}

function king_theme_import() {
	
	global $king;

	$king->assets(array(
		array('css' => THEME_URI.'/core/assets/css/bootstrap.min'),
		array('css' => THEME_URI.'/options/css/theme-pages')
	));
	king_incl_core( 'core'.DS.'sample.php' );

}


add_action('add_meta_boxes','king_page_layout_template_metabox');
/*----------------------------------------------------------*/
#	Add select layout on page edit
/*----------------------------------------------------------*/
function king_page_layout_template_metabox() {
	
	add_meta_box('KingFeildsPage', THEME_NAME.' Theme - Page Settings', 'king_page_fields_meta_box', 'page', 'normal', 'core');
    add_meta_box('KingFeildsTesti', __('Testimonial Options','arkahost'), 'king_testi_fields_meta_box', 'testimonials', 'normal', 'high');
    add_meta_box('KingFeildsTeam', __('Staff Profiles','arkahost'), 'king_staff_fields_meta_box', 'our-team', 'normal', 'high');
    add_meta_box('KingFeildsWork', __('Project\'s Link','arkahost'), 'king_work_fields_meta_box', 'our-works', 'normal', 'high');
    add_meta_box('KingFeildsPricing', __('Pricing Tables Fields','arkahost'), 'king_pricing_fields_meta_box', 'pricing-tables', 'normal', 'high');
    add_meta_box('KingFeildsMegaMenu', __('Extra Setting','arkahost'), 'megamenu_meta_box', 'mega_menu', 'normal', 'high');
}

function king_page_fields_meta_box( $post ){
	
	global $king, $king_options;

	locate_template( 'options'.DS.'options.php', true );
		
	$listHeaders = array();
	if ( $handle = opendir( THEME_PATH.DS.'templates'.DS.'header' ) ){
		
		$listHeaders[ 'default' ] = array('title' => '.Use Global Setting', 'img' => THEME_URI.'/core/assets/images/load-default.jpg' );
		
		while ( false !== ( $entry = readdir($handle) ) ) {
			if( $entry != '.' && $entry != '..' && strpos($entry, '.php') !== false  ){
				$title  = ucwords( str_replace( '-', ' ', basename( $entry, '.php' ) ) );
				$listHeaders[ $entry ] = array('title' => $title, 'img' => THEME_URI.'/templates/header/thumbnails/'.basename( $entry, '.php' ).'.jpg' );
			}
		}
	}
	
	$listFooters = array();
	if ( $handle = opendir( THEME_PATH.DS.'templates'.DS.'footer' ) ){
		$listFooters[ 'default' ] = array('title' => '.Use Global Setting', 'img' => THEME_URI.'/core/assets/images/load-default.jpg' );
		while ( false !== ( $entry = readdir($handle) ) ) {
			if( $entry != '.' && $entry != '..' && strpos($entry, '.php') !== false  ){
				$title  = ucwords( str_replace( '-', ' ', basename( $entry, '.php' ) ) );
				$listFooters[ $entry ] = array('title' => $title, 'img' => THEME_URI.'/templates/footer/thumbnails/'.basename( $entry, '.php' ).'.jpg' );
			}
		}
	}

	$sidebars = array( '' => '--Select Sidebar--' );
	
	if( !empty( $king->cfg['sidebars'] ) ){
		foreach( $king->cfg['sidebars'] as $sb ){
			$sidebars[ sanitize_title_with_dashes( $sb ) ] = esc_html( $sb );
		}
	}
	
	$fields = array(
		array(
			'id' => 'logo',
			'type' => 'upload',
			'title' => __('Upload Logo', 'arkahost'), 
			'sub_desc' => __('This will be display as logo at header of only this page', 'arkahost'),
			'desc' => __('Upload new or from media library to use as your logo. We recommend that you use images without borders and throughout.', 'arkahost'),
			'std' => ''
		),		
		array(
			'id' => 'modal',
			'type' => 'upload',
			'title' => __('Upload Image Modal', 'arkahost'), 
			'sub_desc' => __('Image to show on Modal Window', 'arkahost'),
			'std' => ''
		),
		array(
			'id' => 'modal_action',
			'type' => 'textarea',
			'title' => __('Modal Actions', 'arkahost'),
			'std'	=> '',
			'sub_desc' => 'If you want to more action when Modal Window displays',
			'desc' => wp_kses( __( 'Your HTML code (allows shortcode) will be display into Modal Window, Use shortcode <strong><i>[image]</i></strong> into your code to display the photos you upload above.', 'arkahost' ), array('i'=>array(),'strong'=>array())).'<br />'.esc_html('Example: <a href="#"> [image] </a>')
		),
		array(
			'id' => 'page_title',
			'type' => 'textarea',
			'title' => __('Page Title', 'arkahost'),
			'std'	=> '',
			'sub_desc' => __( 'Page Title will display on Breadcrumn instead default title.', 'arkahost' ),
			'desc' => ''
		),	
		array(
			'id' => 'breadcrumb',
			'type' => 'select',
			'title' => __('Display Breadcrumb', 'arkahost'), 
			'options' => array( 
				'global' => 'Use Global Settings',
				'no' => 'No, Thanks!',
				'page_title1' => 'Style 1', 
				'page_title1 sty2' => 'Style 2', 
				'page_title1 sty3' => 'Style 3', 
				'page_title1 sty4' => 'Style 4', 
				'page_title1 sty5' => 'Style 5', 
				'page_title1 sty6' => 'Style 6', 
				'page_title1 sty7' => 'Style 7', 
				'page_title1 sty8' => 'Style 8', 
				'page_title1 sty9' => 'Style 9', 
				'page_title1 sty10' => 'Style 10', 
				'page_title1 sty11' => 'Style 11', 
				'page_title1 sty12' => 'Style 12',
				'page_title1 sty13' => 'Style 13',
			),
			'std' => '',
			'sub_desc' => __( 'Set for show or dont show breadcrumb for this page.', 'arkahost' )
		),
		
		array(
			'id' => 'breadcrumb_bg',
			'type' => 'upload',
			'title' => __('Upload Breadcrumb Background Image', 'arkahost'), 
			'std' => '',
			'sub_desc' => __( 'Upload your Breadcrumb background image for this page.', 'arkahost' )
		),		
		array(
			'id' => 'breadcrumb_tag',
			'type' => 'select',
			'title' => __('Breadcrumb Title Tag', 'arkahost'),
			'desc' => __('The html tag for title content. Default is H1', 'arkahost'),
			'options' => array(
				'h1' => 'H1',
				'h2' => 'H2',
				'h3' => 'H3',
				'h4' => 'H4',
				'h5' => 'H5',
				'h6' => 'H6',
				'p' => 'P',
				'span' => 'SPAN',				
			),
			'std' => 'h1'
		),
		array(
			'id' => 'sidebar',
			'type' => 'select',
			'title' => __('Select Sidebar', 'arkahost'), 
			'options' => $sidebars,
			'std' => '',
			'sub_desc' => __( 'Select template from Page Attributes at right side', 'arkahost' ),
			'desc' => '<br /><br />'.__( 'Select a dynamic sidebar what you created in theme-panel to display under page layout.', 'arkahost' )
		),
		array(
			'id' => 'description',
			'type' => 'textarea',
			'title' => __('Description', 'arkahost'),
			'std'	=> '',
			'sub_desc' => __( 'The description will show in content of meta tag for SEO + Sharing purpose', 'arkahost' ),
		),
		array(
			'id' => 'header',
			'type' => 'radio_img',
			'title' => __('Select Header', 'arkahost'),
			'sub_desc' => __('Overlap: The header will cover up anything beneath it.', 'arkahost'),
			'options' => $listHeaders,
			'std' => ''
		),
		array(
			'id' => 'footer',
			'type' => 'radio_img',
			'title' => __('Select Footer', 'arkahost'),
			'sub_desc' => __('Select footer to display for only this page. This path has located /templates/footer/{-file-}', 'arkahost'),
			'options' => $listFooters,
			'std' => ''
		)
	);
	
	echo '<textarea name="king[vc_cache]" id="king_vc_cache" style="display:none">'.esc_html( get_post_meta( $post->ID, '_king_page_vc_cache', true) ).'</textarea>';
	
	echo '<div class="nhp-opts-group-tab single-page-settings" style="display:block;padding:0px;">';
	echo '<table class="form-table" style="display:inline-block;border:none;"><tbody>';
	foreach( $fields as $key => $field ){
		
		$field['std'] = get_post_meta( $post->ID,'_king_page_'.$field['id'] , true );
		
		if( empty( $field['std'] ) ){
			if( $field['id'] == 'header' ){
				$field['std'] = 'default';
			}
			if( $field['id'] == 'footer' ){
				$field['std'] = 'default';
			}
			if(  $field['id'] == 'breadcrumb' ){
				$field['std'] = 'global';
			}
		}
		
		locate_template( 'options'.DS.'fields'.DS.$field['type'].'/field_'.$field['type'].'.php', true );
		
		$field_class = 'king_options_'.$field['type'];
		
		if( class_exists( $field_class ) ){
			
			$render = '';
			$obj = new stdClass();
			$obj->extra_tabs = '';
			$obj->sections = '';
			$obj->args = '';
			$render = new $field_class($field, $field['std'], $obj );
			
			echo '<tr><th scope="row">'.esc_html($field['title']).'<span class="description">';
			echo esc_html($field['sub_desc']).'</span></th>';
			echo '<td>';
			
			$render->render();
			
			if( method_exists( $render, 'enqueue' ) ){
				$render->enqueue();
			}	
			
			echo '</td></tr>';
		}
	}
	echo '</tbody></table></div>';
	
}

function king_testi_fields_meta_box( $post ) {

	$testi = get_post_meta( $post->ID , 'king_testi' );
	if( !empty( $testi ) ){
		$testi  = $testi[0];
	}else{
		$testi = array();
	}	
	
?>

	<table>
		<tr>
			<td>
				<label><?php _e('Website','arkahost'); ?>: </label>
			</td>
			<td>	
				<input type="text" name="king[website]" value="<?php echo esc_attr( isset($testi['website'])?$testi['website']:'' );	?>" />
			</td>
		</tr>
		<tr>
			<td>
				<br />
				<label><?php _e('Rate','arkahost'); ?>: </label>
			</td>
			<td>
				<br />
				<i class="fa fa-star"></i> 
				<input type="radio" name="king[rate]" <?php if(isset($testi['rate'])){if($testi['rate']==1)echo 'checked';} ?> value="1" />
				&nbsp; 
				<i class="fa fa-star"></i>
				<i class="fa fa-star"></i> 
				<input type="radio" <?php if(isset($testi['rate'])){if($testi['rate']==2)echo 'checked';} ?> name="king[rate]" value="2" />
				&nbsp; 
				<i class="fa fa-star"></i>
				<i class="fa fa-star"></i>
				<i class="fa fa-star"></i> 
				<input type="radio" name="king[rate]" <?php if(isset($testi['rate'])){if($testi['rate']==3)echo 'checked';} ?> value="3" />
				&nbsp; 
				<i class="fa fa-star"></i>
				<i class="fa fa-star"></i>
				<i class="fa fa-star"></i>
				<i class="fa fa-star"></i>
				<input type="radio" name="king[rate]" <?php if(isset($testi['rate'])){if($testi['rate']==4)echo 'checked';} ?> value="4" />
				&nbsp; 
				<i class="fa fa-star"></i>
				<i class="fa fa-star"></i>
				<i class="fa fa-star"></i>
				<i class="fa fa-star"></i>
				<i class="fa fa-star"></i> 
				<input type="radio" name="king[rate]" <?php if(isset($testi['rate'])){if($testi['rate']==5)echo 'checked';} ?> value="5" />
			</td>
		</tr>
	</table>
	
	
	<input type="hidden" name="king[_type]" value="testi" />
	
<?php
}

function king_staff_fields_meta_box( $post ) {

	$staff = get_post_meta( $post->ID , 'king_staff' );
	if( !empty( $staff ) ){
		$staff  = $staff[0];
	}else{
		$staff = array();
	}	
	
?>

	<table>
		<tr>
			<td>
				<label><?php _e('Position','arkahost'); ?>: </label>
			</td>
			<td>	
				<input type="text" name="king[position]" value="<?php echo esc_attr( isset($staff['position'])?$staff['position']:'' );	?>" />
			</td>
		</tr>
		<tr>
			<td>
				<label><?php _e('Facebook','arkahost'); ?>: </label>
			</td>
			<td>
				<input type="text" name="king[facebook]" value="<?php echo esc_attr( isset($staff['facebook'])?$staff['facebook']:'' );	?>" />
			</td>
		</tr>
		<tr>
			<td>
				<label><?php _e('Twitter','arkahost'); ?>: </label>
			</td>
			<td>
				<input type="text" name="king[twitter]" value="<?php echo esc_attr( isset($staff['twitter'])?$staff['twitter']:'' );	?>" />
			</td>
		</tr>
		<tr>
			<td>
				<label><?php _e('Google+','arkahost'); ?>: </label>
			</td>
			<td>
				<input type="text" name="king[gplus]" value="<?php echo esc_attr( isset($staff['gplus'])?$staff['gplus']:'' );	?>" />
			</td>
		</tr>
		<tr>
			<td>
				<label><?php _e('LinkedIn','arkahost'); ?>: </label>
			</td>
			<td>
				<input type="text" name="king[linkedin]" value="<?php echo esc_attr( isset($staff['linkedin'])?$staff['linkedin']:'' );	?>" />
			</td>
		</tr>
	</table>
	
	<input type="hidden" name="king[_type]" value="staff" />

<?php
}


function king_work_fields_meta_box( $post ) {

	$work = get_post_meta( $post->ID , 'king_work', true );
	if( empty( $work ) ){


		$work = array();
	}	
	
?>

	<input type="text" name="king[link]" value="<?php echo esc_attr( isset($work['link'])?$work['link']:'' ); ?>" style="width: 100%;" />
	
	<input type="hidden" name="king[_type]" value="work" />
	
<?php
}



function king_pricing_fields_meta_box( $post ) {

	$pricing = get_post_meta( $post->ID , 'king_pricing' );
	if( !empty( $pricing ) ){
		$pricing  = $pricing[0];
	}else{
		$pricing = array();
	}	
	
?>

	<table>
		<tr>
			<td>
				<label><?php _e('Price','arkahost'); ?>: </label>
			</td>
			<td>	
				<input type="text" name="king[price]" value="<?php echo esc_attr( isset( $pricing['price'] ) ? $pricing['price'] : '' );	?>" /> / 
				<input type="text" name="king[per]" value="<?php echo esc_attr( isset( $pricing['per'] ) ? $pricing['per'] : '' );	?>" />
			</td>
		</tr>
		<tr>
			<td>
				<label><?php _e('Regularly Price','arkahost'); ?>: </label>
			</td>
			<td>
				<input type="text" name="king[regularly_price]" value="<?php 
					echo esc_attr( isset( $pricing['regularly_price'] ) ? $pricing['regularly_price'] :'' );
				?>" /> 
			</td>
		</tr>
		<tr>
			<td>
				<label><?php _e('Currency','arkahost'); ?>: </label>
			</td>
			<td>
				<input type="text" name="king[currency]" value="<?php 
					echo esc_attr( isset( $pricing['currency'] ) ? $pricing['currency'] : '' );
				?>" /> 
			</td>
		</tr>
		<tr>
			<td>
				<label><?php _e('Best Seller','arkahost'); ?>: </label>
			</td>
			<td>
				<input type="radio" name="king[best_seller]" value="yes" <?php 
					if( isset( $pricing['best_seller'] ) ){
						if( $pricing['best_seller'] == 'yes' ){
							echo 'checked';
						}
					}	
				?> /> Yes  
				<input type="radio" name="king[best_seller]" value="no" <?php 
					if( isset($pricing['best_seller']) ){
						if( $pricing['best_seller'] == 'no' ){
							echo 'checked';
						}
					}	
				?> /> No
			</td>
		</tr>
		<tr>
			<td>
				<label><?php _e('Attributes','arkahost'); ?>: </label>
			</td>
			<td>
				<textarea rows="8" cols="80" name="king[attr]"><?php 
					echo esc_html( isset($pricing['attr'])?$pricing['attr']:'' );	
				?></textarea>
			</td>
		</tr>
		<tr>
			<td>
				<label><?php _e('Text button submit','arkahost'); ?>: </label>
			</td>
			<td>
				<input type="text" name="king[textsubmit]" value="<?php echo esc_attr( isset($pricing['textsubmit'])?$pricing['textsubmit']:'' );	?>" />
			</td>
		</tr>
		<tr>
			<td>
				<label><?php _e('Link submit','arkahost'); ?>: </label>
			</td>
			<td>
				<input type="text" name="king[linksubmit]" value="<?php echo esc_attr( isset($pricing['linksubmit'])?$pricing['linksubmit']:'' );	?>" />
			</td>
		</tr>
	</table>
	
	<input type="hidden" name="king[_type]" value="pricing" />

<?php
}


/*Add post type*/
add_action( 'init', 'king_init' );
function king_init() {

	global $king;

    if( is_admin() ){
   		$king->sysInOut();
   	}else{
   		if( !empty( $king->cfg['admin_bar'] ) ){
   			if( $king->cfg['admin_bar'] != 'show' ){
		   		show_admin_bar(false);
		   	}	
   		}
   	}	
}

/*Add Custom Sidebar*/
function king_widgets_init() {
		
	global $king;
	
	$sidebars = array(
		
		'sidebar' => array( 
			__( 'Main Sidebar', 'arkahost' ), 
			__( 'Appears on posts and pages at left-side or right-side except the optional Front Page template.', 'arkahost' )
		),
		
		'sidebar-woo' => array( 
			__( 'Archive Products Sidebar', 'arkahost' ), 
			__( 'Appears on Archive Products.', 'arkahost' )
		),	
		'sidebar-woo-single' => array( 
			__( 'Single Product Sidebar', 'arkahost' ), 
			__( 'Appears on Single Product detail page', 'arkahost' )
		),
						
		'footer_1' => array( 
			__( 'Footer Column 1', 'arkahost' ), 
			__( 'Appears on column 1 at Footer', 'arkahost' )
		),		
		
		'footer_2' => array( 
			__( 'Footer Column 2', 'arkahost' ), 
			__( 'Appears on column 2 at Footer', 'arkahost' )
		),		
		
		'footer_3' => array( 
			__( 'Footer Column 3', 'arkahost' ), 
			__( 'Appears on column 3 at Footer', 'arkahost' )
		),		
		
		'footer_4' => array( 
			__( 'Footer Column 4', 'arkahost' ), 
			__( 'Appears on column 4 at Footer', 'arkahost' )
		),
		
	);
	
	if( !empty( $king->cfg['sidebars'] ) ){
		foreach( $king->cfg['sidebars'] as $sb ){
			$sidebars[ sanitize_title_with_dashes( $sb ) ] = array(
				esc_html( $sb ), 
				__( 'Dynamic Sidebar - Manage via theme-panel', 'arkahost' )
			);
		}
	}
	
	foreach( $sidebars as $k => $v ){
	
		register_sidebar( array(
			'name' => $v[0],
			'id' => $k,
			'description' => $v[1],
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget' => '</aside>',
			'before_title' => '<h3 class="widget-title"><span>',
			'after_title' => '</span></h3>',
		));	
		
	}
	
}
add_action( 'widgets_init', 'king_widgets_init' );


add_filter( 'image_size_names_choose', 'king_custom_sizes' );
function king_custom_sizes( $sizes ) {
    return array_merge( $sizes, array(
        'large-small' => __('Large Small', 'arkahost'),
    ) );
}

add_filter( 'wp_nav_menu_items','king_mainnav_last_item', 10, 2 ); 
function king_mainnav_last_item( $items, $args ) {
	if( $args->theme_location == 'primary' || $args->theme_location == 'onepage' ){
		
		global $king, $woocommerce;

		if( empty( $king->cfg['searchNav'] ) ){
			$king->cfg['searchNav'] = 'show';
		}	
		/*
		*	Display Search Box
		*/
		if( $king->cfg['searchNav'] == 'show' ){
			$items .= '<li class="dropdown yamm ext-nav search-nav">'.
						  '<a href="#"><i class="icon icon-magnifier"></i></a>'.
						  '<ul class="dropdown-menu">'.
						  '<li>'.get_search_form( false ).'</li>'.
						  '</ul>'.
					  '</li>'; 
		}	
		
	}
	return $items; 
}

/*-----------------------------------------------------------------------------------*/
# Load layout from system before theme loads
/*-----------------------------------------------------------------------------------*/

function king_load_layout( $file ){
	
	global $king, $post;
	
	if( is_home() ){
	
		$cfg = ''; $_file = '';
	
		if( !empty( $king->cfg['blog_layout'] ) ){
			$cfg = $king->cfg['blog_layout'];
		}
		
		if( file_exists( THEME_PATH.DS.'templates'.DS.'blog-'.$cfg.'.php' ) ){
			$_file =  'templates'.DS.'blog-'.$cfg.'.php';
		}
		
		if( get_option('show_on_front',true) == 'page' && $_file === '' ){
			$id = get_option('page_for_posts',true);
			if( !empty( $id ) ){
				$get_page_tem = get_page_template_slug( $id );
			    if( !empty( $get_page_tem ) ){
					$_file = $get_page_tem;
				}	
			}
		}
	
		if( !empty( $_GET['layout'] ) ){
			if( file_exists( THEME_PATH.DS.'templates'.DS.'blog-'.$_GET['layout'].'.php' ) ){
				$_file = 'templates'.DS.'blog-'.$_GET['layout'].'.php';
			}	
		}
		
		if( !empty( $_file ) ){
			return locate_template( $_file );
		}
	}
	
	if( $king->vars( 'action', 'login' ) ){
		return locate_template( 'templates'.DS.'king.login.php' );
	}
	if( $king->vars( 'action', 'register' ) ){
		return locate_template( 'templates'.DS.'king.register.php' );
	}
	if( $king->vars( 'action', 'forgot' ) ){
		return locate_template( 'templates'.DS.'king.forgot.php' );
	}
	
	$king->tp_mode( basename( $file, '.php' ) );
	
	return $file;

}
add_action( "template_include", 'king_load_layout', 99 );

function king_exclude_category( $query ) {
    if ( $query->is_home() && $query->is_main_query() ) {
    	global $king;
    	if( !empty( $king->cfg['timeline_categories'] ) ){
	    	if( $king->cfg['timeline_categories'][0] != 'default' ){
		    	 $query->set( 'cat', implode( ',', $king->cfg['timeline_categories'] ) );	
	    	}
    	}
    }
}
add_action( 'pre_get_posts', 'king_exclude_category' );

function king_admin_notice() {
	if ( get_option('permalink_structure', true) === false ) {
    ?>
    <div class="updated">
        <p>
	        <?php sprintf( wp_kses( __('You have not yet enabled permalink, the 404 page and some functions will not work. To enable, please <a href="%s">Click here</a> and choose "Post name"', 'arkahost' ), array('a'=>array()) ), SITE_URI.'/wp-admin/options-permalink.php' ); ?>
        </p>
    </div>
    <?php
    }
}
add_action( 'admin_notices', 'king_admin_notice' );

// Add slide menu CSS to body tag
add_filter( 'body_class', 'king_body_classes' );
function king_body_classes( $classes ) {
	global $king;
	if(isset($king->cfg['slide_menu']) && $king->cfg['slide_menu'] == 1)
		$classes[] = 'slide-menu';
	return $classes;
}

function kingtheme_the_excerpt($text){
	global $post;
	$pagedes = get_post_meta( $post->ID, '_king_page_description', true );
	if( !empty( $pagedes ) ){
		return esc_attr( $pagedes );
	}
	return $text;
}
add_filter('get_the_excerpt', 'kingtheme_the_excerpt');


//define new ways for our-work item pagination
add_filter('previous_post_link', 'kingtheme_adjacent_post_link', 10, 5);
add_filter('next_post_link', 'kingtheme_adjacent_post_link', 10, 5);


function kingtheme_adjacent_post_link($output, $format, $link, $post, $adjacent){

	if ( !$post ) {
        $output = '';
    }
    
	if( empty( $post->post_type ) || $post->post_type != 'our-works')
		return $output;

	$title = $post->post_title;
    $title = apply_filters( 'the_title', $title, $post->ID );
    $rel = ($adjacent == 'previous')  ? 'prev' : 'next';

    $icon_class = ($adjacent == 'previous')  ? 'fa-chevron-left' : 'fa-chevron-right';
	
	$output = '<a class="our-works-nav our-works-nav-' . $rel . '" href="' . get_permalink( $post ) . '" rel="'. $rel .'" title="' . $title .'"><i class="fa ' . $icon_class .'"></i></a>';

 	return $output;
}


/*
* Defind ajax for newsletter actions
*/
if( !function_exists( 'king_newsletter' ) ){
	
	add_action( 'wp_ajax_king_newsletter', 'king_newsletter' );
	add_action( 'wp_ajax_nopriv_king_newsletter', 'king_newsletter' );

	function king_newsletter () { 
		global $king;

		if( !empty( $_POST[ 'king_newsletter' ] ) ) 
		{
			
			if( $_POST[ 'king_newsletter' ] == 'subcribe' ){

				$email    = $_POST[ 'king_email' ];
				$hasError = false;
				$status   = array();
				
				if ( trim( $email ) === '' ) {
					$status = array( 
						'error',
						__( 'Error: Please enter your email', 'arkahost' )
					);
					$hasError = true;
				}

				if( !$hasError && !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {

					$status = array( 
						'error',
						__( 'Error: Your email is invalid', 'arkahost' )
					);
					$hasError = true;
				}

				if( !$hasError ){

					//check which method in use
					if( isset( $king->cfg['newsletter_method'] ) && $king->cfg['newsletter_method'] == 'mc' ){

						locate_template( 'core' . DS . 'inc' . DS . 'MCAPI.class.php', true);

											
						$api_key =  $king->cfg['mc_api'];	// grab an API Key from http://admin.mailchimp.com/account/api/			
						$list_id = $king->cfg['mc_list_id'];
						$mc_api  = new MCAPI($api_key);
						$mc_api->useSecure(true);

						//If one of config is empty => return error

						if( empty( $api_key ) || empty( $list_id ) ){

							$status = array( 
								'error',
								__('Error: Can not signup into list. Please contact administrator to solve issues.', 'arkahost' )
							);
							$hasError = true;
						}
						else
						{
							if( $mc_api->listSubscribe( $list_id, $email, '') === true && empty( $status) ) {

								$status    = array( 
									'success',
									__('Success! Check your email to confirm sign up.', 'arkahost' )
								);

							}else{

								$status = array( 
									'error',
									__('Error: ' . $mc_api->errorMessage , 'arkahost' )
								);

							}
						}
						
					}
					else /* Subcribe email to post type subcribe */
					{
						if ( !post_type_exists( 'subcribers' ) ){
							$status = array( 
								'error',
								__('Error: Can not signup into list. Please contact administrator to solve issues.', 'arkahost' )
							);
							king_return_ajax( $status);
						}

						if ( !get_page_by_title( $email, 'OBJECT', 'subcribers') )
						{
		
							$subcribe_data = array(
								'post_title'   => wp_strip_all_tags( $email ),
								'post_content' => '',
								'post_type'    => 'subcribers',
								'post_status'  => 'pending'
							);
							
							$subcribe_id = wp_insert_post( $subcribe_data );

							if ( is_wp_error( $subcribe_id ) ) {

								$errors = $id->get_error_messages();

								foreach ( $errors as $error ) {
									$error_msg .= "{$error}\n";
								}

							}else{

								$status    = array( 
									'success',
									__('Success! Your email is subcribed.', 'arkahost' )
								);

							}
		
						}else{

							$status    = array( 
								'error',
								__('Error: This email already is subcribed', 'arkahost' )
							);
						}
					}
					
				}

				king_return_ajax( $status);
			}
		}
	}
}
if( !function_exists( 'king_return_ajax' ) ){

	function king_return_ajax( $status){

		@ob_clean();

		echo '{"status":"' . $status[0] . '","messages":"' . $status[1] . '"}';

		wp_die();

	}
}



function megamenu_meta_box( $post ){
	global $king;

	locate_template( 'options'.DS.'options.php', true );
	$megabox = get_post_meta( $post->ID , 'king_megamenu' );
	if( !empty( $megabox ) ){
		$megabox  = $megabox[0];
	}else{
		$megabox = array();
	}
	?>
	<table>
		<tr>
			<td>
				<label><?php _e('Width Of Menu','arkahost'); ?>: </label>
			</td>
			<td>
				<input type="text" name="king[menu_width]" value="<?php echo esc_attr( isset($megabox['menu_width'])? $megabox['menu_width']:'' );	?>" />
			</td>
		</tr>
	</table>
	
	<input type="hidden" name="king[_type]" value="megamenu" />
	<?php
}