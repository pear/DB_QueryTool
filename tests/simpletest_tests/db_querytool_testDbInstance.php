<?php
// $Id$

require_once dirname(__FILE__).'/db_querytool_test_base.php';

class TestOfDB_QueryToolDbInstance extends TestOfDB_QueryTool
{
    function TestOfDB_QueryToolDbInstance($name = __CLASS__) {
        $this->UnitTestCase($name);
    }
    function testSetDbInstanceDefault () {
        $db =& DB::connect(DB_DSN, $GLOBALS['DB_OPTIONS']);

        $qt =& new DB_QueryTool();
        $qt->setDbInstance($db);
        $dbActual =& $qt->getDbInstance();
        $this->assertEqual($db->fetchmode, $dbActual->fetchmode);
    }
    function SetDbInstanceOldWay () {
        $qt =& new DB_QueryTool(DB_DSN, $GLOBALS['DB_OPTIONS']);
        $db =& $qt->getDbInstance();
        $this->assertTrue(is_a($db, 'mdb_common'));
    }
}

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new TestOfDB_QueryToolDbInstance();
    $test->run(new HtmlReporter());
}
?>