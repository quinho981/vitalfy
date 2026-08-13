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
  restart     Reinicia (sem rebuild) todos os serviços sem estado: nginx-proxy,
              frontend, api-nginx, app, horizon. Não toca db/redis
              de propósito (restart deles derruba conexões/transações ativas
              -- se precisar reiniciá-los, faça consciente, fora deste
              comando: `docker compose -f docker-compose.prod.yml restart db redis`).
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

    echo "==> Confirmando que app e horizon têm o mesmo conteúdo de imagem..."
    # Comparar .Image (o ID de topo) direto é falso positivo em potencial:
    # `up -d --build app horizon` builda os dois em invocações separadas, e o
    # Docker inclui o timestamp de criação no config da imagem -- então o ID
    # de topo pode divergir mesmo com as camadas 100% idênticas (cache
    # totalmente reaproveitado). O que prova divergência real de código é a
    # lista de camadas (RootFS.Layers), não o ID de topo. Visto em produção
    # em 13/08/2026: IDs de topo diferentes, camadas idênticas -- falso
    # alarme, corrigido aqui.
    app_image="$(docker inspect -f '{{.Image}}' vitalfy_app)"
    horizon_image="$(docker inspect -f '{{.Image}}' vitalfy_horizon)"
    app_layers="$(docker inspect -f '{{json .RootFS.Layers}}' "$app_image")"
    horizon_layers="$(docker inspect -f '{{json .RootFS.Layers}}' "$horizon_image")"

    if [[ "$app_layers" != "$horizon_layers" ]]; then
        echo "deploy-prod: ERRO -- app ($app_image) e horizon ($horizon_image) têm CAMADAS diferentes -- código realmente divergente." >&2
        echo "deploy-prod: isso não deveria acontecer com o comando 'app' -- investigue antes de seguir." >&2
        exit 1
    fi

    echo "==> OK: app e horizon com o mesmo conteúdo de imagem (mesmas camadas)."
    if [[ "$app_image" != "$horizon_image" ]]; then
        echo "    (IDs de topo diferem -- $app_image vs $horizon_image -- normal entre builds separados, não indica divergência de código.)"
    fi
}

restart_all() {
    echo "==> Reiniciando serviços sem estado (nginx-proxy, frontend, api-nginx, app, horizon)..."
    # db e redis ficam de fora de propósito: restart em um serviço stateful
    # derruba conexões e transações em andamento sem necessidade -- diferente
    # dos serviços abaixo, que são proxies/processos sem estado local e
    # reiniciam em segundos sem perda.
    "${COMPOSE[@]}" restart nginx-proxy frontend api-nginx app horizon
    echo "==> OK. db e redis não foram tocados -- reinicie-os manualmente e de propósito, se precisar mesmo."
}

deploy_frontend() {
    echo "==> Verificando certificados antes de subir qualquer coisa..."
    "$SCRIPT_DIR/check-certs.sh"

    echo "==> Reconstruindo e reiniciando frontend..."
    "${COMPOSE[@]}" up -d --build frontend
    echo "==> OK: frontend reconstruído."
}

# BE-R14-03 (ai-vitalfy/risks.md#r14): `vendor_data` e `node_modules_data` são
# volumes nomeados por cima do bind mount de ./vitalfy-api:/var/www -- rodar
# `composer install`/`npm ci` no host NÃO chega ao container, o volume nomeado
# tem precedência. É preciso rodar dentro do container `app` (que compartilha
# volume com `horizon`, então um comando já atualiza os dois).
deps_php() {
    echo "==> Instalando dependências PHP dentro do container app (volume vendor_data)..."
    "${COMPOSE[@]}" exec app composer install --no-dev --optimize-autoloader
    echo "==> OK. horizon compartilha o mesmo volume vendor_data -- nada a fazer lá."
}

deps_node() {
    echo "==> Instalando dependências Node dentro do container app (volume node_modules_data)..."
    # Mesma flag usada no build da imagem (docker/php/Dockerfile) -- Puppeteer/
    # Chrome for Testing para geração de PDF via Browsershot (ver DECISIONS.md#d9).
    "${COMPOSE[@]}" exec app npm ci --omit=dev
    echo "==> OK. horizon compartilha o mesmo volume node_modules_data -- nada a fazer lá."
}

case "${1:-help}" in
    app)
        deploy_app
        ;;
    frontend)
        deploy_frontend
        ;;
    deps:php)
        deps_php
        ;;
    deps:node)
        deps_node
        ;;
    restart)
        restart_all
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
