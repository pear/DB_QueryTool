<?php
//
//  $Id$
//

require_once 'PHPUnit/Autoload.php';
require_once 'DB/QueryTool.php';
require dirname(__FILE__) . '/config.php';
require dirname(__FILE__) . '/Common.php';

abstract class tests_TestCase extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        foreach ($GLOBALS['allTables'] as $aTable) {
            $tableObj = new tests_Common($aTable);
            $tableObj->removeAll();
        }
    }

    function tearDown()
    {
/*        global $dbStructure;

        $querytool = new Common();
        foreach ($dbStructure[$querytool->db->phptype]['tearDown'] as $aQuery) {
//print "$aQuery<br><br>";        
            if (DB::isError($ret=$querytool->db->query($aQuery))) {
                die($ret->getUserInfo());
            }
        }
*/        
    }
    
    function assertStringEquals($expected,$actual,$msg='')
    {
        $expected = '~^\s*'.preg_replace('~\s+~','\s*',trim(preg_quote($expected))).'\s*$~i';
        $this->assertRegExp($expected,$actual,$msg);
    }    

}

?>
