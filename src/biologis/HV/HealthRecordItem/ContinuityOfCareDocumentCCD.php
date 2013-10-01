<?php

namespace biologis\HV\HealthRecordItem;

use biologis\HV\HealthRecordItem\GenericTypes\CodableValue;
use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItemFactory;
use QueryPath\Query;
use QueryPath;
use Exception;


class ContinuityOfCareDocumentCCD extends HealthRecordItemData
{

    protected $xmlData = null;
    protected $xmlFrom = null;
    protected $xmlCreated = null;
    protected $xmlDate = null;
    protected $xmlUpdated = null;


    public function __construct(Query $qp)
    {
        parent::__construct($qp);

        $this->xmlData = $qp->find('data-xml ClinicalDocument')->xml();
        $this->xmlFrom = $qp->find('data-xml ClinicalDocument representedOrganization name')->innerXml();
        $this->xmlCreated = $qp->find('data-xml ClinicalDocument effectiveTime')->xml();
        $this->xmlDate = $qp->find('created timestamp')->innerXml();
        $this->xmlUpdated = $qp->find('updated timestamp')->innerXml();
    }

    /**
     * @param null CCD XML Data
     */
    public function setXmlData($xmlData)
    {
        $this->xmlData = $xmlData;
    }

    /**
     * @return CCD XML Data
     */
    public function getXmlData()
    {
        return $this->xmlData;
    }

    /**
     * @param null CCD XML Data
     */
    public function setXmlFrom($xmlFrom)
    {
        $this->xmlFrom = $xmlFrom;
    }

    /**
     * @return CCD XML Data
     */
    public function getXmlFrom()
    {
        return $this->xmlFrom;
    }

    /**
     * @param null CCD XML Data
     */
    public function setXmlDate($xmlDate)
    {
        $this->xmlDate = $xmlDate;
    }

    /**
     * @return CCD XML Data
     */
    public function getXmlDate()
    {
        return $this->xmlDate;
    }

    /**
     * @param null CCD XML Data
     */
    public function setXmlUpdated($xmlUpdated)
    {
        $this->xmlUpdated = $xmlUpdated;
    }

    /**
     * @return CCD XML Data
     */
    public function getXmlUpdated()
    {
        return $this->xmlUpdated;
    }

    /**
     * @param null CCD XML Data
     */
    public function setXmlCreated($xmlCreated)
    {
        $this->xmlCreated = $xmlCreated;
    }

    /**
     * @return CCD XML Data
     */
    public function getXmlCreated()
    {
        return $this->xmlCreated;
    }

}