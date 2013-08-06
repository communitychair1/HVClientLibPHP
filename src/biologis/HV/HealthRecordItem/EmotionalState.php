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
    protected $relatedThingId = null;
    protected $relatedThingVersion = null;
    protected $relatedThingRealationship = null;
    protected $source = null;


    public function __construct(Query $qp) {
        parent::__construct($qp);
        $recordQp = $qp->top()->find("data-xml");
        $commonQp = $qp->find('common');

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

        //Populate the relationship stats from the HV XML
        if($recordQp->find("common related-thing thing-id")->text())
        {
            $this->relatedThingId = $commonQp->find("related-thing thing-id")->text();
        }
        if($recordQp->find("common related-thing version-stamp")->text())
        {
            $this->relatedThingVersion = $commonQp->find("related-thing version-stamp")->text();
        }
        if($recordQp->find("common related-thing relationship-type")->text())
        {
            $this->relatedThingRealationship = $commonQp->find("related-thing relationship-type")->text();
        }
        if($recordQp->find("common source")->text())
        {
            $this->source = $commonQp->find("source")->text();
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
        array $common = null
    )
    {
        /**
         * @var $emotionalState EmotionalState
         */
        $emotionalState = HealthRecordItemFactory::getThing('Emotional State');
        $emotionalState = parent::createFromData($common, $emotionalState);

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
            "wellbeing" => $this->wellbeing,
            "relatedThingId" => $this->relatedThingId,
            "relatedThingVersion" => $this->relatedThingVersion,
            "relatedThingRelationship" => $this->relatedThingRealationship
        );
        if(isset($this->source))
        {
            $myData['source'] = $this->source;
        }
        return array_merge($myData, $parentData);
    }
}
