<?php

namespace biologis\HV;

use biologis\HV\HVClientBaseTest;
use biologis\HV\HealthRecordItem\CCD;

require_once("HVClientBaseTest.php");

class HVCCDTest extends HVClientBaseTest
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
     * Function will get CCD XML for pdf parsing
     */
    public function testCreateCCD()
    {
        /**
         * @var $CCD CCD
         */

        $xmlResponse = $this->hv->getThings(
            array("Continuity of Care Document (CCD)"=>""),
            $this->recordId,
            array(),
            false
        );

        foreach ($xmlResponse as $hvThing) {
            $this->assertNotNull($hvThing);
        }

    }

}