<?php

namespace biologis\HV;

use biologis\HV\HVClientBaseTest;

require_once("HVClientBaseTest.php");

class HVFilterTest extends HVClientBaseTest
{

    /**
     * Sets everything necessary for health vault testing
     */
    protected function setUp()
    {
        parent::setUp();
        $this->hv->connect($this->thumbPrint, $this->privateKey);
    }

    /**
     * Tests the set up configuration
     */
    public function testSetUp()
    {
        $this->assertNotNull($this->hv);
    }

    /**
     * Test using Xpath to filter data returned from HV
     */
    public function testXPathFilters()
    {
        //Test filtered query one:
        //      Return all weight created after 2012
        $XpathQuery1 = "/thing/data-xml/weight/when/date[y &gt; 2012]";

        //Test filter query two:
        //          Return all weights less than 88KG
        $XpathQuery2 = "/thing/data-xml/weight/value[kg &lt; 88]";

        //Init filters for query 1
        $groupReq = array(
            "Weight Measurement" => '<thing-state>Active</thing-state><xpath>' . $XpathQuery1 . '</xpath>'
        );

        $options = array();

        //Make request to retrieve person info;
        $queryData = $this->hv->getThings(
            $groupReq,
            $this->recordId,
            $options,
            false
        );

        // Loop through the first query and assert conditions
        foreach ($queryData as $weight) {
            $year = $weight->{'weight'}->{'when'}->{'date'}->{'y'};

            //Assert the year of each thing returned is greater than 2012
            $this->assertGreaterThan(2012, $year);

            //Assert the value of the weight element exists.
            $this->assertNotNull($weight->{'weight'}->{'value'});
        }

        //Make the second request and filter
        $groupReq["Weight Measurement"] = '<thing-state>Active</thing-state><xpath>' . $XpathQuery2 . '</xpath>';

        //Run query 2
        $queryData = $this->hv->getThings(
            $groupReq,
            $this->recordId,
            $options,
            false
        );

        // Loop through the first query and assert conditions
        foreach ($queryData as $weight) {
            $kg = $weight->{'weight'}->{'value'}->{'kg'};

            //Assert the year of each thing returned is greater than 2012
            $this->assertLessThan(88, $kg);

            //Assert the date of measurement exists
            $this->assertNotNull($weight->{'weight'}->{'when'}->{'date'});
        }

    }


}