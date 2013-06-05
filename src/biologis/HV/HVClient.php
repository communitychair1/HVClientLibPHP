<?php

/**
 * @copyright Copyright 2013 Markus Kalkbrenner, bio.logis GmbH (https://www.biologis.com)
 * @license GPLv2
 * @author Markus Kalkbrenner <info@bio.logis.de>
 */

namespace biologis\HV;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class HVClient implements HVClientInterface, LoggerAwareInterface {

  private $appId;
  private $session;
  private $connector = NULL;
  private $logger = NULL;
  private $healthVaultPlatform = 'https://platform.healthvault-ppe.com/platform/wildcat.ashx';
  private $healthVaultAuthInstance = 'https://account.healthvault-ppe.com/redirect.aspx';

  /**
   * @param string $appId
   *   HealthVault Application ID
   * @param array $session
   *   Session array, in most cases $_SESSION
   */
  public function __construct($appId, &$session, $user) {
    $this->appId = $appId;
    $this->session = & $session;
    $this->user = $user;
  }

  /**
   * @param string $thumbPrint
   *   Certificate thumb print
   * @param string $privateKey
   *   Private key as string or file path to load private key from
   * @param string $country
   *   TODO reference to Microsoft documentation for valid countries
   * @param string $languages
   *   TODO reference to Microsoft documentation for valid languages
   * Mostly replaced in typical workflow, must still be called on initial paring of account.
   */
  public function connect($thumbPrint = NULL, $privateKey = NULL, $country = NULL, $language = NULL) {
    if (!$this->logger) {
      $this->logger = new NullLogger();
    }

    if (!$this->connector) {
      $this->connector = new HVRawConnector($this->appId, $thumbPrint, $privateKey, $this->session);
      $this->connector->setLogger($this->logger);
    }

    $this->connector->setHealthVaultPlatform($this->healthVaultPlatform);

    if ($country) {
      $this->connector->setCountry($country);
    }

    if ($language) {
      $this->connector->setLanguage($language);
    }

    $this->connector->connect();
  }

    /**
     * @param null $thumbPrint
     * @param null $privateKey
     * @param null $country
     * @param null $language
     * Generates Auth Token and Allows for offline access to healthvault.
     * Replaces connect function in typical workflow
     */
    public function offlineConnect($thumbPrint = NULL, $privateKey = NULL, $country = NULL, $language = NULL)
  {
    if(!$this->connector)
    {
      $this->conenctor = new HVRawConnector($this->appId, $thumbPrint, $privateKey, $this->session);
      $this->connector->setLogger(($this->logger));
    }

    $this->connector->setHealthVaultPlatform($this->healthVaultPlatform);

    if ($country) {
      $this->connector->setCountry($country);
    }

    if ($language) {
      $this->connector->setLanguage($language);
    }

    $this->connector->offlineConnect();
  }

  public function disconnect() {
    unset($this->session['healthVault']);
    unset($this->connector);
    $this->connection = NULL;
  }

    /**
     * @param $redirectUrl
     * @return string
     */
    public function getAuthenticationURL($redirectUrl) {
    return HVRawConnector::getAuthenticationURL($this->appId, $redirectUrl, $this->session, $this->healthVaultAuthInstance);
  }

    /**
     * @return PersonInfo
     * @throws HVClientNotConnectedException
     * Only function to still require traditional connect().  Should only be called during initial account pairing.
     */
    public function getPersonInfo() {
    if ($this->connector) {
      $this->connector->authenticatedWcRequest('GetPersonInfo');
      $qp = $this->connector->getQueryPathResponse();
      $qpPersonInfo = $qp->find('person-info');
      if ($qpPersonInfo) {
        return new PersonInfo(qp('<?xml version="1.0"?>' . $qpPersonInfo->xml(), NULL, array('use_parser' => 'xml')));
      }
    }
    else {
      throw new HVClientNotConnectedException();
    }
  }

    /**
     * @param $thingNameOrTypeId
     * @param $recordId
     * @param array $options
     * @return array
     * @throws HVClientNotConnectedException
     * Modified to Retrieve things through offline access
     */
    public function getThings($thingNameOrTypeId, $recordId, $options = array()) {
    if ($this->connector) {
      $typeId = HealthRecordItemFactory::getTypeId($thingNameOrTypeId);

      $options += array(
        'group max' => 30,
      );

      $this->connector->offlineRequest(
        'GetThings',
        '3',
        '<group max="' . $options['group max'] . '"><filter><type-id>' . $typeId . '</type-id></filter><format><section>core</section><xml/></format></group>',
        array('record-id' => $recordId),
        $this->user
      );

      $things = array();
      $qp = $this->connector->getQueryPathResponse();
      $qpThings = $qp->branch()->find('thing');
      foreach ($qpThings as $qpThing) {
        $things[] = HealthRecordItemFactory::getThing(qp('<?xml version="1.0"?>' . $qpThing->xml(), NULL, array('use_parser' => 'xml')));
      }

      return $things;
    }
    else {
      throw new HVClientNotConnectedException();
    }
  }

    /**
     * @param $typeId
     * @param $rootElementType
     * @param $usr
     * @return string
     * @throws HVClientNotConnectedException
     * Returns base64 string for the personal image in healthvault
     * @notes need to refactor to make more efficient
     */
    public function getPersonalImage($typeId, $rootElementType, $usr)
    {
      $options = array();

      if ($this->connector) {

        $options += array(
          'group max' => 30,
        );

        $this->connector->offlineRequest(
          'GetThings',
          '2',
          '<group max="' . $options['group max'] . '"><filter><type-id>' . $typeId . '</type-id></filter><format><section>otherdata</section><xml/></format></group>',
          array('record-id' => $usr->getHvRecordID()),
          $usr
        );

        $qp = $this->connector->getQueryPathResponse();
        $qpThings = $qp->branch()->find('thing');
        foreach ($qpThings as $qpThing) {
          $things[] = HealthRecordItemFactory::getThing(qp('<?xml version="1.0"?>' . $qpThing->xml(), NULL, array('use_parser' => 'xml')));
        }
        $test = $qp->get('document')->textContent;
        $test = explode('/', $test);
        $img = '';
        for($i = 1; $i < sizeof($test); $i++)
        {
          $img .= '/'.$test[$i];
        }

        //$test = $things[0]->$rootElementType;
        return 'data:image/jpeg;base64,' . $img;


      }else{
            throw new HVClientNotConnectedException();
      }
    }

    /**
     * @param $things
     * @param $recordId
     * @throws HVClientNotConnectedException
     * Modified to work with offline access
     */
    public function putThings($things, $recordId) {
    if ($this->connector) {
      $payload = '';

      if($things instanceof HealthRecordItemData) {
        $things = array($things);
      }

      foreach($things as $thing) {
        $payload .= $thing->getItemXml();
      }

      $this->connector->offlineRequest(
        'PutThings',
        '1',
        $payload,
        array('record-id' => $recordId)
      );
    }
    else {
      throw new HVClientNotConnectedException();
    }
  }

    /**
     * @param $healthVaultAuthInstance
     */
    public function setHealthVaultAuthInstance($healthVaultAuthInstance) {
    $this->healthVaultAuthInstance = $healthVaultAuthInstance;
  }

 /**
  * @return string
  */
  public function getHealthVaultAuthInstance() {
    return $this->healthVaultAuthInstance;
  }

  /**
   * @param $healthVaultPlatform
   */
  public function setHealthVaultPlatform($healthVaultPlatform) {
    $this->healthVaultPlatform = $healthVaultPlatform;
  }

    /**
     * @return string
     */
  public function getHealthVaultPlatform() {
    return $this->healthVaultPlatform;
  }

    /**
     * @param HVRawConnectorInterface $connector
     */
  public function setConnector(HVRawConnectorInterface $connector) {
    $this->connector = $connector;
  }

    /**
     * @param LoggerInterface $logger
     * @return null|void
     */
  public function setLogger(LoggerInterface $logger) {
    $this->logger = $logger;
  }

}

class HVClientException extends \Exception {}

class HVClientNotConnectedException extends \Exception {}
