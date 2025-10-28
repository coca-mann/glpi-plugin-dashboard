<?php

include ("../../../inc/includes.php");
global $DB;

Session::checkLoginUser();

$criteria = [
	'SELECT' => 'value',
	'FROM' => 'glpi_plugin_dashboard_config',
	'WHERE' => [
		'name' => 'layout',
		'users_id' => $_SESSION['glpiID']
	]
];
$result_lay = $DB->request($criteria);
$layout = '';
if ($row = $result_lay->next()) {
	$layout = $row['value'];
}
					
//redirect to index
if($layout == '0')
	{
		// sidebar
		$redir = '<meta http-equiv="refresh" content="0; url=index2.php" />';
	}

if($layout == 1 || $layout == '' )
	{
		//top menu
		$redir = '<meta http-equiv="refresh" content="0; url=index1.php" />';
	}						
?>

<!DOCTYPE html>
<html>
<head>
    <title>GLPI - Dashboard - Home</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	 <meta http-equiv="Pragma" content="public">
    <?php echo $redir; ?>        
      	 
</head>
<body style='background-color: #FFF;'>
</body>
</html>
