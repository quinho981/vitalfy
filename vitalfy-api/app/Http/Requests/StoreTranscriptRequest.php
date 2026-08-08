<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTranscriptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'audio' => [
                'required',
                'file',
                // BE-R1-03 (ai-vitalfy/action-plans/backend/R1.md): já
                // alinhado com o client_max_body_size 100M dos três nginx —
                // rejeita antes de chamar Deepgram/Groq. A duração (30 min)
                // só é validada depois da transcrição
                // (TranscriptService::validateAudioDuration), porque nenhuma
                // lib de metadado de áudio está instalada e o tamanho já é um
                // proxy razoável (100MB / 1800s ≈ 447kbps médio) — decisão
                // registrada aqui em vez de adicionar dependência nova só
                // para esta checagem.
                'max:102400',
                'mimetypes:audio/mpeg,audio/mp3,audio/wav,audio/x-wav,audio/mp4,audio/x-m4a,audio/aac,audio/ogg,audio/flac,audio/webm,video/webm,video/mp4'
            ],
            'patient' => 'required|string|max:255',
            'type' => 'required|integer|exists:transcript_types,id',
            'template' => 'required|integer|exists:document_templates,id',
        ];
    }

    /**
     * Mensagens específicas por regra (BE-R1-03): o front distingue
     * "arquivo grande demais" de erro genérico pelo campo `errors.audio`
     * na resposta 422 — ver FE-R1-01 em ai-vitalfy/action-plans/frontend/R1.md.
     */
    public function messages(): array
    {
        return [
            'audio.required' => 'Selecione um arquivo de áudio.',
            'audio.max' => 'O arquivo de áudio deve ter no máximo 100MB.',
            'audio.mimetypes' => 'Formato de áudio não suportado.',
        ];
    }
}
