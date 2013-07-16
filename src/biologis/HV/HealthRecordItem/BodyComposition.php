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

    public function __construct(Query $qp) {
        parent::__construct($qp);

        $recordQp = $qp->find('data-xml');
        $txt = $recordQp->find("data-xml body-composition when")->text();
        if ( !empty($txt) )
        {
            $this->when = $this->getTimestamp('data-xml body-composition when');
        }
        $this->measurementName = $recordQp->find('data-xml body-composition measurement-name text')->text();

        $txt = $recordQp->find('data-xml body-composition value display units')->text();
        if(!empty($txt))
        {
            $this->measurementDisplayUnits = $recordQp->find('data-xml body-composition value display units')->text();
        }
        else{
            $this->measurementDisplayUnits = 'kg';
        }

        $txt = $recordQp->find('data-xml body-composition value kg')->text();
        if(!empty($txt))
        {
            $this->measurementValue = $recordQp->find('data-xml body-composition value kg')->text();
        }
        else{
            $this->measurementValue = null;
            $this->measurementDisplayUnits = null;
        }

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
        $measurementDisplayUnits = null
    )
    {
        $bodyComposition = HealthRecordItemFactory::getThing('Body Composition');

        $bodyComposition->when = $when;
        $bodyComposition->measurementName = $measurementName;
        $bodyComposition->measurementValue = $measurementValue;
        $bodyComposition->measurementPercentage = $measurementPercentage;
        $bodyComposition->measurementDisplayUnits = $measurementDisplayUnits;

        $bodyComposition->setTimestamp('body-composition>when', $when);
        $bodyComposition->getQp()->top()->find('measurement-name text')->text($measurementName);

        if($measurementDisplayUnits == "kg")
        {
            $bodyComposition->removeOrUpdateIfEmpty( "data-xml body-composition value kg", $measurementValue);
        }
        elseif($measurementDisplayUnits == "lbs")
        {
            $bodyComposition->removeOrUpdateIfEmpty( "data-xml body-composition value kg", $measurementValue / 2.20462);
        }
        else
        {
            throw new Exception('Unsupported Units');
        }

        $bodyComposition->removeOrUpdateIfEmpty( "data-xml body-composition value display", $measurementValue);
        $bodyComposition->getQp()->top()->find('data-xml body-composition value display')->attr("units", $measurementDisplayUnits);

        if(is_null($measurementValue))
        {
            $bodyComposition->removeNode("data-xml body-composition mass-value");
        }

        $bodyComposition->removeOrUpdateIfEmpty( "data-xml body-composition value percent-value", $measurementPercentage);

        return $bodyComposition;
    }

    public function getItemJSONArray()
    {
        $parentData = parent::getItemJSONArray();

        $myData = array(
            "when" => $this->when,
            "measurementName" => $this->measurementName,
            "measurementValue" => $this->measurementValue,
            "measurementPercentage" => $this->measurementPercentage,
            "measurementDisplayUnits" => $this->measurementDisplayUnits
        );

        return array_merge($myData, $parentData);
    }
}