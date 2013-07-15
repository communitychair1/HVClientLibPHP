<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Syntinel
 * Date: 7/12/13
 * Time: 2:35 PM
 * To change this template use File | Settings | File Templates.
 */

use biologis\HV\HVClientBaseTest;
use biologis\HV\HealthRecordItem\File;

require_once("HVClientBaseTest.php");


class HVClientTest extends HVClientBaseTest
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
     * Tests the set up configuration
     */
    public function testSetUp()
    {
        $this->assertNotNull($this->hv);
    }


    //Test file for example purposes
    //This should be deleted
    public function testFileSetup()
    {
        echo "SANITY CHECK";

        $file1 = File::createFromFilePath("/Applications/MAMP/htdocs/mentis/portal-web/trunk/vendor/communitychair1/hv-client-lib/src/biologis/HV/HealthRecordItem/testfile.txt");

        $xml = $file1->getItemXml();

        //uncomment to place a file in HV
        //$this->hv->putThings($xml, $this->recordId);

        echo "SANITY CHECK";

    }


}