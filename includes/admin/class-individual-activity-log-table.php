<?php

/**
 * Tabla de frecuencias.
 */
class CPX_Log_Individual_Activity_Table extends WP_List_Table {

	protected $user;

	/**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     */
    public function __construct( $user ) {
       	parent::__construct( array(
	      	'ajax'   => false //We won't support Ajax for this table
  		) );

  		$this->user = $user;
    }

    /**
   	 * Define las columnas de la tabla.
   	 * 
   	 * @return array
   	 */
	public function get_columns() {
	    $columns = array(
			'activity_date' => 'Fecha y Hora',
			'activity_description' => 'Movimiento',
		);

	    return $columns;
	}

	/**
	 * Query
	 *
	 * @param integer $per_page
	 * @param integer $page_number
	 *
	 * @return mixed
	 */
	public function get_records( $per_page = 25, $page_number = 1 ) {

		global $wpdb;

		// Forma el Query para formar todos los rows.
		$sql = "SELECT
		    activity_types.description AS last_activity_description,
		    posts.post_title,
		    cpx.activity_date
		FROM
		    `wp_cpx_user_activity_logs` AS cpx
		LEFT JOIN wp_posts AS posts ON cpx.item_id = posts.ID
		LEFT JOIN wp_cpx_user_activity_types AS activity_types ON cpx.activity_type_id = activity_types.id
		WHERE cpx.user_id = " . $this->user->ID;

		// Revisa los filtros de búsqueda.
		$sql = $this->filter_records($sql);

		// Define el ordenamiento.
		if ( ! empty( $_REQUEST[ 'orderby' ] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST[ 'orderby' ] );
			$sql .= ! empty( $_REQUEST[ 'order' ] ) ? ' ' . esc_sql( $_REQUEST[ 'order' ] ) : ' ASC';
		} else {
			$sql .= ' ORDER BY activity_date DESC ';
		}

		// Limita los resultados por página.
		$sql .= " LIMIT $per_page";
		// Pasa la página.
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		// Ejecuta el query.
		$result = $wpdb->get_results( $sql, 'ARRAY_A' );
		// Formatea los records obtenidos.
		$result = $this->format_records($result);

		return $result;
	}

	/**
	 * Devuelve el query con la WHERE clause para la búsqueda.
	 * 
	 * @param  string $sql query string
	 * @return string
	 */
	private function filter_records ( $sql ) {
		$result = $sql;

		// Si tiene fecha inicial...
		if ( isset( $_GET[ 'start_date' ] ) && $_GET[ 'start_date' ] != '' ) {

			$replaced_date = str_replace('/', '-', $_GET[ 'start_date' ]);
			$date = strtotime($replaced_date);
			$formatted_time = date('Y-m-d 00:00', $date);

			$result .= " AND activity_date > '{$formatted_time}'";
		}

		// Si tiene fecha final.
		if ( (isset( $_GET[ 'end_date' ] ) && $_GET[ 'end_date' ] != '')) {

			$replaced_date = str_replace('/', '-', $_GET[ 'end_date' ]);
			$date = strtotime($replaced_date);
			$formatted_time = date('Y-m-d 23:59', $date);

			$result .= " AND activity_date < '{$formatted_time}'";
		}

		return $result;
	}

	/**
	 * Le da el formato a cada una de las respuestas para no repetir
	 * el nombre del cuestionario ni el título de la pregunta.
	 * 
	 * @param  array $records_array Resultados de la vista
	 * @return array
	 */
	private function format_records( $records_array ) {
		$result = array();

		// Por cada row...
		foreach ( $records_array as $row ) {
			// Formatea la hora del último login.
			$activity_date = strtotime( $row[ 'activity_date' ] );
			$activity_date_formatted = date( 'd/m/Y g:i A', $activity_date );

			// Forma el string de la última actividad.
			$last_activity = $row[ 'last_activity_description' ] . ' ' .
				$row[ 'post_title' ] ?? '';

			// Lo inicializa.
			array_push( $result, array(
				'activity_date' => $activity_date_formatted,
				'activity_description' => $last_activity
			));
		}

		return $result;
	}

	/**
	 * Prepara el contenido de la tabla.
	 * 
	 * @return void
	 */
	public function prepare_items() {
		$columns = $this->get_columns(); 
		$hidden = array(); 
		// $sortable = array('activity_date' => ['date', true] ); 
		$this->_column_headers = array( $columns, $hidden, $this->get_sortable_columns()); 
		$this->items = $this->get_records();
	}

	/**
	  * Get sortable columns
	  * @return array
	  */
	public function get_sortable_columns(){
	    $s_columns = array (
	        'activity_date' => [ 'activity_date', false],
	    );
	    return $s_columns;
	}

	/**
	 * Define qué va a regresar cada columna.
	 * 
	 * @param  array  $item        row
	 * @param  string $column_name nombre de la columna
	 * 
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		switch( $column_name ) { 
			case 'activity_date':
			case 'activity_description':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ) ; // Mostramos todo el arreglo para resolver problemas
		}
	}

	/**
	 * Agrega el filtro de búsqueda. 
	 * 
	 * @param  string $which Indica si es el tablenav de arriba o abajo de la tabla
	 *                       (no es relevante para esta función.)
	 * @return void
	 */
	public function display_tablenav( $which ) {
		$user_id = $this->user->ID;

	    ?>
			<div class="tablenav <?php echo esc_attr( $which ); ?>">

		        <form action="" method="POST">
		        	<input type="hidden" name="download_csv" value="true">
		            <button class="button"> <i class="fas fa-file-excel"></i> Exportar</button>
		        </form>
			 
			    <br class="clear" />
			</div>
	    <?php
	}

	/**
	 * Exporta a excel los datos.
	 * 
	 * @return void
	 */
	public function export_to_csv() {

		if ( isset( $_POST[ 'download_csv' ] ) ) {
			$records = $this->get_records();
			// Si hay contenido.
			if ($records) {
	  			
	  			// Define los headers.
	            ob_clean();
		        header( 'Pragma: public' );
		        header( 'Expires: 0' );
		        header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		        header( 'Cache-Control: private', false );
		        header( 'Content-Type: text/csv' );
		        header( 'Content-Disposition: attachment;filename=Bitácora de ' . $this->user->user_email . '.csv' );
	  
	            $file = fopen('php://output', 'w');
	  
	  			// Agrega la row con los headers.
	            fputcsv( $file, array(
		            	utf8_decode('Fecha y Hora'),
		            	utf8_decode('Movimiento')
	            	)
	        	);
	  
	            // Por cada fila...
	            foreach ( $records as $row ) {
	            	
					$row_array = array(	
						utf8_decode($row[ 'activity_date' ]),
	                	utf8_decode($row[ 'activity_description' ]),
					);

					// Imprime la row.
					fputcsv($file, $row_array);
	            }

	            // Cierra el archivo.
	  			fclose( $file );
		        ob_flush();

	            exit();
	        }
	    }
	}
}



?>