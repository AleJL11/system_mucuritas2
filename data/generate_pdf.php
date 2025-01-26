<?php

    include("./conexion.php");
    require('../fpdf/fpdf.php');

    header('Content-Type: application/json');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

    //session_start();

    $payment_id = isset($_GET['payment_id']) ? $_GET['payment_id'] : null;

    if ($payment_id > 0) {

        try {
            $conn->begin_transaction();

            $stmt = $conn->prepare("SELECT
            cxc.nombre_completo,
            pagos.id AS pagos_id,
            pagos.num_referencia,
            pagos.banco_receptor,
            pagos.monto_num,
            pagos.monto_bs,
            pagos.tPago,
            pagos.meses_pagados,
            pagos.fecha_pago,
            pagos.tPuesto_pago,
            pagos.nPuesto_pago,
            pagos.correlativo
            FROM
                cxc_pagos_relacion
            INNER JOIN
                cxc ON cxc_pagos_relacion.id_cxc = cxc.id
            INNER JOIN
                pagos ON cxc_pagos_relacion.id_pagos = pagos.id
            WHERE
                pagos.id = ?");
            
            $stmt->bind_param("i", $payment_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $nombre_completo    = $row['nombre_completo'];
            $fecha_pago         = $row['fecha_pago'];
            $banco_receptor     = $row['banco_receptor'];
            $num_referencia     = $row['num_referencia'];
            $monto              = $row['monto_num'];
            $montoBs            = $row['monto_bs'];
            $tPago              = $row['tPago'];
            $meses_pagados      = $row['meses_pagados'];
            $tPuesto_pago       = $row['tPuesto_pago'];
            $nPuesto_pago       = $row['nPuesto_pago'];
            $correlativo        = $row['correlativo'];

            // Obtener la fecha actual
            $fecha_actual = date('d/m/Y');

            // Crear PDF
            $pdf = new FPDF('P', 'mm', 'A4');
            $pdf->AddPage();

            // Obtener el ancho de la página
            $pageWidth = $pdf->GetPageWidth();

            // Definir tamaño de los cuadros
            // Info cliente
            $width_cl = 195;
            $height_cl = 105;

            // Info empresa
            $width_em = 195;
            $height_em = 40;

            // Calcular el centro
            $x_cl = ($pageWidth - $width_cl) / 2;
            $x_em = ($pageWidth - $width_em) / 2;

            // Titulo
            $pdf->SetFont('Arial', 'B', 24);
            $pdf->Cell(0, 10, 'Recibo de Pago', 0, 1, 'L');
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 10, 'Fecha: ' . $fecha_actual, 0, 1, 'R');
            $pdf->Cell(0, 10, 'Correlativo: ' . $correlativo, 0, 1, 'R');
            $pdf->Ln(30);

            // Recuadro verde con la información de la empresa
            $pdf->SetFont('Arial', '', 12);
            $pdf->SetTextColor(0, 0, 0); // Negro
            $pdf->SetDrawColor(0, 128, 0); // Verde
            $pdf->SetFillColor(255, 255, 255); // Blanco
            $pdf->Rect($x_em, 45, $width_em, $height_em, 'D'); // Recuadro con borde verde

            // Información de la empresa
            $x_em = 20;
            $y_inicio_em = 50;

            $pdf->SetXY($x_em, $y_inicio_em);
            $pdf->Cell(95, 10, 'Nombre de la Empresa: Estacionamiento Mucuritas 2', 0, 1);

            $pdf->SetXY($x_em, 60);
            $pdf->Cell(95, 10, 'RIF: J-500472919', 0, 1);

            $pdf->SetXY($x_em, $pdf->GetY());
            $pdf->Cell(95, 10, 'Telefono: 0424-1073230', 0, 1);

            // Recuadro con la información del cliente
            $pdf->SetDrawColor(0, 128, 0); // Verde
            $pdf->Rect($x_cl, 95, $width_cl, $height_cl, 'D'); // Recuadro con borde verde

            // Información del cliente
            $pdf->SetXY(15, 97);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(95, 10, 'Nombre y Apellido:', 0);
            $pdf->Cell(85, 10, $nombre_completo, 0, 1, 'R');

            $pdf->SetXY(15, 106);
            $pdf->Cell(95, 10, 'Fecha de Pago:', 0);
            $pdf->Cell(85, 10, $fecha_pago, 0, 1, 'R');

            $pdf->SetXY(15, 116);
            $pdf->Cell(95, 10, 'Banco Emisor:', 0);
            $pdf->Cell(85, 10, $banco_receptor, 0, 1, 'R');

            $pdf->SetXY(15, 126);
            $pdf->Cell(95, 10, 'Numero de Referencia:', 0);
            $pdf->Cell(85, 10, $num_referencia, 0, 1, 'R');

            $pdf->SetXY(15, 136);
            $pdf->Cell(95, 10, 'Monto dolares:', 0);
            $pdf->Cell(85, 10, $monto, 0, 1, 'R');
            
            $pdf->SetXY(15, 146);
            $pdf->Cell(95, 10, 'Monto bolivares:', 0);
            $pdf->Cell(85, 10, $montoBs, 0, 1, 'R');

            $pdf->SetXY(15, 156);
            $pdf->Cell(95, 10, 'Tipo de Pago:', 0);
            $pdf->Cell(85, 10, $tPago, 0, 1, 'R');

            $pdf->SetXY(15, 166);
            $pdf->Cell(95, 10, 'Meses Pagados:', 0);
            $pdf->Cell(85, 10, $meses_pagados, 0, 1, 'R');

            $pdf->SetXY(15, 176);
            $pdf->Cell(95, 10, 'Tipo de Puesto:', 0);
            $pdf->Cell(85, 10, $tPuesto_pago, 0, 1, 'R');

            $pdf->SetXY(15, 186);
            $pdf->Cell(95, 10, 'Numero de Puesto:', 0);
            $pdf->Cell(85, 10, $nPuesto_pago, 0, 1, 'R');

            $pdf->Ln(10);

            // Firma y sello digital
            $y_firma_sello = $pdf->GetY() + 10;

            // Firma
            $pdf->Image('../img/firma_digital.png', 120, $y_firma_sello, 50);

            // Sello
            $pdf->Image('../img/sello_digital.png', 125, $y_firma_sello, 50);

            // Línea negra arriba de la palabra "Sello"
            $pdf->SetDrawColor(0, 0, 0); // Negro
            $pdf->SetXY(135, 230); // Posición X e Y para la línea
            $pdf->Line(100, 240, 198, 240); // Dibuja la línea desde X1, Y1 hasta X2, Y2

            // Texto "Sello" debajo de la línea
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetXY(135, 242);
            $pdf->Cell(95, 10, 'Firma y Sello', 0, 1);

            // Dirección
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetXY(15, $pdf->GetY() + 12);
            $pdf->MultiCell(170, 5, 'Direccion: Av. Principal Jose Antonio Paez, sector UD4, Conjunto Residencial Mucuritas, calle interna local ESTACIONAMIENTO MUCURITAS 2', 0, 1);

            // Salida del PDF
            $pdf->Output('I', 'recibo_pago_' . $fecha_pago . '_' . $nombre_completo .'.pdf');

        } catch (Exception $e) {
            $conn->rollback();
            echo 'Error: ' . $e->getMessage();
        }

    } else {
        echo "ID de pago inválido.";
    }