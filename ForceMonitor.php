<?php
# :vim set noet:

define(MANTIS_DIR, dirname(__FILE__) . '/../..' );
define(MANTIS_CORE, MANTIS_DIR . '/core' );

require_once(MANTIS_DIR . '/core.php');
require_once( config_get( 'class_path' ) . 'MantisPlugin.class.php' );

class ForceMonitorPlugin extends MantisPlugin {
	function register() {
		$this->name = 'ForceMonitor';	# Proper name of plugin
		$this->description = 'Forcefully adds the specified monitors to each new issue.';	# Short description of the plugin
		$this->page = 'config';		   # Default plugin page

		$this->version = '0.2';	 # Plugin version string
		$this->requires = array(	# Plugin dependencies, array of basename => version pairs
			'MantisCore' => '2.0.0',
			);

		$this->author = 'Tamás Gulácsi';		 # Author/team name
		$this->contact = 'T.Gulacsi@unosoft.hu';		# Author/team e-mail address
		$this->url = 'http://www.unosoft.hu';			# Support webpage
	}

	function config() {
		return array(
			'users_always_monitor' => array(),
		);
	}

	function hooks() {
		return array(
			'EVENT_MENU_MANAGE' => 'menu_manage',
			'EVENT_REPORT_BUG' => 'bug_reported',
		);
	}

	function menu_manage( ) {

		if ( access_get_project_level() >= MANAGER) {
			return array( '<a href="' . plugin_page( 'config.php' ) . '">'
				.  plugin_lang_get('config') . '</a>', );
		}
	}

	function bug_reported($p_event, $p_bug_data) {
		log_event( LOG_EMAIL_RECIPIENT, "event=$p_event params=".var_export($p_bug_data, true) );
		$t_bug_id = $p_bug_data->id;
		log_event( LOG_FILTERING, "bug_id=$t_bug_id" );
		$res = array();

		require_once( dirname(__FILE__).'/core/forcemonitor_api.php' );
		$res = array_merge($res, str2list( plugin_config_get( 'users_always_monitor', NULL ) ));

		require_once( MANTIS_CORE . '/bug_api.php' );
		require_once( MANTIS_CORE . '/user_api.php' );
		foreach($res as $t_username) {
			$t_user_id = user_get_id_by_name( $t_username );
			if( $t_user_id ) {
				bug_monitor( $t_bug_id, $t_user_id );
				log_event( LOG_FILTERING, "adding monitor $t_username");
			}
		}
		return $res;
	}

}
