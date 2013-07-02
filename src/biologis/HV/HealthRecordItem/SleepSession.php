<?php

namespace biologis\HV\HealthRecordItem;

use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItemFactory;

/**
 * Sleep Session
 * @see http://developer.healthvault.com/pages/sdk/docs/urn.com.microsoft.wc.thing.sjam.1.html
 */
class SleepSession extends HealthRecordItemData
{

    /**
     * @param $when Required time(). The date and time that the journal entry refers to.
     * @param $bedTime Required time(). The time the person went to bed.
     * @param $wakeTime Required time(). The time the person woke up for a period of activity.
     * @param $sleepMinutes Integer. The number of minutes slept.
     * @param $settlingMinutes Integer. The number of minutes it took to fall asleep.
     * @param $wakeState An evaluation of how the person felt when they got up in the morning. 1 = Wide awake,2 = Awake but a little tired,3 = Sleepy
     * @param array of Awakening $awakening
     * @param array of CodableValue $medications
     * @return SleepRelatedActivity
     */
    public static function createFromData($when, $bedTime, $wakeTime, $sleepMinutes, $settlingMinutes, $wakeState,
                                          $awakening = null, $medications = null)
    {
        /**
         * @var $sleepRelatedActivity SleepRelatedActivity
         */
        $sleepSession = HealthRecordItemFactory::getThing('Sleep Session');

        $sleepSession->setTimestamp('when', $when);

        $sleepSession->setTime($sleepSession->getQp()->top()->find('bed-time'), $bedTime);
        $sleepSession->setTime($sleepSession->getQp()->top()->find('wake-time'), $wakeTime);
        $sleepSession->getQp()->top()->find('sleep-minutes')->text($sleepMinutes);
        $sleepSession->getQp()->top()->find('settling-minutes')->text($settlingMinutes);
        $sleepSession->getQp()->top()->find('wake-state')->text($wakeState);


        // Loop through arrays adding items.
        $settlingNode = $sleepSession->getQp()->top()->find("settling-minutes");
        if ( !empty($medications) )
        {
            foreach ($medications as $item)
            {
                // Insert the XML
                $settlingNode->after("<medications>". $item->getItemXML() . "</medications>");
            }
        }


        if ( !empty($awakening)  )
        {
            foreach ($awakening as $item)
            {
                // Insert the XML
                $settlingNode->after("<awakening>". $item->getItemXML() . "</awakening>");
            }
        }

        return $sleepSession;
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
