<?php

$dbtypes = array(
    'mysql' => 'Mysql',
    'pgsql' => 'PostgreSQL',
    'ibase' => 'Firebird/Interbase',
);

$tests = array(
    'all_tests.php'                 => '<b>All tests</b>',
    'db_querytool_tests_get.php'    => '<b>All get*() tests</b>',
    'db_querytool_tests_usage.php'  => '<b>All usage tests</b>',
    'db_querytool_testGet.php'      => 'testGet',
    'db_querytool_testGetAll.php'   => 'testGetAll',
    'db_querytool_testGetCount.php' => 'testGetCount',
    'db_querytool_testGetQueryString.php' => 'testGetQueryString',
    'db_querytool_testHaving.php'   => 'testHaving',
    'db_querytool_testLimit.php'    => 'testLimit',
    'db_querytool_testWhere.php'    => 'testWhere',
);
?>

<h1>MDB_QueryTool tests</h1>

<ul>


<?php
foreach ($dbtypes as $dbtype => $dbname) {
    echo '<li><h2>'.$dbname.'</h2><ul>';
    foreach ($tests as $testfile => $testname) {
        echo '<li><a href="'.$testfile.'?type='.$dbtype.'">'.$testname.'</a></li>';
    }
    echo '</ul></li>';
}
?>
</ul>
