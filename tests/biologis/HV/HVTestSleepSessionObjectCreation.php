<?php

namespace biologis\HV;

use biologis\HV\HealthRecordItem\GenericTypes\CodedValue;
use biologis\HV\HealthRecordItem\GenericTypes\CodableValue;
use biologis\HV\HealthRecordItem\SleepSession\Awakening;
use biologis\HV\HealthRecordItem\SleepSession;


class HVTestSleepSessionObjectCreation extends \PHPUnit_Framework_TestCase
{

    /**
     * Test creating a coded value
     */
    public function testCreateCodedValue()
    {
        $codedValue = HVTestSleepSessionObjectCreation::createCodedValue1();
        $this->assertNotNull($codedValue);
        $xml = $codedValue->getItemXml();
        $this->assertNotNull($xml);
    }

    /**
     * Test creating a codable value
     */
    public function testCreateCodableValue()
    {
        $codableValue = HVTestSleepSessionObjectCreation::createCodableValue();

        $this->assertNotNull($codableValue);
        $xml = $codableValue->getItemXml();
        $this->assertNotNull($xml);
        // echo $xml;
    }

    public function testCreateAwakening()
    {
        $item = HVTestSleepSessionObjectCreation::createAwakening();
        $this->assertNotNull($item);
        $xml = $item->getItemXml();
        $this->assertNotNull($xml);
        // echo $xml;
    }

    public function testCreateSleepSession1()
    {
        $item = $this::createSleepSession1();
        $this->assertNotNull($item);
        $xml = $item->getItemXml();
        $this->assertNotNull($xml);
        //echo $xml;
    }

    public function testCreateSleepSession2()
    {
        $item = $this::createSleepSession2();
        $this->assertNotNull($item);
        $xml = $item->getItemXml();
        $this->assertNotNull($xml);
        // echo $xml;
    }

    public static function createSleepSession1()
    {
        $item = SleepSession::createFromData(time(), time(), time(), 300, 25, 1);
        return $item;
    }

    public static function createSleepSession2()
    {
        $awakenings = array(HVTestSleepSessionObjectCreation::createAwakening());
        $medications = array(HVTestSleepSessionObjectCreation::createCodableValue());
        $item = SleepSession::createFromData(time(), time(), time(), 300, 25, 1, $awakenings, $medications);
        return $item;
    }

    public static function createSleepSession3()
    {
        $awakenings = array(HVTestSleepSessionObjectCreation::createAwakening(), HVTestSleepSessionObjectCreation::createAwakening());
        $medications = array(HVTestSleepSessionObjectCreation::createCodableValue(), HVTestSleepSessionObjectCreation::createCodableValue());
        $item = SleepSession::createFromData(time(), time(), time(), 300, 25, 1, $awakenings, $medications);
        return $item;
    }

    public static function createCodedValue1()
    {
        return CodedValue::createFromData("1st val", "oral pill", "diabetes", 1);
    }

    public static function createCodedValue2()
    {
        return CodedValue::createFromData("2nd val", "injection", "hiv");
    }

    public static function createCodableValue()
    {
        $codedValue1 = HVTestSleepSessionObjectCreation::createCodedValue1();
        $codedValue2 = HVTestSleepSessionObjectCreation::createCodedValue2();
        return CodableValue::createFromData("My Descriptive Text", array($codedValue1, $codedValue2));
    }


    public static function createAwakening()
    {
        return Awakening::createFromData(time(), 13);
    }


}