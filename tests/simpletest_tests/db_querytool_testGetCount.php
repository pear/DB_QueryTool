<?php
// $Id$

require_once dirname(__FILE__).'/db_querytool_test_base.php';

class TestOfDB_QueryTool_GetCount extends TestOfDB_QueryTool {

    function TestOfDB_QueryTool_GetCount($name = __CLASS__) {
        $this->UnitTestCase($name);
    }
    function _insertSampleRecords($n_records) {
        $this->qt =& new DB_QT(TABLE_USER);
        $newData = $this->_getSampleData(1);
        
        switch ($n_records) {
            case '6':
                $newData['name'] = 'x';
                $this->qt->add($newData);
                $newData['name'] = 'y';
                $this->qt->add($newData);
                $newData['name'] = 'z';
                $this->qt->add($newData);
            case '3':
                $newData['name'] = 'x';
                $this->qt->add($newData);
                $newData['name'] = 'y';
                $this->qt->add($newData);
                $newData['name'] = 'z';
                $this->qt->add($newData);
                break;
        }
    }

    function test_getCount3() {
        $this->_insertSampleRecords(3);
        $this->assertEqual(3, $this->qt->getCount(), 'Wrong count after inserting 3 rows');
    }
    function test_getCount6() {
        $this->_insertSampleRecords(6);
        $this->assertEqual(6, $this->qt->getCount(), 'Wrong count after inserting 6 rows');
    }
    function test_getCountGrouped3() {
        $this->_insertSampleRecords(6);
        $this->qt->setGroup('name');
        $this->assertEqual(3, $this->qt->getCount(), 'Wrong count after 6 inserted and grouping them by name');
    }
    function test_getCountGrouped2() {
        $this->_insertSampleRecords(6);
        $this->qt->setWhere("name='z'");
        $this->assertEqual(2, $this->qt->getCount(), 'setWhere and setGroup should have resulted in two');
    }
    function test_getCountGrouped1() {
        $this->_insertSampleRecords(6);
        $this->qt->setGroup('name');
        $this->qt->setWhere("name='z'");
        $this->assertEqual(1, $this->qt->getCount(), 'setWhere and setGroup should have resulted in one');
    }
    function test_getCountGrouped0() {
        $this->_insertSampleRecords(6);
        $this->qt->setGroup('name');
        $this->qt->setWhere("name='xxx'");
        $this->assertEqual(0, $this->qt->getCount(), 'setWhere and setGroup should have resulted in one');
    }
}

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new TestOfDB_QueryTool_GetCount();
    $test->run(new HtmlReporter());
}
?>