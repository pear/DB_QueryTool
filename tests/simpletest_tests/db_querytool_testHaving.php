<?php
// $Id$

require_once dirname(__FILE__).'/db_querytool_test_base.php';

class TestOfDB_QueryTool_Having extends TestOfDB_QueryTool
{
    function TestOfDB_QueryTool_Having($name = __CLASS__) {
        $this->UnitTestCase($name);
    }
    function test_getHaving() {
        $this->qt =& new DB_QT(TABLE_USER);
        $having_string = 'count(id) = 10';
        $this->qt->setHaving($having_string);
        $this->assertEqual($having_string, $this->qt->getHaving());
    }
    function test_setHaving() {
        // which company has exactly 2 workers???
        $userIds = array();
        $this->qt =& new DB_QT(TABLE_USER);

        $newData = array(
            'login'       => 'hans',
            'qt_password' => '0',
            'name'        => 'Hans Dampf',
            'address_id'  => 0,
            'company_id'  => 1
        );
        $userIds[] = $this->qt->add($newData);

        $this->qt->reset();
        $this->qt->setWhere('id IN ('.implode(', ', $userIds).')');
        $this->qt->setGroup('company_id');
        $this->qt->setHaving('COUNT(id) = 2');

        // there are no company with 2 workers
        $this->assertEqual(array(), $this->qt->getCol('company_id')); 

        $newData = array(
            'login'       => 'rudi',
            'qt_password' => '0',
            'name'        => 'Rudi Ratlos',
            'address_id'  => 0,
            'company_id'  => 1
        );
        $userIds[] = $this->qt->add($newData);
        $newData = array(
            'login'       => 'susi',
            'qt_password' => '0',
            'name'        => 'Susi Sorglos',
            'address_id'  => 0,
            'company_id'  => 5
        );
        $userIds[] = $this->qt->add($newData);

        $this->qt->reset();
        $this->qt->setWhere('id IN ('.implode(', ', $userIds).')');
        $this->qt->setGroup('company_id');
        $this->qt->setHaving('count(id) = 2');

        // company 1 has exactly 2 workers
        $this->assertEqual(array(1), $this->qt->getCol('company_id')); 

        $newData = array(
            'login'       => 'lieschen',
            'qt_password' => '0',
            'name'        => 'Lieschen Mueller',
            'address_id'  => 0,
            'company_id'  => 5
        );
        $userIds[] = $this->qt->add($newData);

        $this->qt->reset();
        $this->qt->setWhere('id IN ('.implode(', ', $userIds).')');
        $this->qt->setOrder('company_id');
        $this->qt->setGroup('company_id');
        $this->qt->setHaving('count(id) = 2');

        // company 1 and 5 has exactly 2 workers
        $this->assertEqual(array(1, 5), $this->qt->getCol('company_id')); 
    }

    function test_addHaving() {
        // which companies has more than one worker on the same place
        // and the company_id must be greater than 1
        $userIds = array();
        $this->qt =& new DB_QT(TABLE_USER);
        $newData = array(
            'login'       => 'hans',
            'qt_password' => '0',
            'name'        => 'Hans Dampf',
            'address_id'  => 1,
            'company_id'  => 1
        );
        $userIds[] = $this->qt->add($newData);

        $newData = array(
            'login'       => 'rudi',
            'qt_password' => '0',
            'name'        => 'Rudi Ratlos',
            'address_id'  => 1,
            'company_id'  => 1
        );
        $userIds[] = $this->qt->add($newData);

        $newData = array(
            'login'       => 'susi',
            'qt_password' => '0',
            'name'        => 'Susi Sorglos',
            'address_id'  => 2,
            'company_id'  => 3
        );
        $userIds[] = $this->qt->add($newData);

        $newData = array(
            'login'       => 'lieschen',
            'qt_password' => '0',
            'name'        => 'Lieschen Mueller',
            'address_id'  => 3,
            'company_id'  => 5
        );
        $userIds[] = $this->qt->add($newData);

        $newData = array(
            'login'       => 'werner',
            'qt_password' => '0',
            'name'        => 'Werner Lehmann',
            'address_id'  => 3,
            'company_id'  => 5
        );
        $userIds[] = $this->qt->add($newData);
        


        $this->qt->setGroup('company_id,address_id');
        $this->qt->setHaving('count(address_id) > 1');
        $this->qt->addHaving('company_id > 1');

        // first test
        $this->assertEqual(array(5), $this->qt->getCol('company_id')); 

		$this->qt->reset();

        $this->qt->setGroup('company_id,address_id');
        $this->qt->addHaving('count(address_id) > 1'); // this is not correct but must also work.
        $this->qt->addHaving('company_id > 1');

        // second test
        $this->assertEqual(array(5), $this->qt->getCol('company_id')); 
    }
}

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new TestOfDB_QueryTool_Having();
    $test->run(new HtmlReporter());
}
?>