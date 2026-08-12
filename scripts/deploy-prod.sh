#!/usr/bin/env bash
set -euo pipefail

# BE-R14-02 (ai-vitalfy/risks.md#r14): `app` e `horizon` usam o mesmo
# Dockerfile (docker-compose.prod.yml) mas são serviços independentes -- nada
# no compose força os dois a serem reconstruídos juntos. Reconstruir só um
# deixa o outro rodando código velho, sem nenhum sinal visível disso. Este
# script torna "app e horizon sempre juntos" o caminho de menor esforço.
#
# Depois de BE-R14-01 (resolução dinâmica de DNS nos nginx), este script NÃO
# precisa reiniciar nginx em cascata -- os três nginx voltam a rota
# sozinhos, dentro do TTL do resolver, sem intervenção manual. Se você está
# lendo isto porque BE-R14-01 foi revertido, essa premissa não vale mais.
#
# Ref: ai-vitalfy/action-plans/backend/R14.md#be-r14-02

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
COMPOSE_FILE="$REPO_ROOT/docker-compose.prod.yml"
COMPOSE=(docker compose -f "$COMPOSE_FILE")

usage() {
    cat <<'EOF'
Uso: deploy-prod.sh <comando>

Comandos:
  app         Reconstrói e reinicia `app` e `horizon` juntos (a única forma
              suportada de atualizar o código do back-end em produção).
  frontend    Reconstrói e reinicia só o `frontend`. Separado de `app` de
              propósito: front e back nem sempre mudam juntos, e o front não
              compartilha imagem com ninguém.
  deps:php    Instala/atualiza dependências PHP dentro do volume nomeado
              `vendor_data`, sem rebuild de imagem.
  deps:node   Idem para dependências Node em `node_modules_data`.
  help        Mostra esta mensagem.

Todos os comandos assumem que o código já está atualizado no host (git pull
feito antes de chamar este script -- ele não faz isso por você).
EOF
}

deploy_app() {
    echo "==> Verificando certificados antes de subir qualquer coisa..."
    "$SCRIPT_DIR/check-certs.sh"

    echo "==> Reconstruindo e reiniciando app + horizon juntos..."
    "${COMPOSE[@]}" up -d --build app horizon

    echo "==> Confirmando que app e horizon compartilham a mesma imagem..."
    app_image="$(docker inspect -f '{{.Image}}' vitalfy_app)"
    horizon_image="$(docker inspect -f '{{.Image}}' vitalfy_horizon)"

    if [[ "$app_image" != "$horizon_image" ]]; then
        echo "deploy-prod: ERRO -- app ($app_image) e horizon ($horizon_image) ficaram com imagens diferentes." >&2
        echo "deploy-prod: isso não deveria acontecer com o comando 'app' -- investigue antes de seguir." >&2
        exit 1
    fi

    echo "==> OK: app e horizon na mesma imagem ($app_image)."
}

deploy_frontend() {
    echo "==> Verificando certificados antes de subir qualquer coisa..."
    "$SCRIPT_DIR/check-certs.sh"

    echo "==> Reconstruindo e reiniciando frontend..."
    "${COMPOSE[@]}" up -d --build frontend
    echo "==> OK: frontend reconstruído."
}

case "${1:-help}" in
    app)
        deploy_app
        ;;
    frontend)
        deploy_frontend
        ;;
    deps:php|deps:node)
        echo "deploy-prod: comando '$1' ainda não implementado nesta versão do script (ver BE-R14-03)." >&2
        exit 1
        ;;
    help|-h|--help)
        usage
        ;;
    *)
        echo "deploy-prod: comando desconhecido: ${1:-}" >&2
        echo "" >&2
        usage >&2
        exit 1
        ;;
esac
