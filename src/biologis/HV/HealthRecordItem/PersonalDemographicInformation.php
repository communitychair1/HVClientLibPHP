<?php

namespace biologis\HV\HealthRecordItem;

use biologis\HV\HealthRecordItemData;
use biologis\HV\HealthRecordItemFactory;

/**
 * Pernsonal Demographic Information Thing
 * @see http://developer.healthvault.com/pages/sdk/docs/urn.com.microsoft.wc.thing.personal.1.html
 */
class PersonalDemographicInformation extends HealthRecordItemData
{

    /**
     * Creates a Health Vault Personal Demographic XML Item.
     *
     * @param array $name
     * @param $birthdate
     * @param $bloodType
     * @param $ethnicity
     * @return mixed
     */

    public static function createFromData(array $name, $birthdate, $bloodType, $ethnicity)
    {
        $personalDemographicInformation = HealthRecordItemFactory::getThing('Personal Demographic Information');
        $personalDemographicInformation->setTimestamp('when', $birthdate);
        $personalDemographicInformation->getQp()->find('blood-type')->text($bloodType);
        $personalDemographicInformation->getQp()->find('ethnicity')->text($ethnicity);
        $personalDemographicInformation->getQp()->find('first')->text($name['first']);
        $personalDemographicInformation->getQp()->find('middle')->text($name['middle']);
        $personalDemographicInformation->getQp()->find('last')->text($name['last']);
        return $personalDemographicInformation;
    }
}
