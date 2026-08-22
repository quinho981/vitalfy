<?php

namespace Tests\Unit;

use App\Exceptions\InvalidMedicalAnalysisException;
use App\Services\DocumentService;
use PHPUnit\Framework\TestCase;

class MedicalAnalysisValidationTest extends TestCase
{
    private function validMedicalAnalysis(): array
    {
        return [
            'red_flags' => [],
            'case_severity' => ['verde'],
            'brief_description' => ['paciente estável'],
            'possible_diagnoses' => [],
            'suggested_cid_codes' => [],
            'suggested_exams' => [],
            'suggested_conducts' => [],
            'missing_clinical_information' => [],
        ];
    }

    public function test_resposta_valida_nao_lanca_excecao(): void
    {
        $service = new DocumentService();

        $service->assertValidMedicalAnalysis($this->validMedicalAnalysis());

        $this->addToAssertionCount(1);
    }

    public function test_case_severity_e_normalizado_antes_de_comparar_ao_enum(): void
    {
        $service = new DocumentService();

        $analysis = $this->validMedicalAnalysis();
        $analysis['case_severity'] = [' Vermelho '];

        $service->assertValidMedicalAnalysis($analysis);

        $this->addToAssertionCount(1);
    }

    public function test_medical_analysis_ausente_lanca_excecao(): void
    {
        $service = new DocumentService();

        $this->expectException(InvalidMedicalAnalysisException::class);

        $service->assertValidMedicalAnalysis(null);
    }

    public function test_chave_obrigatoria_ausente_lanca_excecao(): void
    {
        $service = new DocumentService();

        $analysis = $this->validMedicalAnalysis();
        unset($analysis['red_flags']);

        $this->expectException(InvalidMedicalAnalysisException::class);

        $service->assertValidMedicalAnalysis($analysis);
    }

    public function test_case_severity_fora_do_enum_lanca_excecao(): void
    {
        $service = new DocumentService();

        $analysis = $this->validMedicalAnalysis();
        $analysis['case_severity'] = ['inventado pela transcrição'];

        $this->expectException(InvalidMedicalAnalysisException::class);

        $service->assertValidMedicalAnalysis($analysis);
    }

    public function test_case_severity_vazio_lanca_excecao(): void
    {
        $service = new DocumentService();

        $analysis = $this->validMedicalAnalysis();
        $analysis['case_severity'] = [];

        $this->expectException(InvalidMedicalAnalysisException::class);

        $service->assertValidMedicalAnalysis($analysis);
    }
}
