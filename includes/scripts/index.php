<?php namespace mintpayments\gateway\types; ?>
<?php 
class Scripts{
	
		public  function init() {
	 	
        global $wp_styles;  
        $theme = wp_get_theme();
        $theme_version = $theme->version;  
        
        if( function_exists( 'env_dev' ) ) {
            $theme_version = time();
        }
		
   
    }
	
}
$type= new Scripts();
add_action('wp_enqueue_scripts', [$type , 'init' ], 1000);
?>