<?php

namespace Shetza\SimpleGeoTools;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\View\Requirements;
use LeKoala\GeoTools\GeoExtension;
use LeKoala\GeoTools\Fields\CountryDropdownField;
use LeKoala\GeoTools\Services\GeocodeXyz;

class SimpleGeoExtension extends GeoExtension
{
	private static $geofields = [
		'Latitude', 'Longitude', 'StreetNumber', 'StreetName', 'StreetExtended', 'PostalCode', 'Locality', 'CountryCode'];
	private static $breaks = 0;

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName(self::$geofields);

    	$arr = [
    		TextField::create('StreetName','')->setAttribute('placeholder', _t('Geo.StreetName', "Adresse")),
    		TextField::create('StreetExtended','')->setAttribute('placeholder', _t('Geo.StreetExtended', "Complément d'adresse")),
    		LiteralField::create('Break'. (++self::$breaks), '<div class="break"></div>'),
    		TextField::create('PostalCode','')->setAttribute('placeholder', _t('Geo.PostalCode', "Code postal")),
    		TextField::create('Locality','')->setAttribute('placeholder', _t('Geo.Locality', "Commune")),
    		LiteralField::create('Break'. (++self::$breaks), '<div class="break"></div>'),
    		CountryDropdownField::create('CountryCode','')->setEmptyString(_t('Geo.CountryCode', "Pays")),
    		TextField::create('Latitude','')->setAttribute('placeholder', _t('Geo.Latitude', "Latitude")),
    		TextField::create('Longitude','')->setAttribute('placeholder', _t('Geo.Longitude', "Longitude")),
    	];

    	if ($this->owner->Latitude && $this->owner->Longitude) {
            Requirements::css('shetza/silverstripe-simple-geotools:leaflet/leaflet.css');
            Requirements::javascript('shetza/silverstripe-simple-geotools:leaflet/leaflet.js');
            Requirements::css('shetza/silverstripe-simple-geotools:css/geo-ext.css');
            Requirements::javascript('shetza/silverstripe-simple-geotools:javascript/geo-ext.js');
		
            $arr[] = LiteralField::create('Break'. (++self::$breaks), '<div class="break"></div>');
            $arr[] = LiteralField::create('Leaflet', '<div id="geo-map" style="width: 100%; height: 300px;"></div>');
    	}

    	$fields->addFieldToTab('Root.Main', 
    		CompositeField::create($arr)
    			->setTag('fieldset')
    			->setLegend(_t(get_class($this->owner) .'.Address', _t('Geo.Address', "Adresse")))
    			->addExtraClass('geo-ext'));
    }

    public function onBeforeWrite()
    {
        // Auto geocoding
        if (GeoExtension::$disable_auto_geocode) {
            return false;
        }
        if (!$this->owner->Latitude && $this->owner->StreetName && $this->owner->Locality && $this->owner->CountryCode) {
            $service = new GeocodeXyz;
            $address = $this->owner->StreetNumber .', '. $this->owner->StreetName .', '. $this->owner->Locality .', '. $this->owner->getCountryName();
            if ($result = $service->geocode($address)) {
                $this->owner->Latitude = $result->getCoordinates()->getLatitude();
                $this->owner->Longitude = $result->getCoordinates()->getLongitude();
            }
        }
    }
}
