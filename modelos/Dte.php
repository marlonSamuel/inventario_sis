<?php 
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";
require_once "../config/global.php";
require_once "Venta.php";

Class Dte
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	public function loginDte()
    {
		// The data to send to the API
		$postData = array(
		    'Username' => USERNAME_DTE,
		    'Password' => PASS_DTE
		);

		// Setup cURL
		$ch = curl_init(AUTH_URL_DTE);
		curl_setopt_array($ch, array(
		    CURLOPT_POST => TRUE,
		    CURLOPT_RETURNTRANSFER => TRUE,
		    CURLOPT_HTTPHEADER => array(
		        'Content-Type: application/json'
		    ),
		    CURLOPT_POSTFIELDS => json_encode($postData)
		));

		// Send the request
		$response = curl_exec($ch);

		// Check for errors
		if($response === FALSE){
			return array(
				'message'=>'No se pudo realizar conexión DTE con la SAT, FALLO SERVICIO '.curl_error($ch),
				'token'=> null,
				'rpta'=> false
			);
		    //die(curl_error($ch));
		}

		// Decode the response
		$responseData = json_decode($response, TRUE);

		// Close the cURL handler
		curl_close($ch);

		// Guardar en base de datos
		$timestamp = strtotime($responseData['expira_en']);
		$insert_token = $this->insertar($responseData['Token'],date('Y-m-d',$timestamp),$responseData['otorgado_a']);

		if($insert_token){
			return array(
				'message'=>'Token obtenido correctamente',
				'token' => $responseData['Token'],
				'rpta' => true
			);
		}else{
			return array(
				'message'=>'No se pudo insertar DTE',
				'token'=>null,
				'rpta'=>false
			);
		}
	}

	//Implementamos un método para insertar registros
	public function insertar($token,$expira_en,$otorgado_a)
	{
		$sql="INSERT INTO token_dte (token,expira_en,otorgado_a)
		VALUES ('$token','$expira_en','$otorgado_a')";
		return ejecutarConsulta($sql);
	}
  	

  	//GENERAR XML
    private function generarXML($venta, $detalle)
    {
        try {
            $prefix = "dte";

            $xml = new XMLWriter();
            $xml->openMemory();
            $xml->startDocument('1.0', 'UTF-8');

            /* =============== INICIO GTDocumento ======== */
            $xml->startElementNs($prefix, 'GTDocumento', null);
            $xml->startAttribute("xmlns:{$prefix}");
            $xml->text("http://www.sat.gob.gt/dte/fel/0.2.0");
            $xml->startAttribute("xmlns:xsi");
            $xml->text("http://www.w3.org/2001/XMLSchema-instance");
            $xml->startAttribute("Version");
            $xml->text("0.1");
            $xml->endAttribute();

            /* =============== INICIO SAT ======== */
            $xml->startElementNs($prefix, 'SAT', null);
            $xml->startAttribute("ClaseDocumento");
            $xml->text($prefix);
            $xml->endAttribute();

            /* =============== INICIO DTE ======== */
            $xml->startElementNs($prefix, 'DTE', null);
            $xml->startAttribute("ID");
            $xml->text("DatosCertificados");
            $xml->endAttribute();

            /* =============== INICIO DatosEmision ======== */
            $xml->startElementNs($prefix, 'DatosEmision', null);
            $xml->startAttribute("ID");
            $xml->text("DatosEmision");
            $xml->endAttribute();

            /* =============== INICIO DatosGenerales ======== */
            $xml->startElementNs($prefix, 'DatosGenerales', null);
            $xml->startAttribute("Tipo");
            $xml->text("FPEQ");
            $xml->startAttribute("FechaHoraEmision");
            $fechaTransaccion = date("Y-m-d", strtotime($venta['fecha_hora']));
            $horaTransaccion = date("H:i:s", strtotime($venta['fecha_hora']));
            $xml->text("{$fechaTransaccion}T{$horaTransaccion}");
            $xml->startAttribute("CodigoMoneda");
            $xml->text("GTQ");
            $xml->endAttribute();

            $xml->endElement();
            /* =============== FIN DatosGenerales ======== */

            /* =============== INICIO Emisor ======== */
            $xml->startElementNs($prefix, 'Emisor', null);
            $xml->startAttribute("NITEmisor");
            $xml->text("44235216");
            $xml->startAttribute("NombreEmisor");
            $xml->text("NELSON MAEL, GONZÁLEZ SOLARES");
            $xml->startAttribute("CodigoEstablecimiento");
            $xml->text("1");
            $xml->startAttribute("NombreComercial");
            $xml->text("NEW HORIZON");
            $xml->startAttribute("AfiliacionIVA");
            $xml->text("PEQ");
            $xml->endAttribute();

            /* =============== INICIO DireccionEmisor ======== */
            $xml->startElementNs($prefix, 'DireccionEmisor', null);
            $xml->writeElementNs($prefix, 'Direccion', null, "AVENIDA PRINCIPAL BARRIO EL CAMPAMENTO ZONA 0");
            $xml->writeElementNs($prefix, 'CodigoPostal', null, "01001");
            $xml->writeElementNs($prefix, 'Municipio', null, "Chiquimulilla");
            $xml->writeElementNs($prefix, 'Departamento', null, "Santa Rosa");
            $xml->writeElementNs($prefix, 'Pais', null, "GT");
            $xml->endElement();
            /* =============== FIN DireccionEmisor ======== */

            $xml->endElement();
            /* =============== FIN Emisor ======== */

            /* =============== INICIO Receptor ======== */
            $xml->startElementNs($prefix, 'Receptor', null);
            $xml->startAttribute("NombreReceptor");
            $xml->text($venta['cliente']);
            $xml->startAttribute("CorreoReceptor");
            $xml->text($venta['email']);
            $xml->startAttribute("IDReceptor");
            $xml->text($venta['num_documento'] == null ? 'CF' : $venta['num_documento']);
            $xml->endAttribute();

            /* =============== INICIO DireccionReceptor ======== */
            $xml->startElementNs($prefix, 'DireccionReceptor', null);
            $xml->writeElementNs($prefix, 'Direccion', null, is_null($venta['direccion']) ? 'ciudad' : $venta['direccion']);
            $xml->writeElementNs($prefix, 'CodigoPostal', null, "0");
            $xml->writeElementNs($prefix, 'Municipio', null, "Chiquimulilla");
            $xml->writeElementNs($prefix, 'Departamento', null, "Santa Rosa");
            $xml->writeElementNs($prefix, 'Pais', null, "GT");
            $xml->endElement();
            /* =============== FIN DireccionReceptor ======== */

            $xml->endElement();
            /* =============== FIN Receptor ======== */

            /* =============== INICIO Frases ======== */
            $xml->startElementNs($prefix, 'Frases', null);

            /* =============== INICIO Frase ======== */
            $xml->startElementNs($prefix, 'Frase', null);
            $xml->startAttribute("TipoFrase");
            $xml->text("3");
            $xml->startAttribute("CodigoEscenario");
            $xml->text("1");
            $xml->endAttribute();
            $xml->endElement();
            /* =============== FIN Frase ======== */

            $xml->endElement();
            /* =============== FIN Frases ======== */

            /* =============== INICIO Items ======== */
            $xml->startElementNs($prefix, 'Items', null);

            $totalMontoImpuesto = 0;
            $granTotal = 0;
            $key = 0;
            while ($item=$detalle->fetch_object()) {
                /* =============== INICIO Item ======== */
                $xml->startElementNs($prefix, 'Item', null);
                $xml->startAttribute("NumeroLinea");
                $xml->text($key + 1);
                $xml->startAttribute("BienOServicio");
                $xml->text("B");
                $xml->endAttribute();

                $xml->writeElementNs($prefix, 'Cantidad', null, $item->cantidad);
                $xml->writeElementNs($prefix, 'UnidadMedida', null, "11");

               /* $nombre_producto = $item->product->name;
                if ($item->product->type == 'variable') {
                    $nombre_producto .= " - " . $item->variations->product_variation->name ?? '';
                    $nombre_producto .= " - " . $item->variations->name ?? '';
                }
                $nombre_producto .= " " . $item->variations->sub_sku ?? '';
                if (!empty($item->product->brand->name)) {
                    $nombre_producto .= " , " . $item->product->brand->name;
                }

                $precio = ($item->unit_price_before_discount + $item->item_tax);
                $subTotal = ($item->quantity * $precio);
                $descuento = ($item->quantity * $item->get_discount_amount());*/
                $xml->writeElementNs($prefix, 'Descripcion', null, $item->nombre);
                $xml->writeElementNs($prefix, 'PrecioUnitario', null, $item->precio_venta);
                $xml->writeElementNs($prefix, 'Precio', null, $item->precio_venta * $item->cantidad);
                $xml->writeElementNs($prefix, 'Descuento', null, $item->descuento);

                /* =============== INICIO Impuestos ======== */
                //$xml->startElementNs($prefix, 'Impuestos', null);

                /* =============== INICIO Impuesto ======== */
                //$xml->startElementNs($prefix, 'Impuesto', null);
                //$xml->writeElementNs($prefix, 'NombreCorto', null, "IVA");
                //$xml->writeElementNs($prefix, 'CodigoUnidadGravable', null, "1");

                $total = round(($item->subtotal), 4);
                $granTotal += $total;
                $erc = round(($total / 1.05), 4);
                $iva = (($erc * 5) / 100);
                $totalMontoImpuesto += $iva;

                //$xml->writeElementNs($prefix, 'MontoGravable', null, $erc);
                //$xml->writeElementNs($prefix, 'MontoImpuesto', null, $iva);
                //$xml->endElement();
                /* =============== FIN Impuesto ======== */

                //$xml->endElement();
                /* =============== FIN Impuestos ======== */

                $xml->writeElementNs($prefix, 'Total', null, $total);
                $xml->endElement();
                /* =============== FIN Item ======== */
                $key++;
            }

            $xml->endElement();
            /* =============== FIN Items ======== */

            /* =============== INICIO Totales ======== */
            $xml->startElementNs($prefix, 'Totales', null);

            /* =============== INICIO TotalImpuestos ======== */
            //$xml->startElementNs($prefix, 'TotalImpuestos', null);

            /* =============== INICIO TotalImpuesto ======== */
            //$xml->startElementNs($prefix, 'TotalImpuesto', null);
            //$xml->startAttribute("NombreCorto");
            //$xml->text("IVA");
            //$xml->startAttribute("TotalMontoImpuesto");
            //$xml->text($totalMontoImpuesto);
            //$xml->endElement();
            /* =============== FIN TotalImpuesto ======== */

            //$xml->endElement();
            /* =============== FIN TotalImpuestos ======== */

            $xml->writeElementNs($prefix, 'GranTotal', null, $granTotal);
            $xml->endElement();
            /* =============== FIN Totales ======== */

            $xml->endElement();
            /* =============== FIN DatosEmision ======== */

            $xml->endElement();
            /* =============== FIN DTE ======== */


            /* =============== INICIO Adenda ======== */
            $xml->startElementNs($prefix, 'Adenda', null);

            /* =============== INICIO Informacion_COMERCIAL ======== */
            $xml->startElementNs("{$prefix}comm", 'Informacion_COMERCIAL', null);
            $xml->startAttribute("xmlns:{$prefix}comm");
            $xml->text("https://www.digifact.com.gt/dtecomm");
            $xml->startAttribute("xsi:schemaLocation");
            $xml->text("https://www.digifact.com.gt/dtecomm");
            $xml->endAttribute();

            /* =============== INICIO InformacionAdicional ======== */
            $xml->startElementNs("{$prefix}comm", 'InformacionAdicional', null);
            $xml->startAttribute("Version");
            $xml->text("2020_06_01");
            $xml->endAttribute();

            $xml->writeElementNs("{$prefix}comm", 'REFERENCIA_INTERNA', null, "INVOICE{$venta['idventa']}");
            $xml->writeElementNs("{$prefix}comm", 'FECHA_REFERENCIA', null, "{$fechaTransaccion}T{$horaTransaccion}");
            $xml->writeElementNs("{$prefix}comm", 'VALIDAR_REFERENCIA_INTERNA', null, "VALIDAR");

            $xml->endElement();
            /* =============== FIN InformacionAdicional ======== */

            $xml->endElement();
            /* =============== FIN Informacion_COMERCIAL ======== */

            $xml->endElement();
            /* =============== FIN Adenda ======== */

            $xml->endElement();
            /* =============== FIN SAT ======== */

            $xml->endElement();
            /* =============== FIN GTDocumento ======== */

            //XML GENERADO
            $xml = json_encode($xml->outputMemory(true), JSON_UNESCAPED_UNICODE);
            $xml = str_replace(['"<', '>"', '\\', '>n<'], ['<', '>', '', '><'], $xml);

            return array('xml'=>$xml,'total'=>$granTotal,'impuesto'=>$totalMontoImpuesto);
        } catch (\Throwable $th) {
            throw new Exception("Ocurrio un error al generar el XML que necesita certificar.");
        }
    }

    //GENERAR XML PARA ANULAR
    private function xmlCancel($dte){
    	try {
            $prefix = "dte";

            $xml = new XMLWriter();
            $xml->openMemory();
            $xml->startDocument('1.0', 'UTF-8');

            /* =============== INICIO GTAnulacionDocumento ======== */
            $xml->startElementNs($prefix, 'GTAnulacionDocumento', null);
            $xml->startAttribute("xmlns:{$prefix}");
            $xml->text("http://www.sat.gob.gt/dte/fel/0.1.0");
            $xml->startAttribute("xmlns:xsi");
            $xml->text("http://www.w3.org/2001/XMLSchema-instance");
            $xml->startAttribute("Version");
            $xml->text("0.1");
            $xml->endAttribute();

            /* =============== INICIO SAT ======== */
            $xml->startElementNs($prefix, 'SAT', null);

            /* =============== INICIO AnulacionDTE ======== */
            $xml->startElementNs($prefix, 'AnulacionDTE', null);
            $xml->startAttribute("ID");
            $xml->text("DatosCertificados");
            $xml->endAttribute();

            /* =============== INICIO DatosGenerales ======== */
            $xml->startElementNs($prefix, 'DatosGenerales', null);
            $xml->startAttribute("ID");
            $xml->text("DatosAnulacion");
            $xml->startAttribute("NumeroDocumentoAAnular");
            $xml->text($dte['autorizacion']);
            $xml->startAttribute("NITEmisor");
            $xml->text("44235216");
            $xml->startAttribute("IDReceptor");
            $xml->text($dte['nit_comprador']);
            $xml->startAttribute("FechaEmisionDocumentoAnular");
            $xml->text($dte['fecha_certificacion']);
            $xml->startAttribute("FechaHoraAnulacion");
            $fecha = date("Y-m-d");
            $hora = date("H:i:s");
            $xml->text("{$fecha}T{$hora}");
            $xml->startAttribute("MotivoAnulacion");
            //$usuario = Auth::user();
            $xml->text("Anulación de documento autorizado por el admininistrador");
            $xml->endAttribute();

            $xml->endElement();
            /* =============== FIN DatosGenerales ======== */

            $xml->endElement();
            /* =============== FIN AnulacionDTE ======== */


            $xml->endElement();
            /* =============== FIN SAT ======== */

            $xml->endElement();
            /* =============== FIN GTAnulacionDocumento ======== */

            //XML GENERADO
            $xml = json_encode($xml->outputMemory(true), JSON_UNESCAPED_UNICODE);
            $xml = str_replace(['"<', '>"', '\\', '>n<'], ['<', '>', '', '><'], $xml);

            return $xml;
        } catch (\Throwable $th) {
            throw new Exception("Ocurrio un error al generar el XML que necesita anular.");
        }
    }

    private function obtenerUltimoToken()
    {
    	$sql = 'select * from token_dte order by id_token desc limit 1';
    	return ejecutarConsultaSimpleFila($sql);
    }


    	//Implementamos un método para insertar registros
	public function insertarDTE($idventa,$AcuseReciboSAT,$autorizacion,$serie,$numero,$fecha_dt,$nit_eface,$nombre_eface,$nit_comprador,$nombre_comprador,$backprocesor,$fecha_certificacion,$ResponseDATA1,$ResponseDATA2,$ResponseDATA3,$total,$impuesto)
	{
		$sql="INSERT INTO sat_facturas (idventa,AcuseReciboSAT,autorizacion,serie,numero,fecha_dt,nit_eface,nombre_eface,nit_comprador,nombre_comprador,backprocesor,fecha_certificacion,ResponseDATA1,ResponseDATA2,ResponseDATA3,total,impuesto)
		VALUES ('$idventa','$AcuseReciboSAT','$autorizacion','$serie','$numero','$fecha_dt','$nit_eface','$nombre_eface','$nit_comprador','$nombre_comprador','$backprocesor','$fecha_certificacion','$ResponseDATA1','$ResponseDATA2','$ResponseDATA3','$total','$impuesto')";

		return ejecutarConsulta($sql);
	}

  	//certificar DTE
  	public function certificarDTE($idventa)
    {
    	$venta = new Venta;

    	$token = '';
    	$ultimo_token = $this->obtenerUltimoToken();

    	if($ultimo_token && ($ultimo_token['expira_en'] > date('Ymd'))){
    		$token = $ultimo_token['token'];
    	}else{
    		$login_dte = $this->loginDte();
    		if(!$login_dte['rpta']){
				return $login_dte;
			}else{
				$token = $login_dte['token'];
			}
    	}

  		$show_venta = $venta->mostrar($idventa);
  		$show_detalle = $venta->listarDetalle($idventa);

  		$dataxml = $this->generarXML($show_venta,$show_detalle);
  		$_xml = $dataxml['xml'];

  		//echo htmlspecialchars($_xml);;

  		$ch = curl_init(CERT_URL_DTE);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		curl_setopt($ch, CURLOPT_POSTFIELDS,$_xml);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		  'Authorization:'. $token,
		  'Content-Type: application/json charset=UTF8'
		));

		$response = curl_exec($ch);

		if($response === FALSE){
			return array(
				'message'=>'No se pudo realizar conexión DTE con la SAT, FALLO SERVICIO '.curl_error($ch),
				'token'=> null,
				'rpta'=> false
			);
		    //die(curl_error($ch));
		}
		// Decode the response
		$responseData = json_decode($response, TRUE);

		// Close the cURL handler
		curl_close($ch);

		if($responseData['Codigo'] !== 1){
			return array(
				'message'=>'No se pudo certificar '.$responseData['Mensaje'],
				'token'=> htmlentities($_xml),
				'rpta'=> false
			);
		    //die(curl_error($ch));
		}

		$name_file = '../files/facturas/'.$responseData['Autorizacion'].'.pdf';
		try{
			file_put_contents($name_file, base64_decode($responseData['ResponseDATA3']));
		}catch(\Throwable $th){

		}

		$total = $dataxml['total'];
		$impuesto = $dataxml['impuesto'];

		$inserta_factura = $this->insertarDTE($show_venta['idventa'],$responseData['AcuseReciboSAT'],$responseData['Autorizacion'],$responseData['Serie'],$responseData['NUMERO'],$responseData['Fecha_DTE'],$responseData['NIT_EFACE'],$responseData['NOMBRE_EFACE'],$responseData['NIT_COMPRADOR'],$responseData['NOMBRE_COMPRADOR'],$responseData['BACKPROCESOR'],$responseData['Fecha_de_certificacion'],base64_decode($responseData['ResponseDATA1']),htmlentities(base64_decode($responseData['ResponseDATA2'])),$name_file,$total,$impuesto);

		if($inserta_factura){
			return array(
				'message'=>'Factura registrada correctamente',
				'serie' => $responseData['Serie'],
				'num'=>$responseData['NUMERO'],
				'html'=>base64_decode($responseData['ResponseDATA2']),
				'autorizacion' => $responseData['Autorizacion'],
				'rpta' => true
			);
		}else{
			return array(
				'message'=>'No se pudo registrar Factura',
				'token'=>null,
				'rpta'=>false
			);
		}

    }

    //anular DTE
    public function anularDTE($idventa){
    	$sql = "select * from sat_facturas where idventa = '$idventa'";
    	$result = ejecutarConsultaSimpleFila($sql);

    	if($result){
    		$token = '';
	    	$ultimo_token = $this->obtenerUltimoToken();

	    	if($ultimo_token && ($ultimo_token['expira_en'] > date('Ymd'))){
	    		$token = $ultimo_token['token'];
	    	}else{
	    		$login_dte = $this->loginDte();
	    		if(!$login_dte['rpta']){
					return $login_dte;
				}else{
					$token = $login_dte['token'];
				}
	    	}

    		$_xml = $this->xmlCancel($result);

    		$ch = curl_init(CANCEL_URL_DTE);

			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
			curl_setopt($ch, CURLOPT_POSTFIELDS,$_xml);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			  'Authorization:'. $token,
			  'Content-Type: application/json charset=UTF8'
			));

			$response = curl_exec($ch);

			if($response === FALSE){
				return array(
					'message'=>'No se pudo realizar conexión DTE con la SAT, FALLO SERVICIO '.curl_error($ch),
					'rpta'=> false
				);
			    //die(curl_error($ch));
			}
			// Decode the response
			$responseData = json_decode($response, TRUE);

			// Close the cURL handler
			curl_close($ch);

			if($responseData['Codigo'] !== 1){
				return array(
					'message'=>'No se pudo anular factura '.$responseData['Mensaje'],
					'rpta'=> false,
					'token' => htmlentities($_xml)
				);
			}

			return array(
				'message'=>'Factura anulada correctamente',
				'rpta' => true
			);

    	}else{
    		return array(
				'message'=>'No se pudo anular la factura ',
				'rpta'=> false
			);
    	}
    }

}

?>