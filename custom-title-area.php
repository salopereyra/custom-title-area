<?php
/**
 * Genesis Custom Title Area
 *
 * Add custom classes to site-title and site-description in your Genesis Child Theme. Must be using the Genesis Framework.
 *
 * @package           Genesis_Custom_Title_Area
 * @author            Salome Pereyra
 * @license           GPL-3.0+
 * @link              http://www.salomepereyra.com
 *
 * Plugin Name:       Genesis Custom Title Area
 * Plugin URI:        http://www.salomepereyra.com
 * Description:       Add custom classes to site-title and site-description in your Genesis Child Theme. Must be using the Genesis Framework.
 * Version:           1.0
 * Author:            Salome Pereyra
 * Author URI:        http://www.salomepereyra.com
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       genesis-custom-title-area
 * Domain Path:       /languages/
 * Function prefix:   gcta_
 * GitHub Plugin URI: https://github.com/salopereyra/genesis-custom-title-area
 * GitHub Branch:     master
 */

/*
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 3, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, see http://www.gnu.org/licenses/gpl-3.0.txt
*/

// If this file is called directly, abort.
if( !defined( 'ABSPATH' ) ) {
    die;
}

/**
 * Defining Genesis Community constants
 *
 * @since 0.2.0
 */

if( !defined( 'GCTA_VERSION' ) )define( 'GCTA_VERSION', '0.1' );
if( !defined( 'GCTA_BASE_FILE' ) )define( 'GCTA_BASE_FILE', __FILE__ );

define( 'GCTA_SETTINGS_FIELD', 'gcta-settings' );
define( 'GCTA_DOMAIN', 'genesis-custom-title-area' );


class BE_Custom_Title_Area {
	
	/**
	 * Primary constructor.
	 * @since 0.1
	 */
	function __construct() {

		// Run on plugin activation
		register_activation_hook( __FILE__, array( $this, 'gcta_activation_check' ) );
				
		// Bootstrap and go
		add_action( 'init', array( $this, 'init' ) );	
	}
	/**
	 * Initialize the plugin.
	 * @since 1.0
	 */	
	function init() {

		// Translations
		load_plugin_textdomain( 'genesis-custom-title-area', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );	
	
		register_deactivation_hook( __FILE__, array( $this, 'gcta_myplugin_deactivate' ));
		
		// Test to make sure that Genesis is still running
		if ( 'genesis' !== basename( get_template_directory() ) ) {
		     add_action( 'admin_init', array( $this,'gcta_deactivate') ) ;
		     add_action( 'admin_notices', array( $this,'gcta_error_message' ));
		    return;
		}
		
		// Add settings link on plugin page
		add_filter('plugin_action_links_'.plugin_basename(__FILE__), array( $this, 'gcta_settings_link') );
		
		// Metabox on Theme Settings, for Sitewide Default
		add_filter( 'genesis_theme_settings_defaults',  array( $this, 'gcta_custom_title_defaults'     ) );
		add_action( 'genesis_settings_sanitizer_init',  array( $this, 'gcta_sanitization_filters'      ) );
		add_action( 'genesis_theme_settings_metaboxes', array( $this, 'gcta_custom_title_settings_box' ) );

		// Save changes
		$this->gcta_save();
		
	} //fin init
	
	/**
	 * Checks for activated Genesis Framework and its minimum version before allowing plugin to activate
	 *
	 * @author Nathan Rice, Remkus de Vries, Rian Rietveld, and Jackie D'Elia 
	 * @uses gcta_activation_check()
	 * @since 0.1
	 */
	function gcta_activation_check() {
		
		// Find Genesis Theme Data
		$theme   = wp_get_theme( 'genesis' );
		
		// Get the version
		$version = $theme->get( 'Version' );
	   
		// Set what we consider the minimum Genesis version
		$minimum_genesis_version = '2.2.6';
		
		// Restrict activation to only when the Genesis Framework is activated
		if( basename( get_template_directory() ) != 'genesis' ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			
			// Deactivate ourself
			wp_die( sprintf( __( 'This plugin requires that you have installed the %1$sGenesis Framework version %2$s%3$s or greater.', GCTA_DOMAIN ), '<a href="http://my.studiopress.com/themes/genesis">', '</a>', $minimum_genesis_version ) );
		}
		
		// Set a minimum version of the Genesis Framework to be activated on
		if( version_compare( $version, $minimum_genesis_version, '<' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			
			// Deactivate ourself
			wp_die( sprintf( __( 'You need to update to the latest version of the %1$sGenesis Framework version %2$s%3$s or greater to install this plugin.', GCTA_DOMAIN ), '<a href="http://my.studiopress.com/themes/genesis">', '</a>', $minimum_genesis_version ) );
		}
	}
	
	// Add settings link on plugin page
	function gcta_settings_link($links) { 
	  $settings_link = array(
		'<a href="admin.php?page=genesis">Settings</a>',
	  );
	  //array_unshift($links, $settings_link); 
	  return array_merge( $links, $settings_link );
	  //return $links; 
	}
	
	function gcta_myplugin_deactivate() {
	    flush_rewrite_rules();
	}
	
	// If it is not running, let's deactivate ourselves.
	function gcta_deactivate() {
	      
	      deactivate_plugins( plugin_basename( __FILE__ ) );
	      flush_rewrite_rules();
	}
	
	// show the message why we deactivated.
	function gcta_error_message() {
	
	        $error = sprintf( __( 'Sorry, Genesis Custom Title Area works only with the Genesis Framework. It has been deactivated.', 'genesis-custom-title-area' ) );
	
	        if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
	            $error = $error . sprintf(
	                __( ' But since we\'re talking anyway, did you know that your server is running PHP version %1$s, which is outdated? You should ask your host to update that for you.', 'display-featured-image-genesis' ),
	                PHP_VERSION
	            );
	        }
	        echo '<div class="error"><p>' . esc_attr( $error ) . '</p></div>';
	
	        if ( isset( $_GET['activate'] ) ) {
	            unset( $_GET['activate'] );
	        }
	}
	
	// Register defaults
	function gcta_custom_title_defaults( $defaults ) {

		$defaults['gcta_custom_title'] = 1;
		$defaults['gcta_custom_desciption'] = 0;
	 
		return $defaults;
	}
	
	// Sanitization
	function gcta_sanitization_filters() {
		genesis_add_option_filter( 'safe_html', GENESIS_SETTINGS_FIELD,
			array(
				'gcta_custom_title',
				'gcta_custom_description',
			) );
	}
	
	// Register metabox
	function gcta_custom_title_settings_box( $_genesis_theme_settings_pagehook ) {

		add_meta_box('gcta-theme-setting-title-area', __('Custom Title Area',GCTA_DOMAIN), array( $this,'gcta_custom_title_box'), $_genesis_theme_settings_pagehook, 'main', 'low');
	}
	
	// Create metabox
	function gcta_custom_title_box() {
		?>
		<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row"><?php _e( 'Enable custom clases:',GCTA_DOMAIN ); ?></th>
				<td>
		            <fieldset>
		                <legend class="screen-reader-text"><?php _e( 'Select an option',GCTA_DOMAIN ); ?></legend>

		                <p><label for="gcta_custom_title"><input type="checkbox" name="<?php echo GENESIS_SETTINGS_FIELD ?>[gcta_custom_title]" id="<?php echo GENESIS_SETTINGS_FIELD ?>[gcta_custom_title]" value="1" <?php checked( 1, genesis_get_option( 'gcta_custom_title', GENESIS_SETTINGS_FIELD) ); ?> />
		                <?php _e( 'Add a custom class to each word in the site-title', GCTA_DOMAIN ); ?></label></p>
						
						<p><label for="gcta_example_title" style="font-style: italic;font-size: 13px;"><?php _e( 'Example: <strong>site-title:</strong> "Genesis Framework" to each word a class "custom-title-Genesis" and "custom-title-Framework"', GCTA_DOMAIN ); ?></label></p>		

		                <p><label for="gcta_custom_description"><input type="checkbox" name="<?php echo GENESIS_SETTINGS_FIELD ?>[gcta_custom_description]" id="<?php echo GENESIS_SETTINGS_FIELD ?>[gcta_custom_description]" value="1" <?php checked( 1, genesis_get_option( 'gcta_custom_description', GENESIS_SETTINGS_FIELD ) ); ?> />
		                <?php _e( 'Add a custom class to each word in the site-description', GCTA_DOMAIN ); ?></label></p>

		                <p><label for="gcta_example_title" style="font-style: italic;font-size: 13px;" ><?php _e( 'Example: <strong>site-description:</strong> "It\'s Great" to each word a class "custom-desc-Its" and "custom-desc-Great"', GCTA_DOMAIN ); ?></label></p>		

		            </fieldset>
				</td>
			</tr>
		</tbody>
		</table>
		<?php
	}
	
	//Save changes
	function gcta_save(){

		if (genesis_get_option( 'gcta_custom_title') == 1){

			remove_action( 'genesis_site_title', 'genesis_seo_site_title' );
			add_action( 'genesis_site_title', array( $this, 'gcta_personalizar_site_title') );

		}else {

			remove_action( 'genesis_site_title', array( $this,'gcta_personalizar_site_title') );
			add_action( 'genesis_site_title', 'genesis_seo_site_title' );
		}
			
		if (genesis_get_option( 'gcta_custom_description') == 1){	

			remove_action( 'genesis_site_description', 'genesis_seo_site_description' );
			add_action( 'genesis_site_description', array( $this,'gcta_personalizar_description') );

		}else {

			remove_action( 'genesis_site_description', array( $this,'gcta_personalizar_description') );
			add_action( 'genesis_site_description', 'genesis_seo_site_description' );
		}
	}
	
	function gcta_personalizar_site_title () {
     
        $new_title  = $this->gcta_personalizar_titulo_o_descripcion(get_bloginfo( 'name' )) ;
        $inside = sprintf( '<a href="%s">%s</a>', trailingslashit( home_url() ), $new_title );
      
     
      //* Determine which wrapping tags to use
    	$wrap = genesis_is_root_page() && 'title' === genesis_get_seo_option( 'home_h1_on' ) ? 'h1' : 'p';
    
    	//* A little fallback, in case an SEO plugin is active
    	$wrap = genesis_is_root_page() && ! genesis_get_seo_option( 'home_h1_on' ) ? 'h1' : $wrap;
    
    	//* Wrap homepage site title in p tags if static front page
    	$wrap = is_front_page() && ! is_home() ? 'p' : $wrap;
    
    	//* And finally, $wrap in h1 if HTML5 & semantic headings enabled
    	$wrap = genesis_html5() && genesis_get_seo_option( 'semantic_headings' ) ? 'h1' : $wrap;
    	
    	add_filter( 'genesis_site_title_wrap', $wrap );
    	
            //* Build the title
    	$title  = sprintf( "<{$wrap} %s>", genesis_attr( 'site-title' ) );
    	$title .= "{$inside}</{$wrap}>";	

    	//* Echo (filtered)
		echo apply_filters( 'genesis_seo_title', $title, $inside, $wrap );
    }
	
	function gcta_personalizar_description () {
     
       	//* Set what goes inside the wrapping tags
    	 $inside = $this->gcta_personalizar_titulo_o_descripcion( get_bloginfo( 'description' ));
    	
    
    	//* Determine which wrapping tags to use
    	$wrap = genesis_is_root_page() && 'description' === genesis_get_seo_option( 'home_h1_on' ) ? 'h1' : 'p';
    
    	//* Wrap homepage site description in p tags if static front page
    	$wrap = is_front_page() && ! is_home() ? 'p' : $wrap;
    
    	//* And finally, $wrap in h2 if HTML5 & semantic headings enabled
    	$wrap = genesis_html5() && genesis_get_seo_option( 'semantic_headings' ) ? 'h2' : $wrap;
      
            add_filter( 'genesis_site_description_wrap', $wrap );
            
        $description  =  sprintf( "<{$wrap} %s>", genesis_attr( 'site-description' ) ) ;
    	$description .=  "{$inside}</{$wrap}>";
		
		//* Output (filtered)
		$output = $inside ? apply_filters( 'genesis_seo_description', $description, $inside, $wrap ) : '';
      
      echo $output;
    }
	
	/*=======================================================================================
    * Agrega clases a cada palabra del titulo o la descripción del sitio
    * Add a class to each word in the site title o site description
    * @author Salomé Pereyra
    * @param  get_bloginfo('name') o get_bloginfo('description')
    * @Retrun Devuelve cada palabra del site-title o site-description con clases para 
    * peronalizar
    *
    /*=======================================================================================*/
    function gcta_personalizar_titulo_o_descripcion($titulo){
      
      	//Remove special characters
    	$clases = $this->cleanString($titulo);

        //Divido una oración en varias palabras / Split a sentences in several words
    	$titulo_palabra=explode(" ",$titulo);
    	$clases_palabra=explode(" ",$clases);
        
	    //Armo el nombre de la clase según el parámetro de entrada / Make the name of custom class
	    $clase='custom-';
	    $clase .= (strcmp($titulo, get_bloginfo('name')) == 0)? 'title-':'desc-' ;
	      
	    $max=sizeof($clases_palabra);
	    $wrap='';
	    
	    for ($i=0; $i < $max; $i++){
	      //A cada palabra le agrego una etiqueta span con un nombre de clase unico
	      //  add tag span with the name of custom class
	      $wrap .= sprintf('<span class="%s%s">%s </span>',$clase, $clases_palabra[$i], $titulo_palabra[$i]);
	    }
	      
	    return $wrap;
    }

    function cleanString($String)
	{
		//Replace special caracters 
		$String = str_replace(array('á','à','â','ã','ª','ä'),"a",$String);
	    $String = str_replace(array('Á','À','Â','Ã','Ä'),"A",$String);
	    $String = str_replace(array('Í','Ì','Î','Ï'),"I",$String);
	    $String = str_replace(array('í','ì','î','ï'),"i",$String);
	    $String = str_replace(array('é','è','ê','ë'),"e",$String);
	    $String = str_replace(array('É','È','Ê','Ë'),"E",$String);
	    $String = str_replace(array('ó','ò','ô','õ','ö','º'),"o",$String);
	    $String = str_replace(array('Ó','Ò','Ô','Õ','Ö'),"O",$String);
	    $String = str_replace(array('ú','ù','û','ü'),"u",$String);
	    $String = str_replace(array('Ú','Ù','Û','Ü'),"U",$String);
	    $String = str_replace(array('[','^','´','`','¨','~',']',"!","\'","¡"),"",$String);
	    $String = str_replace("ç","c",$String);
	    $String = str_replace("Ç","C",$String);
	    $String = str_replace("ñ","n",$String);
	    $String = str_replace("Ñ","N",$String);
	    $String = str_replace("Ý","Y",$String);
	    $String = str_replace("ý","y",$String); 
	    $String = str_replace("&aacute;","a",$String);
	    $String = str_replace("&Aacute;","A",$String);
	    $String = str_replace("&eacute;","e",$String);
	    $String = str_replace("&Eacute;","E",$String);
	    $String = str_replace("&iacute;","i",$String);
	    $String = str_replace("&Iacute;","I",$String);
	    $String = str_replace("&oacute;","o",$String);
	    $String = str_replace("&Oacute;","O",$String);
	    $String = str_replace("&uacute;","u",$String);
	    $String = str_replace("&Uacute;","U",$String);
	    $String = str_replace("&#039;","",$String);   //'
	    $String = str_replace("&amp;","and",$String); //&
	    return $String;
	}
	
} //fin clase
new BE_Custom_Title_Area;
    

  