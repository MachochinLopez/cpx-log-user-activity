<?php

/**
 * Contiene las funciones para registrar los logs en la DB.
 */
abstract class CPX_Logger {

	/**
     * Constructor 
     *
     * @return void
     */
    public function __construct() {
        $this->cpx_log_register_hooks();
    }

    /**
     * Método abstracto que requiere a las clases hijas a que 
     * definan los hooks de sus funciones para registrar los 
     * logs.
     * 
     * @return void
     */
    abstract protected function cpx_log_register_hooks();

    /**
     * Inserta el log a la tabla cpx_user_activity_logs.
     *
     * @param  array $args Arreglo con la información del log
     * @return void
     */
    protected function cpx_store_log( $args ) {
        // Inserta las respuestas en la db.
        global $wpdb;
        $table_name = $wpdb->prefix . "cpx_user_activity_logs";

        // Inserta el log.
        $wpdb->insert( $table_name, array(
                'user_id' => $args[ 'user_id' ],
                'activity_type_id' => $args[ 'activity_type_id' ],
                'item_id' => $args[ 'item_id' ],
                'activity_date' => $args[ 'activity_date' ],
            ), array(
                '%d',
                '%d',
                '%s',
                '%s',
            )
        );
    }
}



?>