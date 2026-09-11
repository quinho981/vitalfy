#!/usr/bin/env python3
"""
SH-R23-01, subtarefa 2 — aplica as regras JA CONGELADAS na decisao 2 de
shared/R23.md ao rascunho de document_template_sections_draft.json.

Isto NAO reintroduz julgamento novo: cada campo abaixo e a aplicacao
mecanica de uma regra que o plano ja tem escrita por extenso. Os unicos
pontos de julgamento genuino (colisao de bucket, secao sem "render" claro)
sao listados explicitamente no final, para decisao humana antes do aceite.

Campos finais por secao:
  - key, label, render: herdados do rascunho (render ja e regra mecanica:
    cid se pede <li>{codigo:cid}</li>, list se pede <li>{orientacao}</li>,
    prose default).
  - status_enum: enum fechado de status permitido nos itens desta secao,
    aplicando a tabela da decisao 2 (shared/R23.md linhas 141-148). null =
    "demais" (opcional, default 'relatado', sem enforcement fechado).
  - hints: filtra fragmentos de INSTRUCAO DE FORMATACAO que o regex do
    rascunho capturou por engano (ex: "Separe cada orientacao utilizando:")
    -- decisao 1 diz que hints sao "orientacao de recorte", nunca formato,
    e formato agora e responsabilidade do render (BE-R23-07), nao do prompt.
"""
import json
import re
import sys
from pathlib import Path

DRAFT_PATH = Path(sys.argv[1]) if len(sys.argv) > 1 else Path("document_template_sections_draft.json")
OUT_PATH = Path(sys.argv[2]) if len(sys.argv) > 2 else Path("document_template_sections_final.json")

# Decisao 2 (shared/R23.md, linhas 141-148) -- tabela de enum por substring de key.
# Ordem = prioridade de match quando mais de um substring aparecer na mesma key.
ENUM_RULES = [
    ("exame", ["realizado", "solicitado", "mencionado_sem_especificacao"]),
    ("medica", ["em_uso", "prescrito", "suspenso", "mencionado_sem_especificacao"]),
    ("alergia", ["relatada", "negada"]),
    ("histor", ["relatado", "negado"]),
]

CID_ENUM = ["hipotese", "estabelecido", "descartado"]

# Excecao unica encontrada na curadoria (colisao de bucket alergia/historico):
# "historico_familiar_de_alergias_ou_doencas_imunologicas" (template 1,
# Alergia e Imunologia) -- o substantivo nucleo e "historico familiar", o
# fato registrado e se o HISTORICO foi relatado ou negado, nao se a alergia
# foi. Fica no bucket 'histor', nao 'alergia'. Registrado aqui em vez de
# resolvido por ordem de prioridade silenciosa.
FORCED_BUCKET = {
    "historico_familiar_de_alergias_ou_doencas_imunologicas": "histor",
}

FORMAT_HINT_MARKERS = [
    "separe cada",
    "utilizando",
    "nao adicione nenhum texto extra",
    "inclua apenas o codigo",
]


def strip_accents_lower(s: str) -> str:
    import unicodedata
    s = s.lower()
    return "".join(c for c in unicodedata.normalize("NFKD", s) if not unicodedata.combining(c))


def is_format_hint(hint: str) -> bool:
    low = strip_accents_lower(hint)
    return any(m in low for m in FORMAT_HINT_MARKERS)


def status_enum_for(key: str, label: str, render: str) -> list[str] | None:
    if render == "cid":
        return CID_ENUM

    # "Histórico" (registro: Histórico Familiar, Histórico Cardiovascular
    # Relevante) e "História" (narrativa: História Clínica, História da
    # Doença Atual) sao palavras DIFERENTES em portugues, mas o slug de key
    # remove acento e as funde em "histor". Achado durante a curadoria: a
    # regra de substring da decisao 2 (shared/R23.md) so faz sentido para
    # "Histórico" -- "História" e prosa narrativa sem semantica binaria de
    # relatado/negado, mesma familia de Queixa Principal / HDA.
    if label.startswith("História"):
        return None

    forced = FORCED_BUCKET.get(key)
    if forced:
        for substr, enum in ENUM_RULES:
            if substr == forced:
                return enum

    for substr, enum in ENUM_RULES:
        if substr in key:
            return enum

    return None  # "demais": opcional, default 'relatado', sem enum fechado


def main():
    draft = json.loads(DRAFT_PATH.read_text(encoding="utf-8"))

    final = []
    review_notes = []

    for template in draft:
        sections_out = []
        seen_keys = set()

        for section in template["sections"]:
            key = section["key"]
            render = section["render"]

            if key in seen_keys:
                review_notes.append(
                    f"template {template['template_id']} ({template['template_name']}): "
                    f"key duplicada '{key}' -- revisar manualmente"
                )
            seen_keys.add(key)

            hints = [h for h in section.get("hints", []) if not is_format_hint(h)]

            out_section = {
                "key": key,
                "label": section["label"],
                "render": render,
            }
            if hints:
                out_section["hints"] = hints

            enum = status_enum_for(key, section["label"], render)
            if enum:
                out_section["status_enum"] = enum

            sections_out.append(out_section)

        final.append({
            "template_id": template["template_id"],
            "template_name": template["template_name"],
            "sections": sections_out,
        })

    OUT_PATH.write_text(json.dumps(final, ensure_ascii=False, indent=2), encoding="utf-8")

    total_sections = sum(len(t["sections"]) for t in final)
    with_enum = sum(1 for t in final for s in t["sections"] if "status_enum" in s)

    print(f"templates: {len(final)}")
    print(f"secoes totais: {total_sections}")
    print(f"secoes com status_enum fechado: {with_enum}")
    print(f"secoes 'demais' (sem enum, default relatado): {total_sections - with_enum}")
    print(f"notas de revisao: {len(review_notes)}")
    for n in review_notes:
        print("  - " + n)
    print(f"\nescrito em: {OUT_PATH}")


if __name__ == "__main__":
    main()
