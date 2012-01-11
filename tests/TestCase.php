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
    public static function setUpBeforeClass()
    {
        require dirname(__FILE__) . '/sql.php';
        $querytool = new tests_Common();
        foreach ($dbStructure[$querytool->db->phptype]['setUp'] as $aQuery) {
            if (DB::isError($ret=$querytool->db->query($aQuery))) {
                $this->markTestSkipped($ret->getUserInfo());
            }
        }
    }

    protected function setUp()
    {
        foreach ($GLOBALS['allTables'] as $aTable) {
            $tableObj = new tests_Common($aTable);
            $tableObj->removeAll();
        }
    }

    public static function tearDownAfterClass()
    {
        require dirname(__FILE__) . '/sql.php';
        $querytool = new tests_Common();
        foreach ($dbStructure[$querytool->db->phptype]['tearDown'] as $aQuery) {
            $querytool->db->query($aQuery);
        }
    }

    protected function assertStringEquals($expected,$actual,$msg='')
    {
        $expected = '~^\s*'.preg_replace('~\s+~','\s*',trim(preg_quote($expected))).'\s*$~i';
        $this->assertRegExp($expected,$actual,$msg);
    }

}

?>
