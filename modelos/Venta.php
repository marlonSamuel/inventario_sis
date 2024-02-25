<?php 
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";
require_once "Dte.php";

if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}

Class Venta
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	//Implementamos un método para insertar registros
	public function insertar($idcliente,$idusuario,$tipo_comprobante,$tipo_venta,$serie_comprobante,$num_comprobante,$fecha_hora,$impuesto,$total_venta,$idarticulo,$cantidad,$precio_venta,$descuento)
	{
		$dte=new Dte();
		$flag_dte = false;
		
		beginTransaction();
			$sql="INSERT INTO venta (idcliente,idusuario,tipo_comprobante,tipo_venta,serie_comprobante,num_comprobante,fecha_hora,impuesto,total_venta,estado)
			VALUES ('$idcliente','$idusuario','$tipo_comprobante','$tipo_venta','$serie_comprobante','$num_comprobante','$fecha_hora','$impuesto','$total_venta','Aceptado')";
			//return ejecutarConsulta($sql);
			$idventanew=ejecutarConsulta_retornarID($sql);

			$num_elementos=0;
			$sw=true;

			while ($num_elementos < count($idarticulo))
			{
				$sql_detalle = "INSERT INTO detalle_venta(idventa, idarticulo,cantidad,precio_venta,descuento) VALUES ('$idventanew', '$idarticulo[$num_elementos]','$cantidad[$num_elementos]','$precio_venta[$num_elementos]','$descuento[$num_elementos]')";
				ejecutarConsulta($sql_detalle) or $sw = false;
				$num_elementos=$num_elementos + 1;
			}

			$html = '';
			$autorizacion = '';
			if($tipo_comprobante === 'Factura'){
				//CERTIFICAR DTE
				$cert_dte = $dte->certificarDTE($idventanew);

				if($cert_dte['rpta']){
					$serie_dte = $cert_dte['serie'];
					$num_dte = $cert_dte['num'];
					$html = $cert_dte['html'];
					$autorizacion = $cert_dte['autorizacion'];
					$sql_update = "UPDATE venta SET serie_comprobante='$serie_dte', num_comprobante='$num_dte' WHERE idventa='$idventanew'";
					ejecutarConsulta($sql_update) or $sw = false;
				}else{
					return $cert_dte;
				}
				
			}
		commitTransaction();

		if(!$sw){
			return array(
					'message'=>'No se pudo registrar venta',
					'token'=> null,
					'rpta'=> false
			);
		}

		return array(
			'message'=>'Venta registrada correctamente',
			'token'=> null,
			'rpta'=> $sw,
			'html' => $html,
			'autorizacion' => $autorizacion
		);

	}

	
	//Implementamos un método para anular la venta
	public function anular($idventa)
	{
		$dte=new Dte();
		beginTransaction();
		$sql="UPDATE venta SET estado='Anulado' WHERE idventa='$idventa'";

		$sql2 = "UPDATE sat_facturas set estado = 1 WHERE idventa='$idventa' ";

		$sql_detalle = "SELECT * from detalle_venta WHERE idventa = '$idventa'";
		$list_detalle = ejecutarConsulta($sql_detalle);
		while ($value=$list_detalle->fetch_object()) {
			$sql_articulo="SELECT * FROM articulo WHERE idarticulo='$value->idarticulo'";
			$mostrar_articulo = ejecutarConsultaSimpleFila($sql_articulo);
			$stock = $mostrar_articulo['stock']+$value->cantidad;
			$update_stock = "UPDATE articulo SET stock = '$stock' WHERE idarticulo = '$value->idarticulo'";
			ejecutarConsulta($update_stock);
		}

		ejecutarConsulta($sql2);
		$rspta = ejecutarConsulta($sql);

		$sql_venta="SELECT * FROM venta WHERE idventa='$idventa'";
		$result_venta = ejecutarConsultaSimpleFila($sql_venta);
		if($result_venta['tipo_comprobante'] === 'Factura'){
			$anular_factura = $dte->anularDTE($idventa);
			if(!$anular_factura['rpta']){
				return $anular_factura;
			}
		}
		commitTransaction();

		return array(
			'message'=>$rspta ? 'Venta anulada con éxito' : 'No se pudo anular venta',
			'rpta'=>$rspta
		);
		
	}


	//Implementar un método para mostrar los datos de un registro a modificar
	public function mostrar($idventa)
	{
		$sql="SELECT v.idventa,DATE(v.fecha_hora) as fecha,v.idcliente,v.fecha_hora, p.nombre as cliente, p.email,p.tipo_documento,p.num_documento,p.direccion, u.idusuario,u.nombre as usuario,v.tipo_comprobante,v.tipo_venta,v.serie_comprobante,v.num_comprobante,v.total_venta,v.impuesto,v.estado FROM venta v INNER JOIN persona p ON v.idcliente=p.idpersona INNER JOIN usuario u ON v.idusuario=u.idusuario WHERE v.idventa='$idventa'";
		return ejecutarConsultaSimpleFila($sql);
	}

	public function listarDetalle($idventa)
	{
		$sql="SELECT dv.idventa,dv.idarticulo,a.nombre,dv.cantidad,dv.precio_venta,dv.descuento,(dv.cantidad*dv.precio_venta-dv.descuento) as subtotal FROM detalle_venta dv inner join articulo a on dv.idarticulo=a.idarticulo where dv.idventa='$idventa'";
		return ejecutarConsulta($sql);
	}

	//Implementar un método para listar los registros
	public function listar()
	{
		$idusuario = $_SESSION["idusuario"];

		if($_SESSION["cargo"] == "admin"){
			$sql="SELECT v.idventa,DATE(v.fecha_hora) as fecha,v.idcliente,p.nombre as cliente,u.idusuario,u.nombre as usuario,v.tipo_venta,v.tipo_comprobante,v.serie_comprobante,v.num_comprobante,v.total_venta,v.impuesto,v.estado, sf.autorizacion FROM venta v INNER JOIN persona p ON v.idcliente=p.idpersona INNER JOIN usuario u ON v.idusuario=u.idusuario LEFT JOIN sat_facturas sf ON v.idventa = sf.idventa ORDER by v.idventa desc";
		}else{
			$sql="SELECT v.idventa,DATE(v.fecha_hora) as fecha,v.idcliente,p.nombre as cliente,u.idusuario,u.nombre as usuario,v.tipo_venta,v.tipo_comprobante,v.serie_comprobante,v.num_comprobante,v.total_venta,v.impuesto,v.estado, sf.autorizacion FROM venta v INNER JOIN persona p ON v.idcliente=p.idpersona INNER JOIN usuario u ON v.idusuario=u.idusuario LEFT JOIN sat_facturas sf ON v.idventa = sf.idventa WHERE v.idusuario ='$idusuario' ORDER by v.idventa desc";
		}
		
		return ejecutarConsulta($sql);		
	}

	public function ventacabecera($idventa){
		$sql="SELECT v.idventa,v.idcliente,p.nombre as cliente,p.direccion,p.tipo_documento,p.num_documento,p.email,p.telefono,v.idusuario,u.nombre as usuario,v.tipo_comprobante,v.serie_comprobante,v.num_comprobante,date(v.fecha_hora) as fecha,v.impuesto,v.total_venta FROM venta v INNER JOIN persona p ON v.idcliente=p.idpersona INNER JOIN usuario u ON v.idusuario=u.idusuario WHERE v.idventa='$idventa'";
		return ejecutarConsulta($sql);
	}

	public function ventadetalle($idventa){
		$sql="SELECT a.nombre as articulo,a.codigo,d.cantidad,d.precio_venta,d.descuento,(d.cantidad*d.precio_venta-d.descuento) as subtotal FROM detalle_venta d INNER JOIN articulo a ON d.idarticulo=a.idarticulo WHERE d.idventa='$idventa'";
		return ejecutarConsulta($sql);
	}

	public function getLasIdVenta(){
		$sql = "SELECT idventa from venta order by idventa desc limit 1";
		return ejecutarConsultaSimpleFila($sql);
	}
	
}
?>