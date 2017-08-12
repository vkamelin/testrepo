<html>
<body>
<?php

$geoip_country_code = $_SERVER['GEOIP_COUNTRY_CODE'];
$geoip_country_code3 = $_SERVER['GEOIP_COUNTRY_CODE3'];
$geoip_country_name = $_SERVER['GEOIP_COUNTRY_NAME'];

$geoip_city_country_code = $_SERVER['GEOIP_CITY_COUNTRY_CODE'];
$geoip_city_country_code3 = $_SERVER['GEOIP_CITY_COUNTRY_CODE3'];
$geoip_city_country_name = $_SERVER['GEOIP_CITY_COUNTRY_NAME'];
$geoip_region = $_SERVER['GEOIP_REGION'];
$geoip_city = $_SERVER['GEOIP_CITY'];
$geoip_postal_code = $_SERVER['GEOIP_POSTAL_CODE'];
$geoip_city_continent_code = $_SERVER['GEOIP_CITY_CONTINENT_CODE'];
$geoip_latitude = $_SERVER['GEOIP_LATITUDE'];
$geoip_longitude = $_SERVER['GEOIP_LONGITUDE'];

echo 'country_code: '.$geoip_country_code.'<br>';
echo 'country_code3: '.$geoip_country_code3.'<br>';
echo 'country_name: '.$geoip_country_name.'<br>';

echo 'city_country_code: '.$geoip_city_country_code.'<br>';
echo 'city_country_code3: '.$geoip_city_country_code3.'<br>';
echo 'city_country_name: '.$geoip_city_country_name.'<br>';
echo 'region: '.$geoip_region.'<br>';
echo 'city: '.$geoip_city.'<br>';
echo 'postal_code: '.$geoip_postal_code.'<br>';
echo 'city_continent_code: '.$geoip_city_continent_code.'<br>';
echo 'latitude: '.$geoip_latitude.'<br>';
echo 'longitude: '.$geoip_longitude.'<br>';

?>
</body>
</html>