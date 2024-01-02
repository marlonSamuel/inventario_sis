<?php
//Activamos el almacenamiento en el buffer
ob_start();
if (strlen(session_id()) < 1) 
  session_start();

if (!isset($_SESSION["nombre"]))
{
  echo 'Debe ingresar al sistema correctamente para visualizar el reporte';
}
else
{
	if ($_SESSION['ventas']==1)
	{
		//Check if user has right to access the file. If no, show access denied and exit the script.
		$path = $_SERVER['REQUEST_URI'];
		$paths = explode('/', $path);
		$lastIndex = count($paths) - 1;
		$fileName = $paths[$lastIndex]; // Maybe add some code to detect subfolder if you have them
		// Check if that file exists, if no show some error message
		// Output headers here
		header('Content-Type: application/pdf');
    	header("Content-Disposition: attachment; filename='$paths[$lastIndex]'");

		readfile('files/facturas/'.$fileName);
	}else{
		echo "no posee permisos para ver las facturas";
	}
}
