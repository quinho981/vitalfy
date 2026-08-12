#!/usr/bin/env bash
set -euo pipefail

# BE-R14-04 (ai-vitalfy/risks.md#r14): falha rápido, com mensagem clara, se os
# certificados TLS não foram provisionados -- em vez de deixar o nginx-proxy
# falhar (ou subir sem TLS válido) sem explicação nenhuma. Lê os nomes de
# arquivo exigidos diretamente dos .conf (não hardcoda), para não desatualizar
# se um dia divergirem. Ver ai-vitalfy/action-plans/backend/R14.md#be-r14-04.

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
CERTS_DIR="$REPO_ROOT/vitalfy-nginx-proxy/certs"
CONF_FILES=(
  "$REPO_ROOT/vitalfy-nginx-proxy/conf.d/app.conf"
  "$REPO_ROOT/vitalfy-nginx-proxy/conf.d/vitalfy.conf"
)

missing=0
declare -A checked

for conf in "${CONF_FILES[@]}"; do
    if [[ ! -f "$conf" ]]; then
        echo "check-certs: config esperada não encontrada: $conf" >&2
        exit 1
    fi

    while IFS= read -r path; do
        [[ -z "$path" ]] && continue
        filename="$(basename "$path")"
        [[ -n "${checked[$filename]:-}" ]] && continue
        checked[$filename]=1

        target="$CERTS_DIR/$filename"
        if [[ ! -f "$target" ]]; then
            echo "check-certs: FALTANDO $target (exigido por $(basename "$conf"))" >&2
            missing=1
        elif [[ ! -r "$target" ]]; then
            echo "check-certs: SEM PERMISSÃO DE LEITURA $target (exigido por $(basename "$conf"))" >&2
            missing=1
        fi
    done < <(grep -E '^\s*ssl_certificate(_key)?\s' "$conf" | awk '{print $2}' | tr -d ';')
done

if [[ "$missing" -ne 0 ]]; then
    echo "" >&2
    echo "check-certs: certificado(s) ausente(s) em $CERTS_DIR — ver $CERTS_DIR/README.md" >&2
    exit 1
fi

echo "check-certs: OK — todos os certificados exigidos estão presentes em $CERTS_DIR"
