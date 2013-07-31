<?php

/**
 * @copyright Copyright 2013 Markus Kalkbrenner, bio.logis GmbH (https://www.biologis.com)
 * @license GPLv2
 * @author Markus Kalkbrenner <info@bio.logis.de>
 */

namespace biologis\HV;

use QueryPath\Query;

/** PERSON INFO
 * Class PersonInfo
 * @package biologis\HV
 */
class PersonInfo extends AbstractXmlEntity
{

    /** CONSTRUCTOR
     * @param Query $qp
     */
    public function __construct(Query $qp)
    {
        $this->qp = $qp;
    }

    /** GET RECORD LIST
     * @return array
     * returns an array of user names and associated record id's
     */
    public function getRecordList()
    {
        $records = array();
        foreach ($this->qp->top()->find('record') as $record) {
            $records[$record->attr('id')] = $record->text();
        }
        return $records;
    }

    /** GET RECORDS FORMATTED
     * @return array
     * returns all records associated with the account formatted into an array
     */
    public function getRecordsFormatted()
    {
        $records = array();
        $index = 0;
        $recordBranch = $this->qp->top()->branch()->find('record');
        foreach ($recordBranch as $record) {
            $records[$index]['id'] = $record->attr('id');
            $records[$index]['name'] = $record->attr('name');
            $records[$index]['rel-type'] = $record->attr('rel-type');
            $records[$index]['rel-name'] = $record->attr('rel-name');
            $index++;
        }
        return $records;
    }

    /** Get Record By Id
     * @param $id
     * @return null|object
     */
    public function getRecordById($id)
    {
        $qpRecord = $this->qp->top()->branch()->find('record#' . $id);
        $this->qp->top();
        if ($qpRecord) {
            return (object)array(
                'id' => $id,
                'record-custodian' => $qpRecord->attr('record-custodian'),
                'rel-type' => $qpRecord->attr('rel-type'),
                'rel-name' => $qpRecord->attr('rel-name'),
                'auth-expires' => $qpRecord->attr('auth-expires'),
                'display-name' => $qpRecord->attr('display-name'),
                'state' => $qpRecord->attr('state'),
                'date-created' => $qpRecord->attr('date-created'),
                'max-size-bytes' => $qpRecord->attr('max-size-bytes'),
                'size-bytes' => $qpRecord->attr('size-bytes'),
                'app-record-auth-action' => $qpRecord->attr('app-record-auth-action'),
                'app-specific-record-id' => $qpRecord->attr('app-specific-record-id'),
                'location-country' => $qpRecord->attr('location-country'),
                'date-updated' => $qpRecord->attr('date-updated'),
            );
        }

        return NULL;
    }
}
