<?php 
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Consultas
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	public function comprasfecha($fecha_inicio,$fecha_fin)
	{
		$sql="SELECT DATE(i.fecha_hora) as fecha,u.nombre as usuario, p.nombre as proveedor,i.tipo_comprobante,i.serie_comprobante,i.num_comprobante,i.total_compra,i.impuesto,i.estado FROM ingreso i INNER JOIN persona p ON i.idproveedor=p.idpersona INNER JOIN usuario u ON i.idusuario=u.idusuario WHERE DATE(i.fecha_hora)>='$fecha_inicio' AND DATE(i.fecha_hora)<='$fecha_fin'";
		return ejecutarConsulta($sql);		
	}

	public function ventasfechacliente($fecha_inicio,$fecha_fin,$idcliente)
	{
		if($idcliente == 0 || $idcliente == ""){
			$sql="SELECT DATE(v.fecha_hora) as fecha,u.nombre as usuario, p.nombre as cliente,v.tipo_comprobante,v.serie_comprobante,v.num_comprobante,v.total_venta,v.impuesto,v.estado FROM venta v INNER JOIN persona p ON v.idcliente=p.idpersona INNER JOIN usuario u ON v.idusuario=u.idusuario WHERE DATE(v.fecha_hora)>='$fecha_inicio' AND DATE(v.fecha_hora)<='$fecha_fin'";
		}else{
			$sql="SELECT DATE(v.fecha_hora) as fecha,u.nombre as usuario, p.nombre as cliente,v.tipo_comprobante,v.serie_comprobante,v.num_comprobante,v.total_venta,v.impuesto,v.estado FROM venta v INNER JOIN persona p ON v.idcliente=p.idpersona INNER JOIN usuario u ON v.idusuario=u.idusuario WHERE DATE(v.fecha_hora)>='$fecha_inicio' AND DATE(v.fecha_hora)<='$fecha_fin' AND v.idcliente='$idcliente'";
		
		}

		return ejecutarConsulta($sql);		
			
	}

	public function facturasDTE($fecha_inicio,$fecha_fin){
		$sql="SELECT * from sat_facturas WHERE DATE(fecha_certificacion)>='$fecha_inicio' AND DATE(fecha_certificacion)<='$fecha_fin' order by idfactura desc";
		return ejecutarConsulta($sql);
	}

	public function totalcomprahoy()
	{
		$sql="SELECT IFNULL(SUM(total_compra),0) as total_compra FROM ingreso WHERE DATE(fecha_hora)=SUBDATE(CURDATE(), 1)";
		return ejecutarConsulta($sql);
	}

	public function totalventahoy()
	{
		$sql="SELECT IFNULL(SUM(total_venta),0) as total_venta FROM venta WHERE DATE(fecha_hora)=SUBDATE(CURDATE(), 1)";
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

}

?>