<?php

/**
 * @copyright Copyright 2013 Markus Kalkbrenner, bio.logis GmbH (https://www.biologis.com)
 * @license GPLv2
 * @author Markus Kalkbrenner <info@bio.logis.de>
 */

namespace biologis\HV\HealthRecordItem;

use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItemFactory;
use QueryPath\Query;
use QueryPath;


/**
 * Class WeightMeasurement.
 * @see http://msdn.microsoft.com/en-us/library/dd726619.aspx
 */
class WeightMeasurement extends HealthRecordItemData {

    protected $when = null;
    protected $displayUnits = null;
    protected $weight = null;
    protected $displayWeight = null;

    public function __construct(Query $qp)
    {
        parent::__construct($qp);

        $recordQp = $qp->find('data-xml');
        $this->when = $this->getTimestamp('data-xml weight when');

        $this->weight = $recordQp->find('value kg')->text();

        $txt = $recordQp->find("weight value display")->text();
        if(!empty($txt)){
            $this->displayWeight = $txt;
            $this->displayUnits = $recordQp->find('weight value display')->attr("units");

        }
        else
        {
            $this->displayUnits = "kgs.";
            $this->displayWeight = $recordQp->find('value kg')->text();
        }

    }

    /**
   * @see http://msdn.microsoft.com/en-us/library/dd724265.aspx
   *
   * @param $timestamp
   * @param $weight
   * @return object File
   */
  public static function createFromData($timestamp, $weight) {
        $weightMeasurement = HealthRecordItemFactory::getThing('Weight Measurement');
        $weightMeasurement->setTimestamp('when', $timestamp);
        $weightMeasurement->getQp()->find('kg')->text($weight);
    return $weightMeasurement;
  }

    public function getItemJSONArray(){
        $parentData = parent::getItemJSONArray();

        $myData = array(
            "when" => $this->when,
            "displayWeight" => $this->displayWeight,
            "displayUnits" => $this->displayUnits,
            "weight" => $this->weight
        );

        return array_merge($myData, $parentData);
    }
}
