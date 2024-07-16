<?php 
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}

Class Consultas
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	public function comprasfecha($fecha_inicio,$fecha_fin)
	{
		$idusuario = $_SESSION["idusuario"];

		if($_SESSION["cargo"] == "admin"){
			$sql="SELECT DATE(i.fecha_hora) as fecha,u.nombre as usuario, p.nombre as proveedor,i.tipo_comprobante,i.serie_comprobante,i.num_comprobante,i.total_compra,i.impuesto,i.estado FROM ingreso i INNER JOIN persona p ON i.idproveedor=p.idpersona INNER JOIN usuario u ON i.idusuario=u.idusuario WHERE DATE(i.fecha_hora)>='$fecha_inicio' AND DATE(i.fecha_hora)<='$fecha_fin' AND i.estado = 'Aceptado'";
		}else{
			$sql="SELECT DATE(i.fecha_hora) as fecha,u.nombre as usuario, p.nombre as proveedor,i.tipo_comprobante,i.serie_comprobante,i.num_comprobante,i.total_compra,i.impuesto,i.estado FROM ingreso i INNER JOIN persona p ON i.idproveedor=p.idpersona INNER JOIN usuario u ON i.idusuario=u.idusuario WHERE DATE(i.fecha_hora)>='$fecha_inicio' AND DATE(i.fecha_hora)<='$fecha_fin' AND i.estado = 'Aceptado' AND i.idusuario = '$idusuario'";
		}
		
		return ejecutarConsulta($sql);		
	}

	public function ventasfechacliente($fecha_inicio,$fecha_fin,$idcliente)
	{
		$idusuario = $_SESSION["idusuario"];

		if($_SESSION["cargo"] == "admin"){
			if($idcliente == 0 || $idcliente == ""){
				$sql="SELECT DATE(v.fecha_hora) as fecha,u.nombre as usuario, p.nombre as cliente,v.tipo_comprobante,v.serie_comprobante,v.num_comprobante,v.total_venta,v.impuesto,v.estado FROM venta v INNER JOIN persona p ON v.idcliente=p.idpersona INNER JOIN usuario u ON v.idusuario=u.idusuario WHERE DATE(v.fecha_hora)>='$fecha_inicio' AND DATE(v.fecha_hora)<='$fecha_fin' AND v.estado = 'Aceptado'";
			}else{
				$sql="SELECT DATE(v.fecha_hora) as fecha,u.nombre as usuario, p.nombre as cliente,v.tipo_comprobante,v.serie_comprobante,v.num_comprobante,v.total_venta,v.impuesto,v.estado FROM venta v INNER JOIN persona p ON v.idcliente=p.idpersona INNER JOIN usuario u ON v.idusuario=u.idusuario WHERE DATE(v.fecha_hora)>='$fecha_inicio' AND DATE(v.fecha_hora)<='$fecha_fin' AND v.idcliente='$idcliente' AND v.estado = 'Aceptado'";
			
			}
		}else{
			if($idcliente == 0 || $idcliente == ""){
				$sql="SELECT DATE(v.fecha_hora) as fecha,u.nombre as usuario, p.nombre as cliente,v.tipo_comprobante,v.serie_comprobante,v.num_comprobante,v.total_venta,v.impuesto,v.estado FROM venta v INNER JOIN persona p ON v.idcliente=p.idpersona INNER JOIN usuario u ON v.idusuario=u.idusuario WHERE DATE(v.fecha_hora)>='$fecha_inicio' AND DATE(v.fecha_hora)<='$fecha_fin' AND v.estado = 'Aceptado' AND v.idusuario = '$idusuario' ";
			}else{
				$sql="SELECT DATE(v.fecha_hora) as fecha,u.nombre as usuario, p.nombre as cliente,v.tipo_comprobante,v.serie_comprobante,v.num_comprobante,v.total_venta,v.impuesto,v.estado FROM venta v INNER JOIN persona p ON v.idcliente=p.idpersona INNER JOIN usuario u ON v.idusuario=u.idusuario WHERE DATE(v.fecha_hora)>='$fecha_inicio' AND DATE(v.fecha_hora)<='$fecha_fin' AND v.idcliente='$idcliente' AND v.estado = 'Aceptado' AND v.idusuario = '$idusuario'";
			
			}
		}

		return ejecutarConsulta($sql);					
	}

	public function facturasDTE($fecha_inicio,$fecha_fin){
		$idusuario = $_SESSION["idusuario"];

		if($_SESSION["cargo"] == "admin"){
			$sql="SELECT s.* from sat_facturas s INNER JOIN venta v ON s.idventa = v.idventa
		 	WHERE DATE(fecha_certificacion)>='$fecha_inicio' AND DATE(fecha_certificacion)<='$fecha_fin' order by idfactura desc";
		}else{
			$sql="SELECT s.* from sat_facturas s INNER JOIN venta v ON s.idventa = v.idventa
		 	WHERE DATE(fecha_certificacion)>='$fecha_inicio' AND DATE(fecha_certificacion)<='$fecha_fin' AND v.idusuario = '$idusuario' order by idfactura desc";
		}
		
		return ejecutarConsulta($sql);
	}

	public function totalcomprahoy()
	{
		$sql="SELECT IFNULL(SUM(total_compra),0) as total_compra FROM ingreso WHERE estado = 'Aceptado' AND DATE(fecha_hora)=CURDATE()-1";
		return ejecutarConsulta($sql);
	}

	public function totalventahoy()
	{
		$sql="SELECT IFNULL(SUM(total_venta),0) as total_venta FROM venta WHERE estado = 'Aceptado' AND DATE(fecha_hora)=CURDATE()-1";
		return ejecutarConsulta($sql);
	}

	public function comprasultimos_10dias()
	{
		$sql="SELECT CONCAT(DAY(fecha_hora),'-',MONTH(fecha_hora)) as fecha,SUM(total_compra) as total FROM ingreso WHERE estado = 'Aceptado' GROUP by fecha_hora ORDER BY fecha_hora DESC limit 0,10";
		return ejecutarConsulta($sql);
	}

	public function ventasultimos_12meses()
	{
		$sql="SELECT DATE_FORMAT(fecha_hora,'%M') as fecha,SUM(total_venta) as total FROM venta WHERE estado = 'Aceptado' GROUP by  DATE_FORMAT(fecha_hora,'%M') ORDER BY  DATE_FORMAT(fecha_hora,'%M') DESC limit 0,10";
		return ejecutarConsulta($sql);
	}

	public function consulta_facturas()
	{
		$sql = "select (SELECT count(*) from sat_facturas where estado = 1 AND MONTH(fecha_certificacion) = MONTH(now()) AND YEAR(fecha_certificacion) = YEAR(now())) as total_anuladas, 
			count(*) as cantidad_activas, sum(total) as total, sum(impuesto) as total_impuesto from sat_facturas where estado = 0
			AND MONTH(fecha_certificacion) = MONTH(now()) AND YEAR(fecha_certificacion) = YEAR(now())";
		return ejecutarConsulta($sql);
	}

	public function getSumVentasCompras($anio)
	{
		$sql = "select * from COMPRASVSVENTAS where anio = '$anio' order by anio, mes desc";

		 return ejecutarConsulta($sql);
	}

	public function reporteCompraArticulos($fecha_inicio,$fecha_fin){
		 $sql="select i.fecha_hora fecha, a.codigo, 
				a.nombre producto, ROUND((d.precio_compra / d.cantidad),2) costo_por_articulo, d.cantidad, d.precio_compra costo_total from ingreso i
				inner join detalle_ingreso d on i.idingreso = d.idingreso
				inner join articulo a on d.idarticulo = a.idarticulo
				where i.estado = 'Aceptado' and i.fecha_hora between '$fecha_inicio' and '$fecha_fin'
				order by d.iddetalle_ingreso desc";
		
		return ejecutarConsulta($sql);
	}

}

?>