<?php

namespace biologis\HV\HealthRecordItem;

use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItemFactory;
use QueryPath\Query;

/**
 * Contact Thing
 * @see http://developer.healthvault.com/pages/sdk/docs/urn.com.microsoft.wc.thing.emotion.1.html
 */
class EmotionalState extends HealthRecordItemData
{
    protected $when = null;
    protected $mood = null;
    protected $stress = null;
    protected $wellbeing = null;


    public function __construct(Query $qp) {
        //call the parents constructor
        parent::__construct($qp);

        //Point query-path at data-xml
        $qpRecord = $qp->top()->find("data-xml");

        //If the data exists search for the date
        if ($qpRecord) {
            $text = $qp->top()->find("when date y")->text();

            //If a date exists, create a timestamp
            if (!empty($text))
                $this->when = $this->getTimestamp("when");

            //insert data from xml to array
            $this->mood = $qp->top()->find("mood")->text();
            $this->stress = $qp->top()->find("stress")->text();
            $this->wellbeing= $qp->top()->find("wellbeing")->text();
        }
    }

    /**
     * Creates a Health Vault Emotional State XML Item.
     * @param $when
     * @param null $mood
     * @param null $stress
     * @param null $wellbeing
     * @return mixed
     */
    public static function createFromData($when, $mood = null, $stress = null, $wellbeing = null)
    {
        //Create the emotional state from factory
        $emotionalState = HealthRecordItemFactory::getThing('Emotional State');

        // Save the time
        $emotionalState->setTimestamp('when', $when);

        // Add item or remove node if value is empty
        $emotionalState->removeOrUpdateIfEmpty( "mood", $mood);
        $emotionalState->removeOrUpdateIfEmpty( "stress", $stress);
        $emotionalState->removeOrUpdateIfEmpty( "wellbeing", $wellbeing);

        //return the object
        return $emotionalState;
    }

    /** Return's a json array of emotional states
     * @return array
     */
    public function getItemJSONArray()
    {
        //Get data consistent between all HV objects
        $parentData = parent::getItemJSONArray();

        //Get data is that only relevant to emotional state
        $myData = array(
            "when" => $this->when,
            "mood" => $this->mood,
            "stress" => $this->stress,
            "wellbeing" => $this->wellbeing
        );

        //return the merged array.
        return array_merge($myData, $parentData);
    }
}
