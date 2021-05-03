<?php

require_once CPX_LOG_USER_ACTIVITY_PATH . '/includes/classes/class-cpx-logger.php';

/**
 * Registra los hooks para hacer logs de actividad en el curso.
 */
class CPX_Learnpress_Activity_Logger extends CPX_Logger {
	
	/**
	 * Registra los hooks de iniciar y terminar una lección y
	 * de iniciar y terminar un quiz.
	 *
	 * @return  void
	 */
	protected function cpx_log_register_hooks () {
		add_action( 'learn-press/content-item-summary/lp_lesson', array( $this, 'cpx_log_entered_lesson' ));
		add_action( 'learn-press/user-completed-lesson', array( $this, 'cpx_log_completed_lesson' ) );
		add_action( 'learn-press/user/quiz-started', array( $this, 'cpx_log_started_quiz' ) );
		add_action( 'learn-press/user/quiz-finished', array( $this, 'cpx_log_submitted_quiz' ) );
	}


	/**
	 * Guarda el log de iniciar una lección del curso.
	 * 
	 * @return  void
	 */
	public function cpx_log_entered_lesson() {

		// Toma el usuario de LearnPress.
		$lp_user = LP_Global::user();
		$course = LP_Global::course();
		$item = LP_Global::course_item();
		$completed = $lp_user->has_completed_item( $item->get_id(), $course->get_id() );

		if ( ! $completed ) {
			// Toma la información del usuario.
			$user = wp_get_current_user();
			
			$args = array (
				'user_id' => $user->ID,
		        'activity_type_id' => 3,	// 3 = Inició la lección.
	        	'item_id' => $item->get_id(),
		        'activity_date' => current_time( 'mysql' ),
			);

			// Guarga el log en la DB.
			$this->cpx_store_log( $args );
		}
	}

	/**
	 * Guarda el log de terminar una lección del curso.
	 * 
	 * @return  void
	 */
	public function cpx_log_completed_lesson () {
		
		// Toma la información de la lección.
		$item = LP_Global::course_item();

		// Toma la información del usuario.
		$user = wp_get_current_user();

		$args = array (
			'user_id' => $user->ID,
	        'activity_type_id' => 4,	// 4 = Terminó la lección.
        	'item_id' => $item->get_id(),
	        'activity_date' => current_time( 'mysql' ),
		);

		// Guarga el log en la DB.
		$this->cpx_store_log( $args );
	}

	/**
	 * Guarda el log de empezar un quiz del curso.
	 * 
	 * @return  void
	 */
	public function cpx_log_started_quiz () {

		// Toma la información del quiz.
		$quiz = LP_Global::course_item_quiz();
		
		// Toma la información del usuario.
		$user = wp_get_current_user();
		
		$args = array (
			'user_id' => $user->ID,
	        'activity_type_id' => 5,	// 5 = Inició el quiz.
        	'item_id' => $item->get_id(),
	        'activity_date' => current_time( 'mysql' ),
		);

		// Guarga el log en la DB.
		$this->cpx_store_log( $args );
	}

	/**
	 * Guarda el log de terminar un quiz del curso.
	 * 
	 * @return  void
	 */
	public function cpx_log_submitted_quiz () {

		// Toma la información del quiz.
		$item = LP_Global::course_item();
		
		// Toma la información del usuario.
		$user = wp_get_current_user();

		$args = array (
			'user_id' => $user->ID,
	        'activity_type_id' => 6,	// 6 = Terminó el quiz.
        	'item_id' => $item->get_id(),
	        'activity_date' => current_time( 'mysql' ),
		);

		// Guarga el log en la DB.
		$this->cpx_store_log( $args );
	}
}



?>