<?php

namespace biologis\HV\HealthRecordItem;

use biologis\HV\HealthRecordItem\GenericTypes\CodableValue;
use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItemFactory;
use QueryPath\Query;
use QueryPath;
use Exception;


class ContinuityOfCareRecordCCR extends HealthRecordItemData
{

    protected $xmlData = null;
    protected $xmlFrom = null;
    protected $xmlCreated = null;
    protected $xmlDate = null;
    protected $xmlUpdated = null;


    public function __construct(Query $qp)
    {
        parent::__construct($qp);

        $this->xmlData = $qp->find('data-xml ContinuityOfCareRecord')->xml();
        $this->xmlFrom = $qp->find('data-xml ContinuityOfCareRecord From ActorLink ActorRole Text')->innerXml();
        $this->xmlCreated = $qp->find('data-xml ClinicalDocument effectiveTime')->xml();
        $this->xmlUpdated = $qp->find('eff-date')->innerXml();
    }

    /**
     * @param null CCR XML Data
     */
    public function setXmlData($xmlData)
    {
        $this->xmlData = $xmlData;
    }

    /**
     * @return CCR XML Data
     */
    public function getXmlData()
    {
        return $this->xmlData;
    }

    /**
     * @param null CCR XML Data
     */
    public function setXmlFrom($xmlFrom)
    {
        $this->xmlFrom = $xmlFrom;
    }

    /**
     * @return CCR XML Data
     */
    public function getXmlFrom()
    {
        return $this->xmlFrom;
    }

    /**
     * @param null CCR XML Data
     */
    public function setXmlUpdated($xmlUpdated)
    {
        $this->xmlUpdated = $xmlUpdated;
    }

    /**
     * @return CCR XML Data
     */
    public function getXmlUpdated()
    {
        return $this->xmlUpdated;
    }

    /**
     * @param null CCR XML Data
     */
    public function setXmlCreated($xmlCreated)
    {
        $this->xmlCreated = $xmlCreated;
    }

    /**
     * @return CCR XML Data
     */
    public function getXmlCreated()
    {
        return $this->xmlCreated;
    }

}