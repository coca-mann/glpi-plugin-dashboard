<?php


function plugin_init_dashboard() {

   global $PLUGIN_HOOKS, $LANG ;
	
	$PLUGIN_HOOKS['csrf_compliant']['dashboard'] = true;
	
   Plugin::registerClass('PluginDashboardConfig', [
      'addtabon' => ['Entity']
   ]);  
          
    $PLUGIN_HOOKS["menu_toadd"]['dashboard'] = array('plugins'  => 'PluginDashboardConfig');
    $PLUGIN_HOOKS['config_page']['dashboard'] = 'front/index.php';
                
}


function plugin_version_dashboard(){
	global $DB, $LANG;

	return array('name'			=> __('Dashboard','dashboard'),
					'version' 			=> '1.0.3',
					'author'			   => 'Juliano Ostroski',
					'license'		 	=> 'GPLv2+',
					'homepage'			=> 'https://plugins.glpi-project.org/#/plugin/dashboard',
					'minGlpiVersion'	=> '11.0.0'
					);
}


function plugin_dashboard_check_prerequisites(){
     return true;
}


function plugin_dashboard_check_config($verbose=false){
	if ($verbose) {
		echo 'Installed / not configured';
	}
	return true;
}


?>
