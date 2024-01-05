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

if ($_SESSION['consultav']==1 && $_SESSION['consultac']==1)
{

  $years = range(strftime("%Y", time()), 2000);
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
                          <h1 class="box-title">COMPRA VS VENTAS </h1>
                        <div class="box-tools pull-right">
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <!-- centro -->
                    <div class="panel-body table-responsive" id="listadoregistros">
                        <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12">
                          <label>AÑO</label>
                          <select class="form-control" id="anio">
                              <option><?php echo date('Y'); ?></option>
                              <?php foreach($years as $year) : ?>
                                <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                              <?php endforeach; ?>
                            </select>
                        </div>
                        <table id="tbllistado" class="table table-striped table-bordered table-condensed table-hover">
                          <thead>
                            <th>AÑO</th>
                            <th>MES</th>
                            <th>TOTAL VENTAS</th>
                            <th>TOTAL COMPRAS</th>
                          </thead>
                          <tbody>                            
                          </tbody>
                          <tfoot>
                            <th>AÑO</th>
                            <th>MES</th>
                            <th>TOTAL VENTAS</th>
                            <th>TOTAL COMPRAS</th>
                          </tfoot>
                        </table>
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
<script type="text/javascript" src="scripts/comprasvsventas.js"></script>
<?php 
}
ob_end_flush();
?>


