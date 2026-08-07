export const templateDetailsMap = {
    "Cardiologia": {
        description: `
            A IA da Vitalfy interpreta a conversa entre médico e paciente e organiza automaticamente a
            anamnese cardiológica, identificando sintomas cardiovasculares, fatores de risco e histórico
            clínico relevante.
        `,

        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Sintomas Cardiovasculares Associados",
            "Características da Dor Torácica (se presente)",
            "Fatores de Risco Cardiovascular",
            "Histórico Cardíaco Pessoal",
            "Histórico Familiar Cardiovascular",
            "Medicações em Uso",
            "Exames Cardíacos Realizados ou Solicitados",
            "Suspeita Diagnóstica (CID)",
            "Conduta e Tratamento",
            "Orientações ao Paciente"
        ],

        example: `
            <h2><strong>Dor torácica associada a esforço físico</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente relata dor no peito em aperto iniciada há cerca de 3 dias, principalmente ao subir escadas.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Dor torácica iniciada há três dias, de intensidade moderada, com piora durante esforço físico e melhora ao repouso. Episódios duram aproximadamente 10 minutos.</p>
            <br/>
            <h3><strong>Sintomas Cardiovasculares Associados</strong></h3>
            <p>Paciente refere falta de ar leve durante os episódios e fadiga.</p>
            <br/>
            <h3><strong>Fatores de Risco Cardiovascular</strong></h3>
            <p>Hipertensão arterial e tabagismo há 20 anos.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>I20 - Angina pectoris</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>Solicitado eletrocardiograma e teste ergométrico. Iniciado ajuste da medicação anti-hipertensiva.</p>
            <br/>
            <h3><strong>Orientações ao Paciente</strong></h3>
            <ul>
                <li>Evitar esforços intensos até avaliação completa</li>
                <li>Reduzir consumo de sal</li>
                <li>Retornar após realização dos exames</li>
            </ul>
        `
    },

    "Alergia e Imunologia": {
        description: "Estrutura voltada para avaliação alergológica e imunológica detalhada.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Tipo de Reação Alérgica",
            "Tempo de Início da Reação",
            "Fatores Desencadeantes (Gatilhos)",
            "Histórico Alérgico Pessoal",
            "Histórico Familiar de Alergias ou Doenças Imunológicas",
            "Doenças Associadas",
            "Medicações em Uso",
            "Exposição Ambiental",
            "Exames Realizados ou Sugeridos",
            "Suspeita Diagnóstica (CID)",
            "Conduta e Tratamento",
            "Orientações ao Paciente"
        ],
        example: `
            <h2><strong>Urticária após contato com gato</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente relata aparecimento de placas vermelhas e pruriginosas na pele após contato com gato.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Lesões iniciadas há 2 horas após contato com animal, com prurido intenso e edema localizado. Paciente refere episódios similares anteriores.</p>
            <br/>
            <h3><strong>Tipo de Reação Alérgica</strong></h3>
            <p>Urticária aguda com angioedema leve.</p>
            <br/>
            <h3><strong>Fatores Desencadeantes (Gatilhos)</strong></h3>
            <p>Contato direto com gato, especialmente saliva e pelos.</p>
            <br/>
            <h3><strong>Histórico Alérgico Pessoal</strong></h3>
            <p>Rinite alérgica sazonal, sensibilidade a ácaros.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>L50 - Urticária</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>Prescrito anti-histamínico oral. Orientado evitar contato com gatos. Solicitado testes alérgicos específicos.</p>
            <br/>
            <h3><strong>Orientações ao Paciente</strong></h3>
            <ul>
                <li>Manter afastado de animais domésticos</li>
                <li>Utilizar anti-histamínico conforme prescrição</li>
                <li>Procurar atendimento se houver dificuldade respiratória</li>
            </ul>
        `
    },

    "Anestesiologia": {
        description: "Estrutura voltada para avaliação pré-anestésica.",
        structure: [
            "Queixa Principal",
            "História Clínica",
            "Medicações em Uso",
            "Alergias",
            "Avaliação Pré-Anestésica",
            "Exames Relevantes",
            "Conduta e Planejamento",
            "Orientações",
            "Suspeita Diagnóstica (CID)"
        ],
        example: `
            <h2><strong>Avaliação pré-anestésica para colecistectomia</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente agendado para colecistectomia laparoscópica eletiva.</p>
            <br/>
            <h3><strong>História Clínica</strong></h3>
            <p>Hipertensão arterial controlada, diabetes mellitus tipo 2, obesidade grau I. Sem histórico de complicações anestésicas anteriores.</p>
            <br/>
            <h3><strong>Medicações em Uso</strong></h3>
            <p>Losartana 50mg/dia, Metformina 850mg 2x/dia, Omeprazol 20mg/dia.</p>
            <br/>
            <h3><strong>Avaliação Pré-Anestésica</strong></h3>
            <p>ASA II, via aérea classificada como Mallampati I. Risco cardiovascular moderado. Jejum adequado.</p>
            <br/>
            <h3><strong>Conduta e Planejamento</strong></h3>
            <p>Anestesia geral balanceada. Monitorização invasiva não necessária. Manter medicação habitual no dia da cirurgia, exceto metformina.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Jejum de 8 horas para sólidos e 2 horas para líquidos claros</li>
                <li>Comparecer 2 horas antes do procedimento</li>
                <li>Trazer exames recentes e medicações em uso</li>
            </ul>
        `
    },

    "Angiologia": {
        description: "Estrutura voltada para avaliação vascular detalhada.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Vascular Relevante",
            "Fatores de Risco",
            "Exames Relevantes",
            "Impressão Diagnóstica (CID)",
            "Conduta e Tratamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Varizes em membros inferiores com dor e edema</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente relata dor, peso e edema em membros inferiores, principalmente ao final do dia, com presença de veias dilatadas.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Sintomas iniciados há 5 anos, com progressão gradual. Piora com ortostatismo prolongado e melhora com elevação dos membros.</p>
            <br/>
            <h3><strong>Histórico Vascular Relevante</strong></h3>
            <p>Trombose venosa profunda há 3 anos. Mãe com histórico de varizes.</p>
            <br/>
            <h3><strong>Fatores de Risco</strong></h3>
            <p>Obesidade, trabalho em ortostatismo prolongado, histórico familiar.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>I83 - Varizes dos membros inferiores</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>Solicitado doppler venoso dos membros inferiores. Iniciado uso de meias de compressão elástica. Orientado sobre medidas conservadoras.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Utilizar meias de compressão elástica diariamente</li>
                <li>Evitar permanecer em pé por longos períodos</li>
                <li>Elevar membros inferiores quando possível</li>
                <li>Praticar exercícios físicos regulares</li>
            </ul>
        `
    },

    "Cirurgia Cardiovascular": {
        description: "Estrutura voltada para avaliação cirúrgica cardiovascular.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Cardiovascular Relevante",
            "Exames Relevantes",
            "Avaliação Cirúrgica",
            "Impressão Diagnóstica (CID)",
            "Conduta e Planejamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Avaliação para cirurgia de revascularização miocárdica</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com angina instável refratária ao tratamento clínico, candidato a revascularização miocárdica.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Dor torácica em repouso nos últimos 7 dias, apesar de uso triplo de antianginosos. Teste ergométrico positivo para isquemia extensa.</p>
            <br/>
            <h3><strong>Histórico Cardiovascular Relevante</strong></h3>
            <p>Infarto agudo do miocárdio há 2 anos, angioplastia primária com stent em artéria descendente anterior. Disfunção ventricular esquerda moderada.</p>
            <br/>
            <h3><strong>Avaliação Cirúrgica</strong></h3>
            <p>Indicação de CRM eletiva com 3 enxertos. Risco cirúrgico intermediário (EuroSCORE II 3,5%). Benefício esperado: melhora de sintomas e sobrevida.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>I25.1 - Doença aterosclerótica do coração</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Planejamento</strong></h3>
            <p>CRM programada em 7 dias. Otimização clínica pré-operatória. Suspensão de clopidogrel 5 dias antes da cirurgia.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Manter jejum de 8 horas antes da cirurgia</li>
                <li>Comparecer com acompanhante e exames recentes</li>
                <li>Comunicar intercorrências clínicas antes da cirurgia</li>
            </ul>
        `
    },

    "Cirurgia da Mão": {
        description: "Estrutura voltada para avaliação cirúrgica da mão.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Relevante",
            "Exames Relevantes",
            "Avaliação Cirúrgica",
            "Impressão Diagnóstica (CID)",
            "Conduta e Planejamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Síndrome do túnel do carpo bilateral</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente refere parestesia e dormência em mãos, predominantemente à noite, com piora progressiva nos últimos 6 meses.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Sintomas iniciados há 1 ano, inicialmente noturnos, agora também durante o dia. Piora com atividades repetitivas. Teste de Phalen positivo bilateral.</p>
            <br/>
            <h3><strong>Histórico Relevante</strong></h3>
            <p>Trabalha como digitador há 10 anos. Sem traumas prévios nas mãos.</p>
            <br/>
            <h3><strong>Avaliação Cirúrgica</strong></h3>
        <p>Indicação de liberação do túnel do carpo bilateral. Procedimento ambulatorial sob anestesia local. Recuperação esperada em 4-6 semanas.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>G56.0 - Síndrome do túnel do carpo</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Planejamento</strong></h3>
            <p>Cirurgia programada em 15 dias. Eletroneuromiografia confirmatória já realizada. Orientado sobre pós-operatório e reabilitação.</p>
            <br/>
            <h3><strong>Orientações</strong></n3>
            <ul>
                <li>Evitar atividades repetitivas com as mãos</li>
                <li>Utilizar órteses noturnas temporariamente</li>
                <li>Comparecer jejum de 6 horas para o procedimento</li>
            </ul>
        `
    },

    "Cirurgia de Cabeça e Pescoço": {
        description: "Estrutura voltada para avaliação cirúrgica de cabeça e pescoço.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Relevante",
            "Exames Relevantes",
            "Avaliação Cirúrgica",
            "Impressão Diagnóstica (CID)",
            "Conduta e Planejamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Nódulo tireoidiano com suspeita de malignidade</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com nódulo em tireoide direita detectado em ultrassonografia, com punção aspirativa sugestiva de carcinoma papilífero.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Nódulo identificado há 3 meses em ultrassonografia de rotina. Punção aspirativa com agulha fina demonstrando células atípicas. Assintomático.</p>
            <br/>
            <h3><strong>Histórico Relevante</strong></n3>
            <p>Tabagismo por 20 anos (10 cigarros/dia). Exposição a radiação na infância. Sem histórico familiar de câncer de tireoide.</p>
            <br/>
            <h3><strong>Avaliação Cirúrgica</strong></h3>
            <p>Indicação de tireoidectomia total com esvaziamento cervical central. Risco de lesão do nervo laríngeo recorrente baixo. Benefício: tratamento definitivo.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>C73 - Neoplasia maligna da tireoide</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Planejamento</strong></h3>
            <p>Tireoidectomia programada em 10 dias. Avaliação pré-anestésica realizada. Laringoscopia pré-operatória normal.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Jejum de 8 horas antes da cirurgia</li>
                <li>Suspender medicações que afetam coagulação</li>
                <li>Comparecer com acompanhante</li>
            </ul>
        `
    },

    "Cirurgia do Aparelho Digestivo": {
        description: "Estrutura voltada para avaliação cirúrgica do aparelho digestivo.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Digestivo Relevante",
            "Exames Relevantes",
            "Avaliação Cirúrgica",
            "Impressão Diagnóstica (CID)",
            "Conduta e Planejamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Doença do refluxo gastroesofágico refratária</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com pirose e regurgitação refratárias ao tratamento clínico máximo por 12 meses, com impacto significativo na qualidade de vida.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Sintomas iniciados há 5 anos, com progressão gradual. Uso de IBP em dose máxima sem melhora. Esofagite grau B na endoscopia.</p>
            <br/>
            <h3><strong>Histórico Digestivo Relevante</strong></h3>
            <p>Hérnia de hiato de 3cm. Sem cirurgias abdominais prévias. Obesidade grau I.</p>
            <br/>
            <h3><strong>Avaliação Cirúrgica</strong></h3>
            <p>Indicação de fundoplicatura de Nissen por videolaparoscopia. Risco de disfagia pós-operatória baixo. Taxa de sucesso >90%.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>K21.0 - Doença do refluxo gastroesofágico com esofagite</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Planejamento</strong></h3>
            <p>Cirurgia programada em 14 dias. Manometria esofágica e pHmetria já realizadas. Orientado sobre dieta pós-operatória.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Jejum de 8 horas antes da cirurgia</li>
                <li>Iniciar dieta líquida no pós-operatório imediato</li>
                <li>Evitar alimentos sólidos nas primeiras 2 semanas</li>
            </ul>
        `
    },

    "Cirurgia Geral": {
        description: "Estrutura voltada para avaliação cirúrgica geral.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Cirúrgico Relevante",
            "Exames Relevantes",
            "Avaliação Cirúrgica",
            "Impressão Diagnóstica (CID)",
            "Conduta e Planejamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Hérnia inguinal direita sintomática</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente refere abaulamento em região inguinal direita com dor discreta, piorando com esforço físico.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Abaulamento notado há 6 meses, com aumento progressivo. Dor ocasional, principalmente ao carregar peso. Redutível espontaneamente.</p>
            <br/>
            <h3><strong>Histórico Cirúrgico Relevante</strong></h3>
            <p>Apendicectomia há 10 anos. Sem outras cirurgias. Sem histórico de hérnia contralateral.</p>
            <br/>
            <h3><strong>Avaliação Cirúrgica</strong></h3>
            <p>Indicação de herniorrafia inguinal com tela por videolaparoscopia. Procedimento ambulatorial. Risco de recidiva <5%.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>K40.9 - Hérnia inguinal unilateral sem obstrução ou gangrena</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Planejamento</strong></h3>
            <p>Cirurgia programada em 7 dias. Avaliação pré-anestésica realizada. Orientado sobre cuidados pós-operatórios.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Jejum de 8 horas antes da cirurgia</li>
                <li>Evitar esforços físicos por 30 dias</li>
                <li>Retornar em caso de dor intensa ou vermelhidão</li>
            </ul>
        `
    },

    "Cirurgia Pediátrica": {
        description: "Estrutura voltada para avaliação cirúrgica pediátrica.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Pediátrico Relevante",
            "Exames Relevantes",
            "Avaliação Cirúrgica",
            "Impressão Diagnóstica (CID)",
            "Conduta e Planejamento",
            "Orientações aos Responsáveis"
        ],
        example: `
            <h2><strong>Hérnia umbilical em lactente</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Criança de 8 meses com abaulamento em região umbilical, notado pelos pais há 3 meses.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Abaulamento aumenta com choro e esforço, reduzível espontaneamente. Sem sinais de inflamação ou obstrução. Assintomático.</p>
            <br/>
            <h3><strong>Histórico Pediátrico Relevante</strong></h3>
            <p>Nascido a termo, peso ao nascer 3,2kg. Desenvolvimento neuropsicomotor adequado. Vacinação em dia. Sem cirurgias prévias.</p>
            <br/>
            <h3><strong>Avaliação Cirúrgica</strong></h3>
            <p>Hérnia umbilical com anel de 1,5cm. Indicação de herniorrafia umbilical devido ao tamanho e persistência. Procedimento simples, baixo risco.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>K42.9 - Hérnia umbilical sem obstrução ou gangrena</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Planejamento</strong></h3>
            <p>Cirurgia programada em 10 dias. Procedimento ambulatorial sob anestesia geral. Tempo cirúrgico estimado: 30 minutos.</p>
            <br/>
            <h3><strong>Orientações aos Responsáveis</strong></h3>
            <ul>
                <li>Jejum de 6 horas para leite materno, 4 horas para fórmula</li>
                <li>Trazer documentos da criança e carteira de vacinação</li>
                <li>Observar ferida cirúrgica nos dias seguintes</li>
            </ul>
        `
    },

    "Cirurgia Plástica": {
        description: "Estrutura voltada para avaliação cirúrgica plástica.",
        structure: [
            "Queixa Principal",
            "Objetivo da Consulta",
            "História Clínica Relevante",
            "Avaliação da Região de Interesse",
            "Exames Relevantes",
            "Avaliação Cirúrgica",
            "Impressão Diagnóstica (CID)",
            "Conduta e Planejamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Rinoplastia estética</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente insatisfeito com aparência nasal, deseja corrigir giba nasal e ponte nasal proeminente.</p>
            <br/>
            <h3><strong>Objetivo da Consulta</strong></h3>
            <p>Procedimento estético para melhorar harmonia facial. Expectativas realistas discutidas.</p>
            <br/>
            <h3><strong>História Clínica Relevante</strong></h3>
            <p>Sem cirurgias nasais prévias. Sem alergias conhecidas. Tabagista (5 cigarros/dia por 10 anos).</p>
            <br/>
            <h3><strong>Avaliação da Região de Interesse</strong></h3>
        <p>Giba nasal moderada, ponte nasal proeminente, ponta levemente caída. Função respiratória preservada.</p>
            <br/>
            <h3><strong>Avaliação Cirúrgica</strong></h3>
            <p>Indicação de rinoplastia fechada. Riscos: assimetria, necessidade de revisão. Recuperação: 7-10 dias.</p>
            <br/>
            <h3><strong>Conduta e Planejamento</strong></h3>
            <p>Cirurgia programada em 21 dias. Fotografias pré-operatórias realizadas. Orientado sobre suspensão do tabagismo.</p>
            <br/>
            <h3><strong>Orientações</strong></n3>
            <ul>
                <li>Suspender tabagismo 2 semanas antes da cirurgia</li>
                <li>Evitar aspirina e anti-inflamatórios 7 dias antes</li>
                <li>Utilizar óculos nos primeiros 7 dias pós-cirurgia</li>
            </ul>
        `
    },

    "Cirurgia Torácica": {
        description: "Estrutura voltada para avaliação cirúrgica torácica.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Torácico Relevante",
            "Exames Relevantes",
            "Avaliação Cirúrgica",
            "Impressão Diagnóstica (CID)",
            "Conduta e Planejamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Nódulo pulmonar com suspeita de malignidade</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com nódulo em lobo superior direito detectado em tomografia, com características suspeitas para malignidade.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Nódulo identificado incidentalmente em TC de tórax para investigação de tosse crônica. Tamanho: 2,3cm, espiculado. PET-CT com captação aumentada.</p>
            <br/>
            <h3><strong>Histórico Torácico Relevante</strong></h3>
            <p>Tabagismo por 35 anos (1 maço/dia). Tosse crônica há 6 meses. Sem histórico de câncer pulmonar familiar.</p>
            <br/>
            <h3><strong>Avaliação Cirúrgica</strong></h3>
            <p>Indicação de lobectomia superior direita com linfadenectomia. Risco cirúrgico intermediário. Função pulmonar adequada.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>C78.0 - Neoplasia maligna secundária do pulmão</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Planejamento</strong></h3>
            <p>Toracotomia programada em 14 dias. Broncoscopia e biópsia já realizadas. Avaliação cardiológica pré-operatória normal.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Suspender tabagismo imediatamente</li>
                <li>Realizar exercícios respiratórios pré-operatórios</li>
                <li>Jejum de 8 horas antes da cirurgia</li>
            </ul>
        `
    },

    "Cirurgia Vascular": {
        description: "Estrutura voltada para avaliação cirúrgica vascular.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Vascular Relevante",
            "Exames Relevantes",
            "Avaliação Cirúrgica",
            "Impressão Diagnóstica (CID)",
            "Conduta e Planejamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Doença arterial periférica com claudicação</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com dor em panturrilha direita ao caminhar 200 metros, aliviando com repouso. Claudicação intermitente progressiva.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Sintomas iniciados há 2 anos, com piora gradual. Distância de claudicação reduziu de 500m para 200m. Pulsos distais diminuídos.</p>
            <br/>
            <h3><strong>Histórico Vascular Relevante</strong></h3>
            <p>Tabagismo por 40 anos. Hipertensão arterial. Dislipidemia. Sem revascularizações prévias.</p>
            <br/>
            <h3><strong>Avaliação Cirúrgica</strong></h3>
            <p>Estenose de 70% em artéria femoral superficial direita. Indicação de angioplastia com stent. Alternativa: bypass femoropoplíteo.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>I70.20 - Aterosclerose de artérias de extremidades</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Planejamento</strong></h3>
            <p>Angioplastia programada em 7 dias. Doppler arterial e angiotomografia já realizadas. Otimização clínica em andamento.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Suspender tabagismo imediatamente</li>
                <li>Praticar caminhadas regulares até o limite de dor</li>
                <li>Utilizar medicações prescritas regularmente</li>
            </ul>
        `
    },

    "Clínica Médica": {
        description: "Estrutura voltada para consultas clínicas gerais, avaliação diagnóstica e acompanhamento de condições médicas.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Clínico Relevante",
            "Histórico Familiar Relevante",
            "Hábitos e Estilo de Vida",
            "Exames Relevantes",
            "Avaliação Clínica",
            "Impressão Diagnóstica (CID)",
            "Conduta e Plano Terapêutico",
            "Orientações"
        ],
        example: `
            <h2><strong>Controle de hipertensão arterial sistêmica</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente refere pressão arterial elevada e cefaleia recorrente nas últimas semanas.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Relata episódios frequentes de pressão arterial acima de 150/90 mmHg, acompanhados de cefaleia frontal. Nega dor torácica, dispneia ou síncope. Refere adesão irregular ao tratamento medicamentoso.</p>
            <br/>
            <h3><strong>Histórico Clínico Relevante</strong></h3>
            <p>Hipertensão arterial diagnosticada há 8 anos. Dislipidemia em acompanhamento. Nega internações recentes. Em uso de losartana 50 mg ao dia.</p>
            <br/>
            <h3><strong>Histórico Familiar Relevante</strong></h3>
            <p>Pai hipertenso e diabético. Mãe com histórico de acidente vascular cerebral aos 68 anos.</p>
            <br/>
            <h3><strong>Hábitos e Estilo de Vida</strong></h3>
            <p>Sedentarismo. Alimentação rica em sódio. Nega tabagismo. Consumo ocasional de bebidas alcoólicas.</p>
            <br/>
            <h3><strong>Exames Relevantes</strong></h3>
            <p>Exames laboratoriais recentes sem alterações significativas. Eletrocardiograma sem sinais de isquemia aguda.</p>
            <br/>
            <h3><strong>Avaliação Clínica</strong></h3>
            <p>Hipertensão arterial sistêmica com controle inadequado, possivelmente relacionada à baixa adesão terapêutica e fatores de estilo de vida.</p>
            <br/>
            <h3><strong>Impressão Diagnóstica (CID)</strong></h3>
            <ul>
                <li>I10 - Hipertensão essencial (primária)</li>
                <li>E78.5 - Hiperlipidemia não especificada</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Plano Terapêutico</strong></h3>
            <p>Ajustada dose da medicação anti-hipertensiva. Solicitado MAPA e exames laboratoriais de controle. Reforçadas medidas não farmacológicas.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Realizar atividade física regularmente</li>
                <li>Reduzir consumo de sal na alimentação</li>
                <li>Manter uso correto das medicações prescritas</li>
                <li>Retornar com exames para reavaliação</li>
            </ul>
        `
    },

    "Coloproctologia": {
        description: "Estrutura voltada para avaliação coloproctológica.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Coloproctológico Relevante",
            "Exames Relevantes",
            "Impressão Diagnóstica (CID)",
            "Conduta e Tratamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Hemorroidas sintomáticas grau III</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente refere sangramento anal, prolapso e prurido anal há 6 meses, com piora progressiva.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Sangramento vermelho vivo ao evacuar, necessitando redução manual de mamilos. Dor discreta. Constipação intestinal ocasional.</p>
            <br/>
            <h3><strong>Histórico Coloproctológico Relevante</strong></h3>
            <p>Episódios recorrentes há 3 anos. Tratamento clínico sem melhora. Sem cirurgias anais prévias.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>K64.4 - Hemorroidas de terceiro grau</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>Indicação de hemorroidectomia. Colonoscopia prévia normal. Orientado sobre dieta rica em fibras e hidratação.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Aumentar ingestão de fibras e água</li>
                <li>Evitar esforço evacuatório</li>
                <li>Realizar banho de assento com água morna</li>
            </ul>
        `
    },

    "Dermatologia": {
        description: "Estrutura voltada para avaliação dermatológica.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Dermatológico Relevante",
            "Exames Relevantes",
            "Impressão Diagnóstica (CID)",
            "Conduta e Tratamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Psoríase em placas extensa</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com placas eritematoescamosas em cotovelos, joelhos e couro cabeludo, associadas a prurido intenso.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Lesões iniciadas há 2 anos, com episódios de melhora e piora. Piora recente após período de estresse. Prurido interferindo no sono.</p>
            <br/>
            <h3><strong>Histórico Dermatológico Relevante</strong></h3>
            <p>Psoríase diagnosticada há 2 anos. Tratamento tópico parcialmente eficaz. Sem histórico de artrite psoriásica.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>L40.0 - Psoríase vulgar</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>Iniciado tratamento com corticosteroide tópico e emolientes. Orientado sobre fototerapia. Considerar terapia sistêmica se não houver melhora.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Manter hidratação cutânea com emolientes</li>
                <li>Evitar banhos muito quentes</li>
                <li>Proteger-se do sol nas áreas afetadas</li>
            </ul>
        `
    },

    "Endocrinologia e Metabologia": {
        description: "Estrutura voltada para avaliação endocrinológica.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Endócrino Relevante",
            "Exames Relevantes",
            "Impressão Diagnóstica (CID)",
            "Conduta e Tratamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Diabetes mellitus tipo 2 recém-diagnosticada</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com glicemia elevada detectada em exames de rotina, sem sintomas específicos.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Glicemia de jejum 180mg/dL confirmada em dois exames. Hemoglobina glicosilada 8,2%. Assintomático.</p>
            <br/>
            <h3><strong>Histórico Endócrino Relevante</strong></h3>
            <p>Obesidade grau II. Hipertensão arterial. Dislipidemia. Sem histórico familiar de diabetes tipo 1.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>E11 - Diabetes mellitus tipo 2</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>Iniciado metformina 500mg 2x/dia. Orientado sobre dieta hipocalórica e exercícios físicos. Monitorização glicêmica domiciliar.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Realizar dieta balanceada com redução de carboidratos</li>
                <li>Praticar exercícios físicos regulares (150 min/semana)</li>
                <li>Monitorar glicemia capilar 2x/semana</li>
            </ul>
        `
    },

    "Endoscopia": {
        description: "Estrutura voltada para documentação endoscópica.",
        structure: [
            "Indicação do Procedimento",
            "História Clínica Resumida",
            "Exames Relevantes",
            "Achados ou Resultados Relevantes",
            "Impressão Diagnóstica (CID)",
            "Conduta e Recomendações",
            "Orientações"
        ],
        example: `
            <h2><strong>Endoscopia digestiva alta para investigação de dispepsia</strong></h2>
            <br/>
            <h3><strong>Indicação do Procedimento</strong></h3>
            <p>Investigação de dispepsia persistente há 3 meses, sem resposta ao tratamento clínico.</p>
            <br/>
            <h3><strong>História Clínica Resumida</strong></h3>
            <p>Paciente de 45 anos, com queixas de queimação epigástrica e plenitude pós-prandial. Sem perda de peso ou sangramento digestivo.</p>
            <br/>
            <h3><strong>Achados ou Resultados Relevantes</strong></h3>
            <p>Mucosa gástrica com hiperemia difusa. Úlcera gástrica pré-pilórica de 1cm, com bordas regulares. Biópsias realizadas. Helicobacter pylori positivo.</p>
            <br/>
            <h3><strong>Impressão Diagnóstica (CID)</strong></h3>
            <ul>
                <li>K25.9 - Úlcera gástrica não especificada</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Recomendações</strong></h3>
            <p>Iniciar esquema triplo para erradicação de H. pylori. Repetir endoscopia em 8 semanas para confirmação de cicatrização.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Seguir rigorosamente esquema antibiótico por 14 dias</li>
                <li>Evitar alimentos irritantes (álcool, café, picantes)</li>
                <li>Retornar em caso de sangramento ou dor intensa</li>
            </ul>
        `
    },

    "Gastroenterologia": {
        description: "Estrutura voltada para avaliação gastroenterológica.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Gastroenterológico Relevante",
            "Exames Relevantes",
            "Impressão Diagnóstica (CID)",
            "Conduta e Tratamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Gastrite crônica com H. pylori</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com queimação epigástrica, náuseas e plenitude pós-prandial há 6 meses.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Sintomas intermitentes, piorando após refeições pesadas. Uso ocasional de anti-inflamatórios. Sem perda de peso ou sangramento.</p>
            <br/>
            <h3><strong>Histórico Gastroenterológico Relevante</strong></h3>
            <p>Endoscopia prévia mostrando gastrite antral. Teste de urease positivo para H. pylori. Sem úlceras prévias.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>K29.5 - Gastrite crônica</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>Iniciado esquema triplo para erradicação de H. pylori. Inibidor de bomba de prótons por 4 semanas. Orientado sobre dieta.</p>
            <br/>
            <h3><strong>Orientações</strong></n3>
            <ul>
                <li>Evitar alimentos irritantes e picantes</li>
                <li>Fazer refeições fracionadas e em horários regulares</li>
                <li>Completar esquema antibiótico conforme prescrição</li>
            </ul>
        `
    },

    "Genética Médica": {
        description: "Estrutura voltada para avaliação genética.",
        structure: [
            "Queixa Principal",
            "História Clínica Atual",
            "Histórico Familiar Relevante",
            "Exames Genéticos e Complementares",
            "Hipótese Diagnóstica",
            "Conduta e Recomendações",
            "Orientações",
            "Impressão Diagnóstica (CID)"
        ],
        example: `
            <h2><strong>Avaliação genética para câncer de mama hereditário</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com histórico familiar significativo de câncer de mama, buscando avaliação de risco genético.</p>
            <br/>
            <h3><strong>História Clínica Atual</strong></h3>
            <p>Mulher de 35 anos, sem diagnóstico de câncer. Menarca aos 12 anos, primeiro filho aos 28 anos. Sem uso de anticoncepcionais orais.</p>
            <br/>
            <h3><strong>Histórico Familiar Relevante</strong></h3>
            <p>Mãe com câncer de mama aos 42 anos. Tia materna com câncer de ovário aos 45 anos. Avó materna com câncer de mama.</p>
            <br/>
            <h3><strong>Hipótese Diagnóstica</strong></h3>
            <p>Suspeita de mutação em genes BRCA1/BRCA2. Alto risco familial para câncer de mama e ovário.</p>
            <br/>
            <h3><strong>Conduta e Recomendações</strong></h3>
            <p>Solicitado teste genético para BRCA1/BRCA2. Aconselhamento genético realizado. Rastreamento mamográfico iniciado.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Realizar mamografia anual a partir dos 35 anos</li>
                <li>Considerar ressonância mamária complementar</li>
                <li>Aguardar resultado do teste genético para planejamento</li>
            </ul>
        `
    },

    "Geriatria": {
        description: "Estrutura voltada para avaliação geriátrica.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Geriátrico Relevante",
            "Exames Relevantes",
            "Avaliação Funcional",
            "Impressão Diagnóstica (CID)",
            "Conduta e Tratamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Paciente idoso com múltiplas comorbidades</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente de 78 anos com queixas de fadiga, falta de ar aos esforços e quedas recorrentes.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Fadiga progressiva há 6 meses. Quedas em casa nos últimos 3 meses, sem fraturas. Dispneia aos moderados esforços.</p>
            <br/>
            <h3><strong>Histórico Geriátrico Relevante</strong></h3>
            <p>Hipertensão arterial, diabetes mellitus tipo 2, insuficiência cardíaca classe II. Demência leve em investigação. Polifarmácia (7 medicamentos).</p>
            <br/>
            <h3><strong>Avaliação Funcional</strong></h3>
            <p>Escala de Katz: independente para AVDs. Escala de Lawton: dependência parcial para AIVDs. Teste de Timed Up and Go: 15 segundos.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>I50.9 - Insuficiência cardíaca não especificada</li>
                <li>R54 - Senilidade</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>Revisão de medicações para desprescrição desnecessária. Fisioterapia para prevenção de quedas. Avaliação geriátrica completa.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Remover tapetes e obstáculos em casa</li>
                <li>Utilizar calçados antiderrapantes</li>
                <li>Realizar exercícios de fortalecimento</li>
            </ul>
        `
    },

    "Ginecologia e Obstetrícia": {
        description: "Estrutura voltada para avaliação ginecológica e obstétrica.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Ginecológico e Obstétrico Relevante",
            "Exames Relevantes",
            "Impressão Diagnóstica (CID)",
            "Conduta e Tratamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Sangramento uterino anormal</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente de 42 anos com sangramento vaginal irregular há 4 meses, com ciclos menstruais prolongados e sangramento intermenstrual.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Ciclos menstruais anteriormente regulares (28 dias). Sangramento aumentado em quantidade e duração. Perda de peso não intencional de 3kg.</p>
            <br/>
            <h3><strong>Histórico Ginecológico e Obstétrico Relevante</strong></h3>
            <p>G2P2, última gestação há 8 anos. Sem uso de anticoncepcionais. USG transvaginal prévia mostrando espessamento endometrial.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>N93.9 - Sangramento uterino anormal não especificado</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>Solicitada biópsia de endométrio. USG pélvica transvaginal. Hemograma e dosagem hormonal. Considerar histeroscopia diagnóstica.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Registrar sangramentos em calendário menstrual</li>
                <li>Evitar uso de aspirina e anti-inflamatórios</li>
                <li>Comparecer com resultados dos exames solicitados</li>
            </ul>
        `
    },

    "Hematologia e Hemoterapia": {
        description: "Estrutura voltada para avaliação hematológica.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Hematológico Relevante",
            "Exames Relevantes",
            "Impressão Diagnóstica (CID)",
            "Conduta e Tratamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Anemia ferropriva</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com fadiga, palidez e falta de ar aos esforços há 3 meses.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Fadiga progressiva, limitando atividades diárias. Palidez cutânea e mucosa. Tontura ocasional ao levantar-se.</p>
            <br/>
            <h3><strong>Histórico Hematológico Relevante</strong></h3>
            <p>Sangramento menstrual abundante. Dieta pobre em ferro. Sem histórico familiar de anemias hereditárias.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>D50.9 - Anemia ferropriva não especificada</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>Hemoglobina 8,5g/dL, ferritina baixa. Iniciado sulfato ferroso 3x/dia. Investigação de causa do sangramento menstrual.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Tomar ferro com suco de laranja para melhor absorção</li>
                <li>Aumentar ingestão de alimentos ricos em ferro</li>
                <li>Evitar café, chá e leite próximo ao horário do ferro</li>
            </ul>
        `
    },

    "Homeopatia": {
        description: "Estrutura voltada para avaliação homeopática.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico de Saúde Relevante",
            "Exames Relevantes",
            "Impressão Diagnóstica (CID)",
            "Conduta e Tratamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Cefaleia tensional crônica</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com cefaleia tensional bilateral, pressiva, associada a estresse laboral.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Cefaleia diária, piorando ao final do dia. Intensidade moderada (6/10). Melhora com repouso e massagem.</p>
            <br/>
            <h3><strong>Histórico de Saúde Relevante</strong></h3>
            <p>Estresse laboral elevado. Distúrbios do sono. Ansiedade leve. Sem histórico de enxaqueca.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>G44.2 - Cefaleia tensional tipo episódica crônica</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>Prescrito tratamento homeopático individualizado. Orientações sobre manejo do estresse e técnicas de relaxamento.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Praticar técnicas de relaxamento diariamente</li>
                <li>Manter horários regulares de sono</li>
                <li>Evitar excesso de cafeína</li>
            </ul>
        `
    },

    "Infectologia": {
        description: "Estrutura voltada para avaliação infectológica.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Infectológico Relevante",
            "Exames Relevantes",
            "Impressão Diagnóstica (CID)",
            "Conduta e Tratamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Infecção do trato urinário</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com disúria, polaciúria e dor suprapúbica há 3 dias.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Sintomas iniciados abruptamente. Febre baixa (37,8°C). Urina com aspecto turvo. Sem dor lombar.</p>
            <br/>
            <h3><strong>Histórico Infectológico Relevante</strong></h3>
            <p>Episódio de ITU há 6 meses. Sem uso de cateteres recentes. Sem diabetes ou outras comorbidades.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>N39.0 - Infecção do trato urinário de localização não especificada</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>Urocultura solicitada. Iniciado nitrofurantoína por 7 dias. Hidratação intensa orientada.</p>
            <br/>
            <h3><strong>Orientações</strong></n3>
            <ul>
                <li>Aumentar ingestão de água (2-3L/dia)</li>
                <li>Completar esquema antibiótico conforme prescrição</li>
                <li>Retornar se houver febre alta ou dor lombar</li>
            </ul>
        `
    },

    "Mastologia": {
        description: "Estrutura voltada para avaliação mastológica.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Mamário Relevante",
            "Exames Relevantes",
            "Impressão Diagnóstica (CID)",
            "Conduta e Tratamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Nódulo mamário em mama esquerda</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente detectou nódulo em mama esquerda durante autoexame, indolor e móvel.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Nódulo notado há 2 semanas, sem alterações de tamanho. Sem secreção mamilar ou retração cutânea. Sem dor ou desconforto.</p>
            <br/>
            <h3><strong>Histórico Mamário Relevante</strong></h3>
            <p>Mamografia de rotina há 1 ano normal (BI-RADS 2). Sem histórico familiar de câncer de mama. Menarca aos 13 anos, primeiro filho aos 25 anos.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>D24 - Nódulo benigno da mama</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>USG mamária solicitada. Considerar mamografia diagnóstica complementar. Acompanhamento clínico em 3 meses.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Continuar autoexame mensal</li>
                <li>Comparecer com resultados dos exames</li>
                <li>Observar alterações no nódulo</li>
            </ul>
        `
    },

    "Medicina de Emergência": {
        description: "Estrutura voltada para avaliação de emergência.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Clínico Relevante",
            "Exames Relevantes",
            "Avaliação Clínica",
            "Conduta e Plano Terapêutico",
            "Orientações",
            "Impressão Diagnóstica (CID)"
        ],
        example: `
            <h2><strong>Dor abdominal aguda em fossa ilíaca direita</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com dor abdominal em fossa ilíaca direita há 6 horas, associada a náuseas e vômitos.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Dor iniciada em região periumbilical, migrando para fossa ilíaca direita. Anorexia. Febre de 38°C. Ausência de evacuações há 24h.</p>
            <br/>
            <h3><strong>Avaliação Clínica</strong></h3>
            <p>PA 120/80mmHg, FC 92bpm, Tax 38°C. Abdome tenso em fossa ilíaca direita, sinal de Blumberg positivo. Leucocitose com desvio à esquerda.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>K35 - Apendicite aguda</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Plano Terapêutico</strong></h3>
            <p>USG abdominal de urgência solicitada. Hidratação venosa iniciada. Analgesia e antieméticos. Preparação para possível apendicectomia.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Manter jejum absoluto</li>
                <li>Comunicar piora da dor ou febre alta</li>
                <li>Aguardar avaliação cirúrgica</li>
            </ul>
        `
    },

    "Medicina de Família e Comunidade": {
        description: "Estrutura voltada para avaliação em medicina de família.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico de Saúde Relevante",
            "Exames Relevantes",
            "Impressão Diagnóstica (CID)",
            "Conduta e Plano de Cuidados",
            "Orientações"
        ],
        example: `
            <h2><strong>Hipertensão arterial sistêmica</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com pressão arterial elevada detectada em consulta de rotina.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></n3>
            <p>Pressão arterial média de 150/95mmHg em 3 medições. Assintomático. Sem cefaleia ou tonturas.</p>
            <br/>
            <h3><strong>Histórico de Saúde Relevante</strong></h3>
            <p>Obesidade grau I. Sedentário. Consumo excessivo de sal. Familiar com hipertensão.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>I10 - Hipertensão essencial (primária)</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Plano de Cuidados</strong></h3>
            <p>Iniciado losartana 50mg/dia. Orientado sobre mudanças no estilo de vida. Monitorização domiciliar da PA. Retorno em 30 dias.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Reduzir consumo de sal</li>
                <li>Praticar exercícios físicos regulares</li>
                <li>Monitorar pressão arterial em casa 3x/semana</li>
            </ul>
        `
    },

    "Medicina do Trabalho": {
        description: "Estrutura voltada para avaliação ocupacional.",
        structure: [
            "Motivo da Avaliação",
            "História Clínica Atual",
            "Histórico Ocupacional Relevante",
            "Exames Relevantes",
            "Avaliação da Capacidade Laboral",
            "Conduta e Recomendações",
            "Orientações",
            "Impressão Diagnóstica (CID)"
        ],
        example: `
            <h2><strong>Avaliação de aptidão para trabalho em altura</strong></h2>
            <br/>
            <h3><strong>Motivo da Avaliação</strong></h3>
            <p>Avaliação de aptidão para função de eletricista, com trabalho em altura e exposição à eletricidade.</p>
            <br/>
            <h3><strong>História Clínica Atual</strong></h3>
            <p>Assintomático. Sem histórico de doenças cardíacas ou neurológicas. Sem epilepsia ou perdas de consciência.</p>
            <br/>
            <h3><strong>Histórico Ocupacional Relevante</strong></h3>
            <p>5 anos de experiência como eletricista. Sem acidentes de trabalho prévios. Exposição regular a altura e eletricidade.</p>
            <br/>
            <h3><strong>Avaliação da Capacidade Laboral</strong></h3>
            <p>ECG normal. Teste ergométrico sem alterações isquêmicas. Avaliação neurológica normal. Acuidade visual adequada.</p>
            <br/>
            <h3><strong>Conduta e Recomendações</strong></h3>
            <p>Apto para trabalho em altura e com eletricidade. Recomendado uso de EPIs adequados. Retorno anual para reavaliação.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Utilizar sempre EPIs adequados</li>
                <li>Seguir normas de segurança em altura</li>
                <li>Comunicar qualquer intercorrência de saúde</li>
            </ul>
        `
    },

    "Medicina do Esporte": {
        description: "Estrutura voltada para avaliação esportiva.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Esportivo Relevante",
            "Exames Relevantes",
            "Conduta e Tratamento",
            "Orientações",
            "Impressão Diagnóstica (CID)"
        ],
        example: `
            <h2><strong>Lesão muscular em isquiotibiais</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Atleta com dor aguda em posterior de coxa direita durante sprint, com incapacidade de continuar atividade.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Dor súbita durante aceleração em treino de sprint. Sensação de "estouro" muscular. Edema e equimose localizados.</p>
            <br/>
            <h3><strong>Histórico Esportivo Relevante</strong></h3>
            <p>Fundista profissional, 28 anos. Lesão prévia em isquiotibiais contralateral há 2 anos. Treino intenso recente.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>S83.5 - Lesão de isquiotibiais</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></n3>
            <p>Ressonância magnética confirmou ruptura grau II. Protocolo PRICE inicial. Fisioterapia intensiva. Retorno estimado em 6-8 semanas.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Repouso relativo com carga parcial</li>
                <li>Crioterapia nos primeiros 72 horas</li>
                <li>Seguir protocolo de reabilitação rigorosamente</li>
            </ul>
        `
    },

    "Medicina Física e Reabilitação": {
        description: "Estrutura voltada para avaliação de reabilitação.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Funcional Relevante",
            "Exames Relevantes",
            "Objetivos da Reabilitação",
            "Conduta e Tratamento",
            "Orientações",
            "Impressão Diagnóstica (CID)"
        ],
        example: `
            <h2><strong>Paciente pós-AVC em reabilitação</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente de 65 anos, 3 meses pós-AVC isquêmico em hemisfério esquerdo, com hemiparesia direita e afasia.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>AVC isquêmico há 3 meses. Hemiparesia direita moderada (força 3/5). Afasia de expressão. Marcha com auxílio de andador.</p>
            <br/>
            <h3><strong>Histórico Funcional Relevante</strong></h3>
            <p>Hipertensão arterial, diabetes mellitus tipo 2, fibrilação atrial. Independente para AVDs antes do AVC.</p>
            <br/>
            <h3><strong>Objetivos da Reabilitação</strong></h3>
            <p>Melhora da força muscular em membro superior direito. Recuperação da marcha independente. Melhora da comunicação verbal.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>I69.3 - Sequelas de infarto cerebral</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>Fisioterapia motora 3x/semana. Fonoaudiologia 2x/semana. Terapia ocupacional 2x/semana. Avaliação neurológica periódica.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Realizar exercícios domiciliares diariamente</li>
                <li>Manter medicações em horário regular</li>
                <li>Participar ativamente das sessões de terapia</li>
            </ul>
        `
    },

    "Medicina Intensiva": {
        description: "Estrutura voltada para avaliação em terapia intensiva.",
        structure: [
            "Resumo Clínico",
            "Evolução Atual",
            "Exames Relevantes",
            "Avaliação Clínica",
            "Conduta e Planejamento",
            "Orientações",
            "Impressão Diagnóstica (CID)"
        ],
        example: `
            <h2><strong>Paciente em UTI com sepse</strong></h2>
            <br/>
            <h3><strong>Resumo Clínico</strong></h3>
            <p>Paciente de 58 anos, admitido há 24h com sepse de origem urinária. Instabilidade hemodinâmica inicial, estabilizado com vasopressores.</p>
            <br/>
            <h3><strong>Evolução Atual</strong></h3>
            <p>PA 95/60mmHg com noradrenalina 0,3mcg/kg/min. Diurese 40mL/h. Leucócitos em queda (18.000 para 12.000). Lactato normalizado.</p>
            <br/>
            <h3><strong>Avaliação Clínica</strong></h3>
            <p>Consciente, orientado. Ausculta pulmonar com crepitações em bases. Abdome sem alterações. Sem foco infeccioso evidente além de trato urinário.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>A41.9 - Sepse não especificada</li>
                <li>N39.0 - Infecção do trato urinário</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Planejamento</strong></h3>
            <p>Manter antibiótico por 7 dias totais. Desmame progressivo de vasopressores. Monitorização rigorosa de função renal.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Manter hidratação adequada</li>
                <li>Monitorizar sinais de deterioração</li>
                <li>Preparar para possível alta da UTI em 48h</li>
            </ul>
        `
    },

    "Medicina Legal e Perícia Médica": {
        description: "Estrutura voltada para avaliação médico-legal.",
        structure: [
            "Motivo da Avaliação",
            "Histórico Clínico Relevante",
            "Limitações e Capacidade Funcional",
            "Documentação e Exames Apresentados",
            "Conclusão Pericial",
            "Recomendações"
        ],
        example: `
            <h2><strong>Avaliação de incapacidade laboral</strong></h2>
            <br/>
            <h3><strong>Motivo da Avaliação</strong></h3>
            <p>Avaliação pericial para determinação de incapacidade laboral devido a lombalgia crônica.</p>
            <br/>
            <h3><strong>Histórico Clínico Relevante</strong></h3>
            <p>Lombalgia há 3 anos, piorando com atividades que envolvem flexão do tronco. Tratamentos conservadores sem melhora significativa.</p>
            <br/>
            <h3><strong>Limitações e Capacidade Funcional</strong></h3>
            <p>Dor lombar limita levantamento de pesos >10kg. Dificuldade para permanecer em pé por >30 minutos. Capacidade para trabalho sedentário preservada.</p>
            <br/>
            <h3><strong>Documentação e Exames Apresentados</strong></h3>
            <p>Ressonância magnética mostrando protrusão discal L4-L5. Relatórios de fisioterapia e tratamentos anteriores.</p>
            <br/>
            <h3><strong>Conclusão Pericial</strong></h3>
            <p>Incapacidade parcial para atividades que exigem esforço físico. Aptidão mantida para trabalho administrativo/sedentário.</p>
            <br/>
            <h3><strong>Recomendações</strong></h3>
            <ul>
                <li>Reabilitação funcional contínua</li>
                <li>Adequação do ambiente de trabalho</li>
                <li>Reavaliação em 6 meses</li>
            </ul>
        `
    },

    "Medicina Nuclear": {
        description: "Estrutura voltada para avaliação em medicina nuclear.",
        structure: [
            "Indicação Clínica",
            "História Clínica Resumida",
            "Exames Relevantes",
            "Hipótese Diagnóstica",
            "Conduta e Recomendações",
            "Orientações",
            "Impressão Diagnóstica (CID)"
        ],
        example: `
            <h2><strong>Cintilografia miocárdica para investigação de isquemia</strong></h2>
            <br/>
            <h3><strong>Indicação Clínica</strong></h3>
            <p>Investigação de isquemia miocárdica em paciente com dor torácica atípica e fatores de risco cardiovascular.</p>
            <br/>
            <h3><strong>História Clínica Resumida</strong></h3>
            <p>Mulher de 55 anos, hipertensa, dislipidêmica. Dor torácica ocasional aos esforços moderados. ECG basal normal.</p>
            <br/>
            <h3><strong>Exames Relevantes</strong></h3>
            <p>Cintilografia miocárdica com estresse farmacológico (dipiridamol). Imagens em repouso e estresse comparadas.</p>
            <br/>
            <h3><strong>Hipótese Diagnóstica</strong></h3>
            <p>Suspeita de doença arterial coronariana com isquemia reversível em território da artéria descendente anterior.</p>
            <br/>
            <h3><strong>Conduta e Recomendações</strong></h3>
            <p>Isquemia moderada em parede anterior. Indicação de coronariografia invasiva para confirmação e planejamento terapêutico.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Comparecer para coronariografia conforme agendamento</li>
                <li>Manter medicações em uso</li>
                <li>Evitar esforços intensos até avaliação</li>
            </ul>
        `
    },

    "Medicina Preventiva e Social": {
        description: "Estrutura voltada para avaliação preventiva.",
        structure: [
            "Queixa Principal",
            "História Clínica Atual",
            "Histórico de Saúde Relevante",
            "Exames Relevantes",
            "Avaliação de Risco e Prevenção",
            "Impressão Diagnóstica (CID)",
            "Conduta e Recomendações",
            "Orientações"
        ],
        example: `
            <h2><strong>Consulta de check-up preventivo</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente assintomático, comparecendo para check-up preventivo anual.</p>
            <br/>
            <h3><strong>História Clínica Atual</strong></h3>
            <p>Assintomático. Sem queixas atuais. Histórico de hipertensão arterial controlada.</p>
            <br/>
            <h3><strong>Histórico de Saúde Relevante</strong></h3>
            <p>Hipertensão arterial diagnosticada há 5 anos, controlada. Ex-tabagista (parou há 10 anos). Sobrepeso.</p>
            <br/>
            <h3><strong>Avaliação de Risco e Prevenção</strong></h3>
            <p>Risco cardiovascular intermediário. Necessário rastreamento de diabetes e dislipidemia. Vacinação em dia.</p>
            <br/>
            <h3><strong>Conduta e Recomendações</strong></h3>
            <p>Solicitados hemograma, glicemia, lipidograma, TSH e urina tipo I. Mamografia e colonoscopia conforme idade. Manter estilo de vida saudável.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Manter dieta balanceada e exercícios regulares</li>
                <li>Comparecer com resultados dos exames</li>
                <li>Retorno anual para check-up</li>
            </ul>
        `
    },

    "Nefrologia": {
        description: "Estrutura voltada para avaliação nefrológica.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Nefrológico Relevante",
            "Exames Relevantes",
            "Impressão Diagnóstica (CID)",
            "Conduta e Tratamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Doença renal crônica estágio 3</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com creatinina elevada detectada em exames de rotina, assintomático.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Creatinina 2,1mg/dL, TFGe 38mL/min/1,73m². Assintomático. Sem edema, oligúria ou alterações urinárias.</p>
            <br/>
            <h3><strong>Histórico Nefrológico Relevante</strong></h3>
            <p>Hipertensão arterial há 15 anos. Diabetes mellitus tipo 2 há 10 anos. Sem histórico de doença renal familiar.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>N18.3 - Doença renal crônica estágio 3</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>Otimização de pressão arterial e glicemia. Restrição proteica moderada. Evitar nefrotóxicos. Monitorização trimestral da função renal.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Manter controle rigoroso da pressão arterial</li>
                <li>Restringir sal e proteínas na dieta</li>
                <li>Evitar uso de anti-inflamatórios</li>
            </ul>
        `
    },

    "Neurocirurgia": {
        description: "Estrutura voltada para avaliação neurocirúrgica.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Neurocirúrgico Relevante",
            "Exames Relevantes",
            "Impressão Diagnóstica (CID)",
            "Conduta e Planejamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Meningioma frontal esquerdo</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com cefaleia progressiva e crises convulsivas recentes.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Cefaleia frontal esquerda há 6 meses, progressiva. Duas crises convulsivas tônico-clônicas nos últimos 2 meses. Sem déficits neurológicos focais.</p>
            <br/>
            <h3><strong>Histórico Neurocirúrgico Relevante</strong></h3>
            <p>Sem cirurgias cranianas prévias. Sem histórico de trauma craniano. Epilepsia de início recente.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>D32.0 - Meningioma benigno da fossa craniana anterior</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Planejamento</strong></h3>
            <p>Indicação de ressecção cirúrgica do meningioma. Craniotomia frontal esquerda planejada. Risco de déficit neurológico baixo.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Jejum de 8 horas antes da cirurgia</li>
                <li>Continuar anticonvulsivante conforme prescrição</li>
                <li>Comparecer com acompanhante</li>
            </ul>
        `
    },

    "Neurologia": {
        description: "Estrutura voltada para avaliação neurológica detalhada.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Neurológico Relevante",
            "Exames Relevantes",
            "Impressão Diagnóstica (CID)",
            "Conduta e Tratamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Enxaqueca com aura</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com cefaleia hemicraniana pulsátil, associada a fotofobia e náuseas, precedida por escotomas.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Crises mensais há 5 anos, com piora recente (2x/mês). Aura visual (escotomas) precede dor em 20 minutos. Duração 4-6 horas.</p>
            <br/>
            <h3><strong>Histórico Neurológico Relevante</strong></h3>
            <p>Mãe com história de enxaqueca. Sem história de trauma craniano. Sem uso regular de analgésicos.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>G43.1 - Enxaqueca com aura</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>Iniciado triptano para crises agudas. Considerar profilaxia com topiramato. Orientado sobre diário de cefaleia e gatilhos.</p>
            <br/>
            <h3><strong>Orientações</strong></n3>
            <ul>
                <li>Identificar e evitar gatilhos individuais</li>
                <li>Manter diário de crises</li>
                <li>Utilizar triptano no início da crise</li>
            </ul>
        `
    },

    "Nutrologia": {
        description: "Estrutura voltada para avaliação nutrológica.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Nutrológico Relevante",
            "Exames Relevantes",
            "Impressão Diagnóstica (CID)",
            "Conduta e Tratamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Obesidade grau II</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com ganho de peso progressivo, dificuldade em perder peso mesmo com dietas.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>IMC 35kg/m². Ganho de 20kg nos últimos 5 anos. Tentativas de dieta sem sucesso. Fadiga e dispneia aos esforços.</p>
            <br/>
            <h3><strong>Histórico Nutrológico Relevante</strong></h3>
            <p>Obesidade desde adolescência. Compulsão alimentar ocasional. Sedentarismo. Hipotireoidismo tratado.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>E66.01 - Obesidade por excesso de calorias</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>Plano alimentar hipocalórico individualizado (1500kcal/dia). Programa de exercícios progressivos. Considerar farmacoterapia se não houver resposta.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Seguir plano alimentar rigorosamente</li>
                <li>Praticar exercícios físicos 150min/semana</li>
                <li>Registrar ingestão alimentar diariamente</li>
            </ul>
        `
    },

    "Oftalmologia": {
        description: "Estrutura voltada para avaliação oftalmológica.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Sintomas Oculares Associados",
            "Histórico Oftalmológico",
            "Histórico Médico Geral",
            "Medicações em Uso",
            "Suspeita Diagnóstica (CID)",
            "Exames Oftalmológicos Sugeridos",
            "Conduta e Encaminhamento",
            "Orientações ao Paciente"
        ],
        example: `
            <h2><strong>Catarata senil bilateral</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com visão embaçada progressiva bilateral, dificultando atividades diárias como leitura e direção.</p>
            <br/>
            <h3><strong>História da Doença Atual</h3>
            <p>Visão embaçada iniciada há 2 anos, piorando gradualmente. Dificuldade para dirigir à noite. Sensibilidade aumentada à luz.</p>
            <br/>
            <h3><strong>Sintomas Oculares Associados</strong></h3>
            <p>Halos ao redor de luzes. Visão dupla ocasional. Dificuldade para distinguir cores.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>H25.9 - Catarata senil não especificada</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Encaminhamento</strong></h3>
            <p>Avaliação oftalmológica completa. Acuidade visual reduzida em ambos os olhos. Indicação de facoemulsificação bilateral em estágios.</p>
            <br/>
            <h3><strong>Orientações ao Paciente</strong></h3>
            <ul>
                <li>Utilizar óculos com proteção UV</li>
                <li>Evitar dirigir à noite até cirurgia</li>
                <li>Agendar cirurgia conforme disponibilidade</li>
            </ul>
        `
    },

    "Oncologia Clínica": {
        description: "Estrutura voltada para avaliação oncológica.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Oncológico Relevante",
            "Histórico Familiar Relevante",
            "Exames Relevantes",
            "Avaliação Oncológica",
            "Impressão Diagnóstica (CID)",
            "Conduta e Planejamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Câncer de mama estágio II</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente de 48 anos com nódulo em mama esquerda detectado em mamografia de rastreamento.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Nódulo de 2,5cm em quadrante superior externo esquerdo. Biópsia mostrando carcinoma ductal invasor. Sem linfonodos palpáveis.</p>
            <br/>
            <h3><strong>Histórico Oncológico Relevante</strong></h3>
            <p>Sem tratamentos oncológicos prévios. Menarca aos 12 anos, primeiro filho aos 30 anos. Sem uso de anticoncepcionais orais.</p>
            <br/>
            <h3><strong>Histórico Familiar Relevante</strong></h3>
            <p>Mãe com câncer de mama aos 55 anos. Tia materna com câncer de ovário.</p>
            <br/>
            <h3><strong>Avaliação Oncológica</strong></h3>
            <p>RE positivo, HER2 negativo, Ki-67 25%. Estadiamento: T2N0M0 (estádio IIA). Receptores hormonais positivos.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>C50.9 - Neoplasia maligna da mama não especificada</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Planejamento</strong></h3>
            <p>Quimioterapia neoadjuvante seguida de cirurgia conservadora. Radioterapia adjuvante. Hormonioterapia por 5-10 anos.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Comparecer para início de quimioterapia conforme agendamento</li>
                <li>Manter acompanhamento psicológico</li>
                <li>Comunicar intercorrências durante tratamento</li>
            </ul>
        `
    },

    "Ortopedia e Traumatologia": {
        description: "Estrutura voltada para avaliação ortopédica.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Mecanismo de Trauma ou Lesão",
            "Sintomas Associados",
            "Histórico Ortopédico",
            "Histórico Médico Geral",
            "Medicações em Uso",
            "Exames de Imagem ou Avaliações Realizadas",
            "Suspeita Diagnóstica (CID)",
            "Conduta e Tratamento",
            "Orientações ao Paciente"
        ],
        example: `
            <h2><strong>Fratura de fêmur proximal</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente idoso com dor intensa em quadril direito após queda em casa, incapacidade de deambular.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Queda da própria altura há 4 horas. Dor intensa em quadril direito. Membro inferior encurtado e rodado externamente.</p>
            <br/>
            <h3><strong>Mecanismo de Trauma ou Lesão</strong></h3>
            <p>Queda em casa ao tropeçar em tapete. Trauma de baixa energia. Sem perda de consciência.</p>
            <br/>
            <h3><strong>Sintomas Associados</strong></h3>
            <p>Dor intensa, incapacidade de deambular, edema em quadril direito. Sem outras lesões aparentes.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>S72.0 - Fratura do colo do fêmur</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>Radiografia confirmando fratura transtrocanteriana. Indicação de osteossíntese com haste intramedular. Cirurgia em 24h.</p>
            <br/>
            <h3><strong>Orientações ao Paciente</strong></h3>
            <ul>
                <li>Jejum absoluto para cirurgia</li>
                <li>Manter repouso absoluto no leito</li>
                <li>Analgesia conforme prescrição</li>
            </ul>
        `
    },

    "Otorrinolaringologia": {
        description: "Estrutura voltada para avaliação otorrinolaringológica.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Sintomas Otológicos (Ouvido)",
            "Sintomas Nasais",
            "Sintomas de Garganta e Laringe",
            "Sintomas Associados",
            "Histórico Otorrinolaringológico",
            "Fatores de Risco e Exposição",
            "Medicações em Uso",
            "Exames Realizados ou Sugeridos",
            "Suspeita Diagnóstica (CID)",
            "Conduta e Tratamento",
            "Orientações ao Paciente"
        ],
        example: `
            <h2><strong>Otite média aguda</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com dor em ouvido direito, febre e diminuição da audição há 2 dias.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Dor otalgia intensa, febre até 38,5°C. Diminuição da audição em ouvido direito. Rinorreia anterior há 5 dias.</p>
            <br/>
            <h3><strong>Sintomas Otológicos (Ouvido)</strong></h3>
            <p>Dor intensa em ouvido direito. Sensação de ouvido cheio. Diminuição da audição. Sem otorréia.</p>
            <br/>
            <h3><strong>Sintomas Associados</strong></h3>
            <p>Febre, irritabilidade em crianças. Rinorreia anterior. Tosse leve.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>H66.9 - Otite média não especificada</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>Otosopia mostrando hiperemia e abaulamento de membrana timpânica. Iniciado antibiótico oral. Analgésicos para dor.</p>
            <br/>
            <h3><strong>Orientações ao Paciente</strong></h3>
            <ul>
                <li>Completar esquema antibiótico por 7-10 dias</li>
                <li>Evitar entrada de água no ouvido</li>
                <li>Retornar se dor persistir após 48h</li>
            </ul>
        `
    },

    "Patologia": {
        description: "Estrutura voltada para avaliação patológica.",
        structure: [
            "Indicação Clínica",
            "História Clínica Resumida",
            "Material ou Amostra Avaliada",
            "Achados Relevantes",
            "Impressão Diagnóstica (CID)",
            "Recomendações",
            "Orientações"
        ],
        example: `
            <h2><strong>Laudo anatomopatológico - Biópsia de pele</strong></h2>
            <br/>
            <h3><strong>Indicação Clínica</strong></h3>
            <p>Biópsia excisional de lesão cutânea em dorso para diagnóstico histológico.</p>
            <br/>
            <h3><strong>História Clínica Resumida</strong></h3>
            <p>Paciente de 52 anos, lesão pigmentada em dorso há 2 anos, com crescimento recente e mudança de coloração.</p>
            <br/>
            <h3><strong>Material ou Amostra Avaliada</strong></h3>
            <p>Fragmento de pele medindo 1,2 x 0,8cm, com lesão pigmentada central. Fixado em formol 10%.</p>
            <br/>
            <h3><strong>Achados Relevantes</strong></h3>
            <p>Microscopia: proliferação de melanócitos atípicos na epiderme e derme. Atipia citológica moderada. Margens cirúrgicas livres.</p>
            <br/>
            <h3><strong>Impressão Diagnóstica (CID)</strong></h3>
            <ul>
                <li>D03 - Melanoma in situ</li>
            </ul>
            <br/>
            <h3><strong>Recomendações</strong></h3>
            <p>Ressecção com margens ampliadas recomendada. Acompanhamento dermatológico regular.</p>
            <br/>
            <h3><strong>Orientações</strong></n3>
            <ul>
                <li>Comparecer para ressecção ampliada</li>
                <li>Proteger-se do sol</li>
                <li>Autoexame mensal da pele</li>
            </ul>
        `
    },

    "Patologia Clínica / Medicina Laboratorial": {
        description: "Estrutura voltada para avaliação laboratorial.",
        structure: [
            "Indicação Clínica",
            "História Clínica Resumida",
            "Exames Laboratoriais Relevantes",
            "Interpretação dos Resultados",
            "Impressão Diagnóstica (CID)",
            "Conduta e Recomendações",
            "Orientações"
        ],
        example: `
            <h2><strong>Interpretação de hemograma com anemia</strong></h2>
            <br/>
            <h3><strong>Indicação Clínica</strong></h3>
            <p>Hemograma de rotina para investigação de fadiga e palidez.</p>
            <br/>
            <h3><strong>História Clínica Resumida</strong></h3>
            <p>Paciente de 35 anos, com fadiga crônica, palidez cutânea e mucosa. Sangramento menstrual abundante.</p>
            <br/>
            <h3><strong>Exames Laboratoriais Relevantes</strong></h3>
            <p>Hemoglobina 9,5g/dL, VCM 78fL, HCM 26pg. Ferritina 8ng/mL. Leucócitos e plaquetas normais.</p>
            <br/>
            <h3><strong>Interpretação dos Resultados</strong></h3>
            <p>Anemia microcítica hipocrômica. Ferritina baixa indicando deficiência de ferro. VCM reduzido compatível com anemia ferropriva.</p>
            <br/>
            <h3><strong>Impressão Diagnóstica (CID)</strong></h3>
            <ul>
                <li>D50.9 - Anemia ferropriva não especificada</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Recomendações</strong></h3>
            <p>Sulfato ferroso 3x/dia. Investigar causa da perda sanguínea. Repetir hemograma em 30 dias.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Tomar ferro com suco de laranja</li>
                <li>Aumentar ingestão de ferro na dieta</li>
                <li>Retornar com resultados dos exames</li>
            </ul>
        `
    },

    "Pediatria": {
        description: "Estrutura voltada para avaliação pediátrica.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Pediátrico Relevante",
            "Exames Relevantes",
            "Impressão Diagnóstica (CID)",
            "Conduta e Tratamento",
            "Orientações aos Responsáveis"
        ],
        example: `
            <h2><strong>Febre em lactente</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Lactente de 8 meses com febre há 24 horas, irritabilidade e diminuição da aceitação alimentar.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Febre iniciada há 24h, pico de 39°C. Irritabilidade. Diminuição da aceitação de leite. Sem vômitos ou diarreia. Sem sintomas respiratórios.</p>
            <br/>
            <h3><strong>Histórico Pediátrico Relevante</strong></h3>
            <p>Nascido a termo, peso ao nascer 3,2kg. Vacinação em dia. Desenvolvimento neuropsicomotor adequado. Sem internações prévias.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>R50.9 - Febre não especificada</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>Exame físico sem foco infeccioso evidente. Hemograma e urina tipo I solicitados. Paracetamol para febre. Hidratação incentivada.</p>
            <br/>
            <h3><strong>Orientações aos Responsáveis</strong></h3>
            <ul>
                <li>Manter hidratação com oferta frequente de líquidos</li>
                <li>Administrar antitérmico conforme prescrição</li>
                <li>Retornar se febre persistir por mais 48h ou piorar</li>
            </ul>
        `
    },

    "Pneumologia": {
        description: "Estrutura voltada para avaliação pneumológica.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Respiratório Relevante",
            "Exames Relevantes",
            "Impressão Diagnóstica (CID)",
            "Conduta e Tratamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Asma brônquica moderada</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com dispneia, sibilância e tosse seca, piorando ao esforço e à noite.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
        <p>Sintomas iniciados há 6 meses, com piora progressiva. Crises 2-3x/semana. Despertares noturnos devido a sintomas.</p>
            <br/>
            <h3><strong>Histórico Respiratório Relevante</strong></h3>
            <p>Rinite alérgica. Histórico familiar de asma. Tabagismo negativo. Exposição a ácaros.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>J45 - Asma</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>Espirometria com obstrução reversível. Iniciado corticoide inalatório e broncodilatador de longa ação. Testes alérgicos solicitados.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Utilizar inalador corretamente conforme técnica ensinada</li>
                <li>Evitar gatilhos conhecidos (ácaros, fumo, frio)</li>
                <li>Comparecer para reavaliação em 30 dias</li>
            </ul>
        `
    },

    "Psiquiatria": {
        description: "Estrutura voltada para avaliação psiquiátrica.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Psiquiátrico Relevante",
            "Aspectos Psicossociais Relevantes",
            "Impressão Diagnóstica (CID)",
            "Conduta e Tratamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Transtorno depressivo maior</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com humor deprimido, anedonia, fadiga e dificuldade de concentração há 3 meses.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Sintomas iniciados após perda de emprego. Humor deprimido diário. Perda de interesse em atividades. Distúrbios do sono. Peso corporal aumentado.</p>
            <br/>
            <h3><strong>Histórico Psiquiátrico Relevante</strong></n            <p>Episódio depressivo leve há 5 anos, tratado com sucesso. Sem histórico de mania ou hipomania. Sem tentativas de suicídio.</p>
            <br/>
            <h3><strong>Aspectos Psicossociais Relevantes</strong></h3>
            <p>Desemprego há 3 meses. Suporte familiar limitado. Isolamento social progressivo.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>F32.1 - Episódio depressivo moderado</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>Iniciado antidepressivo (ISRS). Psicoterapia cognitivo-comportual semanal. Avaliação de risco suicídio baixo.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Tomar medicação em horário regular</li>
                <li>Participar das sessões de psicoterapia</li>
                <li>Comunicar pensamentos suicidas se ocorrerem</li>
            </ul>
        `
    },

    "Radiologia e Diagnóstico por Imagem": {
        description: "Estrutura voltada para avaliação radiológica.",
        structure: [
            "Indicação Clínica",
            "História Clínica Resumida",
            "Exames Relevantes",
            "Achados Importantes Relatados",
            "Impressão Diagnóstica (CID)",
            "Recomendações",
            "Orientações"
        ],
        example: `
            <h2><strong>Tomografia computadorizada de tórax</strong></h2>
            <br/>
            <h3><strong>Indicação Clínica</strong></h3>
            <p>Investigação de nódulo pulmonar detectado em radiografia de tórax.</p>
            <br/>
            <h3><strong>História Clínica Resumida</strong></h3>
            <p>Paciente de 62 anos, tabagista por 40 anos. Nódulo de 1,5cm em lobo superior direito em radiografia.</p>
            <br/>
            <h3><strong>Achados Importantes Relatados</strong></h3>
            <p>Nódulo espiculado em lobo superior direito, medindo 1,5cm. Sem linfonodomegalias mediastinais. Sem outras lesões pulmonares. Sem derrame pleural.</p>
            <br/>
            <h3><strong>Impressão Diagnóstica (CID)</strong></h3>
            <ul>
                <li>R91.1 - Achado anormal em exame de imagem do pulmão</li>
            </ul>
            <br/>
            <h3><strong>Recomendações</strong></n3>
            <p>PET-CT para avaliação metabólica. Considerar biópsia percutânea. Acompanhamento radiológico seriado.</p>
            <br/>
            <h3><strong>Orientações</strong></n3>
            <ul>
                <li>Comparecer para PET-CT conforme agendamento</li>
                <li>Suspender tabagismo imediatamente</li>
                <li>Retornar com resultados dos exames</li>
            </ul>
        `
    },

    "Radioterapia": {
        description: "Estrutura voltada para avaliação radioterápica.",
        structure: [
            "Queixa Principal",
            "História Clínica",
            "Tratamentos Oncológicos Relevantes",
            "Exames Relevantes",
            "Impressão Diagnóstica (CID)",
            "Planejamento Terapêutico",
            "Orientações"
        ],
        example: `
            <h2><strong>Radioterapia adjuvante para câncer de mama</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente em pós-operatório de cirurgia conservadora para câncer de mama, encaminhada para radioterapia adjuvante.</p>
            <br/>
            <h3><strong>História Clínica</strong></h3>
            <p>Carcinoma ductal invasor de mama esquerda, estágio IIA. Cirurgia conservadora realizada há 4 semanas. Margens cirúrgicas livres.</p>
            <br/>
            <h3><strong>Tratamentos Oncológicos Relevantes</strong></h3>
            <p>Quimioterapia neoadjuvante (4 ciclos). Planejamento de hormonioterapia por 5 anos após radioterapia.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>C50.9 - Neoplasia maligna da mama</li>
            </ul>
            <br/>
            <h3><strong>Planejamento Terapêutico</strong></h3>
            <p>Radioterapia em mama esquerda, 50Gy em 25 frações. Boost no leito tumoral, 10Gy em 5 frações. Início em 7 dias.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Comparecer diariamente para as sessões</li>
                <li>Cuidar da pele na área irradiada</li>
                <li>Comunicar efeitos colaterais significativos</li>
            </ul>
        `
    },

    "Reumatologia": {
        description: "Estrutura voltada para avaliação reumatológica.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Reumatológico Relevante",
            "Exames Relevantes",
            "Impressão Diagnóstica (CID)",
            "Conduta e Tratamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Artrite reumatoide</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com dor, edema e rigidez matinal em articulações das mãos, punhos e joelhos há 6 meses.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Sintomas iniciados bilateralmente e simetricamente. Rigidez matinal >1 hora. Fadiga generalizada. Piora progressiva.</p>
            <br/>
            <h3><strong>Histórico Reumatológico Relevante</strong></h3>
            <p>Sem histórico de artrite prévia. Fator reumatoide positivo. Sem doenças autoimunes associadas.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>M05.9 - Artrite reumatoide soropositiva</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>Iniciado metotrexato 15mg/semana. Prednisona 10mg/dia como ponte. Fisioterapia para mobilidade articular.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Tomar metotrexato semanalmente no mesmo dia</li>
                <li>Realizar exercícios de mobilidade diariamente</li>
                <li>Retornar em 30 dias para reavaliação</li>
            </ul>
        `
    },

    "Urologia": {
        description: "Estrutura voltada para avaliação urológica.",
        structure: [
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Urológico Relevante",
            "Exames Relevantes",
            "Impressão Diagnóstica (CID)",
            "Conduta e Tratamento",
            "Orientações"
        ],
        example: `
            <h2><strong>Litíase renal</strong></h2>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Paciente com cólica renal aguda, dor em flanco direito e hematúria.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Dor em flanco direito iniciada há 4 horas, intensidade 8/10. Náuseas e vômitos. Hematúria macroscópica.</p>
            <br/>
            <h3><strong>Histórico Urológico Relevante</strong></n        <p>Episódio de litíase há 2 anos. Sem cirurgias urológicas prévias. Hidratação inadequada.</p>
            <br/>
            <h3><strong>Suspeita Diagnóstica (CID)</strong></h3>
            <ul>
                <li>N20.0 - Cálculo renal</li>
            </ul>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>TC de abdome mostrando cálculo de 8mm em ureter direito. Analgesia com AINEs. Hidratação venosa. Considera litotripsia se não expulsar.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Aumentar ingestão de água (2-3L/dia)</li>
                <li>Coletar cálculo se expelido para análise</li>
                <li>Retornar se dor persistir ou piorar</li>
            </ul>
        `
    },

    "Modelo SOAP": {
        description: "Estrutura padrão SOAP (Subjetivo, Objetivo, Avaliação, Plano).",
        structure: [
            "Subjetivo (S)",
            "Objetivo (O)",
            "Avaliação (A)",
            "Impressão Diagnóstica (CID)",
            "Plano (P)",
            "Orientações"
        ],
        example: `
            <h2><strong>Consulta de acompanhamento - Hipertensão</strong></h2>
            <br/>
            <h3><strong>Subjetivo (S)</strong></h3>
            <p>Paciente refere pressão arterial controlada, sem cefaleia ou tonturas. Relata adesão ao tratamento medicamentoso.</p>
            <br/>
            <h3><strong>Objetivo (O)</strong></h3>
            <p>PA 128/82mmHg. FC 72bpm. Peso 78kg. Ausculta cardíaca e pulmonar normais. Sem edema de membros inferiores.</p>
            <br/>
            <h3><strong>Avaliação (A)</strong></h3>
            <p>Hipertensão arterial controlada. Boa adesão ao tratamento. Sem sinais de complicação cardiovascular.</p>
            <br/>
            <h3><strong>Impressão Diagnóstica (CID)</strong></h3>
            <ul>
                <li>I10 - Hipertensão essencial (primária)</li>
            </ul>
            <br/>
            <h3><strong>Plano (P)</strong></h3>
            <p>Manter losartana 50mg/dia. Monitorização domiciliar da PA 3x/semana. Retorno em 3 meses.</p>
            <br/>
            <h3><strong>Orientações</strong></h3>
            <ul>
                <li>Manter dieta hipossódica</li>
                <li>Praticar exercícios físicos regulares</li>
                <li>Registrar pressão arterial em casa</li>
            </ul>
        `
    },

    "Modelo SOAP Veterinário": {
        description: "Estrutura SOAP adaptada para medicina veterinária.",
        structure: [
            "Subjetivo (S)",
            "Nome",
            "Espécie",
            "Raça",
            "Sexo",
            "Idade",
            "Vacinação",
            "Vermifugação",
            "Controle de ectoparasitas",
            "Objetivo (O)",
            "Avaliação (A)",
            "Lista de Problemas",
            "Plano (P)",
            "Orientações ao Tutor"
        ],
        example: `
            <h2><strong>Consulta veterinária - Cão com dermatite</strong></h2>
            <br/>
            <h3><strong>Subjetivo (S)</strong></h3>
            <p>Tutor relata prurido intenso e lesões cutâneas em cão há 2 semanas. Animal coça-se frequentemente, causando feridas.</p>
            <br/>
            <h3><strong>Nome</strong></h3>
            <p>Rex</p>
            <br/>
            <h3><strong>Espécie</strong></h3>
            <p>Canino</p>
            <br/>
            <h3><strong>Raça</strong></h3>
            <p>Golden Retriever</p>
            <br/>
            <h3><strong>Sexo</strong></h3>
            <p>Macho castrado</p>
            <br/>
            <h3><strong>Idade</strong></h3>
            <p>4 anos</p>
            <br/>
            <h3><strong>Vacinação</strong></n3>
            <p>Em dia</p>
            <br/>
            <h3><strong>Vermifugação</strong></h3>
        <p>Última há 3 meses</p>
            <br/>
            <h3><strong>Objetivo (O)</strong></h3>
            <p>Eritema e descamação em região ventral. Alopecia secundária a prurido. Sem linfonodomegalia. Temperatura 38,5°C.</p>
            <br/>
            <h3><strong>Avaliação (A)</strong></n3>
            <p>Dermatite alérgica provável. Infecção bacteriana secundária devido a automutilação.</p>
            <br/>
            <h3><strong>Lista de Problemas</strong></h3>
            <ul>
                <li>Dermatite alérgica</li>
                <li>Piodermite secundária</li>
            </ul>
            <br/>
            <h3><strong>Plano (P)</strong></h3>
            <p>Antibiótico oral por 14 dias. Banho com shampoo antisséptico 2x/semana. Anti-histamínico para prurido.</p>
            <br/>
            <h3><strong>Orientações ao Tutor</strong></h3>
            <ul>
                <li>Utilizar coleira elisabetana para impedir lambedura</li>
                <li>Administrar medicação conforme prescrição</li>
                <li>Retornar em 14 dias para reavaliação</li>
            </ul>
        `
    },

    "Medicina Veterinária": {
        description: "Estrutura voltada para anamnese veterinária.",
        structure: [
            "Identificação do Paciente",
            "Queixa Principal",
            "História da Doença Atual",
            "Histórico Veterinário Relevante",
            "Histórico Preventivo",
            "Exames Relevantes",
            "Avaliação Clínica",
            "Impressão Diagnóstica",
            "Conduta e Tratamento",
            "Orientações ao Tutor"
        ],
        example: `
            <h2><strong>Consulta veterinária - Gato com vômitos</strong></h2>
            <br/>
            <h3><strong>Identificação do Paciente</strong></h3>
            <p>Gato, Siamês, Fêmea, 3 anos, não castrada</p>
            <br/>
            <h3><strong>Queixa Principal</strong></h3>
            <p>Tutor relata vômitos frequentes (2-3x/dia) há 5 dias, associados a perda de apetite.</p>
            <br/>
            <h3><strong>História da Doença Atual</strong></h3>
            <p>Vômitos biliares, anorexia completa há 2 dias. Letargia. Perda de peso estimada 300g. Sem diarreia.</p>
            <br/>
            <h3><strong>Histórico Veterinário Relevante</strong></n            <p>Sem doenças prévias. Vacinação em dia. Acesso à rua supervisionado. Alimentação com ração premium.</p>
            <br/>
            <h3><strong>Histórico Preventivo</strong></n3>
            <p>Vacinação completa. Desparasitação interna há 4 meses. Controle de pulgas e carrapatos em dia.</p>
            <br/>
            <h3><strong>Avaliação Clínica</strong></n3>
            <p>Desidratação moderada (7%). Abdome tenso e doloroso à palpação. Temperatura 39,2°C. Mucosas levemente pálidas.</p>
            <br/>
            <h3><strong>Impressão Diagnóstica</strong></h3>
            <p>Suspeita de corpo estranho gastrointestinal ou pancreatite. Hemograma e bioquímica solicitados.</p>
            <br/>
            <h3><strong>Conduta e Tratamento</strong></h3>
            <p>Hidratação venosa iniciada. Jejum absoluto. Antiemético e analgesia. Ultrassonografia abdominal agendada.</p>
            <br/>
            <h3><strong>Orientações ao Tutor</strong></h3>
            <ul>
                <li>Manter jejum absoluto</li>
                <li>Observar alterações no comportamento</li>
                <li>Comunicar se houver novos vômitos</li>
            </ul>
        `
    }
}