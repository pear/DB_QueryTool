<?php
//
//  $Id$
//


ini_set('include_path',realpath(dirname(__FILE__).'/../../../../').':'.realpath(dirname(__FILE__).'/../../../../../includes').':'.ini_get('include_path'));
require_once 'DB/QueryTool.php';
require_once 'PHPUnit.php';
require_once 'PHPUnit/GUI/HTML.php';
require_once 'PHPUnit/GUI/SetupDecorator.php';

define(DB_DSN,'mysql://root@localhost/test');
define(TABLE_USER,      'QueryTool_user');
define(TABLE_ADDRESS,   'QueryTool_address');
$allTables = array(TABLE_USER,TABLE_ADDRESS);
require_once 'sql.php'; 

require_once 'Common.php';

//
//  common setup (this actually also does the tearDown, since we have the DROP TABLE queries in the setup too
//
$querytool = new Common();
foreach ($dbStructure[$querytool->db->phptype]['setup'] as $aQuery) {
    if (DB::isError($ret=$querytool->db->query($aQuery))) {
        die($ret->getUserInfo());
    }
}

//
//  run the test suite
//
$gui = new PHPUnit_GUI_SetupDecorator(new PHPUnit_GUI_HTML());
$gui->getSuitesFromDir(dirname(__FILE__),'DB_QueryTool_UnitTest_.*');
$gui->show(false);  // we dont want to see the passed test

//print_r($errors);

?>
