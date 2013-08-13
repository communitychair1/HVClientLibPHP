<?php

/**
 * @license GPLv2
 */

namespace biologis\HV;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\SimpleXMLElement;

/**
 * Class HVClient
 * @package biologis\HV
 * @var HVRawConnector $connector
 */
class HVClient implements HVClientInterface, LoggerAwareInterface
{

    private $appId;
    private $config;
    private $connector = NULL;
    private $personId;
    private $logger = NULL;
    private $healthVaultPlatform = 'https://platform.healthvault-ppe.com/platform/wildcat.ashx';
    private $healthVaultAuthInstance = 'https://account.healthvault-ppe.com/redirect.aspx';
    private $thumbPrint = NULL;
    private $privateKey = NULL;


    /** CONSTRUCTOR
     *  Certificate thumb print
     * @param $thumbPrint
     *  Private key as string or file path to load private key from
     * @param $privateKey
     * @param $appId
     * @param null $personId
     * @param array $config
     */
    public function __construct($thumbPrint, $privateKey, $appId,
                                $personId = null, $config = array() )
    {

        $this->thumbPrint = $thumbPrint;
        $this->privateKey = $privateKey;
        $this->appId = $appId;
        $this->personId = $personId;
        $this->config = $config;

        $this->logger = new NullLogger();
    }

    /**
     *  Initialize the Raw Connector if there is not already one, and calls its connect function
     * @return string HV Session Token
     */
    public function connect()
    {
        //If there is no connector, generate one and set it's logger
        if (!$this->connector)
        {
            $this->connector = new HVRawConnector($this->appId, $this->thumbPrint, $this->privateKey, $this->config);
            $this->connector->setLogger($this->logger);
        }

        //Configure connector
        $this->connector->setHealthVaultPlatform($this->healthVaultPlatform);
        $authToken = $this->connector->connect();
        return $authToken;
    }

    /**
     * @return bool True is the client is connected.
     */
    public function isConnected()
    {
        return !empty($this->connector);
    }

    /**
     * Closes connection to healthValut
     * Called when app is closed or when switching between online and offline mode.
     */

    public function disconnect()
    {
        $this->config['healthVault'] = null;
        $this->connector = null;
        $this->connection = NULL;
    }

    /**
     * This will form the URL to present to the user for them to authorize an application to access their HealthVault records.
     * See HVRawConnector
     *
     * @param $redirectUrl
     * @param string $target
     * @param array $additionalTargetQSParams
     * @return string
     */
    public function getAuthenticationURL($redirectUrl, $target = null, $additionalTargetQSParams = null )
    {
        return HVRawConnector::getAuthenticationURL(
            $this->appId,
            $redirectUrl,
            $this->config,
            $this->healthVaultAuthInstance,
            $target,
            $additionalTargetQSParams
        );
    }

    /**
     * @return PersonInfo
     * @throws HVClientNotConnectedException
     * Only function to still require traditional connect().  Should only be called during initial account pairing.
     */
    public function getPersonInfo()
    {
        if ($this->connector)
        {

            $this->connector->makeRequest('GetPersonInfo', 1, '', NULL, $this->personId);

            $qp = $this->connector->getQueryPathResponse();
            $qpPersonInfo = $qp->find('person-info');

            if ($qpPersonInfo)
            {
                return new PersonInfo(qp('<?xml version="1.0"?>' . $qpPersonInfo->xml(), NULL, array('use_parser' => 'xml')));
            }
        }
        else
        {
            throw new HVClientNotConnectedException();
        }
    }

    /**
     * @param $groupAndFilter : associative array of Type ID : and filter
     * @param $recordId
     * @param array $options (Pass in an array of 'thing-ids' if you want to search for specific items in HV.
     * @param bool $base64
     * @return array
     * @throws HVClientNotConnectedException
     * Normal GetThings Method, Used for anything that returns simple XML data that needs to be parsed
     * Works in both Online and Offline mode, and picks the appropriate request depending on which is active.
     */

    public function getThings($groupAndFilter = array(), $recordId, $options = array(), $base64 = false)
    {
        if ($this->connector)
        {
            //create a group of HV type - ID's associated with filters.
            $requestGroupFilter = array();
            foreach($groupAndFilter as $id => $filter){
                $requestGroupFilter[HealthRecordItemFactory::getTypeId($id)] = $filter;
            }

            //set the group max
            if(!array_key_exists('group max', $options)){
                $options += array('group max' => 100);
            }

            //Create the XML info element, check first for Base64
            $info = '';
            if (!$base64)
            {
                foreach($requestGroupFilter as $id => $filter)
                {
                    $info .=
                        '<group max="' . $options['group max'] . '">
                        <filter><type-id>' . $id . '</type-id>'. $filter . '</filter>
                        <format><section>core</section><xml/></format></group>';
                }
                // Maybe the user passed in a list of specific thing ids ?
                if (!empty($options["thing-ids"]))
                {
                    foreach($options["thing-ids"] as $thingId )
                    {
                        $info .=
                            '<group max="' . $options['group max'] . '"><id>' . $thingId . '</id><format><section>core</section><xml/></format></group>';
                    }
                }
                $version = '3';
            }
            else
            {
                $info = '<group max="' . $options['group max'] . '">
                        <filter><type-id>' . key($requestGroupFilter) . '</type-id>'
                        . '</filter><format><section>otherdata</section><xml/></format></group>';
                $version = '2';
            }

            //make the request;
            $this->connector->makeRequest(
                'GetThings',
                $version,
                $info,
                array('record-id' => $recordId),
                $this->personId
            );

            //user query path to get the 'Things'
            $things = array();
            $qp = $this->connector->getQueryPathResponse();
            $qpThings = $qp->branch()->find('thing');

            if (!$base64)
            {
                foreach ($qpThings as $qpThing)
                {
                    $things[] = HealthRecordItemFactory::getThing(qp('<?xml version="1.0"?>' . $qpThing->xml(), NULL, array('use_parser' => 'xml')));
                }
                return $things;
            }
            else
            {
                $imgData = $qp->get('document')->textContent;
                $img = substr($imgData, 73);

                return $img;
            }
        }
        else
        {
            throw new HVClientNotConnectedException();
        }
    }

    /** PUT THING
     *  Puts data of a particular 'thing' type onto Health vault.
     * @param $thing
     * @param $recordId
     * @return string
     * @throws HVClientNotConnectedException
     */
    public function putThings($thing, $recordId)
    {
        if ($this->connector)
        {
            $this->connector->makeRequest(
                'PutThings',
                '1',
                $thing,
                array('record-id' => $recordId),
                $this->personId
            );
        }
        else
        {
            throw new HVClientNotConnectedException();
        }

        $qp = $this->connector->getQueryPathResponse();
        $ar = $qp->toArray();
        return substr($ar[0]->nodeValue, 1);
    }

    /**
     * @return bool
     *
     * Returns whether or not the connection is online or offline
     * TRUE == Online
     * FALSE == Offline
     */

    public function getOnlineMode()
    {
        return isset( $this->config['wctoken'] );
    }

    /**
     * @param $healthVaultAuthInstance
     */

    public function setHealthVaultAuthInstance($healthVaultAuthInstance)
    {
        $this->healthVaultAuthInstance = $healthVaultAuthInstance;
    }

    /**
     * @return string
     */

    public function getHealthVaultAuthInstance()
    {
        return $this->healthVaultAuthInstance;
    }

    /**
     * @param $healthVaultPlatform
     */

    public function setHealthVaultPlatform($healthVaultPlatform)
    {
        $this->healthVaultPlatform = $healthVaultPlatform;
    }

    /**
     * @return string
     */

    public function getHealthVaultPlatform()
    {
        return $this->healthVaultPlatform;
    }

    /**
     * @param HVRawConnectorInterface $connector
     */

    public function setConnector(HVRawConnectorInterface $connector)
    {
        $this->connector = $connector;
    }

    public function getConnector()
    {
        return $this->connector;
    }

    /**
     * @param LoggerInterface $logger
     * @return null|void
     */

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /** GET ITEM TEMPLATE
     * @param $hvItem
     * @param $usrRecordId
     * @param $base64
     * @return SimpleXMLElement if the item exists, otherwise returns false
     *
     * This function gets an item from HealthVault to use as a template and creates a simpleXML Object from it.
     */

    public function getItemTemplate($hvItem, $usrRecordId, $base64)
    {
        $typeId = array(
            $hvItem => ''
        );
        $itemObject = $this->getThings($typeId, $usrRecordId, array(), $base64);
        if($itemObject)
        {
            $sxml = new SimpleXMLElement($itemObject[0]->getItemXml());
            return $sxml;
        }
        else
        {
            return false;
        }
    }

    /** UPSERT ELEMENT IN TEMPALTE
     * @param $sxml
     * @param $elementPath
     * @param $updateVaule
     * @throws Exception
     *
     * This method will take a SimpleXML object in by reference and update or insert an element on it.
     */

    public function upsertElementInTemplate(&$sxml, $elementPath, $updateVaule)
    {

        //TODO - Find a way to get rid of this big messy switch statement

        $splitPath = explode("->", $elementPath);

        $splitPath = array_map('trim', $splitPath);
        $path_count = count($splitPath);
        switch($path_count)
        {
            case 1:
                $sxml->{'data-xml'}->{$splitPath[0]} = $updateVaule;
                break;
            case 2:
                $sxml->{'data-xml'}->{$splitPath[0]}->{$splitPath[1]} = $updateVaule;
                break;
            case 3:
                $sxml->{'data-xml'}->{$splitPath[0]}->{$splitPath[1]}->{$splitPath[2]} = $updateVaule;
                break;
            case 4:
                $sxml->{'data-xml'}->{$splitPath[0]}->{$splitPath[1]}->{$splitPath[2]}->{$splitPath[3]} = $updateVaule;
                break;
            case 5:
                $sxml->{'data-xml'}->{$splitPath[0]}->{$splitPath[1]}->{$splitPath[2]}->{$splitPath[3]}->{$splitPath[4]}= $updateVaule;
                break;
            case 6:
                $sxml->{'data-xml'}->{$splitPath[0]}->{$splitPath[1]}->{$splitPath[2]}->{$splitPath[3]}->{$splitPath[4]}->{$splitPath[5]}= $updateVaule;
                break;
            case 7:
                $sxml->{'data-xml'}->{$splitPath[0]}->{$splitPath[1]}->{$splitPath[2]}->{$splitPath[3]}->{$splitPath[4]}->{$splitPath[5]}->{$splitPath[6]}= $updateVaule;
                break;
            case 8:
                $sxml->{'data-xml'}->{$splitPath[0]}->{$splitPath[1]}->{$splitPath[2]}->{$splitPath[3]}->{$splitPath[4]}->{$splitPath[5]}->{$splitPath[6]}->{$splitPath[7]}= $updateVaule;
                break;
            default:
                throw new Exception('Invalid number of children in XML: ' . $path_count);
                break;
        }
    }

    /**
     * @param $sxml
     * @return string
     * This function will remove the top line from the xml that is passed in. This is needed because
     * more xml is wrapped around the 'thing' xml later in the request, but the function asXML() automatically
     * adds an XML header.
     */

    public function stripXMLHeader($sxml)
    {

        $xmlLines = explode("\n", $sxml->asXML());

        $xmlString = "";
        $flag = false;

        foreach ($xmlLines as $line)
        {
            if ($flag == false)
            {
                $flag = true; //TODO Refactor this so that we have a smarter way of dropping the first line
            }
            else
            {
                $xmlString .= $line;
            }
        }
        return $xmlString;
    }

    /**
     * @param $recordId
     * @param $typeId
     * @param bool $base64
     * @return array or false if there are no items of that type already
     */
    public function getThingId($recordId, $typeId, $base64 = FALSE)
    {
        if($sxml = $this->getItemTemplate($typeId, $recordId, $base64))
        {
            $array = array();
            $thingId = $sxml->{'thing-id'};
            $array[0] = $thingId[0];
            foreach($thingId->attributes() as $key => $value)
            {
                $array[1] = $value;
            }
            return $array;
        }
        else
        {
            return false;
        }
    }

    /**
     * @param $typeId
     * @return int|string
     *
     * This method will translate a typeId into a type name.
     */
    public function translateTypeId($typeId)
    {

        foreach(HVRawConnector::$things as $item => $key)
        {
            if($key == $typeId)
            {
                return $item;
            }
        }
    }

    /**
     *     * @param mixed $appId
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
    }

    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param null $personId
     */
    public function setPersonId($personId)
    {
        $this->personId = $personId;
    }

    /**
     * @return null
     */
    public function getPersonId()
    {
        return $this->personId;
    }

    /**
     * @param null $privateKey
     */
    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;
    }

    /**
     * @return null
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @param null $thumbPrint
     */
    public function setThumbPrint($thumbPrint)
    {
        $this->thumbPrint = $thumbPrint;
    }

    /**
     * @return null
     */
    public function getThumbPrint()
    {
        return $this->thumbPrint;
    }

    /**
     * @param $typeName - Health Vault Thing Type Name (Sleep Session, Peronsal Demographic Information, etc.)
     * @return string - Health Vault Thing ID (8375de98-7465-ae345-8ace-736af3b8e92c, etc.)
     *
     * This method will take a Health Vault Thing Type Name and conver it to the thing type id.
     * It is the inverse of translateTypeId
     */
    public function translateTypeName($typeName)
    {
        foreach(HVRawConnector::$things as $item => $key)
        {
            if($typeName == $item)
            {
                return $key;
            }
        }
    }

}


class HVClientException extends \Exception
{
}

class HVClientNotConnectedException extends \Exception
{
}
