
var spinnerInstance = new MySpinner();

var tabla;
var data_detalle = [];

//Función que se ejecuta al inicio
function init(){
	mostrarform(false);
	listar();

	$("#formulario").on("submit",function(e)
	{
		guardaryeditar(e);	
	});
	//Cargamos los items al select cliente
	$.post("../ajax/venta.php?op=selectCliente", function(r){
	            $("#idcliente").html(r);
	            $('#idcliente').selectpicker('refresh');
	});
	$('#mVentas').addClass("treeview active");
    $('#lVentas').addClass("active");
}

//Función limpiar
function limpiar()
{
	$("#idcliente").val("");
	$("#cliente").val("");
	$("#idcliente").selectpicker('refresh');
	$("#serie_comprobante").val("");
	$("#num_comprobante").val("");
	$("#impuesto").val("0");

	$("#total_venta").val("");
	$(".filas").remove();
	$("#total").html("0");

	//Obtenemos la fecha actual
	var now = new Date();
	var day = ("0" + now.getDate()).slice(-2);
	var month = ("0" + (now.getMonth() + 1)).slice(-2);
	var today = now.getFullYear()+"-"+(month)+"-"+(day) ;
    $('#fecha_hora').val(today);

    //Marcamos el primer tipo_documento
    $("#tipo_comprobante").val("Boleta");
	$("#tipo_comprobante").selectpicker('refresh');

	//Marcamos el primer tipo_documento
    $("#tipo_comprobante").val("CA");
	$("#tipo_comprobante").selectpicker('refresh');
}

//Función mostrar formulario
function mostrarform(flag)
{
	data_detalle = [];
	//limpiar();
	if (flag)
	{
		limpiar();
		$("#listadoregistros").hide();
		$("#formularioregistros").show();
		//$("#btnGuardar").prop("disabled",false);
		$("#btnagregar").hide();
		listarArticulos();

		$("#btnGuardar").hide();
		$("#btnCancelar").show();
		$("#btnAgregarArt").show();
		detalles=0;
		getLastId();
	}
	else
	{
		$("#listadoregistros").show();
		$("#formularioregistros").hide();
		$("#btnagregar").show();
	}
}

//Función cancelarform
function cancelarform()
{
	limpiar();
	mostrarform(false);
}

//Función Listar
function listar()
{
	tabla=$('#tbllistado').dataTable(
	{
		"lengthMenu": [ 5, 10, 25, 75, 100],//mostramos el menú de registros a revisar
		"aProcessing": true,//Activamos el procesamiento del datatables
	    "aServerSide": true,//Paginación y filtrado realizados por el servidor
	    dom: '<Bl<f>rtip>',//Definimos los elementos del control de tabla
	    buttons: [		          
		            'copyHtml5',
		            'excelHtml5',
		            'csvHtml5',
		            'pdf'
		        ],
		"ajax":
				{
					url: '../ajax/venta.php?op=listar',
					type : "get",
					dataType : "json",						
					error: function(e){
						console.log(e.responseText);	
					}
				},
		"language": {
            "lengthMenu": "Mostrar : _MENU_ registros",
            "buttons": {
            "copyTitle": "Tabla Copiada",
            "copySuccess": {
                    _: '%d líneas copiadas',
                    1: '1 línea copiada'
                }
            }
        },
		"bDestroy": true,
		"iDisplayLength": 5,//Paginación
	    "order": [[ 0, "desc" ]]//Ordenar (columna,orden)
	}).DataTable();
}


//Función ListarArticulos
function listarArticulos()
{
	tabla=$('#tblarticulos').dataTable(
	{
		"aProcessing": true,//Activamos el procesamiento del datatables
	    "aServerSide": true,//Paginación y filtrado realizados por el servidor
	    dom: 'Bfrtip',//Definimos los elementos del control de tabla
	    buttons: [		          
		            
		        ],
		"ajax":
				{
					url: '../ajax/venta.php?op=listarArticulosVenta',
					type : "get",
					dataType : "json",						
					error: function(e){
						console.log(e.responseText);	
					}
				},
		"bDestroy": true,
		"iDisplayLength": 5,//Paginación
	    "order": [[ 0, "desc" ]]//Ordenar (columna,orden)
	}).DataTable();
}
//Función para guardar o editar

function guardaryeditar(e)
{
	e.preventDefault(); //No se activará la acción predeterminada del evento
	//$("#btnGuardar").prop("disabled",true);
	modificarSubototales();

	var isValid = validarSock();
	if(!isValid){
		return;
	}

	spinnerInstance.show();
	var formData = new FormData($("#formulario")[0]);
	$.ajax({
		url: "../ajax/venta.php?op=guardaryeditar",
	    type: "POST",
	    data: formData,
	    contentType: false,
	    processData: false,

	    success: function(datos)
	    {
	    	spinnerInstance.hide();  

	    	mostrarform(false);
	        listar();  
	    	try{
	    		data = JSON.parse(datos);
	    	}catch(error){
	    		bootbox.alert(datos);
	    		return;
	    	}               
	        bootbox.alert({
              size: 'large',
              title: data.message,
              message: (data.html !== undefined && data.html !== "") ? data.html:data.message,
              callback: function(result) { 
              	if(data.autorizacion !== undefined && data.autorizacion !== ""){
              		//location.href ='inventarios/files/facturas/'+data.autorizacion+'.pdf';

              		//location.href ='../files/facturas/'+data.autorizacion+'.pdf';
              		window.open(
					  'http://164.92.77.67/files/facturas/'+data.autorizacion+'.pdf',
					  '_blank' // <- This is what makes it open in a new window.
					);
              	}
               }
             });	          
	          
	    },
	    error: function(){
	    	spinnerInstance.hide();
	    }

	});
	limpiar();
}

function validarSock(){
	 	var cant = document.getElementsByName("cantidad[]");
    var pren = document.getElementsByName("nombre[]");
    var dess = document.getElementsByName("stock[]");

    var isValid = true;

    for (var i = 0; i <cant.length; i++) {
    	var inpC=cant[i];
    	var inpN=pren[i];
    	var inpS=dess[i];

    	if(parseInt(inpC.value) > parseInt(inpS.value)){
    		var isValid = false;
    		bootbox.alert("Stock insuficiente para "+inpN.value+" unidades disponibles: "+inpS.value);
    		break;
    	}
    }
    return isValid;
}

function mostrar(idventa)
{
	$.post("../ajax/venta.php?op=mostrar",{idventa : idventa}, function(data, status)
	{
		data = JSON.parse(data);		
		mostrarform(true);

		$("#idcliente").val(data.idcliente);
		$("#idcliente").selectpicker('refresh');
		$("#tipo_comprobante").val(data.tipo_comprobante);
		$("#tipo_comprobante").selectpicker('refresh');
		$("#serie_comprobante").val(data.serie_comprobante);
		$("#num_comprobante").val(data.num_comprobante);
		$("#fecha_hora").val(data.fecha);
		$("#impuesto").val(data.impuesto);
		$("#idventa").val(data.idventa);

		//Ocultar y mostrar los botones
		$("#btnGuardar").hide();
		$("#btnCancelar").show();
		$("#btnAgregarArt").hide();
 	});

 	$.post("../ajax/venta.php?op=listarDetalle&id="+idventa,function(r){
	        $("#detalles").html(r);
	});	
}

//Función para anular registros
function anular(idventa)
{
	bootbox.confirm("¿Está Seguro de anular la venta?", function(result){
		if(result)
        {
        	spinnerInstance.show();
        	$.post("../ajax/venta.php?op=anular", {idventa : idventa}, function(e){
        		spinnerInstance.hide();
        		var data = JSON.parse(e);
        		console.log(data);
        		bootbox.alert(data.message);
	            tabla.ajax.reload();
        	});	
        }
	})
}


function getLastId()
{
	$.post("../ajax/venta.php?op=getLastId", function(data, status)
	{
		data = JSON.parse(data);
		var num_c = 1;
		if(data !== null){
			num_c = parseInt(data.idventa)+1;	
		}
		$("#serie_comprobante").val('A');
		$("#num_comprobante").val(num_c);
 	});
}

//Declaración de variables necesarias para trabajar con las compras y
//sus detalles
var impuesto=5;
var cont=0;
var detalles=0;
//$("#guardar").hide();
$("#btnGuardar").hide();
$("#tipo_comprobante").change(marcarImpuesto);

function marcarImpuesto()
  {
  	var tipo_comprobante=$("#tipo_comprobante option:selected").text();
  	if (tipo_comprobante=='Factura')
    {
        $("#impuesto").val(impuesto); 
    }
    else
    {
        $("#impuesto").val("0"); 
        getLastId();
    }
  }

function agregarDetalle(idarticulo,articulo,precio_venta,stock)
  {
  	var cantidad=1;
    var descuento=0;

    var exists = data_detalle.some(x=>x.idarticulo == idarticulo); //validamos si existe el articulo en la tabla detalle

    //si existe ya no se agrega
    if(exists){
    	return;
    }

    if (idarticulo!="")
    {
    	var subtotal=cantidad*precio_venta;
    	var fila='<tr class="filas" id="fila'+cont+'">'+
    	'<td><button type="button" class="btn btn-danger" onclick="eliminarDetalle('+cont+')">X</button></td>'+
    	'<td><input type="hidden" name="stock[]" value="'+stock+'"> <input type="hidden" name="nombre[]" value="'+articulo+'"> <input type="hidden" name="idarticulo[]" value="'+idarticulo+'">'+articulo+'</td>'+
    	'<td><input oninput="validOnInput()" min="1" type="number" name="cantidad[]" id="cantidad[]" value="'+cantidad+'"></td>'+
    	'<td><input oninput="modificarSubototales()" type="number" step=".01" name="precio_venta[]" id="precio_venta[]" value="'+precio_venta+'"></td>'+
    	'<td><input oninput="modificarSubototales()" type="number" step=".01" name="descuento[]" value="'+descuento+'"></td>'+
    	'<td><span name="subtotal" id="subtotal'+cont+'">'+subtotal+'</span></td>'+
    	'<td><button type="button" onclick="modificarSubototales()" class="btn btn-info"><i class="fa fa-refresh"></i></button></td>'+
    	'</tr>';

    	data_detalle.push({
    		indice: cont,
    		idarticulo: idarticulo,
    		stock: stock
    	});

    	cont++;
    	detalles=detalles+1;
    	$('#detalles').append(fila);
    	modificarSubototales();
    }
    else
    {
    	alert("Error al ingresar el detalle, revisar los datos del artículo");
    }
  }

  function validOnInput(){
  	var cant = document.getElementsByName("cantidad[]");

    for (var i = 0; i <cant.length; i++) {
    	var inpC=cant[i];

    	if(parseInt(inpC.value) < 1){
    		cant[i].value = 1;
    	}
    }
    modificarSubototales();
  }

  function modificarSubototales()
  {
  	var cant = document.getElementsByName("cantidad[]");
    var prec = document.getElementsByName("precio_venta[]");
    var desc = document.getElementsByName("descuento[]");
    var sub = document.getElementsByName("subtotal");

    for (var i = 0; i <cant.length; i++) {
    	var inpC=cant[i];
    	var inpP=prec[i];
    	var inpD=desc[i];
    	var inpS=sub[i];

    	inpS.value=(inpC.value * inpP.value)-inpD.value;
    	document.getElementsByName("subtotal")[i].innerHTML = inpS.value;
    }
    calcularTotales();

  }
  function calcularTotales(){
  	var sub = document.getElementsByName("subtotal");
  	var total = 0.0;

  	for (var i = 0; i <sub.length; i++) {
		total += document.getElementsByName("subtotal")[i].value;
	}
	$("#total").html("Q. " + total);
    $("#total_venta").val(total);
    evaluar();
  }

  function evaluar(){
  	if (detalles>0)
    {
      $("#btnGuardar").show();
    }
    else
    {
      $("#btnGuardar").hide(); 
      cont=0;
    }
  }

  function eliminarDetalle(indice){
  	$("#fila" + indice).remove();
  	calcularTotales();
  	detalles=detalles-1;
  	evaluar()
  	data_detalle = data_detalle.filter(x=>x.indice !== indice);
  }

init();