<?php

namespace biologis\HV\HealthRecordItem;

use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItemFactory;

/**
 * Sleep Related Activity Thing
 * @see http://developer.healthvault.com/pages/sdk/docs/urn.com.microsoft.wc.thing.contact.1.html
 */
class SleepRelatedActivity extends HealthRecordItemData
{

    /**
     * @param $when
     * @param $sleepiness
     * @param array $caffeine
     * @param array $alcohol
     * @param array $nap
     * @param array $exercise
     * @return mixed
     */
    public static function createFromData($when, $sleepiness, $caffeine = array(), $alcohol = array(), $nap = array(), $exercise = array() )
    {
        $sleepRelatedActivity = HealthRecordItemFactory::getThing('Sleep Related Activity');

        return $sleepRelatedActivity;
    }
}
