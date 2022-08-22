<?php
/*
* The plugin bootstrap file
*
* This file is read by WordPress to generate the plugin information in the plugin
* admin area. This file also includes all of the dependencies used by the plugin,
* registers the activation and deactivation functions, and defines a function
* that starts the plugin.
*
*
* Plugin Name: MkRapel Regiones y Ciudades de Chile para WC
* Plugin URI: https://marketingrapel.cl/servicios/plugin-regiones-y-ciudades-de-chile-para-woocommerce/
* Description: Nueva versión del Plugin con las Regiones y Comunas de Chile actualizado al 2020, permitiendo usar las ciudades para establecer las Zonas de Despacho y permite personalizar los Formularios de Pago usados para el Envío y Facturación en WooCommerce.
* Version: 4.3.0
* Author: Marketing Rapel
* Author URI: https://marketingrapel.cl
* License: GPLv3
* Requires at least: 5.0
* Tested up to: 5.8
* Requires PHP: 7.1
* Text Domain: wc-ciudades-y-regiones-de-chile
* WC requires at least: 6.0.0
* WC tested up to: 6.0.0
*/
 
if(!defined( 'ABSPATH' )) exit;

if (!function_exists('is_woocommerce_active')){
	function is_woocommerce_active(){
	    $active_plugins = (array) get_option('active_plugins', array());
	    if(is_multisite()){
		   $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
	    }
	    return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins) || class_exists('WooCommerce');
	}
}

if(is_woocommerce_active()) {
	define('MKRAPEL_CL_VERSION', '4.0');
	!defined('MKRAPEL_CL_BASE_NAME') && define('MKRAPEL_CL_BASE_NAME', plugin_basename( __FILE__ ));
	!defined('MKRAPEL_CL_PATH') && define('MKRAPEL_CL_PATH', plugin_dir_path( __FILE__ ));
	!defined('MKRAPEL_CL_URL') && define('MKRAPEL_CL_URL', plugins_url( '/', __FILE__ ));
	!defined('MKRAPEL_CL_ASSETS_URL') && define('MKRAPEL_CL_ASSETS_URL', MKRAPEL_CL_URL .'assets/');

	require MKRAPEL_CL_PATH . 'classes/class-mkrapel-cl.php';

	function run_mkrapel_cl() {
		$plugin = new MKRAPEL_CL();
	}
	run_mkrapel_cl();
	
	
	require_once ('classes/mkrapel-cl-states-places.php');
    require_once ('classes/mkrapel-cl-filter-by-cities.php');
	
    global $pagenow;
    $GLOBALS['wc_states_places'] = new MkRapel_Region_Ciudad_CL(__FILE__);
	
    add_filter( 'woocommerce_shipping_methods', 'add_filters_by_cities_method' );
    add_action( 'woocommerce_shipping_init', 'filters_by_cities_method' );
	
    function add_filters_by_cities_method( $methods ) {
        $methods['filters_by_cities_shipping_method'] = 'Filters_By_Cities_Method_CL';
        return $methods;
    }
	
}
