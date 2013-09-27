<?php

namespace biologis\HV;

use biologis\HV\HealthRecordItem\GenericTypes\CodableValue;
use biologis\HV\HealthRecordItem\GenericTypes\CodedValue;
use biologis\HV\HealthRecordItem\QuestionAnswer;
use biologis\HV\HVClientBaseTest;

require_once("HVClientBaseTest.php");

class HVQuestionAnswerTest extends HVClientBaseTest
{

    /**
     * Sets everything neccessary for health vault testing
     */
    protected function setUp()
    {
        parent::setUp();
        $this->hv->connect($this->thumbPrint, $this->privateKey);
    }

    /**
     * Tests the set up configuration
     */
    public function testSetUp()
    {
        $this->assertNotNull($this->hv);
    }

    public function testCreateQuestionAnswer()
    {
        $code = CodedValue::createFromData('1','2');
        $question = CodableValue::createFromData('Dose this unit test work?', array());
        $answer1 = CodableValue::createFromData('Answer 1', array($code));
        $answer2 = CodableValue::createFromData('Answer 2', array($code));
        $answerChoice1 = CodableValue::createFromData('Answer Choice 1', array($code));
        $answerChoice2 = CodableValue::createFromData('Answer Choice 2', array($code));


        $qa = QuestionAnswer::createFromData(
            time(),
            $question,
            array($answer1, $answer2),
            array($answerChoice1, $answerChoice2)
        );

        $this->assertNotEmpty($qa, "Question Answer object empty.");

        $xml = $qa->getItemXml();
        $this->assertNotEmpty($xml, "Question Answer itemXml empty");

        $this->hv->putThings($xml, $this->recordId);
        $this->assertNotEmpty($this->hv->getConnector()->getRawResponse(), "No response received from HV");
        $this->assertContains("version", $this->hv->getConnector()->getRawResponse(), "Missing version identifier from response");
    }
}