<?php
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author:  Wolfram Kriesing, Paolo Panto, vision:produktion <wk@visionp.de>
// +----------------------------------------------------------------------+
//
// $Id$
//

ini_set('include_path',realpath(dirname(__FILE__).'/../../../').':'.realpath(dirname(__FILE__).'/../../../../includes').':'.ini_get('include_path'));
    require_once 'DB/QueryTool.php';

    
    // change this!!!!! and the DSN to your DB
    $DB_BACKEND = 'pgsql';



    define ('TABLE_TIME',   'time');
    // the mysql setup!
    if ($DB_BACKEND == 'mysql') {
        $dbDSN = 'mysql://root@localhost/test';
        define ('TABLE_USER',   'user');
    }

    // postgreSQL setup, use PEAR::DB >1.4b1
    if ($DB_BACKEND == 'pgsql') {
        $dbDSN = "pgsql://test:test@/test";
        define ('TABLE_USER',   'uuser');     // user is a reserved word in postgres
    }


    class user extends DB_QueryTool
    {
        var $table =        TABLE_USER;

        // this is default, but to demonstrate it here ...
        var $primaryCol =   'id';

        /**
        *   this table spec assigns a short name to a table name
        *   this short name is needed in case the table name changes
        *   i.e. when u put the appl on a providers db, where you have to prefix
        *   each table, and you dont need to change the entire appl to where you refer
        *   to joined table columns, for that joined results the short name is used
        *   instead of the table name
        */
        var $tableSpec = array(
                                array('name'    =>  TABLE_USER, 'shortName' =>  'user')
                                ,array('name'   =>  TABLE_TIME, 'shortName' =>  'time')
                            );


    }



    // this set an error-callback method, which will be called in case something really went wrong
    // you can use 'setErrorLogCallback' to define the callback method for log-messages - non critical.
    // and you can use 'setErrorSetCallback' to define a callback for errors which prevent the query
    // from properly executing
    $user = new user( $dbDSN , array('errorCallback'=>'myPrint') );
    //$user->setErrorCallback('myPrint'); this could be used too


    //
    //      1
    //
    // get a single entry with a given 'id'
    headline('$user->get(3)');
    $user->reset();     // reset the query-builder, so no where, order, etc. are set
    $res = $user->get(3);
    myPrint($res);
    // query: SELECT * FROM user WHERE id=3


    //
    //      2
    //
    // get all entries from the table
    headline('$user->getAll()');
    $user->reset();     // reset the query-builder, so no where, order, etc. are set
    $res = $user->getAll();
    myPrint($res);
    // query: SELECT * FROM user


    //
    //      3
    //
    // get the first 10 entries from the table (LIMIT 0,10)
    headline('$user->getAll(0,10)');
    $user->reset();     // reset the query-builder, so no where, order, etc. are set
    $res = $user->getAll(0,10);
    myPrint($res);
    // query: SELECT * FROM user LIMIT 0,10


    //
    //      4
    //
    // get all data where the id>3
    headline('using setWhere');
    $user->reset();     // reset the query-builder, so no where, order, etc. are set
    $user->setWhere('id>3');
    $res = $user->getAll();
    myPrint($res);
    // query: SELECT * FROM user WHERE id>3


    //
    //      5
    //
    // setting multiple query-parts
    headline('using set[Where,Order,Group]');
    $user->reset();     // reset the query-builder, so no where, order, etc. are set
    // for proper SQL92 i think we need to select only the col we use in group
    // in mysql you can also leave out the following line
    $user->setSelect('name');
    $user->setWhere('name IS NOT NULL');
    $user->setOrder('name');
    $user->setGroup('name');
    $res = $user->getAll();
    myPrint($res);
    // query: SELECT * FROM user WHERE name IS NOT NULL GROUP BY name ORDER BY name


    //
    //      6
    //
    // setting multiple query-parts
    headline('using setIndex');
    $user->reset();     // reset the query-builder, so no where, order, etc. are set
    $user->setIndex('name');
    $res = $user->getAll();
    myPrint($res);
    // query: SELECT * FROM user
    // the result-array is indexed by 'name'



    //
    //      7
    //
    // setting multiple query-parts
    headline('using setIndex using 2 columns!');
    $user->reset();     // reset the query-builder, so no where, order, etc. are set
    $user->setIndex('name,surname');
    $res = $user->getAll();
    myPrint($res);
    // query: SELECT * FROM user
    // the result-array is indexed by 'name,surname'



    //
    //      8
    //
    // join the table 'time' this automatically detects where there are
    // columns that refer to one another, this uses a regExp that you can simply
    // modify (see $_tableNamePreg and $_columnNamePreg), by default this maps
    // stuff like 'user_id'=>'user.id'
    headline('using autoJoin');
    $user->reset();     // reset the query-builder, so no where, order, etc. are set
    $user->autoJoin('time');
    $user->setOrder('surname');
    $res = $user->getAll();
    myPrint($res);
    // query: SELECT * FROM user,time WHERE user.id=time.user_id ORDER BY surname


    //
    //      9
    //
    // does the same as the example above
    // only that you have to hardcode the join by hand, no autoJoin here
    headline('using setJoin instead of autoJoin');
    $user->reset();     // reset the query-builder, so no where, order, etc. are set
    $user->setJoin('time',TABLE_USER.'.id=time.user_id');
    $user->setOrder('surname');
    $res = $user->getAll();
    myPrint($res);
    // query: SELECT * FROM user,time WHERE user.id=time.user_id ORDER BY surname


    //
    //      10
    //
    headline('adding data using $user->save($data)');
    $data = array('login'=>'new','name'=>'foo','surname'=>'bar');
    $fooBarId = $user->save($data);
    myPrint($fooBarId);
    // query: INSERT INTO user (id,login,name,surname) VALUES (<sequences>,"new","foo","bar")


    //
    //      11
    //
    headline('updating using $user->save($data)');
    $data = array('id'=>$fooBarId,'login'=>'NEW','name'=>'Mr. foo');
    $res = $user->save($data);
    myPrint($res);
    // query: UPDATE user (id,login,name,surname) VALUES (<sequences>,"new","foo","bar")


    //
    //      12
    //
    headline('updating using $user->update($data)');
    $res = $user->update($data);
    myPrint($res);
    // query: UPDATE user (id,login,name,surname) VALUES (<sequences>,"new","foo","bar")


    //
    //      13
    //
    headline("remove the entry \$user->remove($fooBarId)");
    $res = $user->remove($fooBarId);
    myPrint($res);
    // query: DELETE FROM "uuser" WHERE "id" = $fooBarId





    //
    //  helper functions
    //
    function headline( $string='' )
    {
        global $headlineCnt;

        if( $string )
        {
            $headlineCnt++;
            print "<h1>$headlineCnt - $string</h1>";
        }
    }
    function myPrint($data)
    {
        print "<pre>";
        print_r($data);
        print '</pre>';
    }

?>
