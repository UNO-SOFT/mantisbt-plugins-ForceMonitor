<?php
require_once( 'core.php' );
require_api( 'database_api.php' );
$UC_lists = array(
	'modified_issues' => 'SELECT id, summary, last_updated FROM ' . db_get_table( 'bug' )
		. ' ORDER BY last_updated DESC LIMIT 1000'
);


