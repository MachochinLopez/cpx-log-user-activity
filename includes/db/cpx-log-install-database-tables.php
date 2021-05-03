<?php

defined( 'ABSPATH' ) || exit;

/**
 * Crea las tablas necesarias para el plugin.
 *  
 * @return void
 */
function cpx_log_create_plugin_tables() {

    global $wpdb;
    $table_name = $wpdb->prefix . "cpx_user_activity_logs";
    $charset_collate = $wpdb->get_charset_collate();

    // Si la tabla no existe.
    if( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
    	// Crea la tabla.
  		$sql = "CREATE TABLE $table_name (
  			id mediumint(9) NOT NULL AUTO_INCREMENT,
  			user_id mediumint(9) NOT NULL,
            activity_type_id mediumint(9) NOT NULL,
            item_id mediumint(9) NULL,
  			activity_date DATETIME NOT NULL,
  			
  			PRIMARY KEY  (id)
  		) $charset_collate;";

  		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  		dbDelta( $sql );
  	}

    $table_name = $wpdb->prefix . "cpx_user_activity_types";

    // Si la tabla no existe.
    if( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
        // Crea la tabla.
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            description VARCHAR(255) NOT NULL,

            PRIMARY KEY  (id)
            ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );


        // Seedea los datos básicos.
        $sql = "INSERT INTO $table_name (description) 
            VALUES
            ('Inicio de sesión'),
            ('Cierre de sesión'),
            ('Inició la lección'),
            ('Terminó la lección'),
            ('Inició el cuestionario'),
            ('Terminó el cuestionario')";

         // Inserta los registros.
        $wpdb->query($sql);
    }
}



?>