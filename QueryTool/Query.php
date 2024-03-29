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

require_once 'DB.php';


/**
*   this class should be extended
*
*   @package    DB_QueryTool
*   @version    2002/04/02
*   @access     public
*   @author     Wolfram Kriesing <wk@visionp.de>
*/
class DB_QueryTool_Query
{

    /**
    *   var string  the name of the primary column
    */
    var $primaryCol = 'id';

    /**
    *   var string  the current table the class works on
    */
    var $table      = '';

    /**
    *   var object  the db-object, a PEAR::Db-object instance
    */
    var $_db = null;

    /**
    *   var string  the where condition
    */
    var $_where = '';

    /**
    *   var string  the order condition
    */
    var $_order = '';

    /**
    *   var array   contains the join content
    *               the key is the join type, for now we have 'default' and 'left'
    *               inside each key 'table' contains the table
    *                           key 'where' contains the where clause for the join
    */
    var $_join = array();

    /**
    *   @var    string  which column to index the result by
    */
    var $_index = null;

    /**
    *   @var    string  the group-by clause
    */
    var $_group = '';

    /**
    *   @var    boolean     if to use the vp_DB_Result as a result or not
    */
    var $_useResult = false;

    /**
    *   @var    array       the metadata temporary saved
    */
    var $_metadata = array();

    var $_lastQuery = null;

    /**
    *   the rows that shall be selected
    */
    var $_select = '*';
    
    var $_dontSelect = '';

    /**
    *   array   this array saves different modes in which this class works
    *           i.e. 'raw' means no quoting before saving/updating data
    *   @access private
    */
    var $options = array(   'raw'       =>  false,
                            'verbose'   =>  true        // set this to false in a productive environment
                                                        // it will produce error-logs if set to true
                        );

    /**
    *   this array contains information about the tables
    *   those are
    *           'name' -        the real table name
    *           'shortName' -   the short name used, so that when moving the table i.e.
    *                           onto a provider's db and u have to rename the tables to longer names
    *                           this name will be relevant, i.e. when autoJoining, i.e. a table name
    *                           on your local machine is: 'user' but online it has to be 'applName_user'
    *                           then the shortName will be used to determine if a column refers to another
    *                           table, if the colName is 'user_id', it knows the shortName 'user' refers to the table
    *                           'applName_user'
    */
    var $tableSpec = array();

    /**
    *   this is the regular expression that shall be used to find a table's shortName
    *   in a column name, the string found by using this regular expression will be removed
    *   from the column name and it will be checked if it is a table name
    *   i.e. the default '/_id$/' would find the table name 'user' from the column name 'user_id'
    */
    var $_tableNameToShortNamePreg = '/^.*_/';






    /**
    *   this is the constructor, as it will be implemented in ZE2 (php5)
    *
    *   @version    2002/04/02
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      object  db-object
    */
    function __construct( $dsn )
    {
        $this->_db = DB::connect($dsn);
        $this->_db->setFetchMode(DB_FETCHMODE_ASSOC);

        // oracle has all column names in upper case
        if( $this->_db->phptype=='oci8' )
            $this->primaryCol = 'ID';
    }

    /**
    *
    *
    *   @version    2002/04/02
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      object  db-object
    */
    function DB_QueryTool_Query( $dsn )
    {
        $this->__construct( $dsn );
    }

    /**
    *   get the data of a single entry         
    *   if the second parameter is only one column the result will be returned
    *   directly not as an array!
    *
    *   @version    2002/03/05
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      integer the id of the element to retreive
    *   @param      string  if this is given only one row shall be returned, directly, not an array
    *   @return     mixed   (1) an array of the retreived data
    *                       (2) if the second parameter is given and its only one column, only this column's data will be returned
    *                       (3) false in case of failure
    */
    function get( $id , $column='' )
    {
        $table = $this->table;
        $getMethod = 'getRow';
        if( $column && !strpos($column,',') )    // if only one column shall be selected
            $getMethod = 'getOne';

        // we dont use 'setSelect' here, since this changes the setup of the class, we
        // build the query directly
        // if $column is '' then _buildSelect selects '*' anyway, so that's the same behaviour as before
        $query['select'] = $this->_buildSelect( $column );
        $query['where'] = $this->_buildWhere($this->table.'.'.$this->primaryCol.'='.$id);
        $queryString = $this->_buildSelectQuery( $query );

        return $this->returnResult( $this->execute($queryString,$getMethod) );
    }

    /**
    *   gets the data of the given ids
    *
    *   @version    2002/04/23
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      array   this is an array of ids to retreive
    *   @param      string  the column to search in for
    *   @return     mixed   an array of the retreived data, or false in case of failure
    *                       when failing an error is set in $this->_error
    */
    function getMultiple( $ids , $column='' )
    {
        $col = $this->primaryCol;
        if( $column )
            $col = $column;

// FIXXME if $ids has no table.col syntax and we are using joins, the table better be put in front!!!
        $ids = $this->_quoteArray($ids);

        $query['where'] = $this->_buildWhere($col.' IN ('.implode(',',$ids).')');
        $queryString = $this->_buildSelectQuery( $query );

        return $this->returnResult( $this->execute($queryString) );
    }

    /**
    *   get all entries from the DB
    *   for sorting use setOrder!!!, the last 2 parameters are deprecated
    *
    *   @version    2002/03/05
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      int     to start from
    *   @param      int     the number of rows to show
    *   @return     mixed   an array of the retreived data, or false in case of failure
    *                       when failing an error is set in $this->_error
    */
    function getAll( $from=0 , $count=0  )
    {
        //$this->setSelect('*');
        $queryString = $this->_buildSelectQuery();

// FIXXME, one day this should be unified!!!
        if( $this->_db->phptype=='oci8' )
        {
            if( $from && $count )
            {
                if( DB::isError( $queryString = $this->_db->modifyLimitQuery($queryString,$from-1,$count-1)) )
                {
//print_r($queryString);
                    $this->_errorSet( 'vp_DB_Common::getAll modifyLimitQuery failed '.$queryString->getMessage() );
                    $this->_errorLog( $queryString->getUserInfo() );
                    return false;
                }
            }
        }
        else
        {
            if( $count )
            {
                if( DB::isError( $queryString = $this->_db->modifyLimitQuery($queryString,$from,$count)) )
                {
                    $this->_errorSet( 'vp_DB_Common::getAll modifyLimitQuery failed '.$queryString->getMessage() );
                    $this->_errorLog( $queryString->getUserInfo() );
                    return false;
                }
            }
        }

        return $this->returnResult( $this->execute($queryString) );
    }

    /**
    *   get the number of entries
    *
    *   @version    2002/04/02
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param
    *   @return     mixed   an array of the retreived data, or false in case of failure
    *                       when failing an error is set in $this->_error
    */
    function getCount()
    {
/* the following query works on mysql
SELECT count(DISTINCT image.id) FROM image2tree
RIGHT JOIN image ON image.id = image2tree.image_id
the reason why this is needed - i jsut wanted to get the number of rows that do exist if the result is grouped by image.id
the following query is what i tried first, but that returns the number of rows that have been grouped together
for each image.id
SELECT count(*) FROM image2tree
RIGHT JOIN image ON image.id = image2tree.image_id GROUP BY image.id

so that's why we do the following, i am not sure if that is standard SQL and absolutley correct!!!
*/

//FIXXME see comment above if this is absolutely correct!!!
        if( $group = $this->getGroup() )
        {
            $query['select'] = 'count(DISTINCT '.$group.')';
            $query['group'] = '';
        }
        else
            $query['select'] = 'count(*)';

        $query['order'] = '';   // order is not of importance and might freak up the special group-handling up there, since the order-col is not be known
/*# FIXXME use the following line, but watch out, then it has to be used in every method, or this
# value will be used always, simply try calling getCount and getAll afterwards, getAll will return the count :-)
# if getAll doenst use setSelect!!!
*/
        //$this->setSelect('count(*)');
        $queryString = $this->_buildSelectQuery( $query );

        return ($res=$this->execute($queryString,'getOne')) ? $res : 0;
    }

    /**
    *   return an empty element where all the array elements do already exist
    *   corresponding to the columns in the DB
    *
    *   @version    2002/04/05
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @return     array   an empty, or pre-initialized element
    */
    function getDefaultValues()
    {
        $ret = array();
        // here we read all the columns from the DB and initialize them
        // with '' to prevent PHP-warnings in case we use error_reporting=E_ALL
        foreach( $this->metadata() as $aCol=>$x )
        {
            $ret[$aCol] = '';
        }
        return $ret;
    }                        
    
    /**
    *   this is just for BC
    */
    function getEmptyElement()
    {
        $this->getDefaultValues();
    }

    /**
    *   save data, calls either update or add
    *   if the primaryCol is given in the data this method knows that the
    *   data passed to it are meant to be updated (call 'update'), otherwise it will
    *   call the method 'add'. 
    *   If you dont like this behaviour simply stick with the methods 'add'
    *   and 'update' and ignore this one here.
    *   This method is very useful when you have validation checks that have to
    *   be done for both adding and updating, then you can simply overwrite this
    *   method and do the checks in here, and both cases will be validated first.
    *
    *   @version    2002/03/11
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      array   contains the new data that shall be saved in the DB
    *   @return     mixed   the data returned by either add or update-method
    */
    function save( $data )
    {
        if( isset($data[$this->primaryCol]) && $data[$this->primaryCol] )
            return $this->update( $data );
        return $this->add( $data );
    }

    /**
    *   update the member data of a data set
    *
    *   @version    2002/03/06
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      array   contains the new data that shall be saved in the DB
    *                       the id has to be given in the field with the key 'ID'
    *   @return     mixed   true on success, or false otherwise
    */
    function update( $newData )
    {
        if( !isset($newData[$this->primaryCol]) )
        {
            $this->_errorSet('Error updating the new member.');
            return false;
        }
        $id = $newData[$this->primaryCol];
        unset($newData[$this->primaryCol]);

        $newData = $this->_checkColumns($newData,'update');

        $values = array();                        
        $raw = $this->getOption('raw');
        foreach( $newData as $key=>$aData )         // quote the data
            $values[] = "$key=". ( $raw ? $aData : $this->_db->quote($aData) );

        $query = sprintf(   'UPDATE %s SET %s WHERE %s=%s',
                            $this->table,
                            implode(',',$values),
                            $this->primaryCol,$id
                        );
        return $this->execute($query,'query') ? true : false;
    }

    /**
    *   add a new member in the DB
    *
    *   @version    2002/04/02
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      array   contains the new data that shall be saved in the DB
    *   @return     mixed   the inserted id on success, or false otherwise
    */
    function add( $newData )
    {
        unset($newData[$this->primaryCol]);

        $newData = $this->_checkColumns($newData,'add');
        $newData = $this->_quoteArray( $newData );

        if( $this->primaryCol )                     // do only use the sequence if a primary column is given
        {                                           // otherwise the data are written as given
            $id = $this->_db->nextId( $this->table );
            $newData[$this->primaryCol] = $this->getOption('raw') ? $id : $this->_db->quote($id);
        }

        $query = sprintf(   'INSERT INTO %s (%s) VALUES (%s)',
                            $this->table ,
                            implode(',',array_keys($newData)) ,
                            implode(',',$newData)
                        );
        return $this->execute($query,'query') ? $id : false;
    }

    /**
    *   adds multiple new members in the DB
    *
    *   @version    2002/07/17
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      array   contains an array of new data that shall be saved in the DB
    *                       the key-value pairs have to be the same for all the data!!!
    *   @return     mixed   the inserted ids on success, or false otherwise
    */
    function addMultiple( $data )
    {
        if( !sizeof($data) )
            return false;

        $retIds = array();                          // the inserted ids which will be returned
        $allData = array();                         // each row that will be inserted
        foreach( $data as $aData )
        {
            unset($aData[$this->primaryCol]);       // we are adding a new data set, so be sure there is no value for the primary col
            $aData = $this->_checkColumns($aData,'add');
            $aData = $this->_quoteArray( $aData );

            if( $this->primaryCol )                     // do only use the sequence if a primary column is given
            {                                           // otherwise the data are written as given
                $id = $this->_db->nextId( $this->table );     
                $aData[$this->primaryCol] = $this->getOption('raw') ? $id : $this->_db->quote($id);

                $retIds[] = $id;
            }
            $allData[] = '('.implode(',',$aData).')';
        }

        $query = sprintf(   'INSERT INTO %s (%s,%s) VALUES %s',
                            $this->table ,
                            implode(',',array_keys($data[0])) ,
                            $this->primaryCol,
                            implode(',',$allData)
                        );
        return $this->execute($query,'query') ? $retIds : false;
    }

    /**
    *   removes a member from the DB
    *
    *   @version    2002/04/08
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      mixed   integer/string - the value of the column that shall be removed
    *                       array   - multiple columns that shall be matched, the second parameter will be ommited
    *   @param      string  the column to match the data against, only if $data is not an array
    *   @return     boolean
    */
    function remove( $data , $whereCol='' )
    {
        $raw = $this->getOption('raw');

        if( is_array($data) )
        {
//FIXXME check $data if it only contains columns that really exist in the table
            $wheres = array();
            foreach( $data as $key=>$val )
                $wheres[] = $key.'='. ( $raw ? $val : $this->_db->quote($val) );
            $whereClause = implode(' AND ',$wheres);
        }
        else
        {
            if( $whereCol=='' )
                $whereCol = $this->primaryCol;
            $whereClause = $whereCol.'='. ( $raw ? $data : $this->_db->quote($data) );
        }

        $query = sprintf(   'DELETE FROM %s WHERE %s',
                            $this->table,
                            $whereClause
                            );
        return $this->execute($query,'query') ? true : false;
// i think this method should return the ID's that it removed, this way we could simply use the result
// for further actions that depend on those id ... or? make stuff easier, see ignaz::imail::remove
    }

    /**
    *   empty a table
    *
    *   @version    2002/06/17
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @return
    */
    function removeAll()
    {
        $query = sprintf(   'DELETE FROM %s',
                            $this->table);
        return $this->execute($query,'query') ? true : false;
    }

    /**
    *   remove the datasets with the given ids
    *
    *   @version    2002/04/24
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      array   the ids to remove
    *   @return
    */
    function removeMultiple( $ids , $colName='' )
    {
        if( $colName=='' )
            $colName = $this->primaryCol;
        
        $ids = $this->_quoteArray($ids);

        $query = sprintf(   'DELETE FROM %s WHERE %s IN (%s)',
                            $this->table ,
                            $colName ,
                            implode(',',$ids)
                        );
        return $this->execute($query,'query') ? true : false;
    }



    /**
    *   removes a member from the DB and calls the remove methods of the given objects
    *   so all rows in another table that refer to this table are erased too
    *
    *   @version    2002/04/08
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      integer the value of the primary key
    *   @param      string  the column name of the tables with the foreign keys
    *   @param      object  just for convinience, so nobody forgets to call this method
    *                       with at least one object as a parameter
    *   @return     boolean
    */
    function removePrimary( $id , $colName , $atLeastOneObject )
    {
        $argCounter = 2;    // we have 2 parameters that need to be given at least
        // func_get_arg returns false and a warning if there are no more parameters, so
        // we suppress the warning and check for false
        while( $object=@func_get_arg($argCounter++) )
        {
            if( !$object->remove( $id , $colName ) )
            {
                $this->_errorSet('Fehler beim L�schen. Bitte erneut versuchen!');
                return false;
            }
        }

        if( !$this->remove($id) )
        {
            $this->_errorSet('Fehler beim L�schen. Bitte erneut versuchen!');
            return false;
        }
        return true;
    }

    /**
    *   sets the where condition which is used for the current instance
    *
    *   @version    2002/04/16
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      string  the where condition, this can be complete like 'X=7 AND Y=8'
    */
    function setWhere( $whereCondition='' )
    {
        $this->_where = $whereCondition;
//FIXXME parse the where condition and replace ambigious column names, such as "name='Deutschland'" with "country.name='Deutschland'"
// then the users dont have to write that explicitly and can use the same name as in the setOrder i.e. setOrder('name,_net_name,_netPrefix_prefix');
    }

    /**
    *   gets the where condition which is used for the current instance
    *
    *   @version    2002/04/22
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @return     string  the where condition, this can be complete like 'X=7 AND Y=8'
    */
    function getWhere()
    {
        return $this->_where;
    }

    /**
    *   only adds a string to the where clause
    *
    *   @version    2002/07/22
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      string  the where clause to add to the existing one
    *   @param      string  the condition for how to concatenate the new where clause
    *                       to the existing one
    */
    function addWhere( $where , $condition='AND' )
    {
        if( $this->getWhere() )
            $where = $this->getWhere().' '.$condition.' '.$where;

        $this->setWhere( $where );
    }

    /**
    *   add a where-like clause which works like a search for the given string
    *   i.e. calling it like this:
    *       $this->addWhereSearch( 'name' , 'otto hans' )
    *   produces a where clause like this one
    *       LOWER(name) LIKE "%otto%hans%"
    *   so the search finds the given string
    *
    *   @version    2002/08/14
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      string  the column to search in for
    *   @param      string  the string to search for
    */
    function addWhereSearch( $column , $string )
    {
        $string = $this->_db->quote('%'.str_replace(' ','%',strtolower($string)).'%');
        $this->addWhere( "LOWER($column) LIKE $string" );
    }

    /**
    *   sets the order condition which is used for the current instance
    *
    *   @version    2002/05/16
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      string  the where condition, this can be complete like 'X=7 AND Y=8'
    */
    function setOrder( $orderCondition='' , $desc=false )
    {
        $this->_order = $orderCondition .( $desc ? ' DESC' : '' );
    }

    /**
    *   gets the order condition which is used for the current instance
    *
    *   @version    2002/05/16
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @return     string  the order condition, this can be complete like 'ID,TIMESTAMP DESC'
    */
    function getOrder()
    {
        return $this->_order;
    }

    /**
    *   sets a join-condition
    *
    *   @version    2002/06/10
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      mixed   either a string or an array that contains
    *                       the table(s) to join on the current table
    *   @param      string  the where clause for the join
    */
    function setJoin( $table=null , $where=null , $joinType='default' )
    {
//FIXXME make it possible to pass a table name as a string like this too 'user u' where u is the string that can be used
// to refer to this table in a where/order or whatever condition
// this way it will be possible to join tables with itself, like setJoin( array('user u','user u1') )
// this wouldnt work yet, but for doing so we would need to change the _build methods too!!!
// because they use getJoin('tables') and this simply returns all the tables in use but dont take care of the mentioned syntax

        if( $table==null || $where==null)           // remove the join if not sufficient parameters are given
        {
            unset($this->_join[$joinType]);
            return;
        }

        settype($table,'array');
        $this->_join[$joinType]['table'] = $table;
/* this causes problems if we use the order-by, since it doenst know the name to order it by ... :-)
        // replace the table names with the internal name used for the join
        // this way we can also join one table multiple times if it will be implemented one day
        $this->_join['where'] = preg_replace('/'.$table.'/','j1',$where);
*/
        $this->_join[$joinType]['where'] = $where;
    }

    /**
    *   if you do a left join on $this->table you will get all entries
    *   from $this->table, also if there are no entries for them in the joined table
    *   if both parameters are not given the left-join will be removed
    *   NOTE: be sure to only use either a right or a left join
    *
    *   @version    2002/07/22
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      string  the table(s) to be left-joined
    *   @param      string  the where clause for the join
    */
    function setLeftJoin( $table=null , $where=null )
    {
        $this->setJoin( $table , $where , 'left' );
    }

    /**
    *   see setLeftJoin for further explaination on what a left/right join is
    *   NOTE: be sure to only use either a right or a left join
//FIXXME check if the above sentence is necessary and if sql doesnt allow the use of both
    *
    *   @see        setLeftJoin()
    *   @version    2002/09/04
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      string  the table(s) to be right-joined
    *   @param      string  the where clause for the join
    */
    function setRightJoin( $table=null , $where=null )
    {
        $this->setJoin( $table , $where , 'right' );
    }

    /**
    *   gets the join-condition
    *
    *   @version    2002/06/10
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @return     array   gets the join parameters
    */
    function getJoin( $what=null )
    {
        // if the user requests all the join data or if the join is empty, return it
        if( $what == null || !$this->_join)
            $ret = $this->_join;

        switch( strtolower($what) )
        {
            case 'table':
            case 'tables':
                $ret = array();
                foreach( $this->_join as $aJoin )
                {
                    if( sizeof($aJoin['table']) )
                        $ret = array_merge( $ret , $aJoin['table'] );
                }
                break;
            case 'right':   // return right-join data only
            case 'left':    // return left join data only
                break;
        }

        return $ret;
    }

    /**
    *   adds a table and a where clause that shall be used for the join
    *   instead of calling
    *       setJoin(array(table1,table2),'<where clause1> AND <where clause2>')
    *   you can also call
    *       setJoin(table1,'<where clause1>')
    *       addJoin(table2,'<where clause2>')
    *   or where it makes more sense is to build a query which is build out of a
    *   left join and a standard join
    *       setLeftJoin(table1,'<where clause1>')
    *       // results in ... FROM $this->table LEFT JOIN table ON <where clause1>
    *       addJoin(table2,'<where clause2>')
    *       // results in ...  FROM $this->table,table2 LEFT JOIN table ON <where clause1> WHERE <where clause2>
    *
    *   @version    2002/07/22
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param
    */
    function addJoin( $table , $where , $type='default' )
    {
        settype($table,'array');
        $this->_join[$type]['table'] = array_merge($this->_join[$type]['table'],$table);
        $this->_join[$type]['where'] .= trim($this->_join[$type]['where']) ? ' AND '.$where : $where;
    }

    /**
    *   sets the table this class is currently working on
    *
    *   @version    2002/07/11
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      string  the table name
    */
    function setTable($table)
    {
        $this->table = $table;
    }

    /**
    *   gets the table this class is currently working on
    *
    *   @version    2002/07/11
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @return     string  the table name
    */
    function getTable($table)
    {
        return $this->table;
    }

    /**
    *   sets the group-by condition
    *
    *   @version    2002/07/22
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      string  the group condition
    */
    function setGroup( $group='' )
    {
        $this->_group = $group;
//FIXXME parse the condition and replace ambigious column names, such as "name='Deutschland'" with "country.name='Deutschland'"
// then the users dont have to write that explicitly and can use the same name as in the setOrder i.e. setOrder('name,_net_name,_netPrefix_prefix');
    }

    /**
    *   gets the group condition which is used for the current instance
    *
    *   @version    2002/07/22
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @return     string  the group condition
    */
    function getGroup()
    {
        return $this->_group;
    }

    /**
    *   limit the result to return only the columns given in $what
    */
    function setSelect( $what='*' )
    {
        $this->_select = $what;
    }

    /**
    *   add a string to the select part of the query
    *
    *   add a string to the select-part of the query and connects it to an existing
    *   string using the $connectString, which by default is a comma.
    *   (SELECT xxx FROM - xxx is the select-part of a query)
    *
    *   @version    2003/01/08
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      string  the string that shall be added to the select-part
    *   @param      string  the string to connect the new string with the existing one
    *   @return     void
    */
    function addSelect( $what='*' , $connectString=',' )
    {                        
        // if the select string is not empty add the string, otherwise simply set it
        if( $this->_select )
            $this->_select = $this->_select.$connectString.$what;
        else
            $this->_select = $what;
    }

    function getSelect()
    {
        return $this->_select;
    }

    function setDontSelect( $what='' )
    {
        $this->_dontSelect = $what;
    }

    function getDontSelect()
    {
        return $this->_dontSelect;
    }

    /**
    *   reset all the set* settings, with no parameter given it resets all
    *
    *   @version    2002/09/16
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @return     void
    */
    function reset( $what=array() )
    {
        if( sizeof($what) == 0 )
            $what = array('select','dontSelect','group','where','index','order','join','leftJoin','rightJoin');

        foreach( $what as $aReset )
        {
            $this->{'set'.ucfirst($aReset)}();
        }
    }

    /**
    *   set mode the class shall work in          
    *   currently we have the modes:
    *   'raw'   does not quote the data before building the query
    *
    *   @version    2002/09/17
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      string      the mode to be set
    *   @param      mixed       the value of the mode
    *   @return     void
    */
    function setOption( $option , $value )
    {
        $this->options[strtolower($option)] = $value;
    }

    function getOption( $option )
    {
        return $this->options[strtolower($option)];
    }

    /**
    *   @deprecated
    */
    function setMode( $mode )
    {
        $this->setOption( $mode );
    }
    /**
    *   @deprecated
    */
    function getMode( $mode )
    {
        return $this->getOption( $mode );
    }

    /**
    *   quotes all the data in this array if we are not in raw mode!
    */
    function _quoteArray( $data )
    {
        if( !$this->getOption('raw') )
        {
            foreach( $data as $key=>$val )
                $data[$key] = $this->_db->quote($val);
        }
        return $data;
    }

    /**
    *   checks if the columns which are given as the array's indexes really exist
    *   if not it will be unset anyway
    *
    *   @version    2002/04/16
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      string  the actual message, first word should always be the method name,
    *                       to build the message like this: className::methodname
    *   @param      integer the line number
    */
    function _checkColumns( $newData , $method='unknown' )
    {
        if( !$meta = $this->metadata() )    // if no metadata available, return data as given
            return $newData;

        foreach( $newData as $colName=>$x )
        {
            if( !isset($meta[$colName]) )
            {
                $this->_errorLog("$method, column {$this->table}.$colName doesnt exist, value was removed before '$method'",__LINE__);
                unset($newData[$colName]);
            }
            else    // if the current column exists, check the length too, not to write content that is too long
            // prevent DB-errors here
            {
                // do only check the data length if this field is given
// FIXXME use PEAR-defined field for 'DATA_LENGTH'
                if( isset($meta[$colName]['DATA_LENGTH']) &&
                    ($oldLength=strlen($newData[$colName])) > $meta[$colName]['DATA_LENGTH'] )
                {
                    $this->_errorLog("_checkColumns, had to trim column '$colName' from $oldLength to ".
                                        $meta[$colName]['DATA_LENGTH'].' characters.',__LINE__);
                    $newData[$colName] = substr($newData[$colName],0,$meta[$colName]['DATA_LENGTH']);
                }
            }
        }
        return $newData;
    }

    /**
    *   overwrite this method and i.e. print the query $string
    *   to see the final query
    *
    *   @param      string  the query mostly
    */
    function debug($string){}

    //
    //
    //  ONLY ORACLE SPECIFIC, not very nice since it is DB dependent, but we need it!!!
    //
    //

    /**
    *
    *   !!!! query COPIED FROM db_oci8.inc - from PHPLIB !!!!
    *
    * @access        public
    * @see
    * @version  2001/09
    * @author        PHPLIB
    * @param
    * @return
    */
    function metadata( $table='' )
    {
        $full = false;
        if( $table=='' )
            $table = $this->table;

        // to prevent multiple selects for the same metadata
        if( $this->_metadata[$table] )
            return $this->_metadata[$table];

// FIXXXME use oci8 implementation of newer PEAR::DB-version
        if( $this->_db->phptype=='oci8' )
        {
            $count = 0;
            $id    = 0;
            $res   = array();

            //# This is a RIGHT OUTER JOIN: "(+)", if you want to see, what
            //# this query results try the following:
            //// $table = new Table; $this->_db = new my_DB_Sql; // you have to make
            ////                                          // your own class
            //// $table->show_results($this->_db->query(see query vvvvvv))
            ////
            $res=$this->_db->getAll("SELECT T.column_name,T.table_name,T.data_type,".
                "T.data_length,T.data_precision,T.data_scale,T.nullable,".
                "T.char_col_decl_length,I.index_name".
                " FROM ALL_TAB_COLUMNS T,ALL_IND_COLUMNS I".
                " WHERE T.column_name=I.column_name (+)".
                " AND T.table_name=I.table_name (+)".
                " AND T.table_name=UPPER('$table') ORDER BY T.column_id");

            if( DB::isError( $res ) )
            {
                //$this->_errorSet( $res->getMessage() );
                // i think we only need to log here, since this method is never used
                // directly for the user's functionality, which means if it fails it
                // is most probably an appl error
                $this->_errorLog( $res->getUserInfo() );
                return false;
            }
            foreach( $res as $key=>$val )
                $res[$key]['name'] = $val['COLUMN_NAME'];
        }
        else
        {                              
            $res=$this->_db->tableinfo($table);
            if( DB::isError($res) )
                return false;
        }

        $ret = array();
        foreach( $res as $key=>$val )
            $ret[$val['name']] = $val;

        $this->_metadata = $ret;
        return $ret;
    }  // end of method



    //
    //  methods for building the query
    //

    /**
    *   build the from string
    *
    *   @version    2002/07/11
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @return     string  the string added behind FROM
    */
    function _buildFrom()
    {
        $from = $this->table;

        if( $join = $this->getJoin() )   // is join set?
        {
            // handle the standard join thingy
            if( $join['default'] )
            {
                $from .= ','.implode(',',$join['default']['table']);
            }

            // if we also have a left join, add the 'LEFT JOIN table ON condition'
            $joinType = $join['left']?'left':($join['right']?'right':false);
            if( $joinType ) // do we have any of the above checked join-types?
            {
                $from = $from.' '.strtoupper($joinType).' JOIN '.implode(',',$join[$joinType]['table']);

                $where = $join[$joinType]['where'];
                // replace the _TABLENAME_COLUMNNAME by TABLENAME.COLUMNNAME
                // since oracle doesnt work with the _TABLENAME_COLUMNNAME which i think is strange
// FIXXME i think this should become deprecated since the setWhere should not be used like this: '_table_column' but 'table.column'
                $regExp = '/_('.implode('|',$join[$joinType]['table']).')_([^\s]+)/';
                $where = preg_replace( $regExp , '$1.$2' , $where );

                // add the table name before any column that has no table prefix
                // since this might cause "unambigious column" errors
                if( $meta = $this->metadata() )
                    foreach( $meta as $aCol=>$x )
                    {
                        // this covers the LIKE,IN stuff: 'name LIKE "%you%"'  'id IN (2,3,4,5)'
                        $where = preg_replace( '/\s'.$aCol.'\s/' , " {$this->table}.$aCol " , $where );
                        // replace also the column names which are behind a '='
                        // and do this also if the aCol is at the end of the where clause
                        // that's what the $ is for
                        $where = preg_replace( '/=\s*'.$aCol.'(\s|$)/' , "={$this->table}.$aCol " , $where );
                        // replace if colName is first and possibly also if at the beginning of the where-string
                        $where = preg_replace( '/(^\s*|\s+)'.$aCol.'\s*=/' , "$1{$this->table}.$aCol=" , $where );
                    }

                $from = $from.' ON '.$where;
            }
        }

        return $from;
    }
              
    /**
    *   this method gets the short name for a table
    *
    *   get the short name for a table, this is needed to properly build the
    *   'AS' parts in the select query
    *   @param  string  the real table name
    *   @return string  the table's short name
    */
    function getTableShortName( $table )
    {
        $tableSpec = $this->getTableSpec( false );
        if( $tableSpec[$table]['shortName'] )
        {
//print "$table ... ".$tableSpec[$table]['shortName'].'<br>';
            return $tableSpec[$table]['shortName'];
        }

        $possibleTableShortName = preg_replace( $this->_tableNameToShortNamePreg, '' , $table );
//print "$table ... $possibleTableShortName<br>";
        return $possibleTableShortName;
    }

    /**
    *   gets the tableSpec either indexed by the short name or the name
    *   returns the array for the tables given as parameter or if no
    *   parameter given for all tables that exist in the tableSpec
    *
    *   @param      array   table names (not the short names!)
    *   @param      boolean if true the table is returned indexed by the shortName
    *                       otherwise indexed by the name
    *   @return     array   the tableSpec indexed
    */
    function getTableSpec( $shortNameIndexed=true , $tables=array() )
    {
        $newSpec = array();
        foreach( $this->tableSpec as $aSpec )
        {
            if( sizeof($tables)==0 || in_array($aSpec['name'],$tables) )
            {              
                if( $shortNameIndexed )
                    $newSpec[$aSpec['shortName']] = $aSpec;
                else
                    $newSpec[$aSpec['name']] = $aSpec;
            }
        }
        return $newSpec;
    }



    /**
    *   build the 'SELECT <what> FROM ... 'for a select
    *
    *   @version    2002/07/11
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      string      if given use this string
    *   @return     string      the what-clause
    */
    function _buildSelect( $what=null )
    {
        // what has preference, that means if what is set it is used
        // this is only because the methods like 'get' pass an individually built value, which 
        // is supposed to be used, but usually it's generically build using the 'getSelect' values
        if( !$what && $this->getSelect() )
        {
            $what = $this->getSelect();
        }
                                                                                                 
        //
        // replace all the '*' by the real column names, and take care of the dontSelect-columns!
        //
        $dontSelect = $this->getDontSelect();
        $dontSelect = $dontSelect ? explode(',',$dontSelect) : array(); // make sure dontSelect is an array
                                                                                              
        // here we will replace all the '*' and 'table.*' by all the columns that this table
        // contains. we do this so we can easily apply the 'dontSelect' values.
        // and so we can also handle queries like: 'SELECT *,count() FROM ' and 'SELECT table.*,x FROM ' too
        if( strpos( $what , '*' ) !== false )
        {
            // subpattern 1 get all the table names, that are written like this: 'table.*' including '*'
            // for '*' the tablename will be ''
            preg_match_all( '/([^,]*)(\.)?\*\s*(,|$)/U' , $what , $res );
//print "$what ... ";print_r( $res );print "<br>";
            $selectAllFromTables = array_unique($res[1]); // make the table names unique, so we do it all just once for each table
            $tables = array();
            if( in_array('',$selectAllFromTables) ) // was there a '*' ?
            {
                // get all the tables that we need to process, depending on if joined or not
                $tables = $this->getJoin() ?
                                array_merge($this->getJoin('tables'),$this->table) : // get the joined tables and this->table
                                array($this->table);        // create an array with only this->table
            }
            else
            {
                $tables = $selectAllFromTables;
            }                                                       

            $cols = array();
            foreach( $tables as $aTable )       // go thru all the tables and get all columns for each, and handle 'dontSelect'
            {                            
                if( $meta = $this->metadata($aTable) )
                foreach( $meta as $colName=>$x )
                {
                    // handle the dontSelect's
                    if( in_array($colName,$dontSelect) || in_array("$aTable.$colName",$dontSelect) )
                        continue;

                    if( $aTable == $this->table )
                        $cols[$aTable][] = $this->table.".$colName AS $colName";
                    else
                        $cols[$aTable][] = "$aTable.$colName AS _".$this->getTableShortName($aTable)."_$colName";
                }
            }

            // put the extracted select back in the $what  
            // that means replace 'table.*' by the i.e. 'table.id AS _table_id'
            // or if it is the table of this class replace 'table.id AS id'
            if( in_array('',$selectAllFromTables) )
            {
                $allCols = '';
                foreach( $cols as $aTable )
                    $allCols[] = implode(',',$aTable);
                $what = preg_replace( '/(^|,)\*($|,)/' , '$1'.implode(',',$allCols).'$2' , $what );
                // remove all the 'table.*' since we have selected all anyway (because there was a '*' in the select)
                $what = preg_replace( '/[^,]*(\.)?\*\s*(,|$)/U' , '' , $what );
            }
            else
            {
                foreach( $cols as $tableName=>$aTable )
                {
                    if( is_array($aTable) && sizeof($aTable) )
                        // replace all the 'table.*' by their select of each column
                        $what = preg_replace( '/(^|,)\s*'.$tableName.'\.\*\s*($|,)/' , '$1'.implode(',',$aTable).'$2' , $what );
                }
            }
        }

        if( $this->getJoin() )
        {
            // replace all 'column' by '$this->table.column' to prevent ambigious errors
            foreach( $this->metadata() as $aCol=>$x )
            {
                // handle ',id as xid,MAX(id),id' etc.
// FIXXME do this better!!!
                $what = preg_replace("/(^|,|\()\s*$aCol(\)|\s*|,|as)/i","$1{$this->table}.$aCol$2",$what);
            }

            // replace all 'joinedTable.columnName' by '_joinedTable_columnName'
            // this actually only has an effect if there was no 'table.*' for 'table'
            // if that was there, then it has already been done before
            foreach( $this->getJoin('tables') as $aTable )
            {
                if( $meta = $this->metadata($aTable) )
                {
                    foreach( $meta as $aCol=>$x )
                    {
                        // dont put the 'AS' behind it if there is already one
                        if( preg_match("/$aTable.$aCol\s*as/i",$what) )
                            continue;

                        // this covers a ' table.colName ' surrounded by spaces, and replaces it by ' table.colName AS _table_colName'
                        $what = preg_replace( '/\s'.$aTable.'.'.$aCol.'\s/' , " $aTable.$aCol AS _".$this->getTableShortName($aTable)."_$aCol " , $what );
                        // replace also the column names which are behind a ','
                        // and do this also if the aCol is at the end that's what the $ is for
                        $what = preg_replace( '/,\s*'.$aTable.'.'.$aCol.'(,|\s|$)/' , ",$aTable.$aCol AS _".$this->getTableShortName($aTable)."_$aCol$1" , $what );
                        // replace if colName is first and possibly also if at the beginning of the where-string
                        $what = preg_replace( '/(^\s*|\s+)'.$aTable.'.'.$aCol.'\s*,/' , "$1$aTable.$aCol AS _".$this->getTableShortName($aTable)."_$aCol," , $what );
                    }
                }
            }
        }
        return $what;
    }

    /**
    *
    *
    *   @version    2002/07/11
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param
    *   @return
    */
    function _buildWhere( $where='' )
    {
        if( $this->getWhere() )
        {
            if( $where )
                $where = $this->getWhere().' AND '.$where;
            else
                $where = $this->getWhere();
        }

        if( $join = $this->getJoin() )   // is join set?
        {
            // only those where conditions in the default-join have to be added here
            // left-join conditions are added behind 'ON', the '_buildJoin()' does that
            if( strlen($join['default']['where']) > 0 )
            {
                // we have to add this join-where clause here
                // since at least in mysql a query like: select * from tableX JOIN tableY ON ...
                // doesnt work, may be that's even SQL-standard...
                if( trim($where) )
                    $where = $join['default']['where'].' AND '.$where;
                else
                    $where = $join['default']['where'];
            }
            // replace the _TABLENAME_COLUMNNAME by TABLENAME.COLUMNNAME
            // since oracle doesnt work with the _TABLENAME_COLUMNNAME which i think is strange
// FIXXME i think this should become deprecated since the setWhere should not be used like this: '_table_column' but 'table.column'
            $regExp = '/_('.implode('|',$this->getJoin('tables')).')_([^\s]+)/';
            $where = preg_replace( $regExp , '$1.$2' , $where );
            // add the table name before any column that has no table prefix
            // since this might cause "unambigious column" errors
            if( $meta = $this->metadata() )
                foreach( $meta as $aCol=>$x )
                {
                    // this covers the LIKE,IN stuff: 'name LIKE "%you%"'  'id IN (2,3,4,5)'
                    $where = preg_replace( '/\s'.$aCol.'\s/' , " {$this->table}.$aCol " , $where );
                    // replace also the column names which are behind a '='
                    // and do this also if the aCol is at the end of the where clause
                    // that's what the $ is for
                    $where = preg_replace( '/([=<>])\s*'.$aCol.'(\s|$)/' , "$1{$this->table}.$aCol " , $where );
                    // replace if colName is first and possibly also if at the beginning of the where-string
                    $where = preg_replace( '/(^\s*|\s+)'.$aCol.'\s*([=<>])/' , "$1{$this->table}.$aCol$2" , $where );
                }

        }
        return $where;
    }

    /**
    *
    *
    *   @version    2002/07/11
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param
    *   @return
    */
    function _buildOrder()
    {
        $order = $this->getOrder();

        if( $meta = $this->metadata() )
        {
            foreach( $meta as $aCol=>$x )
            {
                // replace aCol in the order clause, to make the column name unambigiuos
                // this is highly important if i.e. only a count(*) is requested, then the columns
                // have no AS clause and ambigious column names might appear, so we prevent that here :-)
                $order = preg_replace( '/(^\s*|\s+|,)'.$aCol.'\s*(,)?/U' , "$1{$this->table}.$aCol$2" , $order );
            }
        }

        return $order;
    }

    function _buildGroup()
    {
        $group = $this->getGroup();

        if( $meta = $this->metadata() )
        {
            foreach( $meta as $aCol=>$x )
            {
                // replace aCol in the order clause, to make the column name unambigiuos
                // this is highly important if i.e. only a count(*) is requested, then the columns
                // have no AS clause and ambigious column names might appear, so we prevent that here :-)
                $group = preg_replace( '/(^\s*|\s+|,)'.$aCol.'\s*(,)?/U' , "$1{$this->table}.$aCol$2" , $group );
            }
        }

        return $group;
    }

    /**
    *
    *
    *   @version    2002/07/11
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      array   this array contains the elements of the query,
    *                       indexed by their key, which are: 'select','from','where', etc.
    *   @return
    */
    function _buildSelectQuery( $query=array() )
    {
        $where = isset($query['where']) ? $query['where'] : $this->_buildWhere();
        if( $where )
            $where = 'WHERE '.$where;

        $order = isset($query['order']) ? $query['order'] : $this->_buildOrder();
        if( $order )
            $order = 'ORDER BY '.$order;

        $group = isset($query['group']) ? $query['group'] : $this->_buildGroup();
        if( $group )
            $group = 'GROUP BY '.$group;

        $queryString = sprintf( 'SELECT %s FROM %s %s %s %s',
                                $query['select'] ? $query['select'] : $this->_buildSelect(),
                                $query['from'] ? $query['from'] : $this->_buildFrom(),
                                $where,
                                $group,
                                $order
                                );
        return $queryString;
    }

    /**
    *
    *
    *   @version    2002/07/11
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param
    *   @return
    */
    function execute( $query=null , $method='getAll' )
    {
        if( $query==null )
            $query = $this->_buildSelectQuery();

// FIXXME on ORACLE this doesnt work, since we return joined columns as _TABLE_COLNAME and the _ in front
// doesnt work on oracle, add a letter before it!!!
        $this->_lastQuery = $query;

        $this->debug($query);
        if( DB::isError( $res = $this->_db->$method($query) ) )
        {                   
            if( $this->getOption('verbose') )
                $this->_errorSet( $res->getMessage() );
            else
                $this->_errorLog( $res->getMessage() );

            $this->_errorLog( $res->getUserInfo() , __LINE__ );
            return false;
        }

        $res = $this->_makeIndexed($res);
        return $res;
    }

    /**
    *
    *
    *   @version    2002/07/11
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param
    *   @return
    */
    function returnResult( &$result )
    {
        if( $this->_useResult )
        {
            if( $result==false )
                return false;
            return new vp_DB_Result( $result );
        }
        return $result;
    }

    /**
    *
    *
    *   @version    2002/07/11
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param
    *   @return
    */
    function &_makeIndexed(&$data)
    {
        // we can only return an indexed result if the result has a number of columns
        if( is_array($data) && sizeof($data) &&
            $key=$this->getIndex() )
        {
            // build the string to evaluate which might be made up out of multiple indexes of a result-row
            $evalString = '$val[\''.implode('\'].\',\'.$val[\'',explode(',',$key)).'\']';   //"

            $indexedData = array();
//FIXXME actually we also need to check ONCE if $val is an array, so to say if $data is 2-dimensional
            foreach( $data as $val )
            {
                eval("\$keyValue = $evalString;");  // get the actual real (string-)key (string if multiple cols are used as index)
                $indexedData[$keyValue] = $val;
            }
            unset($data);
            return $indexedData;
        }

        return $data;
    }


    /**
    *   format the result to be indexed by $key
    *   NOTE: be careful, when using this you should be aware, that if you
    *   use an index which's value appears multiple times you may loose data
    *   since a key cant exist multiple times!!
    *   the result for a result to be indexed by a key(=columnName)
    *   (i.e. 'relationtoMe') which's values are 'brother' and 'sister'
    *   or alike normally returns this:
    *       $res['brother'] = array('name'=>'xxx')
    *       $res['sister'] = array('name'=>'xxx')
    *   but if the column 'relationtoMe' contains multiple entries for 'brother'
    *   then the returned dataset will only contain one brother, since the
    *   value from the column 'relationtoMe' is used
    *   and which 'brother' you get depends on a lot of things, like the sortorder,
    *   how the db saves the data, and whatever else
    *
    *   you can also set indexes which depend on 2 columns, simply pass the parameters like
    *   'table1.id,table2.id' it will be used as a string for indexing the result
    *   and the index will be built using the 2 values given, so a possible
    *   index might be '1,2' or '2108,29389' this way you can access data which
    *   have 2 primary keys. Be sure to remember that the index is a string!
    *
    *   @version    2002/07/11
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param
    *   @return
    */
    function setIndex( $key=null )
    {
        if( $this->getJoin() )   // is join set?
        {
            // replace TABLENAME.COLUMNNAME by _TABLENAME_COLUMNNAME
            // since this is only the result-keys can be used for indexing :-)
            $regExp = '/('.implode('|',$this->getJoin('tables')).')\.([^\s]+)/';
            $key = preg_replace( $regExp , '_$1_$2' , $key );

            // remove the table name if it is in front of '<$this->table>.columnname'
            // since the key doesnt contain it neither
            if( $meta = $this->metadata() )
                foreach( $meta as $aCol=>$x )
                    $key = preg_replace( '/'.$this->table.'\.'.$aCol.'/' , $aCol , $key );

        }
        $this->_index = $key;
    }

    /**
    *
    *
    *   @version    2002/07/11
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param
    *   @return
    */
    function getIndex()
    {
        return $this->_index;
    }

    /**
    *
    *
    *   @version    2002/07/11
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param
    *   @return
    */
    function useResult( $doit=true )
    {
        $this->_useResult = $doit;
        if( $doit )
            require_once 'DB/QueryTool/Result.php';
    }
                          
    
    
    /**
    *   set both callbacks
    */
    function setErrorCallback( $param='' )
    {
        $this->setErrorLogCallback( $param );
        $this->setErrorSetCallback( $param );
    }

    function setErrorLogCallback( $param='' )
    {
        $errorLogCallback = &PEAR::getStaticProperty('DB_QueryTool','_errorLogCallback');
        $errorLogCallback = $param;
    }

    function setErrorSetCallback( $param='' )
    {
        $errorSetCallback = &PEAR::getStaticProperty('DB_QueryTool','_errorSetCallback');
        $errorSetCallback = $param;
    }

    /**
    *   sets error log and adds additional info
    *
    *   @version    2002/04/16
    *   @access     public
    *   @author     Wolfram Kriesing <wk@visionp.de>
    *   @param      string  the actual message, first word should always be the method name,
    *                       to build the message like this: className::methodname
    *   @param      integer the line number
    */
    function _errorLog( $msg , $line='unknown' )
    {
        $this->_errorHandler( 'log' , $msg , $line='unknown' );
/*
        if( $this->getOption('verbose') == true )
        {
            $this->_errorLog( get_class($this)."::$msg ($line)" );
            return;
        }

        if( $this->_errorLogCallback )
            call_user_func( $this->_errorLogCallback , $msg );
*/
    }

    function _errorSet( $msg , $line='unknown' )
    {
        $this->_errorHandler( 'set' , $msg , $line='unknown' );
    }

    function _errorHandler( $logOrSet , $msg , $line='unknown' )
    {
/* what did i do this for?
        if( $this->getOption('verbose') == true )
        {
            $this->_errorHandler( $logOrSet , get_class($this)."::$msg ($line)" );
            return;
        }
*/

        $logOrSet = ucfirst($logOrSet);
        $callback = &PEAR::getStaticProperty('DB_QueryTool','_error'.$logOrSet.'Callback');
        if( $callback )
            call_user_func( $callback , $msg );
//        else
//          ?????

    }




}   // end of class
?>
