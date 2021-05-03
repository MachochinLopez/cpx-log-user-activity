/**
 * Define el Datepicker.
 */
jQuery(function($) {
	$(document).ready(function() {

		$.datepicker.regional['es'] = {
		  closeText: 'Cerrar',
		  prevText: '<Ant',
		  nextText: 'Sig>',
		  currentText: 'Hoy',
		  monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
		  monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
		  dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
		  dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
		  dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
		  weekHeader: 'Sm',
		  dateFormat: 'dd/mm/yy',
		  firstDay: 1,
		  isRTL: false,
		  showMonthAfterYear: false,
		  yearSuffix: ''
		};

		$.datepicker.setDefaults($.datepicker.regional['es']);

		// Datepicker.
		const from = $( ".start_date_input" )
      .datepicker({
        changeMonth: true,
        numberOfMonths: 1,
        maxDate: $( ".end_date_input" ).val()
      })
      .on( "change", function() {
	      to.datepicker( "option", "minDate", from.val() );
      })
      .load(function() {
	      to.datepicker( "option", "minDate", $( ".start_date_input" ).val() );
      });

		const to = $( ".end_date_input" )
			.datepicker({
        changeMonth: true,
        numberOfMonths: 1,
        minDate: $( ".start_date_input" ).val()
      })
      .on( "change", function() {
        from.datepicker( "option", "maxDate", to.val() );
      })
      .load(function() {
	      from.datepicker( "option", "maxDate", $( ".end_date_input" ).val() );
      });
	});
});