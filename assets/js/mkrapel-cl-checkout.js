jQuery(document).ready(function($) {
	$('select.mkrapel-cl-enhanced-select').select2({
		minimumResultsForSearch: 10,
		allowClear : true,
		placeholder: $(this).data('placeholder')
	}).addClass('enhanced');

	//Copied from WooCommerce address-i18n.js. Do not change
	function field_is_required( field, is_required ) {
		if ( is_required ) {
			field.find( 'label .optional' ).remove();
			field.addClass( 'validate-required' );

			field_label_white_space_fix(field);

			if ( field.find( 'label .required' ).length === 0 ) {
				field.find( 'label' ).append(
					'&nbsp;<abbr class="required" title="' +
					wc_address_i18n_params.i18n_required_text +
					'">*</abbr>'
				);
			}
		} else {
			field.find( 'label .required' ).remove();
			field.removeClass( 'validate-required woocommerce-invalid woocommerce-invalid-required-field' );

			field_label_white_space_fix(field);

			if ( field.find( 'label .optional' ).length === 0 ) {
				field.find( 'label' ).append( '&nbsp;<span class="optional">(' + wc_address_i18n_params.i18n_optional_text + ')</span>' );
			}
		}
	}

	$( document.body ).bind( 'country_to_state_changing', function( event, country, wrapper ) {
		if(mkrapel_cl_public_var.is_override_required){
			setTimeout(address_fields_required_validation_fix, 500);
		}
	});

	function address_fields_required_validation_fix(){
		var thisform = $('.woocommerce-checkout');
	  	var locale_fields = $.parseJSON( wc_address_i18n_params.locale_fields );

	  	if(locale_fields){
	  		$.each( locale_fields, function( key, value ) {
				var fids = value.split(',');

				$.each( fids, function( index, fid ) {
					var field = thisform.find( fid.trim() );

					if(field.hasClass('mkrapel-cl-required')){
						field_is_required( field, true );
					}else if(field.hasClass('mkrapel-cl-optional')){
						field_is_required( field, false );
					}
				});
			});
	  	}
	}

	//White space fix. Should be removed once fixed by WooCommerec.
	function field_label_white_space_fix(field){
		var label = field.find( 'label' ).html();
		if(label){
			label = label.replace(/(?:^(?:&nbsp;)+)|(?:(?:&nbsp;)+$)/g, '');
			field.find( 'label' ).html( label.trim() );
		}
	}
});