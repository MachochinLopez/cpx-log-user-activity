<?php 
/**
 * Plugin Name: CPX Log Users Activity
 * Description: Plugin para generar reportes actividad de usuarios.
 * Version: 1.0
 * Author: Copixil
 * Author URI: https://www.copixil.com
 */

defined( 'ABSPATH' ) || exit;
define( 'CPX_LOG_USER_ACTIVITY_PATH', dirname( __FILE__ ) );

/**
 * Ejecuta todo lo necesario al activar el plugin.
 * 
 * @return void.
 */
function cpx_log_activate_plugin() {
	// Si el plugin de LearnPress no está activado...
    if ( ! is_plugin_active( 'learnpress/learnpress.php' ) ) {
		// Marca el error.
	    add_action('admin_notices', 'cpx_log_failed_activation_notice');
	    deactivate_plugins( plugin_basename( __FILE__ ) );
	} else {

		// Requiere la clase que genera las tablas que requiere el plugin.
		require_once 'includes/db/cpx-log-install-database-tables.php';

		// Crea las tablas necesarias en la DB.
		cpx_log_create_plugin_tables();
	}
}

// Al activar el plugin instala las tablas necesarias para la base de datos.
register_activation_hook( __FILE__, 'cpx_log_activate_plugin' );


/**
 * Si los requerimientos se cumplen carga el plugin. Si no, devuelve un error.
 * 
 * @return bool
 */
function cpx_log_load_plugin() {
	// Si el plugin de LearnPress no está activado...
    if ( ! is_plugin_active( 'learnpress/learnpress.php' ) ) {
    	// Marca el error.
	    add_action('admin_notices', 'cpx_log_failed_activation_notice');
	    deactivate_plugins( plugin_basename( __FILE__ ) );
    } else {
	    // Requiere la clase main del plugin.
		require_once 'includes/class-cpx-log-user-activity.php';

		// Instancia la clase prinicpal.
		$cpx_logs = new CPX_Log_User_Activity();
    }
}

// Inicia el plugin una vez que se haya cargado todo.
add_action('plugins_loaded', 'cpx_log_load_plugin');


/**
 * Despliega un notice que informa que el Plugin de Quiz And Survey Master 
 * no está activado y es requisito para que este funcione.
 *
 * @return  void
 */
function cpx_log_failed_activation_notice() {
    echo '<div class="error"><p>El plugin CPX Log Users Activity requiere que el plugin LearnPress esté activado. Instálelo y actívelo antes de activar este plugin.</p></div>';
}

?>