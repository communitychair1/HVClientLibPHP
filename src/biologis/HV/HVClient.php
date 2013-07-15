<?php

/**
 * @license GPLv2
 */

namespace biologis\HV;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\SimpleXMLElement;

class HVClient implements HVClientInterface, LoggerAwareInterface
{

    private $appId;
    private $config;
    /**
     * @var HVRawConnector
     */
    private $connector = NULL;
    private $personId;
    private $logger = NULL;
    private $healthVaultPlatform = 'https://platform.healthvault-ppe.com/platform/wildcat.ashx';
    private $healthVaultAuthInstance = 'https://account.healthvault-ppe.com/redirect.aspx';
    private $thumbPrint = NULL;
    private $privateKey = NULL;

    /**
     * @param $thumbPrint
     * @param $privateKey
     * @param $appId
     * @param $personId
     * @param bool $online
     * @param array $config - Pass in optional items such as recordId, country, language.
     */
    public function __construct($thumbPrint,
                                $privateKey,
                                $appId,
                                $personId = null,
                                $config = array() )
    {

        $this->thumbPrint = $thumbPrint;
        $this->privateKey = $privateKey;
        $this->appId = $appId;
        $this->personId = $personId;
        $this->config = $config;

        $this->logger = new NullLogger();
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
     * Initializes the RawConnecter if there is not already one, and calls it's conncet function
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
     * @param array $options
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

                $imgData = base64_decode($img);

                $f = finfo_open();

                $mime_type = finfo_buffer($f, $imgData, FILEINFO_MIME_TYPE);

                return "data:" . $mime_type . ";base64," . $img;
            }


        }
        else
        {
            throw new HVClientNotConnectedException();
        }
    }

    /**
     * @param $thing
     * @param $recordId
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

    /**
     * @param $hvItem
     * @param $usrRecordId
     * @param $base64
     * @return SimpleXMLElement
     *
     * This function gets an item from HealthVault to use as a template and creates a simpleXML Object from it.
     */

    public function getItemTemplate($hvItem, $usrRecordId, $base64)
    {
        $typeId = array(
            $hvItem => ''
        );
        $itemObject = $this->getThings($typeId, $usrRecordId, array(), $base64);
        $sxml = new SimpleXMLElement($itemObject[0]->getItemXml());

        return $sxml;
    }

    /**
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
     * @return array
     */
    public function getThingId($recordId, $typeId, $base64 = FALSE)
    {
        $sxml = $this->getItemTemplate($typeId, $recordId, $base64);
        $array = array();
        $thingId = $sxml->{'thing-id'};
        $array[0] = $thingId[0];
        foreach($thingId->attributes() as $key => $value)
        {
            $array[1] = $value;
        }
        return $array;
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
    /*
     * @param $pid
     *
     * Sets the person ID.  Used by devices connecting to the portal
     */
    public function setPersonId($pid)
    {
        $this->personId = $pid;
    }

}


class HVClientException extends \Exception
{
}

class HVClientNotConnectedException extends \Exception
{
}
