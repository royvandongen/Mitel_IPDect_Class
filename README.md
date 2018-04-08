Mitel IPDect Class
==================

This PHP Class is made for easier control of the Mitel IPDect Basestations ( IPBS2 ) and might also work for the rebrands of this DECT base.

## Overview

While controlling the IP Dect Basestations is normally done in a better way ( I guess ), i just wanted to be able to control these DECT basestations via my DIY Asterisk Event Setup. This Class wraps around all the basic necessities for controlling the base, adding users and checking the config.

There is also an autodetection of slave stations available. So you can run all the slaves via DHCP.

## Setup
### Requirements
At this moment, this class requires at least the following;

* PHP ( > 5.0.0 )
* php-curl

### Installing

Just call this class via another PHP script like this:

```
include("mitel_ipdect_class.php");
```
and then initiate the class;
```
$mitel = new Mitel_ipdect();
```
you could even initiate the class like this, and leave the config.inc.php empty.

```
$mitel = new Mitel_ipdect("master_ip_address", "username", "password", "https");
```


also make sure to specify the config parameters in config.inc.php

Beware of the "Transport" option. You can choose between http/https here.

```
$host = "master_ip_address";
$user = "username";
$pass = "password";
$transport = "https";
```
