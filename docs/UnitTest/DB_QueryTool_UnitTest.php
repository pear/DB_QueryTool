<?php
//
//  $Id$
//

require_once 'PHPUnit.php';
require_once 'Common.php';

class DB_QueryTool_UnitTest extends PhpUnit_TestCase
{
    function setUp()
    {
        foreach ($GLOBALS['allTables'] as $aTable) {
            $tableObj = new Common($aTable);
            $tableObj->removeAll();
        }

        $this->setLooselyTyped(true);
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
}

?>
