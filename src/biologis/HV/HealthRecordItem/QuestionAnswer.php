<?php
/**
 * Created by JetBrains PhpStorm.
 * User: tylerroussos
 * Date: 9/27/13
 * Time: 8:59 AM
 * To change this template use File | Settings | File Templates.
 */

namespace biologis\HV\HealthRecordItem;

use biologis\HV\HealthRecordItem\GenericTypes\CodableValue;
use biologis\HV\HealthRecordItem\GenericTypes\Common;
use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItemFactory;
use QueryPath\Query;
use QueryPath;
use Exception;


class QuestionAnswer extends HealthRecordItemData
{

    protected $when = null;
    protected $question = null;
    protected $answerChoice = null;
    protected $answer = null;

    public function __construct(Query $qp)
    {
        parent::__construct($qp);

        //Grab the data-xml section fo the record
        $recordQp = $qp->find('data-xml');
        //Check for a timestamp and set the when based on the timestamp
        $txt = $recordQp->find("data-xml question-answer when")->text();
        if ( !empty($txt) )
        {
            $this->when = $this->getTimestamp('data-xml question-answer when');
        }

        $this->question = CodableValue::createFromXML($recordQp->top()->branch('question'));

        $answers = $qp->top()->find('question-answer > answer');
        if ( !empty($answers))
        {
            foreach($answers as $answer)
            {
                $this->answer[] = CodableValue::createFromXML($answer);
            }
        }

        $answerChoices = $qp->top()->find('question-answer > answer-choice');
        if ( !empty($answerChoices))
        {
            foreach($answerChoices as $answerChoice)
            {
                $this->answerChoice[] = CodableValue::createFromXML($answerChoice);
            }
        }
    }

    public static function createFromData(
        $when,
        CodableValue $question,
        array  $answerChoices = null,
        array $answers = null,
        Common $common = null
    ){
        $questionAnswer = HealthRecordItemFactory::getThing('Question Answer');
        $questionAnswer->setCommon($common);

        $questionAnswer->when = $when;
        $questionAnswer->question = $question;
        $questionAnswer->answer = $answers;
        $questionAnswer->answerChoice = $answerChoices;

        $questionAnswer->setTimestamp('question-answer>when', $when);
        $questionAnswer->getQp()->find("question-answer>question")->xml($question->getObjectXml());

        if(!is_null($answerChoices))
        {
            foreach ($answerChoices as $answerChoice)
            {
                $questionAnswer->getQp()->top()->find('data-xml question-answer')
                    ->append("<answer-choice>" . $answerChoice->getObjectXml() . "</answer-choice>");
            }
        }

        if(!is_null($answers))
        {
            foreach ($answers as $answer)
            {
                $questionAnswer->getQp()->top()->find('data-xml question-answer')
                    ->append("<answer>" . $answer->getObjectXml() . "</answer>");
            }
        }

        return $questionAnswer;
    }

    public function getItemJSONArray()
    {
        $parentData = parent::getItemJSONArray();

        $myData = array(
            "timestamp" => $this->when,
            "question" => $this->question->getItemJSONArray()
        );

        if ( !empty($this->answerChoice))
        {
            foreach($this->answerChoice as $answerChoice)
            {
                $myData["answer-choice"][] = $answerChoice->getItemJSONArray();
            }
        }

        if ( !empty($this->answer))
        {
            foreach($this->answer as $answer)
            {
                $myData["answer"][] = $answer->getItemJSONArray();
            }
        }

        return array_merge($myData, $parentData);
    }
}