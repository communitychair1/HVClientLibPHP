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
    protected $relatedThingId = null;
    protected $relatedThingVersion = null;
    protected $relatedThingRealationship = null;
    protected $source = null;

    public function __construct(Query $qp)
    {
        parent::__construct($qp);

        $recordQp = $qp->find('data-xml');
        $commonQp = $qp->find('common');
        $this->when = $this->getTimestamp('data-xml weight when');

        $this->weight = $recordQp->find('value kg')->text();

        $this->displayWeight = $this->weight * 2.20462;

        if($recordQp->find("common related-thing thing-id")->text())
        {
            $this->relatedThingId = $commonQp->find("related-thing thing-id")->text();
        }
        if($recordQp->find("common related-thing version-stamp")->text())
        {
            $this->relatedThingVersion = $commonQp->find("related-thing version-stamp")->text();
        }
        if($recordQp->find("common related-thing relationship-type")->text())
        {
            $this->relatedThingRealationship = $commonQp->find("related-thing relationship-type")->text();
        }
        if($recordQp->find("common source")->text())
        {
            $this->source = $commonQp->find("source")->text();
        }
    }

    /**
   * @see http://msdn.microsoft.com/en-us/library/dd724265.aspx
   *
   * @param $timestamp
   * @param $weight
   * @return object File
   */
    public static function createFromData($timestamp, $weight, $units = null, $displayWeight = null, array $common = null)
    {
        $weightMeasurement = HealthRecordItemFactory::getThing('Weight Measurement');
        $weightMeasurement = parent::createFromData($common, $weightMeasurement);
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
            "timestamp" => $this->when,
            "kgsWeight" => "$this->weight",
            "lbsWeight" => "$this->displayWeight",
            "relatedThingId" => $this->relatedThingId,
            "relatedThingVersion" => $this->relatedThingVersion,
            "relatedThingRelationship" => $this->relatedThingRealationship
        );
        if(isset($this->source))
        {
            $myData['source'] = $this->source;
        }

        return array_merge($myData, $parentData);
    }
}
