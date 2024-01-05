<?php 
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}
if (!isset($_SESSION["nombre"]))
{
  header("Location: ../vistas/login.html");//Validamos el acceso solo a los usuarios logueados al sistema.
}
else
{
//Validamos el acceso solo al usuario logueado y autorizado.
if ($_SESSION['consultac']==1 || $_SESSION['consultav']==1)
{
require_once "../modelos/Consultas.php";

$consulta=new Consultas();

switch ($_GET["op"]){
	case 'comprasfecha':
		$fecha_inicio=$_REQUEST["fecha_inicio"];
		$fecha_fin=$_REQUEST["fecha_fin"];

		$rspta=$consulta->comprasfecha($fecha_inicio,$fecha_fin);
 		//Vamos a declarar un array
 		$data= Array();

 		while ($reg=$rspta->fetch_object()){
 			$data[]=array(
 				"0"=>$reg->fecha,
 				"1"=>$reg->usuario,
 				"2"=>$reg->proveedor,
 				"3"=>$reg->tipo_comprobante,
 				"4"=>$reg->serie_comprobante.' '.$reg->num_comprobante,
 				"5"=>'Q '.number_format($reg->total_compra,2),
 				"6"=>'Q '.number_format($reg->impuesto,2),
 				"7"=>($reg->estado=='Aceptado')?'<span class="label bg-green">Aceptado</span>':
 				'<span class="label bg-red">Anulado</span>'
 				);
 		}
 		$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
 		echo json_encode($results);

	break;


	case 'ventasfechacliente':
		$fecha_inicio=$_REQUEST["fecha_inicio"];
		$fecha_fin=$_REQUEST["fecha_fin"];
		$idcliente=$_REQUEST["idcliente"];

		$rspta=$consulta->ventasfechacliente($fecha_inicio,$fecha_fin,$idcliente);
 		//Vamos a declarar un array
 		$data= Array();

 		while ($reg=$rspta->fetch_object()){
 			$data[]=array(
 				"0"=>$reg->fecha,
 				"1"=>$reg->usuario,
 				"2"=>$reg->cliente,
 				"3"=>$reg->tipo_comprobante,
 				"4"=>$reg->serie_comprobante.'-'.$reg->num_comprobante,
 				"5"=>'Q '.number_format($reg->total_venta,2),
 				"6"=>'Q '.number_format($reg->impuesto,2),
 				"7"=>($reg->estado=='Aceptado')?'<span class="label bg-green">Aceptado</span>':
 				'<span class="label bg-red">Anulado</span>'
 				);
 		}
 		$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
 		echo json_encode($results);

	break;

	case 'facturas_dte':
		$fecha_inicio=$_REQUEST["fecha_inicio"];
		$fecha_fin=$_REQUEST["fecha_fin"];

		$rspta=$consulta->facturasDTE($fecha_inicio,$fecha_fin);
 		//Vamos a declarar un array
 		$data= Array();

 		while ($reg=$rspta->fetch_object()){
 			$url = '../files/facturas/'.$reg->autorizacion.'.pdf';

 			$data[]=array(
 				"0"=>'<a target="_blank" href="'.$url.'"> <button class="btn btn-info"><i class="fa fa-file"></i></button></a>',
 				"1"=>date('d/m/Y h:i:s',strtotime($reg->fecha_certificacion)),
 				"2"=>$reg->AcuseReciboSAT,
 				"3"=>$reg->autorizacion,
 				"4"=>$reg->serie.'-'.$reg->numero,
 				"5"=>$reg->nit_comprador,
 				"6"=>$reg->nombre_comprador,
 				"7"=>'Q '.number_format($reg->total,2),
 				"8"=>'Q '.number_format($reg->impuesto,2),
 				"9"=>($reg->estado==0)?'<span class="label bg-green">Aceptada</span>':
 				'<span class="label bg-red">Anulada</span>'
 				);
 		}
 		$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
 		echo json_encode($results);

	break;

	case 'comprasvsventas':
		$anio=$_REQUEST["anio"];

		$rspta=$consulta->getSumVentasCompras($anio);
 		//Vamos a declarar un array
 		$data= Array();
 		$meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
 		while ($reg=$rspta->fetch_object()){

 			$data[]=array(
 				"0"=>$reg->anio,
 				"1"=>$meses[$reg->mes-1],
 				"2"=>'Q '.number_format($reg->total_venta,2),
 				"3"=>'Q '.number_format($reg->total_compra,2)
 				);
 		}
 		$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
 		echo json_encode($results);

	break;
}
//Fin de las validaciones de acceso
}
else
{
  require 'noacceso.php';
}
}
ob_end_flush();
?>