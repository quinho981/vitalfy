#!/usr/bin/env python3
"""
Rascunho de derivação de document_templates.sections (SH-R23-01, decisão 1).

NAO E O RESULTADO FINAL. E o rascunho que SH-R23-01 (subtarefa 2) manda
revisar a mao — key, label, render, enum de status aplicavel, e a separacao
entre secao e sub-bullet (hints). Corta cada `content` em
"**Regras de formatacao:**" (presente em 55/55) e le os bullets de primeiro
nivel como secoes; bullets indentados abaixo de um bullet de primeiro nivel
viram `hints`.

Heuristica de `render`:
  - "cid" se o bloco da secao (ate o proximo bullet de primeiro nivel) contem
    "<li>{codigo:cid}</li>" ou o label contem "CID".
  - "list" se o bloco contem "<li>{orientacao}</li>" (ou variante de
    orientacao/instrucao em lista).
  - "prose" caso contrario. E o default e o mais comum (55/55 pedem
    <h3><strong> + <p> para todo topico).

Heuristica de `status` aplicavel (so para a revisao humana usar como ponto de
partida — o enum final e decisao editorial, registrada em SH-R23-01 decisao
2):
  - contem "exame" no label -> exams
  - label contem "cid"/"diagnost"/"impress" -> diagnoses (so quando render=cid)
  - label contem "medica" -> medications
  - label contem "alergia" -> allergies
  - label contem "histor" -> personal_history/family_history (revisar a mao
    qual dos dois, ou se e generico)
  - resto -> nenhum enum, status default "relatado"
"""
import json
import re
import sys
import unicodedata
from pathlib import Path

import openpyxl

XLSX_PATH = Path(sys.argv[1]) if len(sys.argv) > 1 else Path("database/data/categories.xlsx")
OUT_PATH = Path(sys.argv[2]) if len(sys.argv) > 2 else Path("sections_draft.json")


def slugify(text: str) -> str:
    text = text.strip().lower()
    text = "".join(
        c for c in unicodedata.normalize("NFKD", text) if not unicodedata.combining(c)
    )
    text = re.sub(r"\([^)]*\)", "", text)  # remove parenteticos do label pro slug
    text = re.sub(r"[^a-z0-9]+", "_", text)
    return text.strip("_")


def guess_render(block: str, label: str) -> str:
    if "{codigo:cid}" in block or "cid" in label.lower():
        return "cid"
    if "{orientacao}" in block or "{problema}" in block:
        return "list"
    return "prose"


def guess_status_hint(label: str, render: str) -> str | None:
    low = label.lower()
    if render == "cid":
        return "diagnoses (hipotese|estabelecido|descartado)"
    if "exame" in low:
        return "exams (realizado|solicitado|mencionado_sem_especificacao)"
    if "medica" in low:
        return "medications (em_uso|prescrito|suspenso|mencionado_sem_especificacao)"
    if "alergia" in low:
        return "allergies (relatada|negada)"
    if "histor" in low:
        return "personal_history/family_history (relatado|negado) -- revisar qual"
    return None


def extract_top_level_bullets(body: str) -> list[tuple[str, str]]:
    """Retorna [(label, bloco_ate_o_proximo_bullet_top_level)]. Bullet de
    primeiro nivel = linha comecando em '* ' sem indentacao."""
    lines = body.splitlines()
    bullets: list[tuple[int, str]] = []
    for i, line in enumerate(lines):
        if re.match(r"^\* (.+)$", line):
            label = re.match(r"^\* (.+)$", line).group(1).strip().rstrip(".")
            bullets.append((i, label))

    result = []
    for idx, (line_no, label) in enumerate(bullets):
        end = bullets[idx + 1][0] if idx + 1 < len(bullets) else len(lines)
        block = "\n".join(lines[line_no:end])
        result.append((label, block))
    return result


def extract_hints(block: str) -> list[str]:
    """Sub-bullets indentados (comecam com espaco + '*') dentro do bloco."""
    hints = []
    for line in block.splitlines()[1:]:
        m = re.match(r"^\s+\*\s+(.+)$", line)
        if m:
            text = m.group(1).strip().rstrip(".")
            if text and not text.startswith("<li>") and len(text) < 200:
                hints.append(text)
    return hints


def is_meta_bullet(label: str) -> bool:
    """Filtra bullets que sao regra de formatacao, nao secao clinica."""
    low = label.lower()
    meta_markers = [
        "titulos devem estar em portugues",
        "estrutura",
        "nao exiba o titulo",
        "nao invente",
        "utilize apenas dados",
        "prontuario medico real",
        "documento clinico real",
        "objetividade, clareza",
        "linguagem tecnica",
    ]
    return any(m in low for m in meta_markers) or label.endswith(":") or len(label) > 60


def main():
    wb = openpyxl.load_workbook(XLSX_PATH)
    ws = wb.active
    rows = list(ws.iter_rows(values_only=True))
    header = [str(c).lower() if c else "" for c in rows[0]]
    data = [dict(zip(header, r)) for r in rows[1:]]

    templates_out = []
    warnings = []

    for row in data:
        tid = row.get("id")
        name = row.get("name")
        content = str(row.get("content") or "")

        if "**Regras de formata" in content:
            body = content.split("**Regras de formata")[0]
        else:
            body = content
            warnings.append(f"template {tid} ({name}): sem separador 'Regras de formatacao'")

        raw_bullets = extract_top_level_bullets(body)
        sections = []
        seen_keys = set()

        for label, block in raw_bullets:
            if is_meta_bullet(label):
                continue

            key = slugify(label)
            if not key:
                continue
            if key in seen_keys:
                key = key + "_2"
                warnings.append(f"template {tid} ({name}): key duplicada para label '{label}', usando '{key}'")
            seen_keys.add(key)

            render = guess_render(block, label)
            hints = extract_hints(block)
            status_hint = guess_status_hint(label, render)

            section = {
                "key": key,
                "label": label,
                "render": render,
            }
            if hints:
                section["hints"] = hints
            if status_hint:
                section["_status_hint_para_revisao"] = status_hint

            sections.append(section)

        if not sections:
            warnings.append(f"template {tid} ({name}): NENHUMA secao derivada -- revisar a mao")

        templates_out.append({
            "template_id": tid,
            "template_name": name,
            "sections": sections,
        })

    OUT_PATH.write_text(json.dumps(templates_out, ensure_ascii=False, indent=2), encoding="utf-8")

    print(f"templates processados: {len(templates_out)}")
    print(f"total de secoes derivadas: {sum(len(t['sections']) for t in templates_out)}")
    print(f"avisos: {len(warnings)}")
    for w in warnings:
        print("  - " + w)
    print(f"\nrascunho escrito em: {OUT_PATH}")


if __name__ == "__main__":
    main()
