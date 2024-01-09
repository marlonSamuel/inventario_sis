
//Función que se ejecuta al inicio
function init(){
	$("#formulario").on("submit",function(e)
	{
		guardaryeditar(e);	
		//var idusuario = $("idusuario").val();
	});
}

function guardaryeditar(e)
{
	e.preventDefault(); //No se activará la acción predeterminada del evento
	var clave = $("#clave").val();
	var clavec = $("#clavec").val();

	if(clave != clavec){
		alert("Las contraseñas no coinciden");
		return;
	}
	var formData = new FormData($("#formulario")[0]);

	$.ajax({
		url: "../ajax/usuario.php?op=cambiarpass",
	    type: "POST",
	    data: formData,
	    contentType: false,
	    processData: false,

	    success: function(datos)
	    {                    
	          bootbox.alert(datos);	
	    }

	});
}

init();