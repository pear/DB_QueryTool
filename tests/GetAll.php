<?php
//
//  $Id$
//

class tests_GetAll extends tests_UnitTest
{
    function _setup()
    {
        $this->user =& new tests_Common(TABLE_USER);
        $this->user->add(array('name' => 'some name'));
        $this->user->add(array('name' => 'some name1'));
        $this->user->add(array('name' => 'some name2'));
        $this->user->add(array('name' => 'some name3'));
    }

    function test_getAll()
    {
        $this->_setup();
        $this->assertEquals(4,sizeof($this->user->getAll()));
    }

    function test_getAllWhereSearch()
    {
        $this->_setup();
        $this->user->addWhereSearch('name', 'some');
        $this->assertEquals(4,sizeof($this->user->getAll()));
    }

    function test_getAllWhereSearch1()
    {
        $this->_setup();
        $this->user->addWhereSearch('name', '4');
        $this->assertEquals(0,sizeof($this->user->getAll()));
    }

    function test_getAllWhereSearch2()
    {
        $this->_setup();
        $this->user->addWhereSearch('name', 'some name');
        $this->assertEquals(2,sizeof($this->user->getAll(0,2)));
    }

    function test_getAllWhereSearch8()
    {
        $this->_setup();
        $this->_setup();
        $this->assertEquals(8,sizeof($this->user->getAll()));
    }

    function test_getAllWhereSearch10()
    {
        $this->_setup();
        $this->_setup();
        $this->assertEquals(8,sizeof($this->user->getAll(0,10)));
    }

    function test_getAllWhereSearch0()
    {
        $this->_setup();
        $this->_setup();
        $this->assertEquals(8,sizeof($this->user->getAll(0,0)));
    }

    function test_getAllWhereSearchEmpty()
    {
        $this->_setup();
        $this->user->addWhereSearch('name', 'some other name');
        $this->assertFalse($this->user->getAll());
    }
}

?>