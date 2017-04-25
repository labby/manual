# Manual

## Description
This is an addon for LEPTON CMS (2.x) to create a manual

## Support/Bugs
Please report bugs on the LEPTON Addon Forum, where also support is available
http://forum.lepton-cms.org/

## Download
Current installable release can be downloaded on
http://www.lepton-cms.com/lepador/modules/manual.php

## Changelog
Detailed Changelog can be seen on
https://github.com/labby/manual

## droplet

A handy droplet can be

```code
if(!isset($id)) return "";

$oManual = manual::getInstance();
return $oManual->get_root_link( $id );
```

Example given (droplet named ”manual_link“):

```
LEPTON_tools[[manual_link?id=14]]
```