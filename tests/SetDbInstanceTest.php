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
// | Author:  Wolfram Kriesing <wolfram@kriesing.de>                      |
// +----------------------------------------------------------------------+
//
// $Id$
//

require_once dirname(__FILE__) . '/TestCase.php';

/**
* This class just checks if the query is returned, not if
* the query was properly rendered. This should be subject to
* some other tests!
*
* @package tests
*/
class tests_SetDbInstanceTest extends tests_TestCase
{
    /**
    * Check if the two instances are the same by comparing
    * the fetchMode, since this is the easiest to compare if
    * two objects are the same in PHP4.
    * We can do that since the querytool sets the fetch mode to
    * DB_FETCHMODE_ASSOC.
    * Not very nice but it works.
    *
    */
    function test_default()
    {
        $db = DB::connect(unserialize(DB_QUERYTOOL_TEST_DSN));

        $qt = new DB_QueryTool();
        $qt->setDbInstance($db);
        $dbActual = $qt->getDbInstance();
        $this->assertEquals($db->fetchmode,$dbActual->fetchmode);
    }

    /**
    * Make sure the way we did it before works too.
    * Passing the DB_DSN to the constructor should also work.
    * And retreiving the db instance should result in a sub class
    * of DB_common.
    */
    function test_oldWay()
    {
        $qt = new DB_QueryTool(unserialize(DB_QUERYTOOL_TEST_DSN));
        $db = $qt->getDbInstance();
        $this->assertTrue(is_a($db,'db_common'));
    }

}

?>
