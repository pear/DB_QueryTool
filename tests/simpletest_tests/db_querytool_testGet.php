<?php
// $Id$

require_once dirname(__FILE__).'/db_querytool_test_base.php';

class TestOfDB_QueryTool_Get extends TestOfDB_QueryTool
{

    function TestOfDB_QueryTool_Get($name = __CLASS__) {
        $this->UnitTestCase($name);
    }

    function setUp() {
        $this->qt =& new DB_QueryTool(DB_DSN, $GLOBALS['DB_OPTIONS']);
        $this->qt->removeAll();
    }
    function tearDown() {
        $this->qt->removeAll();
        unset($this->qt);
    }
    function test_AddGet() {
        $this->qt =& new DB_QT(TABLE_USER);
        $this->qt->table = TABLE_USER;

        $newData = $this->_getSampleData(1);
        $id      = $this->qt->add($newData);
//echo '<pre>';var_dump($this->qt->getQueryString());echo '</pre>';
//echo '<pre>';var_dump($this->qt->db->query($this->qt->getQueryString()));echo '</pre>';
        $this->assertTrue($id != false);

        $newData['id'] = $id;
        $this->assertEqual($newData, $this->qt->get($id));

        $newData = $this->_getSampleData(2);
        $id      = $this->qt->add($newData);
        $this->assertTrue($id != false);
        
        $newData['id'] = $id;
        $this->assertEqual($newData, $this->qt->get($id));
    }

    // test if column==table works, using the table TABLE_QUESTION
    function test_tableEqualsColumn() {
        unset($this->qt);
        $this->qt =& new DB_QT(TABLE_QUESTION);
        //$this->qt->table = TABLE_QUESTION;
//echo '<pre>'; var_dump($this->qt);exit;
        $newData  = array(TABLE_QUESTION => 'Why does this not work?');
        $id       = $this->qt->add($newData);
//echo '<pre>'; print_r($this->qt->table);exit;
        $this->assertTrue($id != false);

        $newData['id'] = $id;
        $this->assertEqual($newData, $this->qt->get($id));
    }

    function test_tableEqualsColumnGetAll() {
        unset($this->qt);
        $this->qt =& new DB_QT(TABLE_QUESTION);
        $newData  = array(TABLE_QUESTION => 'Why does this not work?');
        $id       = $this->qt->add($newData);
        $this->assertTrue($id != false);

        $newData['id'] = $id;
        $data = $this->qt->getAll();
        // assertEquals doesn't sort arrays recursively, so we have to extract the data :-(
        // we cant do this:
        $this->assertEqual(array($newData), $this->qt->getAll());
        //$this->assertEqual($newData, $data[0]);
    }

    // test if column==table works, using the table TABLE_QUESTION
    // this fails in v0.9.3
    // a join makes it fail!!!, the tests above are just convinience tests
    // they are actually meant to work !always! :-)
    function test_tableEqualsColumnJoinedGetAll() {
        $theQuestion = 'Why does this not work?';
        $theAnswer   = 'I dont know!';

        $question =& new DB_QT(TABLE_QUESTION);
        $question->removeAll();

        $newQuest = array(TABLE_QUESTION => $theQuestion);
        $qid = $question->add($newQuest);

        $answer =& new DB_QT(TABLE_ANSWER);
        $answer->removeAll();

        $newAnswer = array(TABLE_QUESTION.'_id' => $qid, TABLE_ANSWER => $theAnswer);
        $aid = $answer->add($newAnswer);

        $question->autoJoin(TABLE_ANSWER);

        //$newData['id'] = $id;
        $data = $question->getAll();
        $expected =  array( '_answer_id' => $aid,
                            '_answer_answer' => $theAnswer,
                            '_answer_question_id' => $qid,
                            'id' => $qid,
                            'question' => $theQuestion);
        // assertEquals doesnt sort arrays recursively, so we have to extract the data :-(
        // we cant do this:     $this->assertEquals(array($newData),$question->getAll());
        $this->assertEqual($expected, $data[0]);
    }

    /**
     * This method actually checks if the functionality that needs to be changed
     * for the above test to work will still work after the change ...
     *
     * check if stuff like MAX(id), LOWER(question), etc. will be converted to
     *     MAX(TABLE_QUESTION.id), LOWER(TABLE_QUESTION.question)
     * this is done for preventing ambiguous column names, that's why it only applies
     * in joined queries ...
     */
    function test_testSqlFunction() {
        $theQuestion = 'Why does this not work?';
        $theAnswer   = 'I dont know!';

        $question =& new DB_QT(TABLE_QUESTION);
        $newQuest = array(TABLE_QUESTION => $theQuestion);
        $qid = $question->add($newQuest);

        $answer    =& new DB_QT(TABLE_ANSWER);
        $newAnswer = array(TABLE_QUESTION.'_id' => $qid, TABLE_ANSWER => $theAnswer);
        $aid = $answer->add($newAnswer);

        $question->autoJoin(TABLE_ANSWER);
//        $question->setSelect('id, '.TABLE_QUESTION.' as question, '.TABLE_ANSWER.' as answer');
        $question->setSelect('MAX(id),'.TABLE_ANSWER.'.id');
        $this->assertTrue(strpos($question->_buildSelectQuery(), 'MAX('.TABLE_QUESTION.'.id)'));

        // check '(question)'
        $question->setSelect('LOWER(question),'.TABLE_ANSWER.'.*');
        $this->assertTrue(strpos($question->_buildSelectQuery(), 'LOWER('.TABLE_QUESTION.'.question)'));

        // check 'id,'
        $question->setSelect('id, '.TABLE_ANSWER.'.*');
        $this->assertTrue(strpos($question->_buildSelectQuery(), TABLE_QUESTION.'.id'));

        // check 'id as qid'
        $question->setSelect('id as qid, '.TABLE_ANSWER.'.*');
        $this->assertTrue(strpos($question->_buildSelectQuery(), TABLE_QUESTION.'.id as qid'));

        // check 'id as qid'
        $question->setSelect('LOWER( question ), '.TABLE_ANSWER.'.*');
        $this->assertTrue(strpos($question->_buildSelectQuery(), 'LOWER( '.TABLE_QUESTION.'.question )'));
    }
}

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new TestOfDB_QueryTool_Get();
    $test->run(new HtmlReporter());
}
?>