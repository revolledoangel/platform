
<div class="content-wrapper">
    <!-- Encabezado -->
    <section class="content-header">
        <h1>
            Media Mix - Inmobiliarias
            <small>Administrar mezcla de campañas</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="home"><i class="fa fa-home"></i> Inicio</a></li>
            <li class="active">Media Mix Inmobiliarias</li>
        </ol>
    </section>

    <!-- Contenido principal -->
    <section class="content">

        <div class="box">

            <div class="box-header with-border">

                <!-- Filtro por periodo -->
                <div class="form-group pull-left" style="margin-right: 15px;">
                    <label for="filterPeriod">Filtrar por periodo:</label>
                    <select id="filterPeriod" class="form-control" style="width: 200px; display: inline-block;">
                        <option value="">Todos</option>
                        <option value="2025-Q1">2025 - Q1</option>
                        <option value="2025-Q2">2025 - Q2</option>
                        <option value="2025-Q3">2025 - Q3</option>
                        <option value="2025-Q4">2025 - Q4</option>
                    </select>
                </div>

                <!-- Filtro por cliente -->
                <div class="form-group pull-left">
                    <label for="filterClient">Filtrar por cliente:</label>
                    <select id="filterClient" class="form-control" style="width: 200px; display: inline-block;">
                        <option value="">Todos</option>
                        <option value="Inmobiliaria Alfa">Inmobiliaria Alfa</option>
                        <option value="Inmobiliaria Beta">Inmobiliaria Beta</option>
                        <option value="Inmobiliaria Gamma">Inmobiliaria Gamma</option>
                    </select>
                </div>

            </div>

            <!-- Tabla -->
            <div class="box-body">
                <div class="table-responsive">
                    <table id="mediaMixTable" class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th style="max-width:150px">Periodo</th>
                                <th style="max-width:200px">Cliente</th>
                                <th style="max-width:150px">Fecha de creación</th>
                                <th style="max-width:150px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>2025-Q1</td>
                                <td>Inmobiliaria Alfa</td>
                                <td>15/01/2025</td>
                                <td>
                                    <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#viewMediaMixModal">
                                        <i class="fa fa-eye"></i> Ver
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>2025-Q2</td>
                                <td>Inmobiliaria Beta</td>
                                <td>03/04/2025</td>
                                <td>
                                    <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#viewMediaMixModal">
                                        <i class="fa fa-eye"></i> Ver
                                    </button>
                                </td>
                            </tr>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

    </section>
</div>

<!-- Modal de vista -->
<div class="modal fade" id="viewMediaMixModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width: 95%;">
        <div class="modal-content">
            <div class="modal-header" style="background:#00013b;color:#fff">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Detalle Media Mix - Inmobiliaria Alfa</h4>
            </div>
            <div class="modal-body">

                <!-- Tabla de detalles -->
                <div class="table-responsive">
                    <table id="mediaMixDetailTable" class="table table-bordered table-striped">
                        <thead style="background-color:#f4f4f4;">
                            <tr>
                                <th>Cliente</th>
                                <th>Proyecto</th>
                                <th>Campaña</th>
                                <th>Es AON</th>
                                <th>Segmentación</th>
                                <th>Plataforma</th>
                                <th>Medio</th>
                                <th>Tipo</th>
                                <th>Formato</th>
                                <th>Moneda</th>
                                <th>Inversión</th>
                                <th>Tipo de Resultado</th>
                                <th>Costo por Resultado</th>
                                <th>Proyección</th>
                                <th>SOI</th>
                                <th>Comentarios</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td rowspan="4" style="vertical-align: middle; text-align:center;">Inmobiliaria Alfa</td>
                                <td>Milano</td>
                                <td>Pauta Regular</td>
                                <td>No</td>
                                <td>LAL</td>
                                <td>Facebook</td>
                                <td>Lead Ads</td>
                                <td>Performance</td>
                                <td>Imagen</td>
                                <td>USD</td>
                                <td>$1,500.00</td>
                                <td>Leads</td>
                                <td>$7.00</td>
                                <td>214</td>
                                <td>20%</td>
                                <td>Core Inversionistas</td>
                                <td>
                                    <button class="btn btn-warning btn-sm btnEditRow">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Brick</td>
                                <td>Interacción Geolocalizado</td>
                                <td>No</td>
                                <td>Geo</td>
                                <td>Facebook</td>
                                <td>Interacción</td>
                                <td>Branding</td>
                                <td>Video</td>
                                <td>USD</td>
                                <td>$100.00</td>
                                <td>Interacciones</td>
                                <td>$0.01</td>
                                <td>20,000</td>
                                <td>6.67%</td>
                                <td>-</td>
                                <td>
                                    <button class="btn btn-warning btn-sm btnEditRow">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Jardines</td>
                                <td>Lead Ads</td>
                                <td>Sí</td>
                                <td>Intereses / LAL</td>
                                <td>Facebook</td>
                                <td>Lead Ads</td>
                                <td>Performance</td>
                                <td>Imagen</td>
                                <td>USD</td>
                                <td>$1,100.00</td>
                                <td>Leads</td>
                                <td>$4.00</td>
                                <td>275</td>
                                <td>20%</td>
                                <td>Campaña activa en TikTok 20 días</td>
                                <td>
                                    <button class="btn btn-warning btn-sm btnEditRow">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Buenavista</td>
                                <td>Pauta Regular</td>
                                <td>No</td>
                                <td>LAL</td>
                                <td>Facebook</td>
                                <td>Lead Ads</td>
                                <td>Performance</td>
                                <td>Imagen</td>
                                <td>USD</td>
                                <td>$1,500.00</td>
                                <td>Leads</td>
                                <td>$5.00</td>
                                <td>300</td>
                                <td>20%</td>
                                <td>-</td>
                                <td>
                                    <button class="btn btn-warning btn-sm btnEditRow">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Resumen -->
                <div style="margin-top:20px;">
                    <table id="summaryTable" class="table table-bordered" style="width:auto; margin-bottom:0;">
                        <tbody>
                            <tr>
                                <th style="background-color:#f4f4f4;">Marca</th>
                                <td>$500.00</td>
                                <td>100</td>
                            </tr>
                            <tr>
                                <th style="background-color:#f4f4f4;">Pauta</th>
                                <td>$7,500.00</td>
                                <td>$5.49</td>
                                <td>1,367</td>
                                <td>100.00%</td>
                            </tr>
                            <tr>
                                <th style="background-color:#f4f4f4;">Fee</th>
                                <td>$1,875.00</td>
                            </tr>
                            <tr>
                                <th style="background-color:#f4f4f4;">Pauta + Fee</th>
                                <td>$9,375.00</td>
                            </tr>
                            <tr>
                                <th style="background-color:#f4f4f4;">Inversión total + IGV</th>
                                <td>$11,062.50</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="exportExcel">
                    <i class="fa fa-file-excel-o"></i> Descargar Excel
                </button>
                <button type="button" class="btn btn-danger" id="exportPDF">
                    <i class="fa fa-file-pdf-o"></i> Descargar PDF
                </button>
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Librerías de exportación -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script>
$(function(){

    // Editar fila
    $(document).on('click', '.btnEditRow', function(){
        let row = $(this).closest('tr');
        row.find('td').not(':first, :last').each(function(){
            let text = $(this).text().trim();
            $(this).html('<input type="text" class="form-control" value="'+text+'">');
        });
        $(this)
            .removeClass('btnEditRow btn-warning')
            .addClass('btnSaveRow btn-success')
            .html('<i class="fa fa-save"></i>');
    });

    // Guardar fila
    $(document).on('click', '.btnSaveRow', function(){
        let row = $(this).closest('tr');
        row.find('td').not(':first, :last').each(function(){
            let val = $(this).find('input').val();
            $(this).html(val);
        });
        $(this)
            .removeClass('btnSaveRow btn-success')
            .addClass('btnEditRow btn-warning')
            .html('<i class="fa fa-pencil"></i>');
    });

    // Exportar a Excel
    $('#exportExcel').on('click', function(){
        let wb = XLSX.utils.book_new();
        let ws1 = XLSX.utils.table_to_sheet(document.querySelector('#mediaMixDetailTable'));
        let ws2 = XLSX.utils.table_to_sheet(document.querySelector('#summaryTable'));
        XLSX.utils.book_append_sheet(wb, ws1, "Detalle");
        XLSX.utils.book_append_sheet(wb, ws2, "Resumen");
        XLSX.writeFile(wb, 'MediaMixRealEstate.xlsx');
    });

    // Exportar a PDF
    $('#exportPDF').on('click', function(){
        const { jsPDF } = window.jspdf;
        let doc = new jsPDF('l', 'pt', 'a4');

        doc.setFontSize(16);
        doc.text("Detalle Media Mix - Inmobiliaria Alfa", 40, 40);

        doc.autoTable({
            html: '#mediaMixDetailTable',
            startY: 60,
            theme: 'grid'
        });

        let finalY = doc.lastAutoTable.finalY + 20;
        doc.text("Resumen", 40, finalY);
        doc.autoTable({
            html: '#summaryTable',
            startY: finalY + 10,
            theme: 'grid'
        });

        doc.save('MediaMixRealEstate.pdf');
    });

});
</script>

<script>
$(document).ready(function(){
    $('#mediaMixTable').DataTable();
});
</script>
