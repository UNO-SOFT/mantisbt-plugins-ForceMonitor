<?php
if( !function_exists('str2list') ) {
	function str2list($p_text, $p_type='name') {
		$t_arr = $p_text != null ? explode(',', $p_text) : array();
		if ( $p_type == 'name' ) {
			$t_arr = uids2names(names2uids($t_arr));
			sort($t_arr);
		} elseif ( $p_type == 'field' ) {
			$t_arr = fids2fields(fields2fids($t_arr));
		}
		return $t_arr;
	}

	function list2str($p_arr, $p_type='name') {
		if ( $p_type == 'name' ) {
			$p_arr = uids2names(names2uids($p_arr));
			sort($p_arr);
		} elseif ( $p_type == 'field' ) {
			$p_arr = fids2fields(fields2fids($p_arr));
		}
		return implode(',', $p_arr);
	}

	function fields2fids($p_arr) {
		if ( $p_arr == null || $p_arr == '' ) {
			return array();
		}
		if ( !is_array($p_arr) ) {
			$p_arr = explode(',', $p_arr);
		}
		require_once( MANTIS_DIR . '/core.php' );
		require_api( 'custom_field_api.php' );
		$ret = array();
		foreach($p_arr as $field) {
			$t_id = custom_field_get_id_from_name( $field );
			if ( $t_id === false ) {
				continue;
			}
			$ret[] = $t_id;
		}
		return $ret;
	}

	function fids2fields($p_arr) {
		if ( $p_arr == null || $p_arr == '' ) {
			return array();
		}
		if ( !is_array($p_arr) ) {
			$p_arr = explode(',', $p_arr);
		}
		require_once( MANTIS_DIR . '/core.php' );
		require_api( 'custom_field_api.php' );
		$ret = array();
		foreach($p_arr as $t_id) {
			$field = custom_field_cache_row( $t_id, $p_trigger_errors = false );
			//echo '<!--<pre>id='.$t_id.'='.var_export($field, true).'</pre>-->';
			if ( $field === false || !is_array( $field ) || !array_key_exists( 'name',  $field ) ) {
				continue;
			}
			$ret[] = $field['name'];
		}
		return $ret;
	}

	function names2uids($p_arr) {
		require_once( MANTIS_DIR . '/core.php' );
		$ret = array();
		foreach($p_arr as $name) {
			$t_id = user_get_id_by_name($name);
			if( $t_id && user_is_enabled($t_id) ) {
				$ret[] = $t_id;
			}
		}
		return $ret;
	}

	function uids2names($p_arr) {
		require_once( MANTIS_DIR . '/core.php' );

		$ret = array();
		foreach($p_arr as $t_id) {
			if( user_is_enabled($t_id) ) {
				$ret[] = user_get_name($t_id);
			}
		}
		return $ret;
	}
}
