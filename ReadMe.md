# EXT:nominatim

Geocoding service via OSM Nominatim (https://wiki.openstreetmap.org/wiki/Nominatim).

## Fetching geo coordinates

Contains a CommandController for fetching and persisting geo coordinates in `EXT:tt_address`:
```
vendor/bin/typo3 nominatim:geocode:ttaddress
```

This extension is be considered as first draft.

## TBD

* Add hook for (re-)fetch of coordinates on save/change of address in backend
* Spot other use cases (e.g. for other extensions/tables), elaborate other useful features
* Make table configuration in `GeocodeTableService` configurable
* Add Documentation how to extend this extension
