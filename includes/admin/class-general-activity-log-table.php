<?php

/**
 * Tabla de frecuencias.
 */
class CPX_Log_General_Activity_Table extends WP_List_Table {

	/**
     * Constructor, we override the parent to pass our own arguments
     * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
     */
    public function __construct() {
       	parent::__construct( array(
	      	'ajax'   => false //We won't support Ajax for this table
  		) );
    }

   	/**
   	 * Define las columnas de la tabla.
   	 * 
   	 * @return array
   	 */
	public function get_columns() {
	    $columns = array(
			'user_nicename' => 'Nombre',
			'user_email' => 'Correo',
			'last_login' => 'Última entrada',
			'last_activity' => 'Último movimiento',
			'binnacle' => 'Bitácora',
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
	public function get_records( $per_page = 10000000, $page_number = 1 ) {

		global $wpdb;

		// Forma el Query para formar todos los rows.
		$sql = "SELECT
			cpx.id,
		    users.ID AS user_id,
		    users.user_nicename,
		    users.user_email,
		    activity_types.description AS last_activity_description,
		    max_date.last_login,
		    posts.post_title,
		    cpx.activity_date AS last_activity_date
		FROM
		    `wp_cpx_user_activity_logs` AS cpx
		LEFT JOIN wp_users AS users ON cpx.user_id = users.ID
		LEFT JOIN wp_posts AS posts ON cpx.item_id = posts.ID
		LEFT JOIN wp_cpx_user_activity_types AS activity_types ON cpx.activity_type_id = activity_types.id
		LEFT JOIN (SELECT
		        user_id,
		        MAX(activity_date) AS last_login
		    FROM
		        `wp_cpx_user_activity_logs`
		    WHERE
		        activity_type_id = 1
		    GROUP BY
		        user_id) AS max_date ON cpx.user_id = max_date.user_id
		WHERE cpx.activity_date = (SELECT MAX(activity_date)
		                 FROM `wp_cpx_user_activity_logs` AS last_activity 
		                 WHERE last_activity.user_id = cpx.user_id)";

		// Revisa los filtros de búsqueda.
		$sql = $this->filter_records($sql);

		$sql .= "GROUP BY cpx.user_id";

		// Define el ordenamiento.
		if ( ! empty( $_REQUEST[ 'orderby' ] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST[ 'orderby' ] );
			$sql .= ! empty( $_REQUEST[ 'order' ] ) ? ' ' . esc_sql( $_REQUEST[ 'order' ] ) : ' ASC';
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
			$last_login = strtotime( $row[ 'last_login' ] );
			$last_login_formatted = date( 'd/m/Y g:i A', $last_login );

			// Formatea la last_activity_date de la última actividad.
			$last_activity = strtotime( $row[ 'last_activity_date' ] );
			$last_activity_formatted = date( 'd/m/Y g:i A', $last_activity );

			// Forma el string de la última actividad.
			$last_activity = $last_activity_formatted . ' - ' .
				$row[ 'last_activity_description' ] .
				($row[ 'post_title' ] != null ? (': "' . $row[ 'post_title' ]. '"') : '');

			// Lo inicializa.
			$result[ $row[ 'user_email' ] ] = array(
				'user_nicename' => $row[ 'user_nicename' ],
				'user_email' => $row[ 'user_email' ],
				'last_login' => $last_login_formatted,
				'last_activity' => $last_activity,
				'binnacle' => '<a href="' . admin_url() . 'admin.php?page=user-activity-logs&user=' . $row[ 'user_id' ] . '">Ver bitácora</a>',
			);
		}

		return $result;
	}

	/**
	 * Devuelve el query con la WHERE clause para la búsqueda.
	 * 
	 * @param  string $sql query string
	 * @return string
	 */
	private function filter_records ($sql) {
		$result = $sql;

		// Si se hizo una búsqueda...
		if ( isset( $_GET[ 'search_phrase' ] ) && $_GET[ 'search_phrase' ] != '' ) {
			$keyword = '%'.sanitize_text_field($_GET[ 'search_phrase' ]).'%';
			$result .= " AND (users.user_email LIKE '{$keyword}' OR users.user_nicename LIKE '{$keyword}') ";
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
		$sortable = array(); 
		$this->_column_headers = array( $columns, $hidden, $sortable ); 
		$this->items = $this->get_records();
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
			case 'user_nicename':
			case 'user_email':
			case 'last_login':
			case 'last_activity':
			case 'binnacle':
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
	public function display_tablenav($which) {
		$search_phrase = $_GET[ 'search_phrase' ] ?? ''; 

	    ?>
			<div class="tablenav <?php echo esc_attr( $which ); ?>">
			 
			    <form action="" method="GET">
		            <input type="hidden" name="page" value="user-activity-logs">
		            <p class="search-box">
		                <label for="search_phrase">Buscar</label>
		                <input type="search" id="search_phrase" name="search_phrase" value="<?php echo $search_phrase ?>">
		                <button class="button">Buscar</button>
		            </p>
		        </form>

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
		        header( 'Content-Disposition: attachment;filename=Actividad general de usuarios.csv' );
	  
	            $file = fopen('php://output', 'w');
	  
	  			// Agrega la row con los headers.
	            fputcsv( $file, array(
		            	utf8_decode('Nombre'),
		            	utf8_decode('Correo'),
		            	utf8_decode('Última entrada'),
		            	utf8_decode('Último movimiento'),
	            	)
	        	);
	  
	            // Por cada fila...
	            foreach ( $records as $row ) {
					// Si es la primera...
					$row_array = array(
						utf8_decode($row[ 'user_nicename' ]),
						utf8_decode($row[ 'user_email' ]),
	                	utf8_decode($row[ 'last_login' ]),
	                	utf8_decode($row[ 'last_activity' ])
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