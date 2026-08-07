const AUDIO_CONFIG = {
    MAX_RECORDING_DURATION: 30 * 60, // 30 min
    MAX_FILE_SIZE: 100 * 1024 * 1024, // 100MB
    ALLOWED_EXTENSIONS: ['mp3', 'wav', 'm4a', 'aac', 'ogg', 'flac', 'mpeg', 'webm']
}

const FREE_TRANSCRIPT_LIMIT = 10

export { AUDIO_CONFIG, FREE_TRANSCRIPT_LIMIT }
