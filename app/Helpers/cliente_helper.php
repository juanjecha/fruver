<?php

if (! function_exists('estrella_cliente')) {
    /** HTML de la estrella (tablas, tarjetas, detalles). Vacío si no es preferencial. */
    function estrella_cliente($preferencial): string
    {
        if (! (int) $preferencial) {
            return '';
        }
        return '<i class="fa fa-star text-warning" title="Cliente preferencial" aria-label="Cliente preferencial"></i>';
    }
}

if (! function_exists('nombre_cliente_texto')) {
    /** Texto plano con estrella (para <option>, que no admite HTML). */
    function nombre_cliente_texto(?string $nombre, $preferencial): string
    {
        return ((int) $preferencial ? '★ ' : '') . ($nombre ?? '');
    }
}

if (! function_exists('dinero')) {
    /** 1234.5 -> "$1,234.50" */
    function dinero($monto): string
    {
        return '$' . number_format((float) $monto, 2);
    }
}

if (! function_exists('fecha_corta')) {
    /** "2026-10-26 14:30:00" -> "26/10/2026" (vacío si no hay fecha) */
    function fecha_corta(?string $fecha): string
    {
        $t = $fecha ? strtotime($fecha) : false;
        return $t ? date('d/m/Y', $t) : '';
    }
}

if (! function_exists('cantidad_texto')) {
    /** "20.00" -> "20" · "2.50" -> "2.5" */
    function cantidad_texto($cantidad): string
    {
        $c = (string) $cantidad;
        return str_contains($c, '.') ? rtrim(rtrim($c, '0'), '.') : $c;
    }
}

if (! function_exists('estado_pedido_texto')) {
    /** Estado de la BD -> texto para el cliente. */
    function estado_pedido_texto(?string $estado): string
    {
        return match ($estado) {
            'confirmado'  => 'Confirmado',
            'en transito' => 'En tránsito',
            'entregado'   => 'Entregado',
            'pagado'      => 'Pagado',
            'credito'     => 'A crédito',
            'cancelado'   => 'Cancelado',
            default       => 'Solicitado',
        };
    }
}
