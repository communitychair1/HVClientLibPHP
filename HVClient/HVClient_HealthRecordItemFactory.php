<?php

/**
 * @copyright Copyright 2013 Markus Kalkbrenner, bio.logis GmbH (https://www.biologis.com)
 * @license GPLv2
 * @author Markus Kalkbrenner <info@bio.logis.de>
 */

require_once 'HVRawConnector.php';

spl_autoload_register('HVClient_HealthRecordItemFactory::autoLoader');


class HVClient_HealthRecordItemFactory {

  private static $classNames = array();
  private static $xmlTemplateCache = array();

  public static function getThing($type_or_qp, $version = 0) {
    $thingNames = array_flip(HVRawConnector::$things);
    $typeId = '';

    if ($type_or_qp instanceof QueryPath) {
      $typeId = $type_or_qp->find(':root type-id')->text();
    }
    elseif (is_string($type_or_qp)) {
      $typeId = HVClient_HealthRecordItemFactory::getTypeId($type_or_qp);
      $template = __DIR__ . '/HealthRecordItem/XmlTemplates/' . $typeId . '.xml';
      if (is_readable($template)) {
        $type_or_qp = qp(file_get_contents($template), NULL, array('use_parser' => 'xml'));
      }
    }
    else {
      throw new Exception('ThingFactory::getThing must be called with a valid thing name or type id or a QueryPath object representing a thing.');
    }

    if ($typeId) {
      if ($type_or_qp instanceof QueryPath) {
        if ($className = HVClient_HealthRecordItemFactory::convertThingNameToClassName($thingNames[$typeId])) {
          return new $className($type_or_qp);
        }
        else {
          throw new Exception('Things of that type id are not supported yet: ' . $typeId);
        }
      }
      else {
        throw new Exception('Creation of new empty things of that type id is not supported yet: ' . $typeId);
      }
    }
    else {
      throw new Exception('Unable to detect type id.');
    }
  }

  public static function getTypeId($thingNameOrTypeId) {
    if (array_key_exists($thingNameOrTypeId, HVRawConnector::$things)) {
      return HVRawConnector::$things[$thingNameOrTypeId];
    }
    elseif (!in_array($thingNameOrTypeId, HVRawConnector::$things)) {
      throw new Exception('Unknown thing name or type id: ' . $thingNameOrTypeId);
    }
    return $thingNameOrTypeId;
  }

  private static function convertThingNameToClassName($thingName) {
    if (!array_key_exists($thingName, HVClient_HealthRecordItemFactory::$classNames)) {
      $className = preg_replace('/[^a-zA-Z0-9]/', ' ', $thingName);
      HVClient_HealthRecordItemFactory::$classNames[$thingName] = 'HVClient_' .
        preg_replace_callback('/\s+(\w)/', function($matches) {
          return strtoupper($matches[1]);
        }, $className);
    }

    return HVClient_HealthRecordItemFactory::$classNames[$thingName];
  }

  private static function convertClassNameToThingName($className) {
    if (in_array($className, HVClient_HealthRecordItemFactory::$classNames)) {
      $thingNames = array_flip(HVClient_HealthRecordItemFactory::$classNames);
      return $thingNames[$className];
    }
  }

  public static function autoLoader($class) {
    if (is_readable(__DIR__ . '/HealthRecordItem/' . $class . '.php')) {
      require(__DIR__ . '/HealthRecordItem/' . $class . '.php');
    }
    else {
      if (HVClient_HealthRecordItemFactory::convertClassNameToThingName($class)) {
        class_alias('HVClient_Thing', $class);
      }
    }
  }
}