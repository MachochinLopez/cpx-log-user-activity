<?php

require CPX_LOG_USER_ACTIVITY_PATH . '/includes/classes/class-cpx-user-activity-logger.php';
require CPX_LOG_USER_ACTIVITY_PATH . '/includes/classes/class-cpx-learnpress-activity-logger.php';

require CPX_LOG_USER_ACTIVITY_PATH . '/includes/admin/class-general-activity-log-table.php';
require CPX_LOG_USER_ACTIVITY_PATH . '/includes/admin/class-individual-activity-log-table.php';

/**
 * Clase principal del plugin.
 */
class CPX_Log_User_Activity {

	/**
     * Constructor 
     *
     * @return void
     */
    public function __construct() {
        $this->cpx_log_activate_plugin();
        $this->cpx_log_generate_admin_pages();
    }

    /**
	 * Define el comportamiento que ocurrirá apenas se active el plugin.
	 * 
	 * @return void
	 */
	public function cpx_log_activate_plugin() {
		// Crea una nueva instancia de los logs de usuario y de los logs
		// de los cursos.
		$user_activity_logger = new CPX_User_Activity_Logger();
		$learnpress_activity_logger = new CPX_Learnpress_Activity_Logger();
	}

	public function cpx_log_generate_admin_pages() {
		// add top level menu page
		add_users_page(
			'Actividad de usuarios', //Page Title
			'Registros de actividad', //Menu Title
			'manage_options', //Capability
			'user-activity-logs', //Page slug
			array( $this, 'cpx_log_general_activity_admin_html' ) //Callback to print html
		);
	}

	/**
	 * Genera el HTML de la página de actividad de usuarios.
	 * 
	 * @return void
	 */
	public function cpx_log_general_activity_admin_html() {

		// Si no es la página de bitácora de cada usuario...
		if ( ! isset( $_GET[ 'user' ] )) {
			?>
				<div class="wrap">
					<!-- Imprime el título de la página. -->
					<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
					
					<!-- Imprime la tabla. -->
					<?php  $this->cpx_log_general_activity_table() ?>

				</div>
			<?php
		} else {
			$user = get_userdata( $_GET[ 'user' ] );
			$url = admin_url() . "admin.php?page=user-activity-logs";

			?>
				<div class="wrap">
					<h1>Bitácora de <strong><?php echo $user->user_nicename ?><strong></h1>
					<br />
					<a class="button" href="<?php echo $url ?>"><< Volver</a>

					<div class="search-box">
						<form action="" method="GET">
							<input type="hidden" name="page" value="user-activity-logs">
							<input type="hidden" name="user" value="<?php echo $user->ID ?>">
							<div class="date-filters">
								<?php if ( isset( $_GET[ 'start_date' ] ) || isset( $_GET[ 'end_date' ] )) :?>
									<a class="date-filters__clear button" href="<?php echo ($url . '&user=' . $user->ID) ?>">Limpiar</a>
								<?php endif ?>
								<div class="date-filters__field">
									<label class="date-filters__label start_date_label" for="start_date">Fecha inicial:</label>
									<input class="date-filters__input start_date_input" name="start_date" type="text" id="start_date" value="<?php echo $_GET['start_date'] ?>" readonly>
								</div>
								<div class="date-filters__field">
									<label class="date-filters__label end_date_label" for="end_date">Fecha final:</label>
									<input class="date-filters__input end_date_input" name="end_date" type="text" id="end_date" value="<?php echo $_GET['end_date'] ?>" readonly>
								</div>
								<button class="button">Buscar</button>
							</div>
						</form>
					</div>

					<!-- Imprime la tabla. -->
					<?php  $this->cpx_log_individual_activity_table( $user ) ?>
				</div>
		  	<?php
		}
	}

	/**
	 * Define el contenido de la vista de resultados de frecuencia de respuestas.
	 *
	 * @param  WP_User $user Instancia del usuario del que se quiere ver su bitácora.
	 * @return void
	 */
	public function cpx_log_individual_activity_table( $user ) {
		// Carga la tabla.
		$table = new CPX_Log_Individual_Activity_Table( $user );
		// Genera el contenido de la vista.
		$this->cpx_log_generate_tab_common_content( $table );
	}

	/**
	 * Define el contenido de la vista de resultados de frecuencia de respuestas.
	 * 
	 * @return void
	 */
	public function cpx_log_general_activity_table() {
		// Carga la tabla.
		$table = new CPX_Log_General_Activity_Table();
		// Genera el contenido de la vista.
		$this->cpx_log_generate_tab_common_content( $table );
	}

	/**
	 * Genera el contenido que comparten las vista de resultados.
	 * 
	 * @param  object $table Instancia de la tabla que se muestra en la vista.
	 * @return void
	 */
	private function cpx_log_generate_tab_common_content($table) {
		// Encola los scripts del datepicker.
		wp_enqueue_script( 'jquery-ui', plugin_dir_url( __DIR__ ) . 'vendor/jquery-ui-1.12.1/jquery-ui.min.js' );
		// Encola el estilo del datepicker.
		wp_enqueue_style( 'jquery-ui-styles', plugin_dir_url( __DIR__ ) . 'vendor/jquery-ui-1.12.1/jquery-ui.min.css' );
		wp_enqueue_script( 'cpx-datepicker', plugin_dir_url( __DIR__ ) . 'js/cpx-jquery-datepicker.js' );
		// Carga los estilos.
		wp_enqueue_style( 'cpx-logs-table-styles', plugin_dir_url( __DIR__ ) . '/css/cpx-logs-table-styles.css' );
		// Encola los estilos de font awesome del tema de ThimPress.
		wp_enqueue_style( 'font-awesome', THIM_URI . 'assets/css/all.min.css', array(), THIM_THEME_VERSION );

		// Prepara el comportamiento del botón para exportar a Excel.
		$table->export_to_csv();
		// Prepara el contenido de la tabla.
		$table->prepare_items();
		// Muestra la tabla.
		$table->display();
	}
}



?>