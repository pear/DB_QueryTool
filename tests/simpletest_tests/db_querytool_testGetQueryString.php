<?php
// $Id$

require_once dirname(__FILE__).'/db_querytool_test_base.php';


class TestOfDB_QueryTool_GetQueryString extends TestOfDB_QueryTool
{
    function TestOfDB_QueryTool_GetQueryString($name = __CLASS__) {
        $this->UnitTestCase($name);
    }
    function test_selectAll() {
        $this->qt =& new DB_QT(TABLE_QUESTION);
        $expected = 'SELECT '.$this->qt->_quoteIdentifier(TABLE_QUESTION).'.'.$this->qt->_quoteIdentifier('id').' AS '.$this->qt->_quoteIdentifier('id')
                   .','.$this->qt->_quoteIdentifier(TABLE_QUESTION).'.'.$this->qt->_quoteIdentifier('question').' AS '.$this->qt->_quoteIdentifier('question')
                   .' FROM '.$this->qt->_quoteIdentifier(TABLE_QUESTION);
        $this->assertEqual($expected, $this->qt->getQueryString());
    }
    function test_selectWithWhere() {
        $this->qt =& new DB_QT(TABLE_QUESTION);
        $this->qt->setWhere('id=1');
        if (DB_TYPE == 'ibase') {
            $expected = 'SELECT '.TABLE_QUESTION.'.id AS id,'
                        .TABLE_QUESTION.'.question AS question'
                        .' FROM '.TABLE_QUESTION
                        .' WHERE id=1';
        } else {
            $expected = 'SELECT '.$this->qt->_quoteIdentifier(TABLE_QUESTION).'.'.$this->qt->_quoteIdentifier('id').' AS '.$this->qt->_quoteIdentifier('id')
                       .','.$this->qt->_quoteIdentifier(TABLE_QUESTION).'.'.$this->qt->_quoteIdentifier('question').' AS '.$this->qt->_quoteIdentifier('question')
                       .' FROM '.$this->qt->_quoteIdentifier(TABLE_QUESTION).' WHERE id=1';
        }
        $this->assertEqual($expected, $this->qt->getQueryString());
    }
    function test_selectWithJoin() {
        $this->qt =& new DB_QT(TABLE_QUESTION);
        $joinOn = TABLE_QUESTION.'.id='.TABLE_ANSWER.'.question_id';
        $this->qt->setJoin(TABLE_ANSWER, $joinOn, 'left');

        if (DB_TYPE == 'ibase') {
            $expected = 'SELECT '.TABLE_ANSWER.'.id AS t_'.TABLE_ANSWER.'_id,'
                        .TABLE_ANSWER.'.answer AS t_'.TABLE_ANSWER.'_answer,'
                        .TABLE_ANSWER.'.question_id AS t_'.TABLE_ANSWER.'_question_id,'
                        .TABLE_QUESTION.'.id AS id,'
                        .TABLE_QUESTION.'.question AS question'
                        .' FROM '.TABLE_QUESTION
                        .' LEFT JOIN '.TABLE_ANSWER.' ON '.$joinOn;
        } else {
            $expected = 'SELECT '.$this->qt->_quoteIdentifier(TABLE_ANSWER).'.'.$this->qt->_quoteIdentifier('id').' AS '.$this->qt->_quoteIdentifier('_answer_id')
                       .','.$this->qt->_quoteIdentifier(TABLE_ANSWER).'.'.$this->qt->_quoteIdentifier('answer').' AS '.$this->qt->_quoteIdentifier('_answer_answer')
                       .','.$this->qt->_quoteIdentifier(TABLE_ANSWER).'.'.$this->qt->_quoteIdentifier('question_id').' AS '.$this->qt->_quoteIdentifier('_answer_question_id')
                       .','.$this->qt->_quoteIdentifier(TABLE_QUESTION).'.'.$this->qt->_quoteIdentifier('id').' AS '.$this->qt->_quoteIdentifier('id')
                       .','.$this->qt->_quoteIdentifier(TABLE_QUESTION).'.'.$this->qt->_quoteIdentifier('question').' AS '.$this->qt->_quoteIdentifier('question')
                       .' FROM '.$this->qt->_quoteIdentifier(TABLE_QUESTION)
                       .' LEFT JOIN '.$this->qt->_quoteIdentifier(TABLE_ANSWER)
                       .' ON '.$joinOn;
        }
        $this->assertEqual($expected, $this->qt->getQueryString());
    }
    function test_selectOneColumn() {
        $this->qt =& new DB_QT(TABLE_QUESTION);
        $this->qt->setWhere('id=1');
        $this->qt->setSelect('id');
        $expected = 'SELECT '.$this->qt->_quoteIdentifier('id')
                   .' FROM '.$this->qt->_quoteIdentifier(TABLE_QUESTION)
                   .' WHERE id=1';
        $this->assertEqual($expected, $this->qt->getQueryString());
    }
    function test_selectTwoColumns() {
        $this->qt =& new DB_QT(TABLE_QUESTION);
        $this->qt->setWhere('id=1');
        $this->qt->setSelect('id,answer');
        $expected = 'SELECT '.$this->qt->_quoteIdentifier('id')
                    .','.$this->qt->_quoteIdentifier('answer')
                    .' FROM '.$this->qt->_quoteIdentifier(TABLE_QUESTION)
                    .' WHERE id=1';
        $this->assertEqual($expected, $this->qt->getQueryString());
    }
    function test_prependTableName() {
        $this->qt =& new DB_QT(TABLE_QUESTION);
        $table = TABLE_QUESTION;

        $fieldlist = 'question';
        $actual = $this->qt->_prependTableName($fieldlist, TABLE_QUESTION);
        $expected = $this->qt->_quoteIdentifier(TABLE_QUESTION).'.'.$this->qt->_quoteIdentifier('question');
        $this->assertEqual($actual, $expected);

        $fieldlist = 'fieldname1,question';
        $actual = $this->qt->_prependTableName($fieldlist, TABLE_QUESTION);
        $expected = 'fieldname1,'.$this->qt->_quoteIdentifier(TABLE_QUESTION).'.'.$this->qt->_quoteIdentifier('question');
        $this->assertEqual($actual, $expected);

        $fieldlist = 'fieldname1,'.TABLE_QUESTION.'.question,fieldname2';
        $actual = $this->qt->_prependTableName($fieldlist, TABLE_QUESTION);
        $expected = 'fieldname1,'.TABLE_QUESTION.'.question,fieldname2';
        $this->assertEqual($actual, $expected);
    }
    function test_quoteIdentifierWithFunctions() {
        $this->qt =& new DB_QT(TABLE_QUESTION);
        $table = TABLE_QUESTION;

        $this->qt->setSelect('question, COUNT(DISTINCT id) AS num_questions');
        $this->qt->setGroup('question');

        if (DB_TYPE == 'ibase') {
            $expected = 'SELECT question,COUNT(DISTINCT id) AS num_questions'
                       .' FROM '.TABLE_QUESTION.'  GROUP BY '.TABLE_QUESTION.'.question';
        } else {
            $expected = 'SELECT '.$this->qt->_quoteIdentifier('question')
                       .',COUNT(DISTINCT id) AS num_questions'
                       .' FROM '.$this->qt->_quoteIdentifier(TABLE_QUESTION)
                       .'  GROUP BY '.$this->qt->_quoteIdentifier(TABLE_QUESTION).'.'.$this->qt->_quoteIdentifier('question');
        }
        $this->assertEqual($expected, $this->qt->getQueryString());
    }
    function test_bug12353() {
        $this->qt =& new DB_QT(TABLE_QUESTION);

        $this->qt->setSelect('_spruch, if(length(_spruch) > 50, concat(left(_spruch, 50), "..."), _spruch) as _kurztext');

        if (DB_TYPE == 'ibase') {
            $expected = 'SELECT _spruch,if(length(_spruch) > 50,concat(left(_spruch,50),"..."),_spruch) AS _kurztext FROM '.TABLE_QUESTION;
        } else {
            $expected = 'SELECT '.$this->qt->_quoteIdentifier('_spruch')
                       .',if(length(_spruch) > 50,concat(left(_spruch,50),"..."),_spruch) AS _kurztext'
                       .' FROM '.$this->qt->_quoteIdentifier(TABLE_QUESTION);
        }
        $this->assertEqual($expected, $this->qt->getQueryString());
    }
}

if (!defined('TEST_RUNNING')) {
    define('TEST_RUNNING', true);
    $test = &new TestOfDB_QueryTool_GetQueryString();
    $test->run(new HtmlReporter());
}
?>