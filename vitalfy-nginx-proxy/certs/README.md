# certs/

Este é o único arquivo deste diretório que é versionado — todo o resto é
gitignorado (`.gitignore` na raiz do repo: `/certs/*` + `!/certs/README.md`) de
propósito, porque chave privada não vai para o Git.

Consequência prática, registrada em [`ai-vitalfy/risks.md`](../../ai-vitalfy/risks.md#-r14--armadilhas-operacionais-do-ambiente-de-produção)
(R14): um checkout novo deste repositório **não** vem com certificado. Sem os
dois arquivos abaixo, `nginx-proxy` não sobe (ou sobe sem TLS funcional) e não
há mensagem óbvia dizendo por quê — a menos que você rode a verificação antes.

## Arquivos exigidos

Os nomes vêm de [`conf.d/app.conf`](../conf.d/app.conf) e
[`conf.d/vitalfy.conf`](../conf.d/vitalfy.conf) (diretivas `ssl_certificate` /
`ssl_certificate_key`):

| Arquivo | Conteúdo |
|---|---|
| `cloudflare-origin.pem` | certificado (Cloudflare Origin CA) |
| `cloudflare-origin.key` | chave privada correspondente |

Usados hoje pelos dois domínios servidos por este proxy: `app.vitalfy.health`
(`app.conf`) e `vitalfy.health`/`www.vitalfy.health` (`vitalfy.conf`) — mesmo
par de arquivo para os dois, porque é o mesmo certificado (wildcard ou SAN
cobrindo ambos; confirme no painel da Cloudflare qual foi emitido).

## Onde obter

Painel da Cloudflare → **SSL/TLS → Origin Server → Create Certificate**
(Origin CA). Copie o certificado gerado para `cloudflare-origin.pem` e a
chave privada para `cloudflare-origin.key`, exatamente com esses nomes, neste
diretório, **antes** do primeiro `docker compose -f docker-compose.prod.yml up`
num servidor novo.

## Verificar antes de subir

```bash
./scripts/check-certs.sh
```

Falha com uma mensagem nomeando o que falta, em vez de deixar o
`docker compose up` falhar de forma genérica (ou o `nginx-proxy` subir sem
TLS válido). Ver [`ai-vitalfy/action-plans/backend/R14.md#be-r14-04`](../../ai-vitalfy/action-plans/backend/R14.md#be-r14-04--checklist-e-verificação-de-certificados-para-servidor-novo).

## Não faça

- Não versione `cloudflare-origin.pem`/`.key` — o `.gitignore` já impede isso
  por padrão (`/certs/*`); não use `git add -f`.
- Não renomeie os arquivos sem atualizar `app.conf`, `vitalfy.conf` **e** o
  script de verificação — os três precisam concordar nos nomes.
