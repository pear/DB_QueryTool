<?php
//
//  $Id$
//

require_once 'DB_QueryTool_UnitTest.php';
require_once 'Common.php';

class DB_QueryTool_UnitTest_Limit extends DB_QueryTool_UnitTest
{
    // test if setLimit works
    function test_setLimit()
    {
        $user = new Common(TABLE_USER);
        $user->setLimit(0,10);
        $this->assertEquals(array(0,10),$user->getLimit());
    }

    // test if setLimit works
    function test_setLimit1()
    {
        $user = new Common(TABLE_USER);
        
        $user->add(array('login'=>1));
        $user->add(array('login'=>2));
        $user->add(array('login'=>3));
        $user->add(array('login'=>4));
        
        $user->setLimit(0,2);
        $this->assertEquals(2,sizeof($user->getAll()));
    
        $user->setLimit(0,3);
        $this->assertEquals(3,sizeof($user->getAll()));
    }

    // test if getAll works
    // setLimit should have no effect when parameters are given to getAll()
    function test_getAll()
    {
        $user = new Common(TABLE_USER);
        $user->setLimit(0,10);
        $user->add(array('login'=>1));
        $user->add(array('login'=>2));
        $user->add(array('login'=>3));
        $user->add(array('login'=>4));
        $this->assertEquals(1,sizeof($user->getAll(0,1)));
        $user->setLimit(0,3);
        $this->assertEquals(2,sizeof($user->getAll(0,2)));
        
        $this->assertEquals(3,sizeof($user->getAll()));
    }

    // test if getAll works
    // setLimit should have no effect when parameters are given to getAll()
    function test_getCol()
    {
        $user = new Common(TABLE_USER);
        $user->setLimit(0,10);
        $user->add(array('login'=>1));
        $user->add(array('login'=>2));
        $user->add(array('login'=>3));
        $user->add(array('login'=>4));
        $this->assertEquals(1,sizeof($user->getCol('id',0,1)));
        $user->setLimit(0,3);
        $this->assertEquals(2,sizeof($user->getCol('id',0,2)));
        
        $this->assertEquals(3,sizeof($user->getCol('id')));
    }
}

?>
