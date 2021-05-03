<?php

require_once CPX_LOG_USER_ACTIVITY_PATH . '/includes/classes/class-cpx-logger.php';

/**
 * Registra los hooks para hacer logs de usuario.
 */
class CPX_User_Activity_Logger extends CPX_Logger {
	
	/**
	 * Registra los hooks de login y logout.
	 *
	 * @return  void
	 */
	protected function cpx_log_register_hooks () {
		add_action( 'wp_login', array( $this, 'cpx_log_login' ), 10, 2 );
		add_action( 'clear_auth_cookie', array( $this,'cpx_log_logout' ) );
	}

	/**
	 * Guarda el log de inicio de sesión en la DB.
	 * 
	 * @param   string  $user_login  Username.
	 * @param   WP_User $user        Instancia del usuario.
	 * 
	 * @return  void
	 */
	public function cpx_log_login ( $user_login, $user ) {

		// Toma el correo del usuario
		$args = array (
			'user_id' => $user->ID,
	        'activity_type_id' => 1,	// 1 = Inicio de sesión.
	        'item_id' => null,
	        'activity_date' => current_time( 'mysql' ),
		);

		// Guarga el log en la DB.
		$this->cpx_store_log( $args );
	}

	/**
	 * Guarda el log de cierre de sesión en la DB.
	 * 
	 * @return  void
	 */
	public function cpx_log_logout () {
		
		// Toma el usuario.
		$user = wp_get_current_user();

		$args = array (
			'user_id' => $user->ID,
	        'activity_type_id' => 2,	// 2 = Cierre de sesión.
	        'item_id' => null,
	        'activity_date' => current_time( 'mysql' ),
		);
		
		// Guarga el log en la DB.
		$this->cpx_store_log( $args );
	}
}





?>