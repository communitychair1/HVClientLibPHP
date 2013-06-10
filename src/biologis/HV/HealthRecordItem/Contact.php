<?php

namespace biologis\HV\HealthRecordItem;

use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItemFactory;

/**
 * Contact Thing
 * @see http://developer.healthvault.com/pages/sdk/docs/urn.com.microsoft.wc.thing.contact.1.html
 */
class Contact extends HealthRecordItemData
{

    /**
     *
     * Creates a Health Vault Contact XML Item.
     * @param array $address
     * @param $phoneNumber
     * @param $email
     * @return mixed
     *
     */

    public static function createFromData(array $address, $phoneNumber, $email)
    {
        $personalDemographicInformation = HealthRecordItemFactory::getThing('Contact');
        $personalDemographicInformation->getQp()->find('street')->text($address['street']);
        $personalDemographicInformation->getQp()->find('city')->text($address['city']);
        $personalDemographicInformation->getQp()->find('state')->text($address['state']);
        $personalDemographicInformation->getQp()->find('country')->text($address['country']);
        $personalDemographicInformation->getQp()->find('number')->text($phoneNumber);
        $personalDemographicInformation->getQp()->find('address')->text($email);
        return $personalDemographicInformation;
    }
}
