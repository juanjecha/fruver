<?php

namespace App\Libraries;

/**
 * Arma el menú a partir de Config\Menu y Config\Permisos.
 *  - tarjetas()  -> las tarjetas del inicio (módulos permitidos al rol)
 *  - construir() -> el menú lateral del módulo actual, con la opción activa marcada
 */
class MenuLateral
{
    private $cfg;

    public function __construct()
    {
        helper(['url', 'permisos']);
        $this->cfg = config('Menu');
    }

    /** Tarjetas de la pantalla de inicio. */
    public function tarjetas(): array
    {
        $tarjetas = [];
        foreach ($this->cfg->modulos as $clave => $modulo) {
            if (! puede(...$modulo['permisos'])) {
                continue;
            }
            $tarjetas[] = [
                'clave'  => $clave,
                'titulo' => $modulo['titulo'],
                'icono'  => $modulo['icono'],
                'url'    => base_url($modulo['entrada']),
            ];
        }
        return $tarjetas;
    }

    /**
     * Menú lateral. $modulo = módulo "desde el que se entró" (lo indica el controlador).
     * Las vistas compartidas (ej. seguimiento de pedidos) pasan null y el módulo se
     * recuerda de la última pantalla visitada: así el menú no cambia al saltar entre accesos rápidos.
     */
    public function construir(?string $modulo = null): ?array
    {
        $ruta   = trim(uri_string(), '/');
        $modulo = $this->resolverModulo($modulo, $ruta);

        if ($modulo === null) {
            return null;
        }

        $def   = $this->cfg->modulos[$modulo];
        $items = [];
        foreach ($def['items'] as $item) {
            if (! puede(...$item['permisos'])) {
                continue;
            }
            $items[] = [
                'etiqueta' => $item['etiqueta'],
                'icono'    => $item['icono'],
                'url'      => base_url($item['url']),
                'activo'   => $this->coincide($ruta, $this->patrones($item)),
            ];
        }

        return ['modulo' => $modulo, 'titulo' => $def['titulo'], 'items' => $items];
    }

    // ───────────── internos ─────────────

    private function resolverModulo(?string $pedido, string $ruta): ?string
    {
        $sesion = session();

        // 1) El controlador lo indicó
        if ($pedido !== null && $this->accesible($pedido)) {
            $sesion->set('modulo_actual', $pedido);
            return $pedido;
        }

        // 2) El módulo recordado sigue siendo válido para esta ruta
        $previo = $sesion->get('modulo_actual');
        if ($previo && $this->accesible($previo) && $this->contiene($previo, $ruta)) {
            return $previo;
        }

        // 3) El primer módulo permitido que contenga esta ruta
        foreach (array_keys($this->cfg->modulos) as $clave) {
            if ($this->accesible($clave) && $this->contiene($clave, $ruta)) {
                $sesion->set('modulo_actual', $clave);
                return $clave;
            }
        }

        return null;
    }

    private function accesible(string $clave): bool
    {
        return isset($this->cfg->modulos[$clave]) && puede(...$this->cfg->modulos[$clave]['permisos']);
    }

    private function contiene(string $clave, string $ruta): bool
    {
        foreach ($this->cfg->modulos[$clave]['items'] as $item) {
            if ($this->coincide($ruta, $this->patrones($item))) {
                return true;
            }
        }
        return false;
    }

    private function patrones(array $item): array
    {
        return array_merge([$item['url']], $item['activo_en'] ?? []);
    }

    private function coincide(string $ruta, array $patrones): bool
    {
        foreach ($patrones as $patron) {
            $regex = '#^' . str_replace('\*', '.*', preg_quote(trim($patron, '/'), '#')) . '$#';
            if (preg_match($regex, $ruta)) {
                return true;
            }
        }
        return false;
    }
}
