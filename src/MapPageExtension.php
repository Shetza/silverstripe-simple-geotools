<?php

namespace Shetza\SimpleGeoTools;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\NumericField;
use SilverStripe\View\Requirements;

/**
 * MapPageExtension
 *
 * @author Shetza <unknownshetza@gmail.com>
 */
class MapPageExtension extends DataExtension
{
}

use SilverStripe\Core\Extension;

class MapPageController extends Extension
{
    private static $url_handlers = [
        'map'    => 'map',
        'forMap' => 'forMap',
    ];
    private static $allowed_actions = [
        'map',
        'forMap',
    ];

    public function map()
    {
        Requirements::css('shetza/silverstripe-simple-geotools:leaflet/leaflet.css');
        Requirements::javascript('shetza/silverstripe-simple-geotools:leaflet/leaflet.js');
        Requirements::javascript('shetza/silverstripe-simple-geotools:leaflet/leaflet.markercluster.js');

        $tileProvider = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
        $tileAttribution = '';

        if (defined('TILE_PROVIDER')) {
            $tileProvider = TILE_PROVIDER;
        }
        if (defined('TILE_ATTRIBUTION')) {
            $tileAttribution = TILE_ATTRIBUTION;
        }

        Requirements::customScript(<<<JS
var _leafletTileProvider = '$tileProvider';
var _leafletAttribution = '$tileAttribution';
JS
        , 'LeafletVars');
        Requirements::javascript('shetza/silverstripe-simple-geotools:javascript/map.js');

        return $this->owner;
    }

    /**
     * Return HTML code to be included in SS template in order to build map.
     */
    public function mapBuilder()
    {
        $options = '';

        // @TODO define map-$ID as id to may use multiple maps
        return '<div class="map" id="geo-map" style="width: 100%; height: 450px;" data-src="'. $this->owner->forMapLink() .'"'. $options .'>
                    <p style="display:none" class="message">'. _t(__CLASS__.'.NO_ITEMS', "Aucune information à afficher") .'</p>
                </div>';
    }

    public function forMapLink()
    {
        return $this->owner->Link('forMap?_d='. date('YmdHis'));
    }

    /**
     * Return JSON list of visible items for the map.
     */
    public function forMap()
    {
        return $this->owner->sendJsonResponse($this->owner->getItemsForMap());
    }

    /**
     * Utility function to send json responses
     *
     * @param array|string $data
     * @param string $message
     */
    public function sendJsonResponse($data, $message = null)
    {
        if (is_string($data)) {
            switch ($data) {
                case self::MESSAGE_WARNING:
                    if (!$message) {
                        $message = 'An error has happened';
                    }
                    $data = array('error' => $message, 'type' => 'warning', 'message' => $message);
                    break;
                case self::MESSAGE_GOOD:
                case self::JSON_OK:
                    if (!$message) {
                        $message = 'Success';
                    }
                    $data = array('success' => $message, 'type' => 'success', 'message' => $message);
                    break;
                case self::MESSAGE_BAD:
                case self::JSON_ERROR:
                    if (!$message) {
                        $message = 'An error has happened';
                    }
                    $data = array('error' => $message, 'type' => 'error', 'message' => $message);
                    break;
            }
        }
        $response = $this->owner->getResponse();
        if (!$response) {
            $response = Controller::curr()->getResponse();
        }
        $response->setBody(json_encode($data));
        $response->addHeader('content-type', 'application/json');
        return $response;
    }

    /**
     * Return list of visible items prepared for the map (and Leaflet integration).
     */
    public function getItemsForMap()
    {
        $data = array();

        if ($this->owner->Items()->exists()) {
            foreach ($this->owner->Items() as $o) {
                $o = $o->toLeafletMapItem();
                if ($o && $o->lat) {
                    $data[] = $o;
                }
            }
        }
        
        return $data;
    }

    public function getLeafletMap($zoom = null, $height = '300px')
    {
        if (!$this->owner->Latitude || !$this->owner->Longitude) {
            return null;
        }
        
        $map = new LeafletMap();
        $map->setLatitude($this->owner->Latitude);
        $map->setLongitude($this->owner->Longitude);
        $map->setUseBuilder(true);
        $map->setEnableClustering(false);
        $map->setHeight($height);
        if ($zoom) $map->setZoom($zoom);
        return $map->forTemplate();
    }

    public function Items()
    {
        throw new Exception("Owner must implement this method that return a list of DataObject");
    }
}
