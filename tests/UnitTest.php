<?php
//
//  $Id$
//

class tests_UnitTest extends PhpUnit_TestCase
{
    function setUp()
    {
        foreach ($GLOBALS['allTables'] as $aTable) {
            $tableObj = new tests_Common($aTable);
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
    
    function assertStringEquals($expected,$actual,$msg='')
    {
        $expected = '~^\s*'.preg_replace('~\s+~','\s*',trim(preg_quote($expected))).'\s*$~i';
        $this->assertRegExp($expected,$actual,$msg);
    }    

}

?>
