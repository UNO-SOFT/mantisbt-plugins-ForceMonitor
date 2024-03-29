<?php
# :vim set noet:

if ( !defined( 'MANTIS_DIR' ) ) {
	define( 'MANTIS_DIR', dirname(__FILE__) . '/../..' );
}
if ( !defined( 'MANTIS_CORE' ) ) {
	define( 'MANTIS_CORE', MANTIS_DIR . '/core' );
}

require_once(MANTIS_DIR . '/core.php');
require_once( config_get( 'class_path' ) . 'MantisPlugin.class.php' );

class ForceMonitorPlugin extends MantisPlugin {
	function register() {
		$this->name = 'ForceMonitor';	# Proper name of plugin
		$this->description = 'Forcefully adds the specified monitors to each new issue.';	# Short description of the plugin
		$this->page = 'config';		   # Default plugin page

		$this->version = '0.5.0';	 # Plugin version string
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
			//'EVENT_DISPLAY_BUG_ID' => 'display_bug_id',
		);
	}

	function menu_manage( ) {

		if ( access_get_project_level() >= MANAGER) {
			return array( '<a href="' . plugin_page( 'config.php' ) . '">'
				.  plugin_lang_get('config') . '</a>', );
		}
	}

	function display_bug_id($p_event, $p_str) {
		if( strlen($p_str) > 1 && substr_compare( $p_str, "#", 0, 1 ) == 0 ) {
			return $p_str;
		}
		return "#" . $p_str;
	}

	function bug_reported($p_event, $p_bug_data) {
		log_event( LOG_EMAIL_RECIPIENT, "event=$p_event params=".var_export($p_bug_data, true) );
		$t_bug_id = $p_bug_data->id;
		$t_project_id = $p_bug_data->project_id;
		log_event( LOG_FILTERING, "bug_id=$t_bug_id" );

		require_once( dirname(__FILE__).'/core/forcemonitor_api.php' );
		$t_users = names2uids( plugin_config_get( 'users_always_monitor', NULL ) );

		require_once( MANTIS_CORE . '/bug_api.php' );
		require_once( MANTIS_CORE . '/user_api.php' );
		foreach( $t_users as $t_user_id => $t_projects ) {
			if( $t_user_id && user_is_enabled( $t_user_id ) ) {
				//log_event( LOG_MAIL, "<-- uid=$t_user_id pid=$t_project_id projects=". var_export($t_projects, TRUE) . "-->");
				if( count( $t_projects ) == 0 || in_array( $t_project_id, $t_projects ) ) {
					bug_monitor( $t_bug_id, $t_user_id );
					log_event( LOG_FILTERING, "adding monitor $t_user_id");
				}
			}
		}
		return $t_users;
	}

}

// vim: set noet:
