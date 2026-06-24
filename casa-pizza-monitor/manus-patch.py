"""
Manus Python script — parche para Healthchecks.io
Agregar estas líneas al script lunch_special_toggle.py existente.

1. Crear cuenta gratis en https://healthchecks.io
2. Crear DOS checks:
      - "Casa Pizza — Lunch Activate"    → copiar UUID → PING_ACTIVATE
      - "Casa Pizza — Lunch Deactivate"  → copiar UUID → PING_DEACTIVATE
3. Configurar period=86400 (24h), grace=3600 (1h) en cada check
"""

import requests  # ya debería estar instalado

# ── Pegar los UUIDs de Healthchecks.io aquí ─────────────────────────────────
PING_ACTIVATE   = "https://hc-ping.com/XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX"
PING_DEACTIVATE = "https://hc-ping.com/YYYYYYYY-YYYY-YYYY-YYYY-YYYYYYYYYYYY"

# ── Agregar al final de la función activate() ────────────────────────────────
def _ping_activate():
    try:
        requests.get(PING_ACTIVATE, timeout=5)
    except Exception:
        pass  # nunca bloquear la automatización por un ping fallido

# ── Agregar al final de la función deactivate() ──────────────────────────────
def _ping_deactivate():
    try:
        requests.get(PING_DEACTIVATE, timeout=5)
    except Exception:
        pass

# ── Ejemplo de cómo integrar en las funciones existentes ────────────────────
#
# def activate_lunch_special():
#     ... (código existente) ...
#     _ping_activate()   # ← agregar esta línea al final
#
# def deactivate_lunch_special():
#     ... (código existente) ...
#     _ping_deactivate() # ← agregar esta línea al final
