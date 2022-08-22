<?php
/**
 * Configuración Filtrar por Ciudad 2
 * @link       https://marketingrapel.cl
 * @since      1.0.0
 * @package    wc-ciudades-y-regiones-de-chile
 * @subpackage wc-ciudades-y-regiones-de-chile/classes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$settings =  array(
    
    'single_method' => array(
        'title' 		=> __( 'Método de Envío Único' ),
        'type' 			=> 'select',
        'description' 	=> __( 'Al hacer un método de envío único, elimina todos los demás e impone sus propias reglas' ),
        'class'         => 'wc-enhanced-select',
        'default' 		=> 'no',
        'desc_tip'		=> true,
        'options'		=> array(
            'yes' 	=> __( 'Si', 'woocommerce' ),
            'no'    => __( 'No', 'woocommerce' )
        )
    ),

    'title' => array(
        'title' 		=> __( 'Nombre del Tipo de Envío', 'woocommerce' ),
        'type' 			=> 'text',
        'description' 	=> __( 'Esto controla el título que ve el usuario durante el pago. en el CheckOut.', 'woocommerce' ),
        'default'		=> __( 'Despacho a Domicilio CL', 'woocommerce' ),
        'desc_tip'		=> true
    ),
    'tax_status' => array(
        'title' 		=> __( 'Estado Impuesto', 'woocommerce' ),
        'type' 			=> 'select',
        'class'         => 'wc-enhanced-select',
        'default' 		=> 'taxable',
        'options'		=> array(
            'taxable' 	=> __( 'Taxable', 'woocommerce' ),
            'none' 		=> _x( 'None', 'Tax status', 'woocommerce' )
        )
    ),
    'cost' => array(
        'title' => __('Valor Despacho'),
        'type' 			=> 'text',
        'default'		=> '0',
        'description'   => __( 'Puedes usar [cost] para determinar un valor según el valor total (Ej: 10*[cost]). Puedes usar [qty] para agregar un valor según la cantidad de productos a comprar (Ej: 10*[qyt]). Puedes usar [free] para determinar un valor de envío sólo si es inferior a un monto mínimo (Ej: [free valor="5500" minimo="10000"]).' ),
        'desc_tip'		=> true
    ),
    'cities' => array(
        'title' => __('Comunas'),
        'type' => 'multiselect',
        'class'       => 'wc-enhanced-select',
        'description' => __( 'Seleccione las Ciudades que hace referencia a la Región que ha agregado anteriormente como Zona de Envío' ),
        'options' => $this->showCitiesRegions(),
        'desc_tip'    => true,
    )
);

$shipping_classes = WC()->shipping->get_shipping_classes();

if ( ! empty( $shipping_classes ) ) {
    $settings['class_costs'] = array(
        'title'       => __( 'Costos de Clase de Envío', 'woocommerce' ),
        'type'        => 'title',
        'default'     => '',
        /* translators: %s: URL for link. */
        'description' => sprintf( __( 'Estos costos se pueden agregar opcionalmente según la <a href="%s"> clase de envío del producto.</a>.', 'woocommerce' ), admin_url( 'admin.php?page=wc-settings&tab=shipping&section=classes' ) ),
    );
    foreach ( $shipping_classes as $shipping_class ) {
        if ( ! isset( $shipping_class->term_id ) ) {
            continue;
        }
        $settings[ 'class_cost_' . $shipping_class->term_id ] = array(
            /* translators: %s: shipping class name */
            'title'             => sprintf( __( '"%s" costo de la clase de envío', 'woocommerce' ), esc_html( $shipping_class->name ) ),
            'type'              => 'text',
            'placeholder'       => __( 'N/A', 'woocommerce' ),
            'description'       => $cost_desc,
            'default'           => $this->get_option( 'class_cost_' . $shipping_class->slug ), // Before 2.5.0, we used slug here which caused issues with long setting names.
            'desc_tip'          => true,
            'sanitize_callback' => array( $this, 'sanitize_cost' ),
        );
    }
    $settings['no_class_cost'] = array(
        'title'             => __( 'Sin costo de clase de envío', 'woocommerce' ),
        'type'              => 'text',
        'placeholder'       => __( 'N/A', 'woocommerce' ),
        'description'       => $cost_desc,
        'default'           => '',
        'desc_tip'          => true,
        'sanitize_callback' => array( $this, 'sanitize_cost' ),
    );
    $settings['type'] = array(
        'title'   => __( 'Tipo de cálculo', 'woocommerce' ),
        'type'    => 'select',
        'class'   => 'wc-enhanced-select',
        'default' => 'class',
        'options' => array(
            'class' => __( 'Por clase: Cargue el envío para cada clase de envío individualmente', 'woocommerce' ),
            'order' => __( 'Por pedido: cargo de envío para la clase de envío más cara', 'woocommerce' ),
        ),
    );
}
return $settings;