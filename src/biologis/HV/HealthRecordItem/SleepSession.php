<?php

namespace biologis\HV\HealthRecordItem;

use biologis\HV\HealthRecordItem\GenericTypes\CodableValue;
use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItem\SleepSession\Awakening;
use biologis\HV\HealthRecordItemFactory;
use QueryPath\Query;
use QueryPath;

/**
 * Sleep Session
 * @see http://developer.healthvault.com/pages/sdk/docs/urn.com.microsoft.wc.thing.sjam.1.html
 */
class SleepSession extends HealthRecordItemData
{

    protected $when = null;
    protected $bedTime = null;
    protected $wakeTime = null;
    protected $sleepMinutes = null;
    protected $settlingMinutes = null;
    protected $wakeState = null;
    protected $awakenings = null;
    protected $medications = null;

    /**
     * @param Query Path of the object
     */
    public function __construct(Query $qp) {
        parent::__construct($qp);

        $recordQp = $qp->find('data-xml');
        $txt = $recordQp->find("data-xml>sleep-am>when")->text();
        if ( !empty($txt) )
        {
            $this->when = $this->getTimestamp('data-xml>sleep-am>when');
        }

        $this->bedTime = $this->getTimestamp('data-xml bed-time');
        $this->wakeTime = $this->getTimestamp('data-xml wake-time');
        $this->sleepMinutes = $recordQp->find('wake-time')->text();
        $this->sleepMinutes = $recordQp->find('sleep-minutes')->text();
        $this->settlingMinutes = $recordQp->find('settling-minutes')->text();
        $this->wakeState = $recordQp->find('wake-state')->text();

        $txt = $recordQp->find("data-xml medications")->text();

        if (!empty($txt))
        {
            $this->medications = CodableValue::createFromXML($recordQp->top()->find('data-xml medications'));
        }

        $items= $recordQp->top()->find('data-xml awakening');
        foreach ($items as $index=>$qpItem)
        {
            $this->awakenings[] = Awakening::createFromXML($qpItem);
        }
    }


    /**
     * @param $when Required time(). The date and time that the journal entry refers to.
     * @param $bedTime Required time(). The time the person went to bed.
     * @param $wakeTime Required time(). The time the person woke up for a period of activity.
     * @param $sleepMinutes Integer. The number of minutes slept.
     * @param $settlingMinutes Integer. The number of minutes it took to fall asleep.
     * @param $wakeState An evaluation of how the person felt when they got up in the morning. 1 = Wide awake,2 = Awake but a little tired,3 = Sleepy
     * @param array of Awakening $awakening
     * @param array of CodableValue $medications
     * @return SleepSession
     */
    public static function createFromData($when, $bedTime, $wakeTime, $sleepMinutes, $settlingMinutes, $wakeState,
                                          $awakening = null, $medications = null)
    {
        /**
         * @var $sleepSession SleepSession
         */
        $sleepSession = HealthRecordItemFactory::getThing('Sleep Session');


        // Save member access
        $sleepSession->when  =$when;
        $sleepSession->bedTime = $bedTime;
        $sleepSession->wakeTime = $wakeTime;
        $sleepSession->sleepMinutes = $sleepMinutes;
        $sleepSession->settlingMinutes = $settlingMinutes;
        $sleepSession->wakeState= $wakeState;
        $sleepSession->awakenings = $awakening;
        $sleepSession->medications = $medications;

        $sleepSession->setTimestamp('sleep-am>when', $when);
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
                $xml = $item->getQp()->innerXml();
                $settlingNode->after( "<medications>" . $xml . "</medications>" );
            }
        }


        if ( !empty($awakening)  )
        {
            foreach ($awakening as $item)
            {
                // Insert the XML
                $settlingNode->after($item->getItemXML("awakening"));
            }
        }

        return $sleepSession;
    }

    public function getItemJSONArray()
    {
        $parentData = parent::getItemJSONArray();

        $myData = array(
            "when" => $this->when,
            "bedTime" => $this->bedTime,
            "wakeTime" => $this->wakeTime,
            "sleepMinutes" => $this->sleepMinutes,
            "settlingMinutes" => $this->settlingMinutes,
            "wakeState" => $this->wakeState
        );

        // Loop over arrays and get their JSON data.
        if (!empty($this->awakenings))
        {
            foreach($this->awakenings as $index=>$awakening)
            {
                $myData["awakenings"][] = $awakening->getItemJSONArray();
            }
        }
        if ( !empty($this->medications))
        {
            $myData["medications"] = $this->medications->getItemJSONArray();
        }

        return array_merge($myData, $parentData);
    }
}
