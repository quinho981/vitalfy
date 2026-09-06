# vitalfy-landing

Landing page estática da Vitalfy (`vitalfy.health`). Deploy via `git pull` — sem build no servidor (ver `DECISIONS.md#d10`).

## Desenvolvimento local

Servir a pasta com qualquer static server (ou o container `landing` do compose). As páginas em `*.html` carregam CSS/JS de `assets/`.

## Build de CSS (Tailwind)

O CSS do Tailwind é gerado **localmente (ou em CI)** e commitado em `assets/css/tailwind.css`. O servidor só serve o arquivo estático.

```bash
cd vitalfy-landing
npm install          # só na primeira vez / quando package-lock mudar
npm run build:css    # obrigatório após adicionar/alterar classes Tailwind em qualquer *.html
```

Não rode `npm install` no host de produção: `node_modules/` ficaria público no bind mount do container `landing`.

## Assets JS

| Arquivo | Origem |
|---|---|
| `assets/js/lucide.js` | Lucide UMD **1.41.0** vendorizado (não usar CDN) |
| `assets/js/landing.js` | Interatividade comum (tema, ícones, nav, menu, reveal, demo tabs) |
| `assets/js/pricing.js` | Exclusivo de `pricing.html` (período Pro + ROI) |

O script de tema pré-paint permanece **inline** no `<head>` de cada HTML (evita flash). O hash CSP correspondente está em `vitalfy-nginx-proxy/conf.d/vitalfy.conf` — qualquer edição do bloco exige recalcular o `sha256`.
