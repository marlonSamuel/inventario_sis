<?php 
//Ip de la pc servidor de base de datos
define("DB_HOST","164.92.77.67");

//Nombre de la base de datos
define("DB_NAME", "new_horizon_db");

//Usuario de la base de datos
define("DB_USERNAME", "nelson");

//Contraseña del usuario de la base de datos
define("DB_PASSWORD", "nelson2195");
//define("DB_PASSWORD", "Km0r3N0123@@");

//definimos la codificación de los caracteres
define("DB_ENCODE","utf8");

//Definimos una constante como nombre del proyecto
define("PRO_NOMBRE","NEW HORIZON");

//Ruta del servidor
//define("RUTA_SERVER", "/var/www/html/macking_sis");

//variables para facturación electronica

define("AUTH_URL_DTE","https://felgttestaws.digifact.com.gt/gt.com.fel.api.v3/api/login/get_token");
define("CERT_URL_DTE","https://felgttestaws.digifact.com.gt/gt.com.fel.api.v3/api/FELRequestV2?NIT=000044235216&TIPO=CERTIFICATE_DTE_XML_TOSIGN&FORMAT=XML,PDF,HTML&USERNAME=TESTUSER");
define("CANCEL_URL_DTE","https://felgttestaws.digifact.com.gt/gt.com.fel.api.v3/api/FELRequestV2?NIT=000044235216&TIPO=ANULAR_FEL_TOSIGN&FORMAT=XML,PDF,HTML&USERNAME=TESTUSER");

define("USERNAME_DTE","GT.000044235216.TESTUSER");
define("PASS_DTE","f1Ufe?u%");

?>