<?php

namespace biologis\HV\HealthRecordItem;

use biologis\HV\HealthRecordItem\GenericTypes\CodableValue;
use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItemFactory;
use QueryPath\Query;
use QueryPath;
use Exception;


class BodyComposition extends HealthRecordItemData
{

    protected $when = null;
    protected $measurementName = null;
    protected $measurementValue = null;
    protected $measurementPercentage = null;
    protected $mesaurementDisplayUnits = null;
    protected $mesaurementDisplayValue = null;

    public function __construct(Query $qp) {
        parent::__construct($qp);

        //Grab the data-xml section fo the record
        $recordQp = $qp->find('data-xml');
        $commonQp = $qp->find('common');
        //Check for a timestamp and set the when based on the timestamp
        $txt = $recordQp->find("data-xml body-composition when")->text();
        if ( !empty($txt) )
        {
            $this->when = $this->getTimestamp('data-xml body-composition when');
        }
        //Get the name of the measurement
        $this->measurementName = $recordQp->find('data-xml body-composition measurement-name text')->text();

        //Set the display units if they are set in the XML, otherwise assume they are kilograms (the default)
        $txt = $recordQp->find('data-xml body-composition value display')->text();
        if(!empty($txt))
        {
            $this->measurementDisplayUnits = $recordQp->find('data-xml body-composition value display')->attr('units');
            $this->measurementDisplayValue = $recordQp->find('data-xml body-composition value display')->text();
        }
        else{
            $this->measurementDisplayUnits = 'kg';
            $this->measurementDisplayValue = null;
        }

        //Pull the value from the XML, if it is not set, assume that it is a percent-value record
        $txt = $recordQp->find('data-xml body-composition value kg')->text();
        if(!empty($txt))
        {
            $this->measurementValue = $recordQp->find('data-xml body-composition value kg')->text();
        }
        else{
            $this->measurementValue = null;
            $this->measurementDisplayUnits = null;
            $this->mesaurementDisplayValue = null;
        }

        //Grab the Percentage values from the XML is they are there, if not clear out the variable
        $txt = $recordQp->find('data-xml body-composition value percent-value')->text();
        if(!empty($txt))
        {
            $this->measurementPercentage = $recordQp->find('data-xml body-composition value percent-value')->text();
        }
        else{
            $this->measurementPercentage = null;
        }
    }

    public static function createFromData(
        $when,
        $measurementName,
        $measurementValue = null,
        $measurementPercentage = null,
        $measurementDisplayUnits = null,
        array $common = null
    )
    {
        //Create a Body Composition Object
        $bodyComposition = HealthRecordItemFactory::getThing('Body Composition');
        $bodyComposition = parent::createCommonFromData($common, $bodyComposition);

        //Set the object's varibles to the passed variables
        $bodyComposition->when = $when;
        $bodyComposition->measurementName = $measurementName;
        $bodyComposition->measurementValue = $measurementValue;
        $bodyComposition->measurementPercentage = $measurementPercentage;
        $bodyComposition->measurementDisplayUnits = $measurementDisplayUnits;

        //Generate the XML when object from the timestamp
        $bodyComposition->setTimestamp('body-composition>when', $when);
        $bodyComposition->getQp()->top()->find('measurement-name text')->text($measurementName);

        //Parse the units and setip the measurement values based on the units that were passed
        if($measurementDisplayUnits == "kg")
        {
            $bodyComposition->removeOrUpdateIfEmpty( "data-xml body-composition value kg", $measurementValue);
        }
        elseif($measurementDisplayUnits == "lbs")
        {
            $bodyComposition->removeOrUpdateIfEmpty( "data-xml body-composition value kg", $measurementValue / 2.20462);
        }
        elseif(!is_null($measurementDisplayUnits))
        {
            //If an oddball set of units were passed in, throw an exception
            throw new Exception('Unsupported Units');
        }

        //Set the Display units and the display value
        $bodyComposition->removeOrUpdateIfEmpty( "data-xml body-composition value display", $measurementValue);
        $bodyComposition->getQp()->top()->find('data-xml body-composition value display')->attr("units", $measurementDisplayUnits);

        //If this was not a value record, remove that part of the xml
        if(is_null($measurementValue))
        {
            $bodyComposition->removeNode("data-xml body-composition mass-value");
        }

        //Set the percentage value in the XMLif it is set
        $bodyComposition->removeOrUpdateIfEmpty( "data-xml body-composition value percent-value", $measurementPercentage);

        return $bodyComposition;
    }

    public function getItemJSONArray()
    {
        $parentData = parent::getItemJSONArray();

        //generate and return a JSON object of the record
        $myData = array(
            "timestamp" => $this->when,
            "measurementName" => $this->measurementName,
            "measurementValue" => $this->measurementValue,
            "measurementPercentage" => $this->measurementPercentage,
            "measurementDisplayUnits" => $this->measurementDisplayUnits
        );
        return array_merge($myData, $parentData);
    }
}