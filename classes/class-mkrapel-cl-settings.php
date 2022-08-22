<?php
/**
 * Configuración General
 * @link       https://marketingrapel.cl
 * @since      4.0.0
 * @package    wc-ciudades-y-regiones-de-chile
 * @subpackage wc-ciudades-y-regiones-de-chile/classes
 */

defined( 'ABSPATH' ) || exit;

if(!class_exists('MKRAPEL_CL_Settings')) :

class MKRAPEL_CL_Settings {
	public function __construct() {
		
	}
	public function enqueue_styles_and_scripts($hook) {
		if(strpos($hook, 'page_checkout_form') === false) {
			return;
		}

		$deps = array('jquery', 'jquery-ui-dialog', 'jquery-ui-sortable', 'jquery-tiptip', 'woocommerce_admin', 'select2', 'wp-color-picker');

		wp_enqueue_style('woocommerce_admin_styles');
		wp_enqueue_style('mkrapel-cl-admin-style', MKRAPEL_CL_ASSETS_URL . 'css/mkrapel-cl-admin.css', MKRAPEL_CL_VERSION);
		wp_enqueue_script('mkrapel-cl-admin-script', MKRAPEL_CL_ASSETS_URL . 'js/mkrapel-cl-admin.js', $deps, MKRAPEL_CL_VERSION, true);
	}
	public function MKRAPEL_CL_capability() {
		$allowed = array('manage_woocommerce', 'manage_options');
		$capability = apply_filters('mkrapel_cl_required_capability', 'manage_woocommerce');

		if(!in_array($capability, $allowed)){
			$capability = 'manage_woocommerce';
		}
		return $capability;
	}
	public function admin_menu() {
		$capability = $this->MKRAPEL_CL_capability();
		$this->screen_id = add_submenu_page('woocommerce', __('Diseño del Formulario de Pago', 'wc-ciudades-y-regiones-de-chile'), __('Formularios de Pago', 'wc-ciudades-y-regiones-de-chile'), $capability, 'checkout_form', array($this, 'output_settings'));
		$this->screen_id = add_submenu_page('woocommerce', __('Zonas de Envío', 'wc-ciudades-y-regiones-de-chile'), __('Zonas de Envío', 'wc-ciudades-y-regiones-de-chile'), $capability, 'wc-settings&tab=shipping', array($this, 'output_settings'));
		$this->screen_id = add_submenu_page('woocommerce', __('Nueva Zona de Envío', 'wc-ciudades-y-regiones-de-chile'), __('Nueva Zona de Envío', 'wc-ciudades-y-regiones-de-chile'), $capability, 'wc-settings&tab=shipping&zone_id=new', array($this, 'output_settings'));
		$this->screen_id = add_submenu_page('woocommerce', __('Medios de Pago', 'wc-ciudades-y-regiones-de-chile'), __('Medios de Pago', 'wc-ciudades-y-regiones-de-chile'), $capability, 'wc-settings&tab=checkout', array($this, 'output_settings'));
	}
	public function add_screen_id($ids){
		$ids[] = 'woocommerce_page_checkout_form';
		$ids[] = strtolower(__('WooCommerce', 'wc-ciudades-y-regiones-de-chile')) .'_page_checkout_form';

		return $ids;
	}
	public function plugin_action_links($links) {
		$config_link    = '<a href="'.admin_url('admin.php?page=checkout_form').'">'. __('Configuración Checkout', 'wc-ciudades-y-regiones-de-chile') .'</a>';
		$info_link      = '<a href="https://marketingrapel.cl/servicios/plugin-regiones-y-ciudades-de-chile-para-woocommerce/">'. __('Documentación', 'wc-ciudades-y-regiones-de-chile') .'</a>';
		array_unshift($links, $config_link, $info_link);
		return $links;
	}
	private function output_review_request_link(){  }
	public function output_settings(){
		$this->output_review_request_link();
		$tab = $this->get_current_tab();

		echo '<div class="mkrapel-cl-wrap">';
		if($tab === 'advanced_settings'){			
			$advanced_settings = MKRAPEL_CL_Settings_Advanced::instance();	
			$advanced_settings->render_page();		
		}else{
			$general_settings = MKRAPEL_CL_Settings_General::instance();	
			$general_settings->render_page();
		}
		echo '</div">';
	}
	public function get_current_tab(){
		return isset( $_GET['tab'] ) ? esc_attr( $_GET['tab'] ) : 'fields';
	}
}

endif;

