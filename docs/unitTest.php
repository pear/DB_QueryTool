<?php
//
//  $Id$
//

!!!!! NOT WORKING YET !!!!

ini_set( 'include_path' , ini_get('include_path').':'.realpath('../../..') );

require_once('vp/Page/Error.php');
require_once('vp/DB/Common.php');
require_once('PHPUnit/PHPUnit.php');

$applError = new vp_Page_Error;
$dbDSN = 'mysql://root@localhost/vp_DB_Test';

if( DB::isError($db = DB::connect($dbDSN,true)) )
{
    print 'DB::connect failed!!!<br>';
    print_r($db);
}
else
    $db->setFetchMode( DB_FETCHMODE_ASSOC );

class modules_common extends vp_DB_Common
{
    function modules_common($table=null)
    {
        global $db, $applError;

        if($table != null)
            $this->table = $table;
        parent::vp_DB_Common( $db , $applError );
    }
}


class vp_DB_Test_Common extends PhpUnit_TestCase
{
    function setUp()
    {
        // make instances we need
        $address = new modules_common('address');
        $user = new modules_common('user');

        // empty tables
        $address->removeAll();
        $user->removeAll();

        // fill it with test data
        $addressIds = array();
        $newData = array(   'city'      =>  'Munich',
                            'zip'       =>  '80800',
                            'street'    =>  'Tegernseer Landstr. 7',
                            'phone'     =>  '089 27463-8748'    //
                        );
        $addressIds[] = $address->add( $newData );
        $newData['city'] = 'Berlin';
        $newData['street'] = 'Mitterer Landstr. 9';
        $addressIds[] = $address->add( $newData );

        $userIds = array();
        $newData = array(   'login'     =>  'cain',
                            'password'  =>  '0',
                            'name'      =>  'Lutz Testern',
                            'address_id'=>  $addressIds[0]
                        );
        $userIds[] = $user->add( $newData );
        $newData['address_id'] = $addressIds[1];
        $newData['login'] = 'test';
        $newData['name'] = 'Mr. Test Testokowskji';
        $userIds[] = $user->add( $newData );
    }

    function testAddGet()
    {
        $user = new modules_common('user');
        $newData = array(   'login'     =>  'cain',
                            'password'  =>  '0',
                            'name'      =>  'Lutz Testern',
                            'address_id'=>  0,
                            'company_id'=>  0
                        );
        $userId = $user->add( $newData );

        $dbData = $user->get($userId);
        foreach( $newData as $key=>$val )
            // check each key singlely since we dont know the types
            $this->assertEquals( $newData[$key] , $dbData[$key] , $test.' not equal!');

// actually i would use those lines for checking the result, but we r still discussing on the pear-dev
//        $newData['id'] = $userId;
//        $this->assertEquals( $newData , $user->get($userId) , 'Insert failed!');
    }

    function testGetMultiple1()
    {
        // insert multiple rows and read them back by their ids
        $user = new modules_common('user');
        $newData[0] = array('login'     =>  'cain',
                            'password'  =>  '0',
                            'name'      =>  'Lutz Testern',
                            'address_id'=>  0,
                            'company_id'=>  0
                        );
        $newData[1] = $newData[0];
        $newData[1]['login'] = 'tester';
        $newData[1]['name'] = 'Tester retseT';

        $userIds = array();
        $userIds[] = $user->add( $newData[0] );
        $userIds[] = $user->add( $newData[1] );

        $rows = $user->getMultiple($userIds);
        foreach( $newData as $key=>$val )
        {
            foreach( $val as $dataKey=>$x )
                $this->assertEquals( $rows[$key][$dataKey] , $newData[$key][$dataKey] , $dataKey.' not equal!');
        }
    }

    //function tearDown{}
}


$suite = new PHPUnit_TestSuite();
$suite->addTestSuite('vp_DB_Test_Common');

$result = PHPUnit::run($suite);
echo $result->toHTML();


if( $applError->existAny() )
    print 'ERRORS:<br><br>';$applError->getAll();



/*
ini_set('include_path',ini_get('include_path').':'.realpath(dirname(__FILE__).'/../../..'));

require_once('vp/DB/Common.php');

class testSimple extends vp_DB_Common
{

    var $table = 'country';

    function debug( $string )
    {
        print("$string<br>");
    }

}

require_once('DB.php');
$db = DB::connect('mysql://root@localhost/vp_test');
$db->setFetchMode( DB_FETCHMODE_ASSOC );

require_once('vp/Page/Error.php');
$error = new vp_Page_Error();

if( $which == 'simple' )
{
    $testSimple = new testSimple( $db , $error );
    //get( $id , $column='' )
    $testSimple->get(1);
    $testSimple->get(2);
    $testSimple->get(3,'name');

    //getMultiple( $ids , $column='' )
    $testSimple->getMultiple( array(1,3) );
    $testSimple->getMultiple( array(1,2,3) );
    $testSimple->getMultiple( array('Deutschland','Österreich') , 'name' );

    //getAll( $from=0 , $count=0 , $orderBy='' , $orderDesc=false )
    $testSimple->getAll();
    $testSimple->getAll(0,2);
    $testSimple->getAll(0,10);
    $testSimple->setOrder('name');
    $testSimple->getAll(0,10);
    $testSimple->setOrder('name',true);
    $testSimple->getAll(0,10);
    $testSimple->setOrder();

    //getCount()
    $testSimple->getCount();

    //getEmptyElement()
    $testSimple->getEmptyElement();

    //save( $data )
    //update( $newData )
    //add( $newData )
    $res = $testSimple->get(1);
    unset($res['id']);
    $res['phonePrefix'] = 48;
    $newId = $testSimple->save( $res );  // insert a new row, should call 'add'
    $res = $testSimple->get($newId);
    $res['name'] = 'Frankreich';
    $res['phonePrefix'] = 12;
    $testSimple->save( $res );          // this should call 'update'

    $res = array('name' => 'Norwegen' , 'phonePrefix' => 13);
    $newId = $testSimple->save( $res );  // insert a new row, should call 'add'


    //remove( $data , $whereCol='' )
    $testSimple->remove( $newId );  // remove 'Norwegen'
    $testSimple->remove( 'Frankreich' , 'name' );


    //removeMultiple( $ids , $colName='' )
    $testSimple->removeMultiple( array('Deutschland','Schweiz') , 'name' );
}
//
//  testing join and where clause
//

class testJoined extends vp_DB_Common
{

    var $table = 'country';

    function debug( $string )
    {
        print("$string<br>");
    }

}

if( $which == 'joined' )
{
    $testJoined = new testJoined( $db , $error );
    $testJoined->setJoin( array('net','netPrefix') , 'net.id=netPrefix.net_id AND net.country_id=country.id' );

    $testJoined->setOrder('name,_net_name,_netPrefix_prefix');
    $testJoined->getAll();
    $testJoined->getAll(0,2);
    $testJoined->getAll(0,10);

    $testJoined->setWhere("country.name='Deutschland'");
    $testJoined->getAll();
    $testJoined->getAll(0,2);
    $testJoined->getAll(0,10);
    $testJoined->setIndex('netPrefix.id');
    $testJoined->getAll();
    $testJoined->setIndex();

    //getMultiple( $ids , $column='' )
    $testJoined->getMultiple( array(1,3) , 'id' );
    $testJoined->getMultiple( array(1,3) , 'country.id' );
    $testJoined->getMultiple( array(1,2,3) , 'country.id' );
    $testJoined->getMultiple( array('Deutschland','Österreich') , 'country.name' );
    $testJoined->getMultiple( array('D1','D2') , 'net.name' );
    $testJoined->getMultiple( array('D1','D2') , '_net_name' );

}

print("<br><br>ERRORS<br><br>");
print_r($error->getAll());
*/
?>
