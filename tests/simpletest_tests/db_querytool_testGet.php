<?php
// $Id$

require_once dirname(__FILE__).'/db_querytool_test_base.php';

class TestOfDB_QueryTool_Get extends TestOfDB_QueryTool
{

    function TestOfDB_QueryTool_Get($name = __CLASS__) {
        $this->UnitTestCase($name);
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

    function test_GetTrimmed() {
        $this->qt = new DB_QT(TABLE_USER);
        $this->qt->table = TABLE_USER;

        $newData = $this->_getSampleData(1);
        $newData['name'] = str_repeat($newData['name'], 10); //exceeds field size
        $newData['address_id'] = 123456789012; //within 11 bytes - more than 11 digits
        $id      = $this->qt->add($newData);
        $this->assertTrue($id != false);

        $newData['id'] = $id;
        $retrieved = $this->qt->get($id);
        $this->assertEqual($newData['name'], $retrieved['name']);
        $this->assertEqual($newData['address_id'], $retrieved['address_id']);
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

        $newData1 = $this->_getSampleData(1);
        $id      = $this->qt->add($newData1);
        $this->assertTrue($id != false);

        $newData2 = $this->_getSampleData(2);
        $id      = $this->qt->add($newData2);
        $this->assertTrue($id != false);

        $this->qt->setSelect('name');
        $this->assertEqual($newData1['name'], $this->qt->getOne());

        $this->qt->setSelect('COUNT(*)');
        $this->assertEqual(2, $this->qt->getOne());
    }

    // test if column==table works, using the table TABLE_QUESTION
    function test_tableEqualsColumn() {
        unset($this->qt);
        $this->qt =& new DB_QT(TABLE_QUESTION);
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
        if (DB_TYPE == 'ibase') {
            $expected =  array(
                't_answer_id'          => $aid,
                't_answer_answer'      => $theAnswer,
                't_answer_question_id' => $qid,
                'id'                   => $qid,
                'question'             => $theQuestion,
            );
        } else {
            $expected =  array(
                '_answer_id'          => $aid,
                '_answer_answer'      => $theAnswer,
                '_answer_question_id' => $qid,
                'id'                  => $qid,
                'question'            => $theQuestion,
            );
        }
        // assertEquals doesn't sort arrays recursively, so we have to extract the data :-(
        // we can't do this:     $this->assertEquals(array($newData),$question->getAll());
        $this->assertEqual($expected, $data[0]);
    }

    function test_innerJoin()
    {
        $theQuestion = 'Why does this not work?';
        $theAnswer   = 'I dont know!';

        $question = new DB_QT(TABLE_QUESTION);
        $question->removeAll();

        $newQuest = array(TABLE_QUESTION => $theQuestion);
        $qid = $question->add($newQuest);

        $answer = new DB_QT(TABLE_ANSWER);
        $answer->removeAll();

        $newAnswer = array(TABLE_QUESTION.'_id' => $qid, TABLE_ANSWER => $theAnswer);
        $aid = $answer->add($newAnswer);

        $question->setJoin(TABLE_ANSWER ,TABLE_QUESTION.'.id = '.TABLE_ANSWER.'.question_id' , 'inner');

        $data = $question->getAll();

        if (DB_TYPE == 'ibase') {
            $expected =  array(
                't_answer_id'          => $aid,
                't_answer_answer'      => $theAnswer,
                't_answer_question_id' => $qid,
                'id'                   => $qid,
                'question'             => $theQuestion,
            );
        } else {
            $expected =  array(
                '_answer_id'          => $aid,
                '_answer_answer'      => $theAnswer,
                '_answer_question_id' => $qid,
                'id'                  => $qid,
                'question'            => $theQuestion,
            );
        }
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
        $question->setSelect('MAX(id),'.TABLE_ANSWER.'.id');
        $expected = 'MAX('.TABLE_QUESTION.'.id)';
        $this->assertTrue(strpos($question->_buildSelectQuery(), $expected));

        // check '(question)'
        $question->setSelect('LOWER(question),'.TABLE_ANSWER.'.*');
        $expected = 'LOWER('.TABLE_QUESTION.'.question)';
        $this->assertTrue(strpos($question->_buildSelectQuery(), $expected));

        // check 'id,'
        $question->setSelect('id, '.TABLE_ANSWER.'.*');
        $this->assertTrue(strpos($question->_buildSelectQuery(), TABLE_QUESTION.'.id'));

        // check 'id as qid'
        $question->setSelect('id as qid, '.TABLE_ANSWER.'.*');
        $expected = $question->_quoteIdentifier(TABLE_QUESTION).'.'.$question->_quoteIdentifier('id').' AS qid';
        $this->assertTrue(strpos($question->_buildSelectQuery(), $expected));

        // check 'id as qid'
        $question->setSelect('LOWER( question ), '.TABLE_ANSWER.'.*');
        $expected = 'LOWER( '.TABLE_QUESTION.'.question )';
        $this->assertTrue(strpos($question->_buildSelectQuery(), $expected));
    }

    /**
     * This method checks if the setJoin() method is working correctly
     *
     * check if stuff like MAX(id), LOWER(question), etc. will be converted to
     *     MAX(TABLE_QUESTION.id), LOWER(TABLE_QUESTION.question)
     * this is done for preventing ambiguous column names, that's why it only applies
     * in joined queries ...
     */
    function test_setJoin()
    {
        $theQuestion = 'Why does this not work?';
        $theAnswer   = 'I dont know!';

        $question = new DB_QT(TABLE_QUESTION);
        $newQuest = array(TABLE_QUESTION => $theQuestion);
        $qid = $question->add($newQuest);
        $this->assertTrue($qid != false);

        $answer    = new DB_QT(TABLE_ANSWER);
        $newAnswer = array(TABLE_QUESTION.'_id' => $qid, TABLE_ANSWER => $theAnswer);
        $aid = $answer->add($newAnswer);
        $this->assertTrue($aid != false);

        $joinOn = TABLE_QUESTION.'.id='.TABLE_ANSWER.'.question_id';
        $question->setJoin(TABLE_ANSWER, $joinOn);

        if (DB_TYPE == 'ibase') {
            $expected =  array(
                't_answer_id'          => $aid,
                't_answer_answer'      => $theAnswer,
                't_answer_question_id' => $qid,
                'id'                   => $qid,
                'question'             => $theQuestion,
            );
        } else {
            $expected =  array(
                '_answer_id'          => $aid,
                '_answer_answer'      => $theAnswer,
                '_answer_question_id' => $qid,
                'id'                  => $qid,
                'question'            => $theQuestion,
            );
        }
        $this->assertEqual($expected, $question->get($qid));
    }

    /**
     * This method checks if the getJoin() method is working correctly
     */
    function test_getJoin()
    {
        $question = new DB_QT(TABLE_QUESTION);
        $joinOn1 = TABLE_QUESTION.'.id='.TABLE_ANSWER.'.question_id';
        $question->setJoin(TABLE_ANSWER, $joinOn1);

        $all = array(
            'default' => array(TABLE_ANSWER => $joinOn1),
        );
        $tables = array(TABLE_ANSWER);
        $right  = array();
        $left   = array();

        $this->assertEqual($all,    $question->getJoin());
        $this->assertEqual($tables, $question->getJoin('tables'));
        $this->assertEqual($right,  $question->getJoin('right'));
        $this->assertEqual($left,   $question->getJoin('left'));

        //--------------------------------------------------------

        $joinOn2 = TABLE_USER.'.id='.TABLE_ANSWER.'.question_id';
        $question->setRightJoin(TABLE_USER, $joinOn2);

        $all = array(
            'default' => array(TABLE_ANSWER => $joinOn1),
            'right'   => array(TABLE_USER   => $joinOn2),
        );
        $tables = array(TABLE_ANSWER, TABLE_USER);
        $right  = array(TABLE_USER => $joinOn2);
        $left   = array();

        $this->assertEqual($all,    $question->getJoin());
        $this->assertEqual($tables, $question->getJoin('tables'));
        $this->assertEqual($right,  $question->getJoin('right'));
        $this->assertEqual($left,   $question->getJoin('left'));
    }
}

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new TestOfDB_QueryTool_Get();
    $test->run(new HtmlReporter());
}
?>