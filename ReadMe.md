# EXT:nominatim

Geocoding service via OSM Nominatim (https://wiki.openstreetmap.org/wiki/Nominatim).

## Fetching geo coordinates

Contains a CommandController for fetching and persisting geo coordinates in arbitrary
database tables.
```
vendor/bin/typo3 nominatim:geocode:fetch
```

Example configuration given for `EXT:tt_address`:
```
module.tx_nominatim {
    tables {
        tt_address {
            sourceFields {
                address = address
                postalcode = zip
                city = city
                country = country
            }
            targetFields {
                latitude = latitude
                longitude = longitude
            }
        }
    }
}
```
NOTE: the postalcode/zip value is actually not send to Nominatim, as it apparently 
makes the search loose and may lead to "zero" results. 

## TBD

* Add hook for (re-)fetch of coordinates on save/change of address in backend
* Spot other use cases (e.g. for other extensions/tables), elaborate other useful features
* Add signal/slot for actions before & after database operations
* Add Documentation how to extend this extension
