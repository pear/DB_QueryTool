<?php
//
//  $Id$
//

require_once 'DB_QueryTool_UnitTest.php';
require_once 'Common.php';

class DB_QueryTool_UnitTest_Get extends DB_QueryTool_UnitTest
{
    function test_AddGet()
    {
        $user = new Common(TABLE_USER);
        $newData = array(   'login'     =>  'cain',
                            'password'  =>  '0',
                            'name'      =>  'Lutz Testern',
                            'address_id'=>  0,
                            'company_id'=>  0
                        );
        $userId = $user->add( $newData );       
        $newData['id'] = $userId;
        $this->assertEquals($newData,$user->get($userId));
    
        $newData = array(   'login'     =>  '',
                            'password'  =>  '',
                            'name'      =>  '',
                            'address_id'=>  0,
                            'company_id'=>  0
                        );
        $userId = $user->add( $newData );       
        $newData['id'] = $userId;
        $this->assertEquals($newData,$user->get($userId));
    }
}

?>
