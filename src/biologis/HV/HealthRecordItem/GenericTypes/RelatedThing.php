<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ofields
 * Date: 8/12/13
 * Time: 4:27 PM
 * To change this template use File | Settings | File Templates.
 */
namespace biologis\HV\HealthRecordItem\GenericTypes;

use QueryPath\Query;
use QueryPath;

class RelatedThing {

    private $thingId;
    private $versionStamp;
    // Only set this if the previous 2 items are empty
    private $clientThingId;
    private $relationshipType;

    public function __construct(Query $qp = null)
    {
        if ( empty($qp))
        {
            return;
        }
        $this->thingId = $qp->top()->find("thing-id")->text();
        $this->versionStamp = $qp->top()->find("version-stamp")->text();
        $this->clientThingId = $qp->top()->find("client-thing-id")->text();
        $this->relationshipType = $qp->top()->find("relationshipType")->text();
    }


    public function getObjectXml()
    {
        /**
         * @var $parent QueryPath
         */
        $qp = QueryPath::withXML();
        $qp->top()->append("<related-thing/>");

        $qp->top()
            ->append("<thing-id/>")
            ->find("thing-id")
            ->text($this->thingId);

        if ( !empty($this->versionStamp))
        {
            $qp->top()
                ->append("<version-stamp/>")
                ->find("version-stamp")
                ->text($this->versionStamp);
        }
        if ( !empty($this->versionStamp))
        {
            $qp->top()
                ->append("<relationship-type/>")
                ->find("relationship-type")
                ->text($this->relationshipType);
        }
        if ( !empty($this->clientThingId))
        {
            $qp->top()
                ->append("<client-thing-id/>")
                ->find("client-thing-id")
                ->text($this->clientThingId);
        }

        return $qp->top()->xml(true);
    }


    /**
     * @return Item array
     */
    public function getItemJSONArray()
    {
        $myData["thing-id"] = $this->thingId;
        if ( !empty($this->versionStamp))
        {
            $myData["version"] = $this->versionStamp;
        }
        if ( !empty($this->clientThingId))
        {
            $myData["client-thing-id"] = $this->clientThingId;
        }
        return $myData;
    }

    /**
     * @param mixed $clientThingId
     */
    public function setClientThingId($clientThingId)
    {
        $this->clientThingId = $clientThingId;
    }

    /**
     * @return mixed
     */
    public function getClientThingId()
    {
        return $this->clientThingId;
    }

    /**
     * @param mixed $thingId
     */
    public function setThingId($thingId)
    {
        $this->thingId = $thingId;
    }

    /**
     * @return mixed
     */
    public function getThingId()
    {
        return $this->thingId;
    }

    /**
     * @param mixed $versionStamp
     */
    public function setVersionStamp($versionStamp)
    {
        $this->versionStamp = $versionStamp;
    }

    /**
     * @return mixed
     */
    public function getVersionStamp()
    {
        return $this->versionStamp;
    }

    /**
     * @param mixed $relationshipType
     */
    public function setRelationshipType($relationshipType)
    {
        $this->relationshipType = $relationshipType;
    }

    /**
     * @return mixed
     */
    public function getRelationshipType()
    {
        return $this->relationshipType;
    }


}