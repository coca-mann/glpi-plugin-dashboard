<?php

class PluginDashboardConfig extends CommonDBTM {
	

   static protected $notable = true;
   
   /**
    * @see CommonGLPI::getMenuName()
   **/
   static function getMenuName() {
      return __('Dashboard');
   }
   
   /**
    *  @see CommonGLPI::getMenuContent()
    *
    *  @since version 0.5.6
   **/
   static function getMenuContent() {
   	global $CFG_GLPI;
   
   	$menu = array();

      $menu['title']   = __('Dashboard','dashboard');
      $menu['page']    = '/plugins/dashboard/front/index.php';
   	return $menu;
   }	

// Entity Tab	

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      switch (get_class($item)) {
         case 'Entity':
            return array(1 => __('Dashboard map','dashboard'));
         default:
            return '';
      }
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch (get_class($item)) {
         case 'Entity':
            //$config = new self($item->fields['id']);
            $config = new self();
            $config->showFormDisplay();
            break;
      }
      return true;
   }

  /**
    * Print the config form for display
    *
    * @return Nothing (display)
    * */
   function showFormDisplay() {
      global $CFG_GLPI, $DB;

      if (!Config::canView()) {
         return false;
      }           
      
      //get entity coordinates
      if(isset($_GET['id'])) {
	      $iterator = $DB->request([
	         'FROM' => 'glpi_plugin_dashboard_map',
	         'WHERE' => [
	            'entities_id' => $_GET['id']
	         ],
	         'LIMIT' => 1
	      ]);
	      
	      if (count($iterator) > 0) {
	         $ent_info = $iterator->current();
	         $LNG = $ent_info['lng'];
	         $LAT = $ent_info['lat'];
	      } else {
	         $LNG = '';
	         $LAT = '';
	      }
		}
		else {
			$LNG = '';
			$LAT = '';
		}	

      $canedit = Session::haveRight(Config::$rightname, UPDATE);
      if ($canedit) {         
         echo "<form name='form' action='../plugins/dashboard/front/map/insert_coord.php' method='post'>";
      }
      echo Html::hidden('config_context', ['value' => 'dashboard']);
      echo Html::hidden('config_class', ['value' => __CLASS__]);            

      echo "<div class='center' id='tabsbody'>";

      echo "<table class='tab_cadre_fixe' style='width:95%;'>";

      echo "<tr><th colspan='4'>" . __('Setup') . "</th></tr>";     
      
      echo "<tr class='tab_bg_2'></tr>";      		

      echo "<tr class='tab_bg_2'>";      
      echo "<td>". __('Latitude') ."</td>";      
      echo "<td><input type='text' class='form-control' id='lat' name='lat' value=".$LAT."></td>";           
		echo "</tr>";		

      echo "<tr class='tab_bg_2'>";      
      echo "<td width='110px'>". __('Longitude') ."</td>";
      echo "<td><input type='text' class='form-control' id='lng' name='lng' value=".$LNG."></td>";
		echo "</tr>";
		
      echo "<tr class='tab_bg_2'><td>&nbsp;</td></tr>";      

      echo "<td><input type='hidden' id='id' name='id' value=".$_GET['id']."></td>";           
		
		if ($canedit) {
         echo "<tr class='tab_bg_2'>";
         echo "<td colspan='4' class='center'>";
         echo "<input type='submit' name='update' class='submit' value=\"" . _sx('button', 'Save') . "\">";
         echo "</td></tr>";
      }
		
      echo "</table></div>";
      Html::closeForm();
   }

   /**
    * Get configuration value for a user
    *
    * @param string $name Configuration name
    * @param int $user_id User ID
    * @return string Configuration value
    */
   static function getValue($name, $user_id = 0) {
      global $DB;
      
      $iterator = $DB->request([
         'FROM' => 'glpi_plugin_dashboard_config',
         'WHERE' => [
            'name' => $name,
            'users_id' => $user_id
         ],
         'LIMIT' => 1
      ]);
      
      if (count($iterator) > 0) {
         $row = $iterator->current();
         return $row['value'];
      }
      
      return '';
   }

   static function getYears($num_years = -1) {
      global $DB;
  
      $iterator = null;
  
      if ($num_years == -1) {
          // Obter todos os anos, em ordem ascendente
          $iterator = $DB->request([
              // CORREÇÃO: Alias 'AS year' dentro da QueryExpression
              'SELECT'   => [new \Glpi\DBAL\QueryExpression('DATE_FORMAT(date, "%Y") AS year')],
              'DISTINCT' => true,
              'FROM'     => 'glpi_tickets',
              'WHERE'    => [
                  'is_deleted' => 0,
                  'date'       => ['IS NOT', null]
              ],
              // CORREÇÃO: GROUPBY deve usar o alias definido no SELECT
              'GROUPBY'  => ['year'],
              'ORDERBY'  => ['year ASC'] // ORDERBY também pode usar o alias
          ]);
  
      } else {
          // Obter os últimos N anos, em ordem descendente
          $iterator = $DB->request([
               // CORREÇÃO: Alias 'AS year' dentro da QueryExpression
              'SELECT'   => [new \Glpi\DBAL\QueryExpression('DATE_FORMAT(date, "%Y") AS year')],
              'DISTINCT' => true,
              'FROM'     => 'glpi_tickets',
              'WHERE'    => [
                  'is_deleted' => 0,
                  'date'       => ['IS NOT', null]
              ],
              // CORREÇÃO: GROUPBY deve usar o alias definido no SELECT
              'GROUPBY'  => ['year'],
              'ORDERBY'  => ['year DESC'], // ORDERBY também pode usar o alias
              'LIMIT'    => $num_years
          ]);
      }
  
      $years = [];
      if ($iterator) {
          // Itera sobre o resultado do $DB->request
          foreach ($iterator as $row) {
              // A coluna agora será 'year' por causa do alias
              $years[] = $row['year'];
          }
      }
  
      // Se $num_years != -1, os anos foram obtidos em ordem DESC, então revertemos para ASC
      if ($num_years != -1) {
         rsort($years); // Ordena em ordem decrescente e depois reverte para ascendente
         $years = array_reverse($years);
      }
  
  
      return $years;
   }

   /**
     * Get ticket statistics for a year
     *
     * @param string $year Year to get statistics for
     * @param string $entity_filter Entity filter (comma-separated IDs)
     * @return int Number of tickets
     */
    static function getTicketCountForYear($year, $entity_filter = '') {
      global $DB;

      $where = [
          'is_deleted' => 0,
          'RAW'        => [
              "DATE_FORMAT(`date`, '%Y')" => $year
          ]
      ];

      if (!empty($entity_filter)) {
          $entity_ids = array_filter(array_map('intval', explode(',', $entity_filter)));
          if (!empty($entity_ids)) {
              $where['entities_id'] = ['IN', $entity_ids];
          }
      }

      $iterator = $DB->request([
          // CORREÇÃO: Usar um alias válido para COUNT
          'COUNT' => 'total',
          'FROM'  => 'glpi_tickets',
          'WHERE' => $where
      ]);

      if ($iterator && count($iterator) > 0) {
          $row = $iterator->current();
          // CORREÇÃO: Acessar o resultado usando o alias
          return (int)$row['total'];
      }

      return 0;
  }

  /**
   * Get ticket statistics for a month
   *
   * @param string $month Month to get statistics for (YYYY-MM format)
   * @param string $entity_filter Entity filter (comma-separated IDs)
   * @return int Number of tickets
   */
  static function getTicketCountForMonth($month, $entity_filter = '') {
      global $DB;

      $where = [
          'is_deleted' => 0,
          'RAW'        => [
              "DATE_FORMAT(`date`, '%Y-%m')" => $month
          ]
      ];

      if (!empty($entity_filter)) {
          $entity_ids = array_filter(array_map('intval', explode(',', $entity_filter)));
          if (!empty($entity_ids)) {
              $where['entities_id'] = ['IN', $entity_ids];
          }
      }

      $iterator = $DB->request([
          // CORREÇÃO: Usar um alias válido para COUNT
          'COUNT' => 'total',
          'FROM'  => 'glpi_tickets',
          'WHERE' => $where
      ]);

      if ($iterator && count($iterator) > 0) {
          $row = $iterator->current();
           // CORREÇÃO: Acessar o resultado usando o alias
          return (int)$row['total'];
      }

      return 0;
  }

  /**
   * Get ticket statistics for today
   *
   * @param string $today Today's date (YYYY-MM-DD format)
   * @param string $entity_filter Entity filter (comma-separated IDs)
   * @return int Number of tickets
   */
  static function getTicketCountForToday($today, $entity_filter = '') {
      global $DB;

      $where = [
          'is_deleted' => 0,
          'RAW'        => [
              "DATE_FORMAT(`date`, '%Y-%m-%d')" => $today
          ]
      ];

      if (!empty($entity_filter)) {
          $entity_ids = array_filter(array_map('intval', explode(',', $entity_filter)));
          if (!empty($entity_ids)) {
              $where['entities_id'] = ['IN', $entity_ids];
          }
      }

      $iterator = $DB->request([
           // CORREÇÃO: Usar um alias válido para COUNT
          'COUNT' => 'total',
          'FROM'  => 'glpi_tickets',
          'WHERE' => $where
      ]);

      if ($iterator && count($iterator) > 0) {
          $row = $iterator->current();
          // CORREÇÃO: Acessar o resultado usando o alias
          return (int)$row['total'];
      }

      return 0;
  }

   /**
    * Get user count
    *
    * @param string $entity_filter Entity filter
    * @return int Number of users
    */
   static function getUserCount($entity_filter = '') {
      global $DB;
      
      $where = [
         'is_deleted' => 0,
         'is_active' => 1
      ];
      
      if ($entity_filter) {
         $iterator = $DB->request([
            'COUNT' => 'DISTINCT glpi_users.id',
            'FROM' => 'glpi_users',
            'LEFT JOIN' => [
               'glpi_profiles_users' => [
                  'ON' => [
                     'glpi_users' => 'id',
                     'glpi_profiles_users' => 'users_id'
                  ]
               ]
            ],
            'WHERE' => array_merge($where, [
               'glpi_profiles_users.entities_id' => ['IN', explode(',', $entity_filter)]
            ])
         ]);
      } else {
         $iterator = $DB->request([
            'COUNT' => 'id',
            'FROM' => 'glpi_users',
            'WHERE' => $where
         ]);
      }
      
      $row = $iterator->current();
      return $row['COUNT'];
   }


}
?>   