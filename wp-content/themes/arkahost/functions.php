<?php

/*
*
*	(c) king-theme.com
*
*/

###Load core of theme###
function king_incl_core( $file ){
	
	$path = trailingslashit( get_template_directory() ).$file;
	
	if( file_exists( $path ) ){
		require_once( $path );
	}else{
		wp_die( 'Could not load theme file: '.$path );
	}
	
}
king_incl_core( trailingslashit('core').'king.define.php' );
#
#
#	End Load
#
#
