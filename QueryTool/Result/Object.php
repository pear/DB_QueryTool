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
// | Author:  Roman Dostovalov, Com-tec-so S.A.<roman.dostovalov@ctco.lv> |
// +----------------------------------------------------------------------+
//
// $Id$
//

/**
 * Include parent class
 */
require_once 'DB/QueryTool/Result.php';

// ------------------------------------------------------------------------

/**
 *	Result row class
 */
class DB_QueryTool_Result_Row
{
	/**
	 * create object properties form array
	 * @param $arr
	 */
	function DB_QueryTool_Result_Row($arr)
	{
        foreach ($arr as $key => $value) {
		    $this->$key = $value;
        }
	}
}

// ------------------------------------------------------------------------

/**
 * @package    DB_QueryTool
 * @access     public
 * @author     Roman Dostovalov <roman.dostovalov@ctco.lv>
 */
class DB_QueryTool_Result_Object extends DB_QueryTool_Result
{
    // {{{ fetchRow

	/**
	 * This function emulates PEAR_DB function FetchRow
	 * With this function DB_QueryTool can transparently replace PEAR_DB
	 *
	 * @todo implement fetchmode support?
	 * @access    public
	 * @return    void
	 */
	function fetchRow()
	{
		$arr = $this->getNext();
		if (!PEAR::isError($arr)) {
		    $row = new DB_QueryTool_Result_Row($arr);
			return $row;
		}
		return false;
	}

	// }}}
}
?>