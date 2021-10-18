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

	function names2uids( $p_arr ) {
		if ( $p_arr == null || $p_arr == '' ) {
			return array();
		}
		if ( !is_array($p_arr) ) {
			$p_arr = explode(',', $p_arr);
		}
		require_once( MANTIS_DIR . '/core.php' );

		$t_ret = array();
		foreach( $p_arr as $t_name ) {
            // username:projectA+projectB
            $pos = strpos($t_name, ':');
            $t_projects = array();
            if( $pos !== false ) {
                $t_projects = names2pids( substr($t_name, $pos+1) );
                $t_name = substr($t_name, 0, $pos);
                //echo "<!-- pos=$pos name=$t_name projects=".var_export($t_projects, TRUE)."-->";
            }
			$t_user_id = user_get_id_by_name($t_name);
			if( $t_user_id && user_is_enabled( $t_user_id ) ) {
				$t_ret[$t_user_id] = $t_projects;
			}
		}
        //echo '<!-- names2uids(' . var_export($p_arr, TRUE) . '): '. var_export($t_ret, TRUE) . '; -->';
		return $t_ret;
	}

	function uids2names( $p_arr ) {
		require_once( MANTIS_DIR . '/core.php' );

		$t_ret = array();
		foreach( $p_arr as $t_user => $t_projects ) {
			if( user_is_enabled( $t_user ) ) {
                $t_name = user_get_name( $t_user );
                if( count($t_projects) == 0 ) {
                    $t_ret[] = $t_name;
                } else {
                    $t_ret[] = $t_name . ":" . pids2names( $t_projects );
                }
                //echo "<!-- user=$t_user ".count($t_ret). '. '. $t_ret[count($t_ret)-1].'; -->';
			}
		}
        //echo '<!-- uids2names(' . var_export($p_arr, TRUE) . '): '. var_export($t_ret, TRUE) . '; -->';
		return $t_ret;
	}

    function names2pids( $p_arr ) {
		if ( $p_arr == null || $p_arr == '' ) {
			return array();
		}
		if ( !is_array($p_arr) ) {
			$p_arr = explode(',', $p_arr);
		}
		require_once( MANTIS_DIR . '/core.php' );

		$t_ret = array();
        foreach( $p_arr as $t_name ) {
            $t_id = project_get_id_by_name( $t_name );
            if( $t_id ) {
                $t_ret[] = $t_id;
            }
        }
        //echo '<!-- names2pids(' . var_export($p_arr, TRUE) . '): '. var_export($t_ret, TRUE) . '; -->';
        return $t_ret;
    }
	function pids2names( $p_arr ) {
		require_once( MANTIS_DIR . '/core.php' );

		$t_ret = array();
        foreach( $p_arr as $t_id ) {
            $t_name = project_get_name( $t_id );
            if( $t_name ) {
                $t_ret[] = $t_name;
            }
        }
        //echo '<!-- pids2names(' . var_export($p_arr, TRUE) . '): '. implode('+', $t_ret) . '; -->';
        return implode('+', $t_ret);
    }
}
