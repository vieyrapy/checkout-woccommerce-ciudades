<?php
/**
 * Configuración General Avanzada
 * @link       https://marketingrapel.cl
 * @since      4.0.0
 * @package    wc-ciudades-y-regiones-de-chile
 * @subpackage wc-ciudades-y-regiones-de-chile/classes
 */

defined( 'ABSPATH' ) || exit;

if(!class_exists('MKRAPEL_CL_Settings_Advanced')):

class MKRAPEL_CL_Settings_Advanced {
	protected static $_instance = null;
	protected $tabs = '';

	private $settings_fields = NULL;
	private $cell_props = array();
	private $cell_props_CB = array();
	private $cell_props_TA = array();

	public function __construct() {
		$this->tabs = array( 'fields' => 'Formularios de Pago', 'advanced_settings' => 'Configuración Avanzada');
		$this->init_constants();
	}

	public static function instance() {
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function init_constants(){
		$this->cell_props = array( 
			'label_cell_props' => 'class="label"', 
			'input_cell_props' => 'class="field"',
			'input_width' => '260px',
			'label_cell_th' => true
		);

		$this->cell_props_TA = array( 
			'label_cell_props' => 'class="label"', 
			'input_cell_props' => 'class="field"',
			'rows' => 10,
			'cols' => 100,
		);

		$this->cell_props_CB = array( 
			'label_props' => 'style="margin-right: 40px;"', 
		);
		
		$this->settings_fields = $this->get_advanced_settings_fields();
	}

	public function get_advanced_settings_fields(){
		return array(
			'enable_label_override' => array(
				'name'=>'enable_label_override', 'label'=>'Habilitar la anulación de etiquetas para los campos de dirección.', 'type'=>'checkbox', 'value'=>'1', 'checked'=>1
			),
			'enable_placeholder_override' => array(
				'name'=>'enable_placeholder_override', 'label'=>'Habilitar la anulación del marcador de posición para los campos de dirección.', 'type'=>'checkbox', 'value'=>'1', 'checked'=>1
			),
			'enable_class_override' => array(
				'name'=>'enable_class_override', 'label'=>'Habilitar la anulación de clase para los campos de dirección.', 'type'=>'checkbox', 'value'=>'1', 'checked'=>0
			),
			'enable_priority_override' => array(
				'name'=>'enable_priority_override', 'label'=>'Habilitar la anulación de prioridad para los campos de dirección.', 'type'=>'checkbox', 'value'=>'1', 'checked'=>1
			),
			'enable_required_override' => array(
				'name'=>'enable_required_override', 'label'=>'Habilite la anulación de validación requerida para los campos de dirección.', 'type'=>'checkbox', 'value'=>'1', 'checked'=>0
			),
		);
	}

	public function render_page(){
		$this->render_tabs();
		$this->render_content();
	}
		
	public function save_advanced_settings($settings){
		$result = update_option(MKRAPEL_CL_Utils::OPTION_KEY_ADVANCED_SETTINGS, $settings);
		return $result;
	}
	
	private function reset_settings(){
		delete_option(MKRAPEL_CL_Utils::OPTION_KEY_ADVANCED_SETTINGS);
		$this->print_notices('La configuración se restableció correctamente.', 'updated', false);
	}
	
	private function save_settings(){
		$settings = array();
		
		foreach( $this->settings_fields as $name => $field ) {
			$value = '';
			
			if($field['type'] === 'checkbox'){
				$value = !empty( $_POST['i_'.$name] ) ? $_POST['i_'.$name] : '';

			}else if($field['type'] === 'multiselect_grouped'){
				$value = !empty( $_POST['i_'.$name] ) ? $_POST['i_'.$name] : '';
				$value = is_array($value) ? implode(',', wc_clean(wp_unslash($value))) : wc_clean(wp_unslash($value));

			}else if($field['type'] === 'text' || $field['type'] === 'textarea'){
				$value = !empty( $_POST['i_'.$name] ) ? $_POST['i_'.$name] : '';
				$value = !empty($value) ? wc_clean( wp_unslash($value)) : '';

			}else{
				$value = !empty( $_POST['i_'.$name] ) ? $_POST['i_'.$name] : '';
				$value = !empty($value) ? wc_clean( wp_unslash($value)) : '';
			}
			
			$settings[$name] = $value;
		}
				
		$result = $this->save_advanced_settings($settings);
		if ($result == true) {
			$this->print_notices('Sus cambios han sido guardados.', 'updated', false);
		} else {
			$this->print_notices('Sus cambios no se guardaron debido a un error (¡o no hizo ninguno!).', 'error', false);
		}	
	}
	
	private function render_content(){
		if(isset($_POST['reset_settings']))
			$this->reset_settings();	
			
		if(isset($_POST['save_settings']))
			$this->save_settings();
			
    	$this->render_plugin_settings();
	}

	private function render_plugin_settings(){
		$settings = MKRAPEL_CL_Utils::get_advanced_settings();
		?>            
        <div style="padding-left: 30px;">               
		    <form id="advanced_settings_form" method="post" action="">
                <table class="mkrapel-cl-settings-table mkrapel-cl-form-table">
                    <tbody>
                    <?php
                    $this->render_locale_override_settings($settings);
					?>
                    </tbody>
                </table> 
                <p class="submit">
					<input type="submit" name="save_settings" class="btn btn-small btn-primary" value="Guardar Cambios">
                    <input type="submit" name="reset_settings" class="btn btn-small" value="Restablecer" 
					onclick="return confirm('¿Está seguro de que desea restablecer la configuración predeterminada? todos tus cambios serán eliminados.');">
            	</p>
            </form>
    	</div>       
    	<?php
	}

	private function render_locale_override_settings($settings){
		$this->render_form_elm_row_title('Configuración de anulación de configuración regional');
		$this->render_form_elm_row_cb($this->settings_fields['enable_label_override'], $settings, true);
		$this->render_form_elm_row_cb($this->settings_fields['enable_placeholder_override'], $settings, true);
		$this->render_form_elm_row_cb($this->settings_fields['enable_class_override'], $settings, true);
		$this->render_form_elm_row_cb($this->settings_fields['enable_priority_override'], $settings, true);
		$this->render_form_elm_row_cb($this->settings_fields['enable_required_override'], $settings, true);
	}

	public function render_form_elm_row_title($title=''){
		?>
		<tr>
			<td colspan="3" class="section-title" ><?php echo $title; ?></td>
		</tr>
		<?php
	}

	private function render_form_elm_row_cb($field, $settings=false, $merge_cells=false){
		$name = $field['name'];
		if(is_array($settings) && isset($settings[$name])){
			if($field['value'] === $settings[$name]){
				$field['checked'] = 1;
			}else{
				$field['checked'] = 0;
			}
		}

		if($merge_cells){
			?>
			<tr>
				<td colspan="3">
		    		<?php $this->render_form_field_element($field, $this->cell_props_CB, false); ?>
		    	</td>
		    </tr>
			<?php
		}else{
			?>
			<tr>
				<td colspan="2"></td>
				<td class="field">
		    		<?php $this->render_form_field_element($field, $this->cell_props_CB, false); ?>
		    	</td>
		    </tr>
			<?php
		}
	}

	public function render_form_field_element($field, $atts = array(), $render_cell = true){
		if($field && is_array($field)){
			$args = shortcode_atts( array(
				'label_cell_props' => '',
				'input_cell_props' => '',
				'label_cell_colspan' => '',
				'input_cell_colspan' => '',
			), $atts );
		
			$ftype     = isset($field['type']) ? $field['type'] : 'text';
			$flabel    = isset($field['label']) && !empty($field['label']) ? __($field['label'], 'wc-ciudades-y-regiones-de-chile') : '';
			$sub_label = isset($field['sub_label']) && !empty($field['sub_label']) ? __($field['sub_label'], 'wc-ciudades-y-regiones-de-chile') : '';
			$tooltip   = isset($field['hint_text']) && !empty($field['hint_text']) ? __($field['hint_text'], 'wc-ciudades-y-regiones-de-chile') : '';
			
			$field_html = '';
			
			if($ftype == 'text'){
				$field_html = $this->render_form_field_element_inputtext($field, $atts);
				
			}else if($ftype == 'textarea'){
				$field_html = $this->render_form_field_element_textarea($field, $atts);
				   
			}else if($ftype == 'checkbox'){
				$field_html = $this->render_form_field_element_checkbox($field, $atts, $render_cell);   
				$flabel 	= '&nbsp;';  
			}
			
			if($render_cell){
				$required_html = isset($field['required']) && $field['required'] ? '<abbr class="required" title="required">*</abbr>' : '';
				
				$label_cell_props = !empty($args['label_cell_props']) ? $args['label_cell_props'] : '';
				$input_cell_props = !empty($args['input_cell_props']) ? $args['input_cell_props'] : '';
				
				?>
				<td <?php echo $label_cell_props ?> >
					<?php echo $flabel; echo $required_html; 
					if($sub_label){
						?>
						<br/><span class="thpladmin-subtitle"><?php echo $sub_label; ?></span>
						<?php
					}
					?>
				</td>
				<?php $this->render_form_fragment_tooltip($tooltip); ?>
				<td <?php echo $input_cell_props ?> ><?php echo $field_html; ?></td>
				<?php
			}else{
				echo $field_html;
			}
		}
	}

	private function prepare_form_field_props($field, $atts = array()){
		$field_props = '';
		$args = shortcode_atts( array(
			'input_width' => '',
			'input_name_prefix' => 'i_',
			'input_name_suffix' => '',
		), $atts );
		
		$ftype = isset($field['type']) ? $field['type'] : 'text';
		
		if($ftype == 'multiselect'){
			$args['input_name_suffix'] = $args['input_name_suffix'].'[]';
		}
		
		$fname  = $args['input_name_prefix'].$field['name'].$args['input_name_suffix'];
		$fvalue = isset($field['value']) ? esc_html($field['value']) : '';
		
		$input_width  = $args['input_width'] ? 'width:'.$args['input_width'].';' : '';
		$field_props  = 'name="'. $fname .'" value="'. $fvalue .'" style="'. $input_width .'"';
		$field_props .= ( isset($field['placeholder']) && !empty($field['placeholder']) ) ? ' placeholder="'.$field['placeholder'].'"' : '';
		$field_props .= ( isset($field['onchange']) && !empty($field['onchange']) ) ? ' onchange="'.$field['onchange'].'"' : '';
		
		return $field_props;
	}

	private function render_form_field_element_inputtext($field, $atts = array()){
		$field_html = '';
		if($field && is_array($field)){
			$field_props = $this->prepare_form_field_props($field, $atts);
			$field_html = '<input type="text" '. $field_props .' />';
		}
		return $field_html;
	}
	
	private function render_form_field_element_textarea($field, $atts = array()){
		$field_html = '';
		if($field && is_array($field)){
			$args = shortcode_atts( array(
				'rows' => '5',
				'cols' => '100',
			), $atts );
		
			$fvalue = isset($field['value']) ? $field['value'] : '';
			$field_props = $this->prepare_form_field_props($field, $atts);
			$field_html = '<textarea '. $field_props .' rows="'.$args['rows'].'" cols="'.$args['cols'].'" >'.$fvalue.'</textarea>';
		}
		return $field_html;
	}

	private function render_form_field_element_checkbox($field, $atts = array(), $render_cell = true){
		$field_html = '';
		if($field && is_array($field)){
			$args = shortcode_atts( array(
				'label_props' => '',
				'cell_props'  => 3,
				'render_input_cell' => false,
			), $atts );
		
			$fid 	= 'a_f'. $field['name'];
			$flabel = isset($field['label']) && !empty($field['label']) ? __($field['label'], 'wc-ciudades-y-regiones-de-chile') : '';
			
			$field_props  = $this->prepare_form_field_props($field, $atts);
			$field_props .= isset($field['checked']) && $field['checked'] === 1 ? ' checked' : '';
			
			$field_html  = '<input type="checkbox" id="'. $fid .'" '. $field_props .' />';
			$field_html .= '<label for="'. $fid .'" '. $args['label_props'] .' > '. $flabel .'</label>';
		}
		if(!$render_cell && $args['render_input_cell']){
			return '<td '. $args['cell_props'] .' >'. $field_html .'</td>';
		}else{
			return $field_html;
		}
	}

	public function render_form_fragment_tooltip($tooltip = false){
		$tooltip_html = '';
		?>
		<td style="width: 26px; padding:0px;"><?php echo $tooltip_html; ?></td>
		<?php
	}


	public function print_notices($msg, $type='updated', $return=false){
		$notice = '<div class="mkrapel-cl-notice '. $type .'"><p>'. __($msg, 'wc-ciudades-y-regiones-de-chile') .'</p></div>';
		if(!$return){
			echo $notice;
		}
		return $notice;
	}

	/******* TABS & SECTIONS *******/
	/*******************************/
	public function get_current_tab(){
		return isset( $_GET['tab'] ) ? esc_attr( $_GET['tab'] ) : 'fields';
	}

	public function render_tabs(){
		$current_tab = $this->get_current_tab();

		if(empty($this->tabs)){
			return;
		}
		
		echo '<h2 class="mkrapel-cl-tabs nav-tab-wrapper woo-nav-tab-wrapper">';
		foreach( $this->tabs as $id => $label ){
			$active = ( $current_tab == $id ) ? 'nav-tab-active' : '';
			$label  = __($label, 'wc-ciudades-y-regiones-de-chile');
			echo '<a class="nav-tab '.$active.'" href="'. $this->get_admin_url($id) .'">'.$label.'</a>';
		}
		echo '</h2>';	
	}
	
	public function get_admin_url($tab = false, $section = false){
		$url = 'admin.php?page=checkout_form';
		if($tab && !empty($tab)){
			$url .= '&tab='. $tab;
		}
		if($section && !empty($section)){
			$url .= '&section='. $section;
		}
		return admin_url($url);
	}
}

endif;
