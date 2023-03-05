# silverstripe-simple-geotools

composer requirements :

```
{
    ...
    "require": {
        "shetza/silverstripe-simple-geotools": "dev-main"
    },
    "repositories": {
        "shetza": {
            "type": "git",
            "url": "https://github.com/shetza/silverstripe-simple-geotools"
        }
    },
    ...
}
```

to disable auto geocode (in app/_config.php):
```
LeKoala\GeoTools\GeoExtension::$disable_auto_geocode = true;
```
