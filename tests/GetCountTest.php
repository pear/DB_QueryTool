<?php
//
//  $Id$
//

require_once dirname(__FILE__) . '/TestCase.php';

class tests_GetCountTest extends tests_TestCase
{
    function _setup($mode)
    {
        $this->user = new tests_Common(TABLE_USER);
        switch ($mode) {
            case '6':
                $this->user->add(array('name'=>'x'));
                $this->user->add(array('name'=>'y'));
                $this->user->add(array('name'=>'z'));
            case '3':
                $this->user->add(array('name'=>'x'));
                $this->user->add(array('name'=>'y'));
                $this->user->add(array('name'=>'z'));
                break;
        }
    }

    function test_getCount3()
    {
        $this->_setup(3);
        $this->assertEquals(3,$this->user->getCount(),'Wrong count after inserting 3 rows');
    }

    function test_getCount6()
    {
        $this->_setup(6);
        $this->assertEquals(6,$this->user->getCount(),'Wrong count after inserting 6 rows');
    }

    function test_getCountGrouped3()
    {
        $this->_setup(6);
        $this->user->setGroup('name');
        $this->assertEquals(3,$this->user->getCount(),'Wrong count after 6 inserted and grouping them by name');
    }

    function test_getCountGrouped2()
    {
        $this->_setup(6);
        $this->user->setWhere("name='z'");
        $this->assertEquals(2,$this->user->getCount(),'setWhere and setGroup should have resulted in two');
    }

    function test_getCountGrouped1()
    {
        $this->_setup(6);
        $this->user->setGroup('name');
        $this->user->setWhere("name='z'");
        $this->assertEquals(1,$this->user->getCount(),'setWhere and setGroup should have resulted in one');
    }

    function test_getCountGrouped0()
    {
        $this->_setup(6);
        $this->user->setGroup('name');
        $this->user->setWhere("name='xxx'");
        $this->assertEquals(0,$this->user->getCount(),'setWhere and setGroup should have resulted in one');
    }

    function test_getCountWithOffset()
    {
        $this->_setup(6);
        $this->user->setLimit(0, 5);
        $this->assertEquals(6, $this->user->getCount(),'setLimit and setGroup should have resulted in one');

        $this->user->setLimit(5, 5);
        $this->assertEquals(6, $this->user->getCount(),'setLimit and setGroup should have resulted in one');
    }

}

?>
