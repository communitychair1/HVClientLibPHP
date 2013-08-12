<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ofields
 * Date: 8/12/13
 * Time: 4:27 PM
 * To change this template use File | Settings | File Templates.
 */
namespace biologis\HV\HealthRecordItem\GenericTypes;

use biologis\HV\HealthRecordItem\GenericTypes\RelatedThing;
use QueryPath\Query;
use QueryPath;

class Common {

    private $source;
    private $note;
    private $tags;
    private $extensionXML;
    private $relatedThings = array();
    private $clientThingId;

    public function __construct(Query $qp = null)
    {
        if ( empty($qp))
        {
            return;
        }
        $this->source = $qp->top()->find("source")->text();
        $this->note = $qp->top()->find("note")->text();
        $this->tags = $qp->top()->find("tags")->text();
        // TODO: Am I grabbing this correctly ?
        $this->extensionXML = $qp->top()->find("extension")->xml();
        $this->clientThingId = $qp->top()->find("client-thing-id")->text();

        // Loop through each related thing
        $relatedThingArr = $qp->find('related-thing');
        if ( !empty($relatedThingArr))
        {
            foreach($relatedThingArr as $relatedThing)
            {
                $this->relatedThings[] = new RelatedThing($relatedThing);
            }
        }
    }

    public function getObjectXml()
    {
        /**
         * @var $parent QueryPath
         */
        $qp = QueryPath::withXML();
        $qp->top()->append("<common/>");

        if ( !empty($this->source))
        {
            $qp->top()
                ->append("<source/>")
                ->find("source")
                ->text($this->source);
        }
        if ( !empty($this->note))
        {
            $qp->top()
                ->append("<note/>")
                ->find("note")
                ->text($this->note);
        }
        if ( !empty($this->tags))
        {
            $qp->top()
                ->append("<tags/>")
                ->find("tags")
                ->text($this->tags);
        }
        // TODO: This isn't being set correctly. Need to find the last child and append the extension XML there.
        if ( !empty($this->extensionXML))
        {
            $qp->top()
                ->last()
                ->append($this->extensionXML);
        }
        if ( !empty($this->relatedThings))
        {
            foreach($this->relatedThings as $relatedThing)
            {
                /**
                 * @var $relatedThing RelatedThing
                 */
                $qp->top()->append( $relatedThing->getObjectXml() );
            }
        }
        if ( !empty($this->clientThingId))
        {
            $qp->top()
                ->append("<client-thing-id/>")
                ->find("common > client-thing-id")
                ->text($this->clientThingId);
        }


        // Did we have any XML ?
        $txt = $qp->top()->text();
        if ( empty( $txt ) )
        {
            return null;
        }
        return $qp->top()->xml(true);
    }



    /**
     * @return Item array
     */
    public function getItemJSONArray()
    {
        $myData = array();
        if ( !empty($this->source))
        {
            $myData["source"] = $this->source;
        }
        if ( !empty($this->note))
        {
            $myData["note"] = $this->note;
        }
        if ( !empty($this->tags))
        {
            $myData["tags"] = $this->tags;
        }

        if ( !empty($this->extension))
        {
            $myData["extension"] = $this->extension;
        }

        if ( !empty($this->clientThingId))
        {
            $myData["client-thing-id"] = $this->clientThingId;
        }

        if ( !empty($this->relatedThings))
        {
            foreach($this->relatedThings as $relatedThing)
            {
                /**
                 * @var $relatedThing RelatedThing
                 */
                $myData["related-things"][] = $relatedThing->getItemJSONArray();
            }
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
     * @param mixed $extensionXML
     */
    public function setExtensionXML($extensionXML)
    {
        $this->extensionXML = $extensionXML;
    }

    /**
     * @return mixed
     */
    public function getExtensionXML()
    {
        return $this->extensionXML;
    }



    /**
     * @param mixed $note
     */
    public function setNote($note)
    {
        $this->note = $note;
    }

    /**
     * @return mixed
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param array $relatedThings
     */
    public function setRelatedThings($relatedThings)
    {
        $this->relatedThings = $relatedThings;
    }

    /**
     * @return array
     */
    public function getRelatedThings()
    {
        return $this->relatedThings;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return mixed
     */
    public function getTags()
    {
        return $this->tags;
    }



}