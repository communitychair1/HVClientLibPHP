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
    protected $weight = null;
    protected $displayWeight = null;

    public function __construct(Query $qp)
    {
        parent::__construct($qp);

        $recordQp = $qp->find('data-xml');
        $this->when = $this->getTimestamp('data-xml weight when');

        $this->weight = $recordQp->find('value kg')->text();

        $this->displayWeight = $this->weight * 2.20462;
    }

    /**
   * @see http://msdn.microsoft.com/en-us/library/dd724265.aspx
   *
   * @param $timestamp
   * @param $weight
   * @return object File
   */
    public static function createFromData($timestamp, $weight, $units = null, $displayWeight = null)
    {
        $weightMeasurement = HealthRecordItemFactory::getThing('Weight Measurement');
        $weightMeasurement->setTimestamp('when', $timestamp);
        $weightMeasurement->getQp()->find('kg')->text($weight);


        $weightMeasurement->removeOrUpdateIfEmpty('value display', $displayWeight);
        if(!(is_null($units)))
        {
            $weightMeasurement->getQp()->find('value display')->attr("units", $units);
        }
    return $weightMeasurement;
  }

    public function getItemJSONArray(){
        $parentData = parent::getItemJSONArray();

        $myData = array(
            "when" => $this->when,
            "kgWeight" => $this->weight,
            "lbsWeight" => "$this->displayWeight"
        );

        return array_merge($myData, $parentData);
    }
}
