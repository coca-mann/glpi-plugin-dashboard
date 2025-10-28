<?php

function plugin_dashboard_install(){
	
	global $DB, $LANG;
	
	if (! $DB->TableExists("glpi_plugin_dashboard_count")) {
        $query = "CREATE TABLE `glpi_plugin_dashboard_count` 
        (`id` INTEGER AUTO_INCREMENT, `type` INTEGER , `quant` INTEGER, PRIMARY KEY (`id`))
						ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci; ";
						
        $DB->doQuery($query);
        
        $insert = "INSERT INTO glpi_plugin_dashboard_count (type,quant) VALUES ('1','1')";
        $DB->doQuery($insert);
     } 	
    
//map
   if (! $DB->TableExists("glpi_plugin_dashboard_map")) {
		$query_map = "CREATE TABLE IF NOT EXISTS `glpi_plugin_dashboard_map` (
	  `id` int(4) NOT NULL AUTO_INCREMENT,
	  `entities_id` int(4) NOT NULL,
	  `location` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
	  `lat` float NOT NULL,
	  `lng` float NOT NULL,
	  PRIMARY KEY (`id`,`entities_id`)) 
	  ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
	
		$DB->doQuery($query_map);
		
	}	
	
	
	//configs
	
	if (! $DB->TableExists("glpi_plugin_dashboard_config")) {
		
		$query_conf = "CREATE TABLE IF NOT EXISTS `glpi_plugin_dashboard_config` (
	  `id` int(4) NOT NULL AUTO_INCREMENT,
	  `name` varchar(50) NOT NULL,
	  `value` varchar(25) NOT NULL,
	  `users_id` varchar(25) NOT NULL DEFAULT '',
	  PRIMARY KEY (`id`,`name`,`value`,`users_id`),
	  UNIQUE KEY `name` (`name`,`users_id`),
	  KEY `name_2` (`name`,`users_id`)) 
	  ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ; ";
	
	  $DB->doQuery($query_conf);
	 
	}	


	if ($DB->TableExists("glpi_plugin_dashboard_count")) {
		
		// Verificar se a tabela já tem a estrutura correta
		$check_query = "SHOW COLUMNS FROM glpi_plugin_dashboard_count LIKE 'id'";
		$result = $DB->request($check_query);
		$has_auto_increment = false;
		
		foreach ($result as $row) {
			if (strpos($row['Extra'], 'auto_increment') !== false) {
				$has_auto_increment = true;
				break;
			}
		}
		
		// Se não tem AUTO_INCREMENT, adicionar
		if (!$has_auto_increment) {
			$query_alt = "ALTER TABLE `glpi_plugin_dashboard_count` MODIFY `id` INTEGER AUTO_INCREMENT; ";		
			$DB->doQuery($query_alt);
		}
	}
	
	
	if ($DB->TableExists("glpi_plugin_dashboard_config")) {
		
		$query_alt = "ALTER TABLE glpi_plugin_dashboard_config MODIFY value varchar(125); ";				
		$DB->doQuery($query_alt);
		
		//Config entities
		$query_ent = "SELECT users_id FROM glpi_plugin_dashboard_config WHERE name = 'entity' AND value = '-1' ";		
		$result = $DB->request($query_ent);		
		
		foreach ($result as $row) {
			$query = "UPDATE glpi_plugin_dashboard_config SET value = '' WHERE name = 'entity' AND users_id = ".$row['users_id']." ";
			$DB->doQuery($query);
		}				
	}
	
	if ($DB->TableExists("glpi_plugin_dashboard_map")) {	
		$query_alt = "ALTER TABLE `glpi_plugin_dashboard_map` ADD UNIQUE (`location`); ";		
		$DB->doQuery($query_alt);	
	}	
		
	return true;
}


function plugin_dashboard_uninstall(){

	global $DB;
	
	$drop_count = "DROP TABLE glpi_plugin_dashboard_count";
	$DB->doQuery($drop_count); 	
	
	$drop_map = "DROP TABLE glpi_plugin_dashboard_map";
	$DB->doQuery($drop_map);
	
	$drop_config = "DROP TABLE glpi_plugin_dashboard_config";
	$DB->doQuery($drop_config);
	
	$restore_mode = "SET sql_mode=(SELECT CONCAT(@@sql_mode,',ONLY_FULL_GROUP_BY'));";
	$DB->doQuery($restore_mode);
	
	return true;

}

?>
