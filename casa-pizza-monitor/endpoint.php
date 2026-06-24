<?php
/**
 * Casa de Pizza — Lunch Monitor Endpoint
 * Agregar en: Code Snippets plugin → "Add New" → PHP snippet → Run everywhere
 *
 * Expone: GET /wp-json/casa/v1/lunch-status?key=TU_CLAVE_SECRETA
 */

defined('ABSPATH') || exit;

/* ── Clave de acceso (cámbiala por algo aleatorio) ─────────────────────────── */
define('CASA_MONITOR_KEY', 'cambiar-por-clave-secreta-aleatoria');

/* ── Registro del endpoint ──────────────────────────────────────────────────── */
add_action('rest_api_init', function () {
    register_rest_route('casa/v1', '/lunch-status', [
        'methods'             => 'GET',
        'callback'            => 'casa_lunch_status_response',
        'permission_callback' => function (WP_REST_Request $req) {
            return hash_equals(CASA_MONITOR_KEY, (string) $req->get_param('key'));
        },
    ]);
});

function casa_lunch_status_response(): WP_REST_Response {
    $tz  = new DateTimeZone('America/Los_Angeles');
    $now = new DateTime('now', $tz);

    /* ── Productos ──────────────────────────────────────────────────────────── */
    $ids      = [480, 479, 478, 477, 476, 475, 474, 473];
    $products = [];
    $visible_count = 0;

    foreach ($ids as $id) {
        $p = wc_get_product($id);
        if (!$p) {
            $products[] = ['id' => $id, 'name' => "Product $id", 'visibility' => 'unknown'];
            continue;
        }
        $vis        = $p->get_catalog_visibility(); // "visible" | "hidden" | "search" | "catalog"
        $is_visible = $vis === 'visible';
        if ($is_visible) $visible_count++;
        $products[] = [
            'id'         => $id,
            'name'       => $p->get_name(),
            'visibility' => $vis,
            'active'     => $is_visible,
        ];
    }

    /* ── WP Cron ────────────────────────────────────────────────────────────── */
    $act_ts   = wp_next_scheduled('casa_lunch_activate');
    $deact_ts = wp_next_scheduled('casa_lunch_deactivate');

    $fmt = function (?int $ts) use ($tz): ?string {
        if (!$ts) return null;
        return (new DateTime('@' . $ts))->setTimezone($tz)->format('Y-m-d\TH:i:sP');
    };

    /* ── Respuesta ──────────────────────────────────────────────────────────── */
    return rest_ensure_response([
        'server_time'   => $now->format('Y-m-d\TH:i:sP'),
        'lunch_active'  => $visible_count > 0,
        'visible_count' => $visible_count,
        'total'         => count($ids),
        'products'      => $products,
        'cron'          => [
            'next_activate'   => $fmt($act_ts),
            'next_deactivate' => $fmt($deact_ts),
            'activate_set'    => (bool) $act_ts,
            'deactivate_set'  => (bool) $deact_ts,
        ],
    ]);
}
