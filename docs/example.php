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

    $dbDSN = 'mysql://root@localhost/test';


//ini_set('include_path',realpath(dirname(__FILE__).'/../../../').':'.ini_get('include_path'));
    require_once 'DB/QueryTool.php';
    

    class user extends DB_QueryTool
    {
        var $table =        'user';
        
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
                                array('name'    =>  'user', 'shortName' =>  'user')
                                ,array('name'   =>  'time', 'shortName' =>  'time')
                            );


    }                   
    
    

    $user = new user( $dbDSN );
    // this set an error-callback method, which will be called in case something really went wrong
    // you can use 'setErrorLogCallback' to define the callback method for log-messages - non critical.
    // and you can use 'setErrorSetCallback' to define a callback for errors which prevent the query
    // from properly executing
    $user->setErrorCallback('myPrint');
             
    
    //
    //      1
    //
    // get a single entry with a given 'id'
    $user->reset();     // reset the query-builder, so no where, order, etc. are set
    $res = $user->get(3);
    myPrint($res,'$user->get(3)');
    // query: SELECT * FROM user WHERE id=3


    //
    //      2
    //
    // get all entries from the table
    $user->reset();     // reset the query-builder, so no where, order, etc. are set
    $res = $user->getAll();
    myPrint($res,'$user->getAll()');
    // query: SELECT * FROM user


    //
    //      3
    //
    // get the first 10 entries from the table (LIMIT 0,10)
    $user->reset();     // reset the query-builder, so no where, order, etc. are set
    $res = $user->getAll(0,10);
    myPrint($res,'$user->getAll(0,10)');
    // query: SELECT * FROM user LIMIT 0,10


    //
    //      4
    //
    // get all data where the id>3
    $user->reset();     // reset the query-builder, so no where, order, etc. are set
    $user->setWhere('id>3');
    $res = $user->getAll();
    myPrint($res,'using setWhere');
    // query: SELECT * FROM user WHERE id>3


    //
    //      5
    //
    // setting multiple query-parts
    $user->reset();     // reset the query-builder, so no where, order, etc. are set
    $user->setWhere('name IS NOT NULL');
    $user->setOrder('name');
    $user->setGroup('name');
    $res = $user->getAll();
    myPrint($res,'using set[Where,Order,Group]');
    // query: SELECT * FROM user WHERE name IS NOT NULL GROUP BY name ORDER BY name


    //
    //      6
    //
    // setting multiple query-parts
    $user->reset();     // reset the query-builder, so no where, order, etc. are set
    $user->setIndex('name');
    $res = $user->getAll();
    myPrint($res,'using setIndex');
    // query: SELECT * FROM user
    // the result-array is indexed by 'name'



    //
    //      7
    //
    // setting multiple query-parts
    $user->reset();     // reset the query-builder, so no where, order, etc. are set
    $user->setIndex('name,surname');
    $res = $user->getAll();
    myPrint($res,'using setIndex using 2 columns!');
    // query: SELECT * FROM user
    // the result-array is indexed by 'name,surname'



    //
    //      8
    //
    // join the table 'time' this automatically detects where there are
    // columns that refer to one another, this uses a regExp that you can simply
    // modify (see $_tableNamePreg and $_columnNamePreg), by default this maps
    // stuff like 'user_id'=>'user.id'
    $user->reset();     // reset the query-builder, so no where, order, etc. are set
    $user->autoJoin('time');
    $user->setOrder('surname');
    $res = $user->getAll();
    myPrint($res,'using autoJoin');
    // query: SELECT * FROM user,time WHERE user.id=time.user_id ORDER BY surname


    //
    //      9
    //
    // does the same as the example above
    // only that you have to hardcode the join by hand, no autoJoin here
    $user->reset();     // reset the query-builder, so no where, order, etc. are set
    $user->setJoin('time','user.id=time.user_id');
    $user->setOrder('surname');
    $res = $user->getAll();
    myPrint($res,'using setJoin instead of autoJoin');
    // query: SELECT * FROM user,time WHERE user.id=time.user_id ORDER BY surname


    function myPrint( $data , $string='' )
    {
        global $headlineCnt;

        if( $string )
        {
            $headlineCnt++;
            print "<h1>$headlineCnt - $string</h1>";
        }
        print "<pre>";
        print_r($data);
        print '</pre>';
    }

?>