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

/**
* This class just checks if the query is returned, not if
* the query was properly rendered. This should be subject to
* some other tests!
*
* @package tests
*/
class tests_GetQueryString extends tests_UnitTest
{
    function _setup()
    {
        $this->question =& new tests_Common(TABLE_QUESTION);
    }

    function test_selectAll()
    {
        $this->_setup();
        $this->assertStringEquals(
                            'SELECT question.id AS "id",question.question AS "question" FROM question'
                            ,$this->question->getQueryString());
    }

    function test_selectWithWhere()
    {
        $this->_setup();
        $this->question->setWhere('id=1');
        $this->assertStringEquals(
                            'SELECT question.id AS "id",question.question AS "question" FROM question'.
                            ' WHERE id=1'
                            ,$this->question->getQueryString());
    }
}

?>
