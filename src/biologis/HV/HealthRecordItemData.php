<?php

/**
 * @copyright Copyright 2013 Markus Kalkbrenner, bio.logis GmbH (https://www.biologis.com)
 * @license GPLv2
 * @author Markus Kalkbrenner <info@bio.logis.de>
 */

namespace biologis\HV;

use QueryPath\Query;

/**
 * Class HealthRecordItemData.
 * @see http://msdn.microsoft.com/en-us/library/microsoft.health.itemtypes.healthrecorditemdata.aspx
 */
class HealthRecordItemData extends AbstractXmlEntity
{
    protected $typeId;
    protected $version;
    protected $thingId;

    /** CONSTRUCTOR
     * @param Query $qp
     */
    public function __construct(Query $qp)
    {
        $this->qp = $qp;
        $this->typeId = $this->qp->top()->find('type-id')->text();
        $this->thingId = $this->qp->top()->find('thing-id')->first()->text();
        $this->version = $this->qp->top()->find('thing-id')->attr("version-stamp");
        $this->payloadElement = 'data-xml';
    }

    /** GET TYPE ID
     * @return mixed
     */
    public function getTypeId()
    {
        return $this->typeId;
    }

    /** GET ITEM XML
     * @see http://msdn.microsoft.com/en-us/library/dd724732.aspx
     * @param string $element
     * @return string
     * @throws Exception
     */
    public function getItemXml($element = '')
    {
        $qpElement = $this->qp->top();

        if (!$element) {
            $element = 'thing';
        } else {
            $qpElement = $qpElement->branch()->find($element);
            $this->qp->top();
        }

        if ($qpElement) {
            return "<$element>" . $qpElement->innerXML() . "</$element>";
        } else {
            throw new Exception();
        }
    }

    /** SET TIME STAMP
     * Helper function to set a timestamp in a Health Record Item.
     *
     * Using simple unix timestamps is easier than creating a something like a
     * HealthServiceDateTime class in php. This helper function serializes a
     * timestamp in a XML format like HealthServiceDateTime.
     *
     * @see http://msdn.microsoft.com/en-us/library/dd726570.aspx
     *
     * @param $selector
     * @param int $timestamp
     */
    public function setTimestamp($selector, $timestamp = 0)
    {
        if (!$timestamp) {
            $timestamp = time();
        }

        $qpElement = $this->qp->top()->branch()->find($selector);
        $qpElement->find('date')->replaceWith('<date>' . date('<\y>Y</\y><\m>n</\m><\d>j</\d>', $timestamp) . '</date>');
        $qpElement->find('time')->replaceWith('<time>' . date('<\h>H</\h><\m>i</\m><\s>s</\s><\f>0</\f>', $timestamp) . '</time>');
    }


    /** GET TIME STAMP
     * Helper function to get a timestamp from a Health Record Item.
     *
     * Using simple unix timestamps is easier than creating a something like a
     * HealthServiceDateTime class in php. This helper function creates a
     * timestamp from a HealthServiceDateTime serialized as XML.
     *
     * @see http://msdn.microsoft.com/en-us/library/dd726570.aspx
     * @param $selector
     * @return int
     */
    public function getTimestamp($selector)
    {
        $qpElement = $this->qp->top()->branch()->find($selector);
        return mktime(
            (int)$qpElement->branch()->find('h')->text(),
            (int)$qpElement->branch()->find('time m')->text(),
            (int)$qpElement->branch()->find('s')->text(),
            (int)$qpElement->branch()->find('date m')->text(),
            (int)$qpElement->branch()->find('d')->text(),
            (int)$qpElement->branch()->find('y')->text()
        );
    }


    /** POPULATE TIME DATA
     * @param $elementBranch : Query path branch
     * @param $baseDate : QP of the timestamp of the entire item.
     * @return int
     */
    public function populateTimeData($elementBranch, $baseDate)
    {

        $timeStamp = mktime(
            (int)$elementBranch->find('h')->text() ? (int)$elementBranch->find('h')->text() : 0,
            (int)$elementBranch->find('m')->text() ? (int)$elementBranch->find('m')->text() : 0,
            (int)$elementBranch->find('s')->text() ? (int)$elementBranch->find('s')->text() : 0,
            (int)$baseDate->top()->find('date m')->text(),
            (int)$baseDate->top()->find('d')->text(),
            (int)$baseDate->top()->find('y')->text()
        );

        return $timeStamp;
    }

    /** REMOVE OR UPDATE IF EMPTY
     * Will remove the node if the value is empty. Otherwise, set the value.
     *
     * @param $nodeName - Name of the node to search for, using QueryPath
     * @param $value - Value to add in. If empty, the node will be removed.
     */
    public function removeOrUpdateIfEmpty($nodeName, $value)
    {
        $node = $this->getQp()->find($nodeName);
        if (!empty($node)) {
            if (empty($value)) {
                $this->removeNode($nodeName);
            } else {
                $node->text($value);
            }
        }
    }

    /** REMOVE NODE
     *  This method will remove a node from the xml.
     *
     * @param $nodeName
     */
    public function removeNode($nodeName)
    {
        $node = $this->getQp()->find($nodeName);
        $node->remove();
    }

    /** SET TIME
     * Helper function to add the necessary XML for a time
     * @param int $node
     * @param $tstamp
     */
    public function setTime($node, $tstamp)
    {
        $node->append(date('<\h>H</\h><\m>i</\m><\s>s</\s><\f>0</\f>', $tstamp));
    }

    /** GET ITEM JSON ARRAY
     * Helper function to load up the base json array. Child classes
     * can add the additional record data as needed.
     */
    public function getItemJSONArray()
    {
        $data = array(
            "type-id" => $this->typeId,
            "version" => $this->version,
            "thing-id" => $this->thingId
        );
        return $data;
    }

    /**
     * This method will setup the common block of the HV Record. If there are no items
     * for that record, the common block is removed from the xml record.
     *
     * @param $common - an array which contains the source, thing-id, version-stamp, and relationship-type
     * @param $record - the HV record to be processed
     * @return mixed - the HV record after processing
     */
    public static function createFromData($common, $record)
    {
        $record->removeOrUpdateIfEmpty( "common source", $common['source']);
        $record->removeOrUpdateIfEmpty( "common related-thing thing-id", $common['thing-id']);
        $record->removeOrUpdateIfEmpty( "common related-thing version-stamp", $common['version-stamp']);
        $record->removeOrUpdateIfEmpty( "common related-thing relationship-type", $common['relationship-type']);

        if(is_null($common['thing-id']))
        {
            $record->removeNode("common related-thing");
        }

        if(is_null($common['source']))
        {
            $record->removeNode("common source");
        }

        if(is_null($common['source']) && is_null($common['thing-id']))
        {
            $record->removeNode("common");
        }

        return $record;
    }
}
