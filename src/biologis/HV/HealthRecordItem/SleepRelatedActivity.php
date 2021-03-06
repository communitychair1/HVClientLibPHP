<?php

namespace biologis\HV\HealthRecordItem;

use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItemFactory;
use biologis\HV\HealthRecordItem\SleepRelatedActivity\Activity;
use Mentis\BaseBundle\Services\MentisLogger;
use QueryPath\Query;

/**
 * Sleep Related Activity Thing
 * @see http://developer.healthvault.com/pages/sdk/docs/urn.com.microsoft.wc.thing.sjpm.1.html
 */
class SleepRelatedActivity extends HealthRecordItemData
{

    protected $when = null;
    protected $caffeine = null;
    protected $alcohol = array();
    protected $nap = null;
    protected $exercise = array();
    protected $sleepiness = null;

    /**
     * @param Query Path of the object
     */
    public function __construct(Query $qp)
    {
        parent::__construct($qp);
        $recordQp = $qp->find('data-xml');
        $this->when = $this->getTimestamp('sleep-pm>when');


        //Populate Exercise Data from HV
        $exerciseBranch = $recordQp->branch('exercise');
        $index = 0;
        foreach ($exerciseBranch as $exerciseEntry) {
            $this->exercise[$index]['when'] =
                $this->populateTimeData($exerciseEntry->branch('when'), $this->qp->find('sleep-pm>when'));
            $this->exercise[$index]['minutes'] =
                $exerciseEntry->Branch('minutes')->text();
            $index++;
        }
        $exerciseBranch = null;

        //Populate Caffeine Data from HV
        $caffeineBranch = $recordQp->branch('caffeine');
        $this->caffeine =
            $this->populateTimeData($caffeineBranch, $this->qp->find('sleep-pm>when'));
        $caffeineBranch = null;

        //Populate Alcohol Data from HV
        $alcoholBranch = $recordQp->branch('alcohol');
        $this->alcohol =
            $this->populateTimeData($alcoholBranch, $this->qp->find('sleep-pm>when'));
        $alcoholBranch = null;

        //Populate Nap Data from HV
        $napBranch = $recordQp->branch('nap');
        $index = 0;
        foreach ($napBranch as $napEntry) {
            $this->nap[$index]['when'] =
                $this->populateTimeData($napEntry->branch('when'), $this->qp->find('sleep-pm>when'));
            $this->nap[$index]['minutes'] =
                $napEntry->Branch('minutes')->text();
            $index++;
        }
        $napEntry = null;

        //Populate TimeStamp Data from HV
        $this->when = $this->getTimestamp("sleep-pm>when");

        //Populate the Sleepiness Data from HV
        $this->sleepiness = $recordQp->find("sleep-pm sleepiness")->text();
    }

    /**
     * @param $when
     * @param $sleepiness 1 = So sleepy had to struggle to stay awake during much of the day,2 = Somewhat tired, 3 = Fairly alert, 4 = Wide awake
     * @param array $caffeine timestamp
     * @param array $alcohol timestamp
     * @param array $naps Activity
     * @param array $exercises Activity
     * @return mixed
     */

    public static function createFromData(
        $when,
        $sleepiness,
        $caffeine = array(),
        $alcohol = array(),
        $naps = array(),
        $exercises = array(),
        $common = null
    )
    {
        /**
         * @var $sleepRelatedActivity SleepRelatedActivity
         */
        $sleepRelatedActivity = HealthRecordItemFactory::getThing('Sleep Related Activity');
        $sleepRelatedActivity->setCommon($common);

        $sleepRelatedActivity->setTimestamp('sleep-pm>when', $when);

        if (!(is_null($sleepiness))) {
            $sleepRelatedActivity->getQp()->find('sleepiness')->text($sleepiness);
        }

        // Save ref to parent node so we can append new nodes
        $parentNode = $sleepRelatedActivity->qp->top()->find("sleep-pm>when");

        // Loop through arrays adding items.
        if (!(is_null($exercises))) {
            foreach ($exercises as $item) {
                // Should be a time, so just add it.
                $sleepRelatedActivity->addActivity($parentNode, "exercise", $item);
            }
        }

        if (!(is_null($naps))) {
            foreach ($naps as $item) {
                // Should be a time, so just add it.
                $sleepRelatedActivity->addActivity($parentNode, "nap", $item);
            }
        }
        if (!(is_null($alcohol)) && is_array($alcohol) || is_object($alcohol)) {
            foreach ($alcohol as $tstamp) {
                // Should be a time, so just add it.
                $sleepRelatedActivity->addTime($parentNode, "alcohol", $tstamp);
            }
        }else if (!(is_null($alcohol))){
            $sleepRelatedActivity->addTime($parentNode, "alcohol", $alcohol);
        }

        if (!(is_null($caffeine)) && is_array($caffeine) || is_object($caffeine)) {
            foreach ($caffeine as $tstamp) {
                // Should be a time, so just add it.
                $sleepRelatedActivity->addTime($parentNode, "caffeine", $tstamp);
            }
        }else if (!(is_null($caffeine))){
            $sleepRelatedActivity->addTime($parentNode, "caffeine", $caffeine);
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

    /**
     * @return array
     *      Returns JSON formatted array of
     *      Sleep related activities.
     */
    public function getItemJSONArray()
    {
        $parentData = parent::getItemJSONArray();

        $myData = array(
            "timestamp" => $this->when,
            "caffeine" => $this->caffeine,
            "alcohol" => $this->alcohol,
            "nap" => $this->nap,
            "exercise" => $this->exercise,
            "sleepiness" => $this->sleepiness
        );
        return array_merge($myData, $parentData);
    }


}
