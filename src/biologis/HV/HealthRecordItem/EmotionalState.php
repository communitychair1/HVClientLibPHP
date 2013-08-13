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
        parent::__construct($qp);
        $recordQp = $qp->top()->find("data-xml");

        if ($recordQp) {
            $text = $qp->top()->find("when date y")->text();
            if (!empty($text))
            {
                $this->when = $this->getTimestamp("when");
            }

            $this->mood = $qp->top()->find("mood")->text();
            $this->stress = $qp->top()->find("stress")->text();
            $this->wellbeing= $qp->top()->find("wellbeing")->text();
        }
    }

    /**
     *
     * Creates a Health Vault Emotional State XML Item.
     * @return mixed
     *
     */
    public static function createFromData(
        $when,
        $mood = null,
        $stress = null,
        $wellbeing = null,
        $common = null
    )
    {
        /**
         * @var $emotionalState EmotionalState
         */
        $emotionalState = HealthRecordItemFactory::getThing('Emotional State');
        $emotionalState->setCommon($common);

        // Save the time
        $emotionalState->setTimestamp('when', $when);
        // Add item or remove node if value is empty
        $emotionalState->removeOrUpdateIfEmpty( "mood", $mood);
        $emotionalState->removeOrUpdateIfEmpty( "stress", $stress);
        $emotionalState->removeOrUpdateIfEmpty( "wellbeing", $wellbeing);

        return $emotionalState;
    }

    public function getItemJSONArray()
    {
        $parentData = parent::getItemJSONArray();

        $myData = array(
            "timestamp" => $this->when,
            "mood" => $this->mood,
            "stress" => $this->stress,
            "wellbeing" => $this->wellbeing
        );

        return array_merge($myData, $parentData);
    }
}
