<?php 
//Ip de la pc servidor de base de datos
define("DB_HOST","143.198.156.48");

//Nombre de la base de datos
define("DB_NAME", "mcking_db");

//Usuario de la base de datos
define("DB_USERNAME", "kim");

//Contraseña del usuario de la base de datos
define("DB_PASSWORD", "Kimberly123#");
//define("DB_PASSWORD", "Km0r3N0123@@");

//definimos la codificación de los caracteres
define("DB_ENCODE","utf8");

//Definimos una constante como nombre del proyecto
define("PRO_NOMBRE","Macking");

//Ruta del servidor
//define("RUTA_SERVER", "/var/www/html/macking_sis");

//variables para facturación electronica

define("AUTH_URL_DTE","https://felgttestaws.digifact.com.gt/gt.com.fel.api.v3/api/login/get_token");
define("CERT_URL_DTE","https://felgttestaws.digifact.com.gt/gt.com.fel.api.v3/api/FELRequestV2?NIT=000044653948&TIPO=CERTIFICATE_DTE_XML_TOSIGN&FORMAT=XML,PDF,HTML&USERNAME=PRUEBAS56");
define("CANCEL_URL_DTE","https://felgttestaws.digifact.com.gt/gt.com.fel.api.v3/api/FELRequestV2?NIT=000044653948&TIPO=ANULAR_FEL_TOSIGN&FORMAT=XML,PDF,HTML&USERNAME=PRUEBAS56");

define("USERNAME_DTE","GT.000044653948.PRUEBAS56");
define("PASS_DTE","w&LWv8h_");

?>