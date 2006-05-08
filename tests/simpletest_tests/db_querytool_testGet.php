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
        $this->qt->table = TABLE_TRANSLATION;
        $this->qt->removeAll();
        $this->qt->table = TABLE_USER;
        $this->qt->removeAll();
        $this->qt->table = TABLE_QUESTION;
        $this->qt->removeAll();
        $this->qt->table = TABLE_ANSWER;
        $this->qt->removeAll();
        $this->qt->db->dropSequence(TABLE_USER);
        $this->qt->db->dropSequence(TABLE_QUESTION);
        $this->qt->db->dropSequence(TABLE_ANSWER);
    }
    function tearDown() {
        $this->qt->table = TABLE_TRANSLATION;
        $this->qt->removeAll();
        $this->qt->table = TABLE_USER;
        $this->qt->removeAll();
        $this->qt->table = TABLE_QUESTION;
        $this->qt->removeAll();
        $this->qt->table = TABLE_ANSWER;
        $this->qt->removeAll();
        $this->qt->db->dropSequence(TABLE_USER);
        $this->qt->db->dropSequence(TABLE_QUESTION);
        $this->qt->db->dropSequence(TABLE_ANSWER);
        unset($this->qt);
    }
    function test_AddGet() {
        $this->qt =& new DB_QT(TABLE_USER);
        $this->qt->table = TABLE_USER;

        $newData = $this->_getSampleData(1);
        $id      = $this->qt->add($newData);
        $this->assertTrue($id != false);

        $newData['id'] = $id;
        $this->assertEqual($newData, $this->qt->get($id));

        $newData = $this->_getSampleData(2);
        $id      = $this->qt->add($newData);
        $this->assertTrue($id != false);
        
        $newData['id'] = $id;
        $this->assertEqual($newData, $this->qt->get($id));
    }
    
    function test_AddGetPKNotInteger() {
        $this->qt =& new DB_QT(TABLE_TRANSLATION);
        $this->qt->table = TABLE_TRANSLATION;
        $this->qt->primaryCol = 'string';

        $newData = array('string' => 'aaa', 'translation' => 'AAA');
        $id      = $this->qt->add($newData);
        $this->assertTrue($id != false);

        $this->assertEqual($newData, $this->qt->get($id));

        $newData = array('string' => 'bbb', 'translation' => 'BBB');
        $id      = $this->qt->add($newData);
        $this->assertTrue($id != false);

        $this->assertEqual($newData, $this->qt->get($id));
    }

    function test_GetOne() {
        $this->qt =& new DB_QT(TABLE_USER);
        $this->qt->table = TABLE_USER;
        //$this->qt->primaryCol = 'id';

        $newData1 = $this->_getSampleData(1);
        $id      = $this->qt->add($newData1);
        $this->assertTrue($id != false);

        $newData2 = $this->_getSampleData(2);
        $id      = $this->qt->add($newData2);
        $this->assertTrue($id != false);

        $this->qt->setSelect('name');
        $this->assertEqual($newData1['name'], $this->qt->getOne());
    }

    // test if column==table works, using the table TABLE_QUESTION
    function test_tableEqualsColumn() {
        unset($this->qt);
        $this->qt =& new DB_QT(TABLE_QUESTION);
        //$this->qt->table = TABLE_QUESTION;
        
        $newData  = array(TABLE_QUESTION => 'Why does this not work?');
        $id       = $this->qt->add($newData);
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
        // we can't do this:
        $this->assertEqual(array($newData), $this->qt->getAll());
        //$this->assertEqual($newData, $data[0]);
    }

    // test if column==table works, using the table TABLE_QUESTION
    // this fails in v0.9.3
    // a join makes it fail!!!, the tests above are just convenience tests
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
        // assertEquals doesn't sort arrays recursively, so we have to extract the data :-(
        // we can't do this:     $this->assertEquals(array($newData),$question->getAll());
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