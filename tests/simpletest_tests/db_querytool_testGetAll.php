<?php
// $Id$

require_once dirname(__FILE__).'/db_querytool_test_base.php';

class TestOfDB_QueryTool_GetAll extends TestOfDB_QueryTool
{
    function TestOfDB_QueryTool_GetAll($name = __CLASS__) {
        $this->UnitTestCase($name);
    }
    function _insertSampleRecords() {
        $this->qt =& new DB_QT(TABLE_USER);
        $newData = $this->_getSampleData(1);
        
        $newData['name'] = 'some name';
        $this->qt->add($newData);
        $newData['name'] = 'some name1';
        $this->qt->add($newData);
        $newData['name'] = 'some name2';
        $this->qt->add($newData);
        $newData['name'] = 'some name3';
        $this->qt->add($newData);
    }
    function test_getAll() {
        $this->_insertSampleRecords();
        $this->assertEqual(4, sizeof($this->qt->getAll()));
    }

    function test_getAllWhereSearch() {
        $this->_insertSampleRecords();
        $this->qt->addWhereSearch('name', 'some');
        $this->assertEqual(4, sizeof($this->qt->getAll()));
    }

    function test_getAllWhereSearch1() {
        $this->_insertSampleRecords();
        $this->qt->addWhereSearch('name', '4');
        $this->assertEqual(0, sizeof($this->qt->getAll()));
    }

    function test_getAllWhereSearch2() {
        $this->_insertSampleRecords();
        $this->qt->addWhereSearch('name', 'some name');
        $this->assertEqual(2, sizeof($this->qt->getAll(0, 2)));
    }

    function test_getAllWhereSearch8() {
        $this->_insertSampleRecords();
        $this->_insertSampleRecords();
        $this->assertEqual(8, sizeof($this->qt->getAll()));
    }

    function test_getAllWhereSearch10() {
        $this->_insertSampleRecords();
        $this->_insertSampleRecords();
        $this->assertEqual(8, sizeof($this->qt->getAll(0, 10)));
    }

    function test_getAllWhereSearch0() {
        $this->_insertSampleRecords();
        $this->_insertSampleRecords();
        $this->assertEqual(8, sizeof($this->qt->getAll(0, 0)));
    }
}

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new TestOfDB_QueryTool_GetAll();
    $test->run(new HtmlReporter());
}
?>