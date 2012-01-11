<?php
//
//  $Id$
//

require_once dirname(__FILE__) . '/TestCase.php';

class tests_WhereTest extends tests_TestCase
{

    function test_setWhere()
    {
        $user = new tests_Common(TABLE_USER);
        $whereClause = 'name='.$user->db->quote('Wolfram');
        $user->setWhere($whereClause);
        $this->assertEquals($whereClause, $user->getWhere());

        $whereClause = 'name='.$user->db->quote('"test"oli');
        $user->setWhere($whereClause);
        $this->assertEquals($whereClause, $user->getWhere());

        $user = new tests_Common(TABLE_USER);
        $whereClause = 'name='.$user->db->quote('Wolfram');
        $user->setWhere($whereClause);
        $whereClause1 = 'name='.$user->db->quote('Kriesing');
        $user->addWhere($whereClause1);
        $this->assertEquals("$whereClause AND $whereClause1", $user->getWhere());

        $whereClause = 'name='.$user->db->quote('"test"oli');
        $user->setWhere($whereClause);
        $whereClause1 = 'name='.$user->db->quote('"testirt"oli');
        $user->addWhere($whereClause1, 'OR');
        $this->assertEquals("$whereClause OR $whereClause1", $user->getWhere());
    }

    function test_addWhereSearch()
    {
        $user = new tests_Common(TABLE_USER);
        $user->removeAll();
        $user->add(array('name'=>'Wolfram Kriesing'));
        $user->add(array('name'=>'WOLFRAM Daniel KrIESIng'));
        $user->add(array('name'=>' kriesing   wolfram '));
        $user->setWhere();
        $user->addWhereSearch('name', 'Wolfram Kriesing');
        $this->assertEquals(2, $user->getCount(), 'getCount(): Did not find the inserted number of user names.');

        $user->add(array('name'=>'Wolfram and here goes some string Kriesing but it should be found'));
        $user->add(array('name'=>'%Wolfram man in the middle :-) Kriesing and smthg behind%'));
        $this->assertEquals(4, $user->getCount(), 'getCount(): Did not find the inserted number of user names.');
        $user->removeAll();
    }

}

?>
