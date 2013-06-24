<?php

namespace biologis\HV\HealthRecordItem;

use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItemFactory;
use biologis\HV\HealthRecordItem\SleepRelatedActivity\Activity;

/**
 * Sleep Related Activity Thing
 * @see http://developer.healthvault.com/pages/sdk/docs/urn.com.microsoft.wc.thing.sjpm.1.html
 */
class SleepRelatedActivity extends HealthRecordItemData
{

    /**
     * @param $when
     * @param $sleepiness 1 = So sleepy had to struggle to stay awake during much of the day,2 = Somewhat tired, 3 = Fairly alert, 4 = Wide awake
     * @param array $caffeine timestamp
     * @param array $alcohol timestamp
     * @param array $naps Activity
     * @param array $exercises Activity
     * @return mixed
     */
    public static function createFromData($when, $sleepiness, $caffeine = array(), $alcohol = array(), $naps = array(), $exercises = array() )
    {
        /**
         * @var $sleepRelatedActivity SleepRelatedActivity
         */
        $sleepRelatedActivity = HealthRecordItemFactory::getThing('Sleep Related Activity');

        $sleepRelatedActivity->setTimestamp('when', $when);
        $sleepRelatedActivity->getQp()->find('sleepiness')->text($sleepiness);

        // Save ref to parent node so we can append new nodes
        $parentNode = $sleepRelatedActivity->qp->top()->find("when");

        // Loop through arrays adding items.
        foreach ($exercises as $item)
        {
            // Should be a time, so just add it.
            $sleepRelatedActivity->addActivity($parentNode, "exercise", $item);
        }

        foreach ($naps as $item)
        {
            // Should be a time, so just add it.
            $sleepRelatedActivity->addActivity($parentNode, "nap", $item);
        }

        foreach ($alcohol as $tstamp)
        {
            // Should be a time, so just add it.
            $sleepRelatedActivity->addTime($parentNode, "alcohol", $tstamp);
        }

        foreach ($caffeine as $tstamp)
        {
            // Should be a time, so just add it.
            $sleepRelatedActivity->addTime($parentNode, "caffeine", $tstamp);
        }

        return $sleepRelatedActivity;
    }


    /**
     * Helper function to add the necessary XML for Activity
     * @param $parent
     * @param int $nodeName
     * @param $tstamp
     */
    public function addTime($parent, $nodeName, $tstamp)
    {
        $parent->after("<$nodeName>" . date('<\h>H</\h><\m>i</\m><\s>s</\s><\f>0</\f>', $tstamp) . "</$nodeName>");
    }

    /**
     * Helper function to create the necessary XML for the Activity
     * @param $parent
     * @param $nodeName
     * @param Activity $activity
     */
    public function addActivity($parent, $nodeName, Activity $activity)
    {
        $xml = "<$nodeName><when>"
                . date('<\h>H</\h><\m>i</\m><\s>s</\s><\f>0</\f>', $activity->when)
                . "</when><minutes>"
                . $activity->minutes
                . "</minutes></$nodeName>";
        $parent->after($xml);
    }


}