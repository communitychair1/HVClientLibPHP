<?php

namespace biologis\HV;

use biologis\HV\HVClientBaseTest;

class HVRelationshipsTest extends HVClientBaseTest
{


    /**
     * Sets everything neccessary for health vault testing
     */
    protected function setUp()
    {
        parent::setUp();
        $this->hv->connect($this->thumbPrint, $this->privateKey);
    }


    /**
     * Test:  Gather Authorized Records
     *      test gathering all records assocaited with an account
     */
    public function testGatherAuthorizedRecords()
    {
        //Make request to retreive person info;
        $this->personInfo = $this->hv->getPersonInfo();

        //Assert it's existance
        $this->assertNotEmpty($this->personInfo->record);

        //Create array for all records tied to account
        $allRecords = array();

        //loop through all records and add them to the array
        for($i = 0; $i < count($this->personInfo->record); $i++)
            $allRecords[$i] = $this->personInfo->record[$i];

        //loop through all records and make assertions
        foreach($allRecords as $record)
        {
            //assert that the id of each array exist
            $this->assertNotEmpty($record['id'], 'Id exists in this record');

            //assert rel-type and rel-name exist for building relationships
            // It is important we make sure all relevant relationships are correct
            switch($record['rel-type'])
            {
                case 1:
                    $this->assertEquals($record['rel-name'], 'Self');
                    $this->assertEquals($record['id'], $this->recordId);
                    break;
                case 2:
                    $this->assertEquals($record['rel-name'], 'Other');
                    break;
                case 3:
                    $this->assertEquals($record['rel-name'], 'Spouse');
                    break;
                case 5:
                    $this->assertEquals($record['rel-name'], 'Child');
                    break;
                case 6:
                    $this->assertEquals($record['rel-name'], 'Guardian');
                    break;
                case 7:
                    $this->assertEquals($record['rel-name'], 'Patient');
                    break;
                case 8:
                    $this->assertEquals($record['rel-name'], 'Parent');
                    break;
                case 10:
                    $this->assertEquals($record['rel-name'], 'Relative');
                    break;
                default:
                    $this->assertNotEmpty($record['rel-name']);
            }
        }
    }
}