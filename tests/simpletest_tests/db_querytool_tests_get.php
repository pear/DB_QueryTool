<?php
// $Id$

require_once 'simple_include.php';
require_once 'db_querytool_include.php';

class DB_QueryToolTests_Get extends GroupTest {
    function DB_QueryToolTests_Get() {
        $this->GroupTest('DB_QueryTool Get Tests');
        $this->addTestFile('db_querytool_testGet.php');
        $this->addTestFile('db_querytool_testGetAll.php');
        $this->addTestFile('db_querytool_testGetCount.php');
        $this->addTestFile('db_querytool_testGetQueryString.php');
    }
}

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new DB_QueryToolTests_Get();
    $test->run(new HtmlReporter());
}
?>