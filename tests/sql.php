<?php
    // $Id$

    $dbStructure = array(
        'mysql' => array(
            'setup' => array(
                    "DROP TABLE IF EXISTS ".TABLE_USER.";"
                    ,"DROP TABLE IF EXISTS ".TABLE_ADDRESS.";"
                    ,"DROP TABLE IF EXISTS ".TABLE_USER."_seq;"
                    ,"DROP TABLE IF EXISTS ".TABLE_ADDRESS."_seq;"
                    ,"DROP TABLE IF EXISTS ".TABLE_QUESTION.";"
                    ,"DROP TABLE IF EXISTS ".TABLE_QUESTION."_seq;"
                    ,"DROP TABLE IF EXISTS ".TABLE_ANSWER.";"
                    ,"DROP TABLE IF EXISTS ".TABLE_ANSWER."_seq;"
                    
                    ,"CREATE TABLE ".TABLE_ADDRESS." (
                        id int(11) NOT NULL default '0',
                        city varchar(100) NOT NULL default '',
                        zip varchar(5) NOT NULL default '',
                        street varchar(100) NOT NULL default '',
                        phone varchar(100) NOT NULL default '',
                        PRIMARY KEY  (id)
                    ) TYPE=MyISAM;"

                    ,"CREATE TABLE ".TABLE_USER." (
                        id int(11) NOT NULL default '0',
                        login varchar(255) NOT NULL default '',
                        password varchar(255) NOT NULL default '',
                        name varchar(255) NOT NULL default '',
                        address_id int(11) NOT NULL default '0',
                        company_id int(11) NOT NULL default '0',
                        PRIMARY KEY  (id)
                    ) TYPE=MyISAM;"
                    
                    ,"CREATE TABLE ".TABLE_QUESTION." (
                        id int(11) NOT NULL default '0',
                        ".TABLE_QUESTION." varchar(255) NOT NULL default '',
                        PRIMARY KEY  (id)
                    ) TYPE=MyISAM;"
                
                    ,"CREATE TABLE ".TABLE_ANSWER." (
                        id int(11) NOT NULL default '0',
                        ".TABLE_ANSWER." varchar(255) NOT NULL default '',
                        ".TABLE_QUESTION."_id int(11) NOT NULL default '0',
                        PRIMARY KEY  (id)
                    ) TYPE=MyISAM;"
                
                ),

            'tearDown'  =>  array(
                    "DROP TABLE IF EXISTS ".TABLE_USER.";"
                    ,"DROP TABLE IF EXISTS ".TABLE_USER."_seq;"
                    ,"DROP TABLE IF EXISTS ".TABLE_ADDRESS.";"
                    ,"DROP TABLE IF EXISTS ".TABLE_ADDRESS."_seq;"
                    ,"DROP TABLE IF EXISTS ".TABLE_QUESTION.";"
                    ,"DROP TABLE IF EXISTS ".TABLE_QUESTION."_seq;"
                    ,"DROP TABLE IF EXISTS ".TABLE_ANSWER.";"
                    ,"DROP TABLE IF EXISTS ".TABLE_ANSWER."_seq;"
            )
        ),

        'pgsql' =>  array(
            'setup' =>  array(),
            'tearDown'=>array()
        )        
    );
        
    

?>
