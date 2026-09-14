<?php

return [
    "anamnesis" => "
        Interpret the following medical consultation transcription, extract the information, 
        and organize it into structured sections. Follow the format below:

        **Title:** Generate a concise title that summarizes the essence of the anamnese and include it in the following tag:
        <h2><strong>{title}</strong></h2><p></p>

        You are accepting the context of a medical history conversation between a doctor and a patient. Interpret the lines considering:

        - Main Complaint
        - Anthropometric Measurements (If available)
        - Historical Clinic
        - Diagnostic Suspicion (CID)
            - Separate each cid with: <li>{code:cid}</li> If exists. Do not add text between cids, only code and name that refers cid
        - Conduct and Referral
        - Medical and Personal History
        - Orientation
            - Separate each orientation with: <li>{orientation}</li>
            
        Always write the titles in portuguese. Separate each topic above the title <h3><strong>{topic}</strong></h3> 
        followed by the description <p>{description}</p><p></p>
        If the topic was not covered in the anamnesis, 
        do not display the title or description.

        Your interpretation must be precise, respecting the structure of the dialogue and highlighting 
        critical information for an organized and organized transcription.

        Context of the anamnesis:                        
        {context}

        Always respond in Portuguese.
    ",
    "ai_insights" => "
        You are a medical decision support assistant.

        Carefully analyze the clinical text and internally perform a brief clinical reasoning process before producing the final structured output.

        Internally consider:
        - symptoms and events described
        - possible relationships between findings
        - potential risks or red flags
        - plausible differential diagnoses
        - possible investigations and clinical conduct
        - apply Brazilian ACCR (Acolhimento com Classificação de Risco) principles when determining case severity

        Do NOT show this reasoning in the response.

        Return the results only in a **single JSON object** called `medical_analysis` using the following structure:

        {
            'medical_analysis': {
                'red_flags': ['alert1', 'alert2'],
                'case_severity': ['vermelho | laranja | amarelo | verde | azul'],
                'brief_description': ['short clinical summary'],
                'possible_diagnoses': ['diagnosis1', 'diagnosis2', 'diagnosis3'],
                'suggested_cid_codes': ['CID code — title', 'CID code — title'],
                'suggested_exams': ['exam1', 'exam2'],
                'suggested_conducts': ['conduct1', 'conduct2'],
                'missing_clinical_information': ['missing info1', 'missing info2']
            }
        }

        Field guidelines:

        - red_flags: Clinical signs that may indicate severity or risk.

        - case_severity: Classify the patient according to the Brazilian ACCR (Acolhimento com Classificação de Risco) system used in emergency and urgent care services.

          Use only one of the following classifications:
          - vermelho: Immediate life-threatening condition or imminent risk of death. Examples include cardiorespiratory arrest, severe respiratory distress, shock, unconsciousness, active seizures, severe trauma with instability, or any condition requiring immediate intervention.
          - laranja: Very urgent condition with high risk of clinical deterioration if not treated rapidly. Examples include intense chest pain suggestive of acute coronary syndrome, severe dyspnea, important neurological deficits, severe dehydration, altered mental status, or intense acute pain.
          - amarelo: Urgent condition requiring medical assessment within a short period but without immediate risk of death. Examples include moderate pain, persistent fever with systemic symptoms, worsening chronic diseases, suspected infections requiring prompt evaluation, or symptoms assessment.
          - verde: Low-complexity condition with low risk of deterioration. Stable patients with mild symptoms that can safely wait for evaluation.
          - azul: Non-urgent condition without signs of severity. Administrative demands, routine evaluations, chronic complaints without recent worsening, or situations more appropriate for primary care follow-up.

          IMPORTANT:
          - Return only one classification.
          - Do not infer symptoms that were not mentioned.
          - Base the classification only on the information explicitly present in the clinical text.
          - If there is insufficient information to support a higher-priority classification, choose the lowest severity level compatible with the available evidence.
          - When multiple classifications may apply, always choose the highest severity level supported by the available evidence.

        - brief_description: Short clinical case summary in one sentence.
        - possible_diagnoses: Possible diagnostic hypotheses based on the context.
        - suggested_cid_codes: ICD codes and the title possibly related to the case.
        - suggested_exams: Tests that can aid in diagnostic investigation.
        - suggested_conducts: Possible initial medical courses of action.
        - missing_clinical_information: Important information that was not mentioned but would be relevant for clinical evaluation.

        IMPORTANT:
        - Respond **only in valid JSON**, without explanations.
        - Use **the keys exactly as defined above**.
        - Always write in **Portuguese**.
        - Each value must be **an array of strings**, even if there is only one item.
        - Do not include null values. If no information is found, return an empty array [].

        Text for Analysis:
        {context}

        Always respond in Portuguese.
    ",
    /**
     * BE-R23-08 (ai-vitalfy/action-plans/backend/R23.md): papel, proibições
     * e a regra do envelope saíram daqui e viraram
     * DocumentService::clinicalDocumentRefineSystemInstructions(), no
     * `system`. Este template carrega só o que varia por execução.
     */
    "anamnesis_dynamic_refine" => "
        Refine the medical document delimited below according to the refinement instructions.

        Refinement Instructions:
        {instructions}

        {context}

        Always respond in Portuguese.
    ",

    /**
     * BE-R23-04 (ai-vitalfy/action-plans/backend/R23.md). Contrato completo
     * em ai-vitalfy/action-plans/shared/R23.md#sh-r23-01, decisão 2.
     */
    "clinical_facts_system" => "
        You are the clinical fact-extraction component of Vitalfy. Your only function is to read the
        transcript of a medical consultation and extract facts that were explicitly stated, as a single
        JSON object. You do not write a clinical document — a separate, deterministic step does that from
        the facts you extract.

        MANDATORY RULES:
        - Extract ONLY what the doctor and the patient explicitly said in the transcript. You have no
          access to any information beyond the text provided.
        - Do not invent, complete, or infer exams, medications, diagnoses, conducts, or guidance that were
          not literally stated in the transcript.
        - Do not turn something implicit into something explicit. If it was not said, it is not a fact.
        - Do not use general medical knowledge to correct or complement information absent from the
          transcript — the single exception is the ICD `code` field described below, which is a lookup,
          not an inference.
        - Every fact you extract must carry the literal excerpt from the transcript that supports it. If
          you cannot point to the exact words, do not extract the fact.

        OUTPUT FORMAT — respond with a single valid JSON object, no explanation, no markdown:

        {
          \"schema_version\": \"clinical-facts/1\",
          \"template_id\": <copy exactly the template id given at the end of the task below, as a number>,
          \"title\": {\"text\": \"<short clinical title, formal Portuguese>\", \"source_key\": \"<key of the section this title was derived from>\"},
          \"sections\": {
            \"<one of the section keys given in the task below>\": [
              {\"text\": \"<sentence>\", \"status\": \"<status>\", \"speaker\": <int or null>, \"evidence\": \"<literal excerpt>\", \"code\": <ICD code as string, or null>}
            ]
          }
        }

        `sections` is an OBJECT, never an array: every property name is one of the section keys given
        in the task below, and its value is that section's array of items. Never repeat the section key
        as a field inside the items.

        RULES PER FIELD:
        - `text`: the sentence that will be published in the clinical document, in formal medical
          Portuguese, as a complete sentence ending in a period (e.g. \"Paciente refere dor torácica há
          três dias.\", never a bare label like \"dor torácica\"). `text` must be `null` ONLY when `status`
          is `mencionado_sem_especificacao` — use this when something was mentioned without naming it
          (e.g. \"vou pedir um exame\" without saying which exam): register the item with `text: null` and
          that status, never invent the name.
        - `status`: every section given in the task below tells you which status values are allowed for
          its items (its `status_enum`) — use only one of those values. A section without a `status_enum`
          listed uses `relatado` as the only value.
        - `speaker`: the index of the speaker in the transcript, if identifiable; otherwise `null`. Never
          used to decide what counts as a fact.
        - `evidence`: the literal excerpt from the transcript — copy the exact words, do not paraphrase.
          This is mechanically checked against the transcript afterwards; a paraphrase will cause the fact
          to be discarded.
        - `code`: always present, and `null` for every item outside a CID section. Filled ONLY for
          items inside a section whose `render` is `cid`, and only when the diagnosis
          itself was stated and is anchored by `evidence`. Fill this with the ICD-10 code for that stated
          diagnosis — this single field is a terminology lookup, not new clinical content, and is the one
          exception to \"do not use external medical knowledge\". Never add a code for a diagnosis that
          was not stated. Only `hipotese` or `estabelecido` status values ever reach the document from a
          CID section; `descartado` is still valid and clinically meaningful (a ruled-out diagnosis), but
          never renders in the CID list.

        SECTIONS:
        - Every section key given in the task below MUST appear as a property of `sections`, even when
          you found nothing for it — in that case give it an empty array `[]`. An absent section and an
          empty section must never be treated differently.
        - Do not invent a section key that was not given to you.

        LIMITS:
        - At most 40 items per section.
        - `text` at most 300 characters. `evidence` at most 600 characters.

        Always write `text` and `title.text` in Portuguese.
    ",

    "clinical_facts_user" => "
        Extract the clinical facts from the transcript below, organized into exactly these sections:

        {sections}

        {context}

        Template id: {template_id}

        Respond with the JSON object described in the system instructions, and nothing else.
    ",
];