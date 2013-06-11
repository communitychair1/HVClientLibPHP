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
    private $session;
    private $online;
    private $connector = NULL;
    private $personId;
    private $logger = NULL;
    private $healthVaultPlatform = 'https://platform.healthvault-ppe.com/platform/wildcat.ashx';
    private $healthVaultAuthInstance = 'https://account.healthvault-ppe.com/redirect.aspx';

    /**
     * @param $appId
     * @param $session
     * @param $personId
     * @param $online
     */

    public function __construct($appId, &$session, $personId, $online = true)
    {
        $this->appId = $appId;
        $this->session = & $session;
        $this->personId = $personId;
        $this->online = $online;
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

    public function connect($thumbPrint = NULL, $privateKey = NULL, $country = NULL, $language = NULL)
    {
        if (!$this->logger)
        {
            $this->logger = new NullLogger();
        }

        //If there is no connector, generate one and set it's logger
        if (!$this->connector)
        {
            $this->connector = new HVRawConnector($this->appId, $thumbPrint, $privateKey, $this->session, $this->online);
            $this->connector->setLogger($this->logger);
        }

        //Configure connector
        $this->connector->setHealthVaultPlatform($this->healthVaultPlatform);

        if ($country)
        {
            $this->connector->setCountry($country);
        }

        if ($language)
        {
            $this->connector->setLanguage($language);
        }

        $this->connector->connect();

    }

    /**
     * Closes connection to healthValut
     * Called when app is closed or when switching between online and offline mode.
     */

    public function disconnect()
    {
        $this->session['healthVault'] = null;
        $this->connector = null;
        $this->connection = NULL;
    }

    /**
     * @param $redirectUrl
     * @return string
     */

    public function getAuthenticationURL($redirectUrl)
    {
        return HVRawConnector::getAuthenticationURL($this->appId, $redirectUrl, $this->session, $this->healthVaultAuthInstance);
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
            $this->connector->authenticatedWcRequest('GetPersonInfo');
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
     * @param $thingNameOrTypeId
     * @param $recordId
     * @param array $options
     * @param bool $base64
     * @return array
     * @throws HVClientNotConnectedException
     * Normal GetThings Method, Used for anything that returns simple XML data that needs to be parsed
     * Works in both Online and Offline mode, and picks the appropriate request depending on which is active.
     */
     
    public function getThings($thingNameOrTypeId, $recordId, $options = array(), $base64 = false)
    {
        if ($this->connector)
        {
            $typeId = HealthRecordItemFactory::getTypeId($thingNameOrTypeId);


            $options += array(
                'group max' => 30,
            );

            if (!$base64)
            {
                $info = '<group max="' . $options['group max'] . '"><filter><type-id>' . $typeId . '</type-id></filter><format><section>core</section><xml/></format></group>';
                $version = '3';
            }
            else
            {
                $info = '<group max="' . $options['group max'] . '"><filter><type-id>' . $typeId . '</type-id></filter><format><section>otherdata</section><xml/></format></group>';
                $version = '2';
            }


            if ($this->online)
            {
                $this->connector->authenticatedWcRequest(
                    'GetThings',
                    $version,
                    $info,
                    array('record-id' => $recordId)
                );
            }
            else
            {
                $this->connector->offlineRequest(
                    'GetThings',
                    $version,
                    $info,
                    array('record-id' => $recordId),
                    $this->personId
                );
            }

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
                $imgData = explode('/', $imgData);
                $img = '';
                for ($i = 1; $i < sizeof($imgData); $i++)
                {
                    $img .= '/' . $imgData[$i];
                }

                //$test = $things[0]->$rootElementType;
                return 'data:image/jpeg;base64,' . $img;
            }


        }
        else
        {
            throw new HVClientNotConnectedException();
        }
    }

    /**
     * @param $things
     * @param $recordId
     * @throws HVClientNotConnectedException
     * Modified to work with offline access
     */
     
    public function putThings($thing, $recordId)
    {
        if ($this->connector)
        {

            if($this->online)
            {
                $this->connector->authenticatedWcRequest(
                    'PutThings',
                    '1',
                    $thing,
                    array('record-id' => $recordId),
                    $this->personId
                );
            }

            else
            {
                $this->connector->offlineRequest(
                    'PutThings',
                    '1',
                    $thing,
                    array('record-id' => $recordId),
                    $this->personId
                );
            }

        }
        else
        {
            throw new HVClientNotConnectedException();
        }
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
        return $this->online;
    }

    /**
     * Closes the current connection and reinitialize it in offline mode
     */
     
    public function offlineMode()
    {
        if ($this->online)
        {
            $this->online = false;
            //$this->disconnect();
            $this->connecter = null;
            $this->connect();
        }
    }

    /**
     * Closes the current connection and reinitialize it in online mode
     */
     
    public function onlineMode()
    {
        if (!$this->online)
        {
            $this->online = true;
            $this->connector = null;
            $this->connect();
        }
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
     * This function gets an item from healthvault to use as a tempalte and creates a simpleXML Object from it.
     */

    public function getItemTemplate($hvItem, $usrRecordId, $base64)
    {
        $itemObject = $this->getThings($hvItem, $usrRecordId, array(), $base64);
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
                $sxml->{'data-xml'}->{$splitPath[0]}->{$splitPath[1]}->{$splitPath[2]}->{$splitPath[3]}->{$splitPath[4]}= $updateVaule;
                break;
            case 7:
                $sxml->{'data-xml'}->{$splitPath[0]}->{$splitPath[1]}->{$splitPath[2]}->{$splitPath[3]}->{$splitPath[4]}= $updateVaule;
                break;
            case 8:
                $sxml->{'data-xml'}->{$splitPath[0]}->{$splitPath[1]}->{$splitPath[2]}->{$splitPath[3]}->{$splitPath[4]}= $updateVaule;
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

}

class HVClientException extends \Exception
{
}

class HVClientNotConnectedException extends \Exception
{
}
