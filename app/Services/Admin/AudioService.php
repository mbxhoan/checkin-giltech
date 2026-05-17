<?php

namespace App\Services\Admin;

use App\Models\Audio;
use App\Services\BaseService;
use Illuminate\Support\Facades\Http;

class AudioService extends BaseService
{
    public function __construct()
    {
        $this->model = resolve(Audio::class);
    }

    public function event()
    {
        return app(EventService::class);
    }

    public function generateSpeech(Audio $audio)
    {
        $apiKey = config('openai.api_key'); // store in config/openai.php
        $openaiEndpoint = 'https://api.openai.com/v1/audio/speech';

        $response = Http::withHeaders([
            'Authorization'     => "Bearer {$apiKey}",
            'Content-Type'      => 'application/json',
        ])->withBody(json_encode([
            'model'             => 'gpt-4o-mini-tts', // or 'tts-1' / 'tts-1-hd'
            'input'             => $audio->text,
            'voice'             => $audio->voice ?? 'alloy',
        ]), 'application/json')
        ->sink(storage_path("app/public/medias/sounds/audio_{$audio->id}.mp3"))
        ->post($openaiEndpoint);

        if ($response->successful()) {
            $audio->file_path = "medias/sounds/audio_{$audio->id}.mp3";
            $audio->save();

            return [
                'success'       => true,
                'message'       => 'Speech generated successfully.',
                'file'          => asset("storage/{$audio->file_path}")
            ];
        } else {
            return [
                'success'       => false,
                'error_code'    => 500,
                'message'       => 'Failed to generate speech',
                'error'         => $response->body(),
            ];
        }
    }
}
