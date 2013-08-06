<?php

namespace biologis\HV\HealthRecordItem;

use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItemFactory;
use QueryPath\Query;


class Exercise extends HealthRecordItemData
{
    protected $when = null;
    protected $title = "";
    protected $distance = null;
    protected $distanceDisplay = null;
    protected $distDisplayUnits = '';
    protected $duration = null;
    protected $activity = '';

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
        $this->distance = $recordQp->find("distance>m")->text();

        //Populate Distance display field
        $this->distanceDisplay = $recordQp->find("distance>display")->text();

        //Populate Distance Units
        $txt = $recordQp->find("distance value display")->attr("units");
        if(!empty($txt))
        {
            $this->distDisplayUnits = $recordQp->find("distance value display")->attr("units");
        }
        else
        {
            $this->distDisplayUnits = 'm';
        }

        //Populate Duration field
        $this->duration = $recordQp->find("duration")->text();

        //Populate the activity field
        $this->activity = $recordQp->find("activity>text")->text();

    }

    public static function  CreateFromData(
        $when = null,
        $title = "",
        $distance = null,
        $distanceDisplay = null,
        $duration = null,
        $activity = '',
        $distDisplayUnits = 'meters'
    )
    {
        /**
         * @var $exercise Exercise
         */
        //Create the object from data
        $exercise = HealthRecordItemFactory::getThing('Exercise');
        $exercise->setTimestamp('when', $when);
        $exercise->removeOrUpdateIfEmpty('title', $title);
        $exercise->removeOrUpdateIfEmpty('distance m', $distance);
        $exercise->removeOrUpdateIfEmpty('distance display', $distanceDisplay);
        $exercise->getQp()->find('distance display')->attr('units', $distDisplayUnits);
        $exercise->removeOrUpdateIfEmpty('duration', $duration);
        $exercise->removeOrUpdateIfEmpty('activity text', $activity);

        return $exercise;
    }


    public function getItemJSONArray(){
        // add Timestamp and version to the array
        $parentData = parent::getItemJSONArray();

        // add all exercise data to the array
        $exerciseData = array(
            "timestamp" => $this->when,
            "title" => $this->title,
            "distance" => $this->distance,
            "distanceDisplay" => $this->distanceDisplay,
            "distanceDisplayUnits" => $this->distDisplayUnits,
            "duration" => $this->duration,
            "activity" => $this->activity
        );

        //merge the array's and return them
        return array_merge($exerciseData, $parentData);
    }

}