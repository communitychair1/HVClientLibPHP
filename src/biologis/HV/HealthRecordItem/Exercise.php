<?php

namespace biologis\HV\HealthRecordItem;

use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItemFactory;
use biologis\HV\HealthRecordItem\SleepRelatedActivity\Activity;
use QueryPath\Query;


class Exercise extends HealthRecordItemData
{
    protected $when = null;
    protected $title = "";
    protected $distance = null;
    protected $duration = null;
    protected $activity = null;

    public function __construct(Query $qp)
    {
        //Run the parents constructor
        parent::__construct($qp);

        //point QP at the data
        $recordQp = $qp->find('data-xml');

        //Set the timestamp
        $this->when = $this->getTimestamp('exercise>when');

        //Populate Title field
        $this->title = $recordQp->find("title")->text();

        //Populate Distance field
        $this->distance = $recordQp->find("distance")->text();

        //Populate Duration field
        $this->duration = $recordQp->find("duration")->text();

        //Populate the activity field
        $this->activity = $recordQp->find("activity")->text();

    }

    public static function  CreateFromData(
        $when = null,
        $title = "",
        $distance = null,
        $duration = null,
        $activity = null
    )
    {
        /**
         * @var $exercise Exercise
         */
        //Create the object from data
        $exercise = HealthRecordItemFactory::getThing('Exercise');
        $exercise->removeOrUpdateIfEmpty('when', $when);
        $exercise->removeOrUpdateIfEmpty('title', $title);
        $exercise->removeOrUpdateIfEmpty('distance', $distance);
        $exercise->removeOrUpdateIfEmpty('duration', $duration);
        $exercise->removeOrUpdateIfEmpty('activity', $activity);
    }


    public function getItemJSONArray(){
        // add Timestamp and version to the array
        $parentData = parent::getItemJSONArray();

        // add all exercise data to the array
        $exerciseData = array(
            "when" => $this->when,
            "title" => $this->title,
            "distance" => $this->distance,
            "duration" => $this->duration,
            "activity" => $this->activity
        );

        //merge the array's and return them
        return array_merge($exerciseData, $parentData);
    }

}