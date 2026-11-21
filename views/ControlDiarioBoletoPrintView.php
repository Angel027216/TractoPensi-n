<?php
$tipoUnidadImpresion = [
    'CAMIONETA' => 'Camioneta',
    'TRAILER_SENCILLO' => 'Tráiler sencillo',
    'TORTON' => 'Tortón',
    'TRAILER_FULL_DOBLE' => 'Tráiler full / doble remolque',
];
$fechaImpresion = static function (?string $fecha): string {
    return $fecha ? date('d/m/Y H:i', strtotime($fecha)) : 'Pendiente';
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Boleto #<?= (int) $boletoImpresion['folio'] ?> - Tracto Pensión</title>
    <link rel="icon" type="image/png" href="public/assets/images/iconopestaña.png?v=20261121">
    <style>
        body { color: #222; font: 15px Arial, sans-serif; margin: 24px auto; max-width: 720px; padding: 0 20px; }
        h1, h2 { margin: 0 0 12px; }
        .ticket { border: 1px solid #444; padding: 24px; }
        .muted { color: #666; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 24px; margin-top: 20px; }
        .field { border-bottom: 1px solid #ddd; padding-bottom: 8px; }
        .label { color: #666; display: block; font-size: 12px; margin-bottom: 4px; text-transform: uppercase; }
        .value { font-weight: 600; overflow-wrap: anywhere; }
        .folio { font-size: 28px; font-weight: 700; }
        .actions { margin: 16px 0; }
        @media print {
            body { margin: 0; max-width: none; padding: 0; }
            .actions { display: none; }
            .ticket { border: 0; }
        }
        @media (max-width: 520px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="actions">
    <button type="button" onclick="window.print()">Imprimir boleto</button>
    <button type="button" onclick="window.close()">Cerrar</button>
</div>
<main class="ticket">
    <h1>Tracto Pensión San Leonardo</h1>
    <p class="muted">Boleto de control diario</p>
    <div class="folio">Folio #<?= (int) $boletoImpresion['folio'] ?></div>
    <div class="grid">
        <div class="field">
            <span class="label">Estado</span>
            <span class="value"><?= e((string) $boletoImpresion['estado']) ?></span>
        </div>
        <div class="field">
            <span class="label">Operador o empresa</span>
            <span class="value"><?= e((string) $boletoImpresion['operador_empresa']) ?></span>
        </div>
        <div class="field">
            <span class="label">Entrada</span>
            <span class="value"><?= e($fechaImpresion((string) $boletoImpresion['fecha_hora_entrada'])) ?></span>
        </div>
        <div class="field">
            <span class="label">Salida</span>
            <span class="value"><?= e($fechaImpresion($boletoImpresion['fecha_hora_salida'] !== null ? (string) $boletoImpresion['fecha_hora_salida'] : null)) ?></span>
        </div>
        <div class="field">
            <span class="label">Marca de unidad</span>
            <span class="value"><?= e((string) $boletoImpresion['marca_trailer']) ?></span>
        </div>
        <div class="field">
            <span class="label">Tipo de unidad</span>
            <span class="value"><?= e($tipoUnidadImpresion[$boletoImpresion['tipo_unidad']] ?? (string) $boletoImpresion['tipo_unidad']) ?></span>
        </div>
        <div class="field">
            <span class="label">Placa del tracto</span>
            <span class="value"><?= e((string) $boletoImpresion['placa_tracto']) ?></span>
        </div>
        <div class="field">
            <span class="label">Placa de caja 1</span>
            <span class="value"><?= e((string) $boletoImpresion['placa_caja_1']) ?></span>
        </div>
        <?php if (!empty($boletoImpresion['placa_caja_2'])): ?>
            <div class="field">
                <span class="label">Placa de caja 2</span>
                <span class="value"><?= e((string) $boletoImpresion['placa_caja_2']) ?></span>
            </div>
        <?php endif; ?>
        <div class="field">
            <span class="label">Tarifa</span>
            <span class="value"><?= e((string) $boletoImpresion['descripcion_tarifa_aplicada']) ?></span>
        </div>
        <div class="field">
            <span class="label">Importe</span>
            <span class="value">$<?= e(number_format((float) $boletoImpresion['monto_tarifa_aplicada'], 2)) ?></span>
        </div>
    </div>
</main>
</body>
</html>
