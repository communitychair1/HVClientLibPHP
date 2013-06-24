<?php

namespace biologis\HV\HealthRecordItem;

use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItemFactory;

/**
 * Contact Thing
 * @see http://developer.healthvault.com/pages/sdk/docs/urn.com.microsoft.wc.thing.emotion.1.html
 */
class EmotionalState extends HealthRecordItemData
{

    /**
     *
     * Creates a Health Vault Emotional State XML Item.
     * @return mixed
     *
     */

    public static function createFromData($when, $mood = null, $stress = null, $wellbeing = null)
    {
        /**
         * @var $emotionalState EmotionalState
         */
        $emotionalState = HealthRecordItemFactory::getThing('Emotional State');
        // Save the time
        $emotionalState->setTimestamp('when', $when);
        // Add item or remove node if value is empty
        $emotionalState->removeOrUpdateIfEmpty( "mood", $mood);
        $emotionalState->removeOrUpdateIfEmpty( "stress", $stress);
        $emotionalState->removeOrUpdateIfEmpty( "wellbeing", $wellbeing);
        return $emotionalState;
    }

}
