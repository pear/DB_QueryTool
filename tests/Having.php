<?php
//
//  $Id$
//

class tests_Having extends tests_UnitTest
{
    function test_setHaving()
    {   // which company has exactly 2 workers???
        $userIds = array();
        $user = new tests_Common(TABLE_USER);
        $newData = array(   'login'     =>  'hans',
                            'password'  =>  '0',
                            'name'      =>  'Hans Dampf',
                            'address_id'=>  0,
                            'company_id'=>  1
                        );
        $userIds[] = $user->add( $newData );

        $user->reset();
        $user->setWhere('id IN ('.implode(', ', $userIds).')');
        $user->setGroup('company_id');
        $user->setHaving('count(id) = 2');

		$this->assertEquals(array(), $user->getCol('company_id')); // there are no company with 2 workers

        $newData = array(   'login'     =>  'rudi',
                            'password'  =>  '0',
                            'name'      =>  'Rudi Ratlos',
                            'address_id'=>  0,
                            'company_id'=>  1
                        );
        $userIds[] = $user->add( $newData );
        $newData = array(   'login'     =>  'susi',
                            'password'  =>  '0',
                            'name'      =>  'Susi Sorglos',
                            'address_id'=>  0,
                            'company_id'=>  5
                        );
        $userIds[] = $user->add( $newData );

        $user->reset();
        $user->setWhere('id IN ('.implode(', ', $userIds).')');
        $user->setGroup('company_id');
        $user->setHaving('count(id) = 2');

		$this->assertEquals(array(1), $user->getCol('company_id')); // company 1 has exactly 2 workers

        $newData = array(   'login'     =>  'lieschen',
                            'password'  =>  '0',
                            'name'      =>  'Lieschen Mueller',
                            'address_id'=>  0,
                            'company_id'=>  5
                        );
        $userIds[] = $user->add( $newData );

        $user->reset();
        $user->setWhere('id IN ('.implode(', ', $userIds).')');
        $user->setGroup('company_id');
        $user->setHaving('count(id) = 2');

		$this->assertEquals(array(1, 5), $user->getCol('company_id')); // company 1 and 5 has exactly 2 workers
    }

    function test_addHaving()
    {   // which companies has more than one worker one the same place and the company_id must be greater than 1
        $userIds = array();
        $user = new tests_Common(TABLE_USER);
        $newData = array(   'login'     =>  'hans',
                            'password'  =>  '0',
                            'name'      =>  'Hans Dampf',
                            'address_id'=>  1,
                            'company_id'=>  1
                        );
        $userIds[] = $user->add( $newData );

        $newData = array(   'login'     =>  'rudi',
                            'password'  =>  '0',
                            'name'      =>  'Rudi Ratlos',
                            'address_id'=>  1,
                            'company_id'=>  1
                        );
        $userIds[] = $user->add( $newData );

        $newData = array(   'login'     =>  'susi',
                            'password'  =>  '0',
                            'name'      =>  'Susi Sorglos',
                            'address_id'=>  2,
                            'company_id'=>  3
                        );
        $userIds[] = $user->add( $newData );

        $newData = array(   'login'     =>  'lieschen',
                            'password'  =>  '0',
                            'name'      =>  'Lieschen Mueller',
                            'address_id'=>  3,
                            'company_id'=>  5
                        );
        $userIds[] = $user->add( $newData );

        $newData = array(   'login'     =>  'werner',
                            'password'  =>  '0',
                            'name'      =>  'Werner Lehmann',
                            'address_id'=>  3,
                            'company_id'=>  5
                        );
        $userIds[] = $user->add( $newData );

        $user->setGroup('company_id,address_id');
        $user->setHaving('COUNT(address_id) > 1');
        $user->addHaving('company_id > 1');

		$this->assertEquals(array(5), $user->getCol('company_id')); // first test

		$user->reset();

        $user->setGroup('address_id,address_id');
        $user->addHaving('COUNT(address_id) > 1'); // this is not correct but must also work.
        $user->addHaving('company_id > 1');

		$this->assertEquals(array(5), $user->getCol('company_id')); // second test
    }

    function test_getHaving()
    {
        $user = new tests_Common(TABLE_USER);

        $having_string = 'COUNT(id) = 10';

        $user->setHaving($having_string);

        $this->assertEquals($having_string, $user->getHaving());
    }

}

?>
