<?php
//Activamos el almacenamiento en el buffer
ob_start();
session_start();

if (!isset($_SESSION["nombre"]))
{
  header("Location: login.html");
}
else
{
require 'header.php';

if ($_SESSION['escritorio']==1)
{
  require_once "../modelos/Consultas.php";
  $consulta = new Consultas();
  $rsptac = $consulta->totalcomprahoy();
  $regc=$rsptac->fetch_object();
  $totalc=$regc->total_compra;

  $rsptav = $consulta->totalventahoy();
  $regv=$rsptav->fetch_object();
  $totalv=$regv->total_venta;

  //Datos para mostrar el gráfico de barras de las compras
  $compras10 = $consulta->comprasultimos_10dias();
  $fechasc='';
  $totalesc='';
  while ($regfechac= $compras10->fetch_object()) {
    $fechasc=$fechasc.'"'.$regfechac->fecha .'",';
    $totalesc=$totalesc.$regfechac->total .','; 
  }

  //Quitamos la última coma
  $fechasc=substr($fechasc, 0, -1);
  $totalesc=substr($totalesc, 0, -1);

   //Datos para mostrar el gráfico de barras de las ventas
  $ventas12 = $consulta->ventasultimos_12meses();
  $fechasv='';
  $totalesv='';
  while ($regfechav= $ventas12->fetch_object()) {
    $fechasv=$fechasv.'"'.$regfechav->fecha .'",';
    $totalesv=$totalesv.$regfechav->total .','; 
  }

  //Quitamos la última coma
  $fechasv=substr($fechasv, 0, -1);
  $totalesv=substr($totalesv, 0, -1);

  $cantidad_facts_activas = 0;
  $total_facts = 0;
  $total_impuestos = 0;
  $cantidad_anuladas = 0;

  $rsptafacts = $consulta->consulta_facturas();
  $regfacts=$rsptafacts->fetch_object();

  $cantidad_facts_activas=$regfacts->cantidad_activas;
  $total_facts=$regfacts->total;
  $total_impuestos=$regfacts->total_impuesto;
  $cantidad_anuladas = $regfacts->total_anuladas;

  //Datos para mostrar el gráfico de barras de las ventas
  $compraventa = $consulta->getSumVentasCompras(date('Y'));
  $fechascompraventa='';
  $totalcompras='';
  $totalventas = '';
  while ($regcv= $compraventa->fetch_object()) {
     $fechascompraventa=$fechascompraventa.'"'.$regcv->mes .'-'.$regcv->anio. '",';
     $totalcompras=$totalcompras.$regcv->total_compra .','; 
     $totalventas=$totalventas.$regcv->total_venta .','; 
  }

?>
<!--Contenido-->
      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">        
        <!-- Main content -->
        <section class="content">
            <div class="row">
              <div class="col-md-12">
                  <div class="box">
                    <div class="box-header with-border">
                          <h1 class="box-title">INICIO </h1>
                        <div class="box-tools pull-right">
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <!-- centro -->
                    <div class="panel-body">
                      <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                          <div class="small-box bg-aqua">
                              <div class="inner">
                                <h4 style="font-size:17px;">
                                  <strong>Q. <?php echo $total_facts; ?></strong>
                                </h4>
                                <p>Total facturas DTE este mes</p>
                              </div>
                              <div class="icon">
                                <i class="ion ion-bag"></i>
                              </div>
                              <a href="facturasfecha.php" class="small-box-footer">Facturas <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>


                        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                          <div class="small-box bg-red">
                              <div class="inner">
                                <h4 style="font-size:17px;">
                                  <strong>Q. <?php echo $total_impuestos; ?></strong>
                                </h4>
                                <p>Impuestos generados este mes</p>
                              </div>
                              <div class="icon">
                                <i class="ion ion-bag"></i>
                              </div>
                              <a href="facturasfecha.php" class="small-box-footer">Facturas <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                          <div class="small-box bg-aqua">
                              <div class="inner">
                                <h4 style="font-size:17px;">
                                  <strong><?php echo $cantidad_facts_activas; ?></strong>
                                </h4>
                                <p>Cantidad de facturas DTE este mes</p>
                              </div>
                              <div class="icon">
                                <i class="ion ion-bag"></i>
                              </div>
                              <a href="facturasfecha.php" class="small-box-footer">Facturas <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                          <div class="small-box bg-red">
                              <div class="inner">
                                <h4 style="font-size:17px;">
                                  <strong> <?php echo $cantidad_anuladas; ?></strong>
                                </h4>
                                <p>Facturas anuladas este mes</p>
                              </div>
                              <div class="icon">
                                <i class="ion ion-bag"></i>
                              </div>
                              <a href="facturasfecha.php" class="small-box-footer">Facturas <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>

                    </div>

                    <div class="panel-body">
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                          <div class="small-box bg-aqua">
                              <div class="inner">
                                <h4 style="font-size:17px;">
                                  <strong>Q. <?php echo $totalc; ?></strong>
                                </h4>
                                <p>Compras hoy</p>
                              </div>
                              <div class="icon">
                                <i class="ion ion-bag"></i>
                              </div>
                              <a href="ingreso.php" class="small-box-footer">Compras <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                          <div class="small-box bg-green">
                              <div class="inner">
                                <h4 style="font-size:17px;">
                                  <strong>Q. <?php echo $totalv; ?></strong>
                                </h4>
                                <p>Ventas hoy</p>
                              </div>
                              <div class="icon">
                                <i class="ion ion-bag"></i>
                              </div>
                              <a href="venta.php" class="small-box-footer">Ventas <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">

                        <div  class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                          <div class="box box-primary">
                              <div class="box-header with-border">
                                Compras vs Ventas año actual
                              </div>
                              <div class="box-body">
                                <canvas id="comprasventas" width="400" height="600"></canvas>
                              </div>
                          </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                          <div class="box box-primary">
                              <div class="box-header with-border">
                                Compras de los últimos 10 días
                              </div>
                              <div class="box-body">
                                <canvas id="compras" width="400" height="300"></canvas>
                              </div>
                          </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                          <div class="box box-primary">
                              <div class="box-header with-border">
                                Ventas de los últimos 12 meses
                              </div>
                              <div class="box-body">
                                <canvas id="ventas" width="400" height="300"></canvas>
                              </div>
                          </div>
                        </div>


                    </div>
                    <!--Fin centro -->
                  </div><!-- /.box -->
              </div><!-- /.col -->
          </div><!-- /.row -->
      </section><!-- /.content -->

    </div><!-- /.content-wrapper -->
  <!--Fin-Contenido-->
<?php
}
else
{
  require 'noacceso.php';
}

require 'footer.php';
?>

<script src="../public/js/Chart.min.js"></script>
<script src="../public/js/Chart.bundle.min.js"></script> 
<script type="text/javascript">
var ctx = document.getElementById("compras").getContext('2d');
var compras = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [<?php echo $fechasc; ?>],
        datasets: [{
            label: 'Compras en Q. de los últimos 10 días',
            data: [<?php echo $totalesc; ?>],
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)'
            ],
            borderColor: [
                'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
                'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero:true,
                    callback: function(value, index, values) {
                       return value.toLocaleString("es-GT",{style:"currency", currency:"GTQ"});
                     }
                }
            }]
        }
    }
});

var ctx = document.getElementById("comprasventas").getContext('2d');
var compras = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [<?php echo $fechascompraventa; ?>],
        datasets: [
          {
            label: 'Ventas',
            data: [<?php echo $totalventas; ?>],
            borderColor: 'rgba(75, 192, 192, 1)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
          },
          {
            label: 'Compras',
            data: [<?php echo $totalcompras; ?>],
            borderColor: 'rgba(255,99,132,1)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
          }
        ]
    },
    options: {
      maintainAspectRatio: false,
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero:true,
                    callback: function(value, index, values) {
                       return value.toLocaleString("es-GT",{style:"currency", currency:"GTQ"});
                     }
                }
            }]
        }
    }
});

var ctx = document.getElementById("ventas").getContext('2d');
var ventas = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [<?php echo $fechasv; ?>],
        datasets: [{
            label: 'Ventas en Q. de los últimos 12 Meses',
            data: [<?php echo $totalesv; ?>],
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)'
            ],
            borderColor: [
                'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
                'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero:true,
                    callback: function(value, index, values) {
                       return value.toLocaleString("es-GT",{style:"currency", currency:"GTQ"});
                     }
                }
            }]
        }
    }
});
</script>


</script>


<?php 
}
ob_end_flush();
?>


