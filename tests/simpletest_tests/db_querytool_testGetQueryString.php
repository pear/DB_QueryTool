<?php
// $Id$

require_once dirname(__FILE__).'/db_querytool_test_base.php';


class TestOfDB_QueryTool_GetQueryString extends TestOfDB_QueryTool
{
    function TestOfDB_QueryTool_GetQueryString($name = __CLASS__) {
        $this->UnitTestCase($name);
    }
    function test_selectAll() {
        $this->qt =& new DB_QT(TABLE_QUESTION);
        if (DB_TYPE == 'ibase') {
            $expected = 'SELECT question.id AS id,question.question AS question FROM question';
        } else {
            $expected = 'SELECT question.id AS "id",question.question AS "question" FROM question';
        }
        $this->assertEqual($expected, $this->qt->getQueryString());
    }
    function test_selectWithWhere() {
        $this->qt =& new DB_QT(TABLE_QUESTION);
        $this->qt->setWhere('id=1');
        if (DB_TYPE == 'ibase') {
            $expected = 'SELECT question.id AS id,question.question AS question FROM question WHERE id=1';
        } else {
            $expected = 'SELECT question.id AS "id",question.question AS "question" FROM question WHERE id=1';
        }
        $this->assertEqual($expected, $this->qt->getQueryString());
    }
}

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new TestOfDB_QueryTool_GetQueryString();
    $test->run(new HtmlReporter());
}
?>