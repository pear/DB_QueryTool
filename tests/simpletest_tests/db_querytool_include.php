<?php
// $Id$

require_once 'DB/QueryTool.php';
require_once dirname(__FILE__).'/db_settings.php';

class DB_QT extends DB_QueryTool
{
    var $tableSpec = array(
        array(
            'name'      => TABLE_QUESTION,
            'shortName' => TABLE_QUESTION
        ),
        array(
            'name'      => TABLE_ANSWER,
            'shortName' => TABLE_ANSWER
        ),
    );

    function DB_QT($table = null)
    {
        if (!is_null($table)) {
            $this->table = $table;
        }
        parent::DB_QueryTool(DB_DSN, $GLOBALS['DB_OPTIONS']);
        $this->setErrorSetCallback(array(&$this,'errorSet'));
        $this->setErrorLogCallback(array(&$this,'errorLog'));
    }

    //
    //  just for the error handling
    //

    function errorSet($msg)
    {
        $GLOBALS['_Common_Errors'][] = array('set', $msg);
    }

    function errorLog($msg)
    {
        $GLOBALS['_Common_Errors'][] = array('log', $msg);
    }

    function getErrors()
    {
        return $GLOBALS['_Common_Errors'];
    }
}
?>