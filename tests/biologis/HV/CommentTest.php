<?php


namespace biologis\HV;

use biologis\HV\HealthRecordItem\GenericTypes\CodableValue;
use biologis\HV\HealthRecordItem\GenericTypes\CodedValue;
use biologis\HV\HVClientBaseTest;
use biologis\HV\HealthRecordItem\Comment;

require_once("HVClientBaseTest.php");

class CommentTest extends HVClientBaseTest
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
     * @coversNothin
     */
    public function testSetUp()
    {
        $this->assertNotNull($this->hv);
    }

    /**
     * @covers biologis\HV\HealthRecordItem\Comment::createFromData
     * @covers biologis\HV\HealthRecordItem\Comment::getItemJSONArray
     */
    public function testCreateComment()
    {
        $code = CodedValue::createFromData('1', 'valType');
        $category = CodableValue::createFromData('Category', array($code));
        $comment = Comment::createFromData('1234567890', 'Test Content', $category);

        $this->assertInstanceOf('biologis\HV\HealthRecordItem\Comment', $comment);

        $xml = $comment->getItemXml();
        $this->assertNotEmpty($xml, "Comment itemXml empty");

        $commentArray = $comment->getItemJSONArray();

        $this->assertEquals(1234567890, $commentArray['timestamp']);
        $this->assertEquals('Test Content', $commentArray['content']);
        $this->assertArrayHasKey('text', $commentArray['category']);

        $this->hv->putThings($xml, $this->recordId);
        $this->assertNotEmpty($this->hv->getConnector()->getRawResponse(), "No response received from HV");
        $this->assertContains("version", $this->hv->getConnector()->getRawResponse(), "Missing version identifier from response");
    }
}
