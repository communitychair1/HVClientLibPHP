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

    //Epoch timestamp of when the assessment was taken
    protected $when = null;

    //A codeable object to represent the question
    protected $question = null;

    //Array of codeable objects of answer choices
    protected $answerChoice = null;

    //Array of codeable objects of answers
    protected $answer = null;

    /**
     * @param Query $qp
     */
    public function __construct(Query $qp)
    {
        parent::__construct($qp);

        //Grab the data-xml section fo the record
        $recordQp = $qp->find('data-xml');
        //Check for a timestamp and set the when based on the timestamp
        $txt = $recordQp->find("data-xml question-answer when")->text();
        if ( !empty($txt) )
        {
            //Set the when from the XML timestamp
            $this->when = $this->getTimestamp('data-xml question-answer when');
        }

        //Create the question codeable object from the XML
        $this->question = CodableValue::createFromXML($recordQp->top()->branch('question'));

        //Loop through the answers and create codeable values based on it
        $answers = $qp->top()->find('question-answer > answer');
        if ( !empty($answers))
        {
            foreach($answers as $answer)
            {
                $this->answer[] = CodableValue::createFromXML($answer);
            }
        }

        //Loop through the answer choices and create codeable objects from the XML
        $answerChoices = $qp->top()->find('question-answer > answer-choice');
        if ( !empty($answerChoices))
        {
            foreach($answerChoices as $answerChoice)
            {
                $this->answerChoice[] = CodableValue::createFromXML($answerChoice);
            }
        }
    }

    /**
     * @param $when
     * @param CodableValue $question
     * @param array $answerChoices - array of codable values
     * @param array $answers - array of codable values
     * @param Common $common - Common Block
     * @return Question Answer Object
     */
    public static function createFromData(
        $when,
        CodableValue $question,
        array  $answerChoices = null,
        array $answers = null,
        Common $common = null
    ){
        //Make a base QA Object
        $questionAnswer = HealthRecordItemFactory::getThing('Question Answer');

        //Set the common block
        $questionAnswer->setCommon($common);

        //Set the object's member variables
        $questionAnswer->when = $when;
        $questionAnswer->question = $question;
        $questionAnswer->answer = $answers;
        $questionAnswer->answerChoice = $answerChoices;

        //Set the timestamp in the XML
        $questionAnswer->setTimestamp('question-answer>when', $when);
        $questionAnswer->getQp()->find("question-answer>question")->xml($question->getObjectXml());

        //If answer choices have been passed in, add them to the xml
        if(!is_null($answerChoices))
        {
            foreach ($answerChoices as $answerChoice)
            {
                $questionAnswer->getQp()->top()->find('data-xml question-answer')
                    ->append("<answer-choice>" . $answerChoice->getObjectXml() . "</answer-choice>");
            }
        }

        //If answers have been passed in, add them to the xml
        if(!is_null($answers))
        {
            foreach ($answers as $answer)
            {
                $questionAnswer->getQp()->top()->find('data-xml question-answer')
                    ->append("<answer>" . $answer->getObjectXml() . "</answer>");
            }
        }

        //Return the question object
        return $questionAnswer;
    }

    /**
     * @return array Representation of the QA Object
     */
    public function getItemJSONArray()
    {
        $parentData = parent::getItemJSONArray();

        $myData = array(
            "timestamp" => $this->when,
            "question" => $this->question->getItemJSONArray()
        );

        //If there are answer choices, grab the JSON Arrays and append them to the return array
        if ( !empty($this->answerChoice))
        {
            foreach($this->answerChoice as $answerChoice)
            {
                $myData["answer-choice"][] = $answerChoice->getItemJSONArray();
            }
        }

        //If there are answers, grab the JSON Arrays and append them to the return array
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