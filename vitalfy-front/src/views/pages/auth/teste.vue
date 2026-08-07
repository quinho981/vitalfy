<template>
  <div class="container">
    <div class="header">
      <h1>Transcrição em Tempo Real</h1>
      <div class="status-indicator" :class="{ active: isRecording, connected: isConnected }">
        {{ getStatusText() }}
      </div>
    </div>

    <div class="controls">
      <button 
        @click="toggleRecording" 
        :disabled="isConnecting"
        :class="{ recording: isRecording, connecting: isConnecting }"
        class="record-button"
      >
        <span v-if="isConnecting">Conectando...</span>
        <span v-else-if="isRecording">🛑 Parar Gravação</span>
        <span v-else>🎤 Iniciar Gravação</span>
      </button>
    </div>

    <div class="transcription-container">
      <h2>Transcrições</h2>
      
      <!-- Transcrição em tempo real (provisória) -->
      <div v-if="currentTranscript" class="current-transcript">
        <div class="transcript-item interim">
          <span class="timestamp">{{ formatTime(Date.now()) }}</span>
          <span class="text">{{ currentTranscript }}</span>
          <span class="status">Em andamento...</span>
        </div>
      </div>

      <!-- Lista de transcrições finalizadas -->
      <div class="transcripts-list">
        <div 
          v-for="(transcript, index) in transcripts" 
          :key="index"
          class="transcript-item final"
        >
          <span class="timestamp">{{ formatTime(transcript.timestamp) }}</span>
          <span class="text">{{ transcript.text }}</span>
          <span class="confidence">{{ (transcript.confidence * 100).toFixed(1) }}%</span>
        </div>
      </div>

      <div v-if="transcripts.length === 0 && !currentTranscript" class="empty-state">
        Clique em "Iniciar Gravação" para começar a transcrever
      </div>
    </div>

    <div class="connection-info">
      <div class="info-item">
        <strong>Status da Conexão:</strong> 
        <span :class="connectionStatusClass">{{ connectionStatus }}</span>
      </div>
      <div class="info-item">
        <strong>Keep Alive:</strong> 
        <span :class="{ active: keepAliveActive }">
          {{ keepAliveActive ? 'Ativo' : 'Inativo' }}
        </span>
      </div>
      <div class="info-item">
        <strong>Áudio Detectado:</strong> 
        <span :class="{ active: audioDetected }">
          {{ audioDetected ? 'Sim' : 'Não' }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onBeforeUnmount } from 'vue'

// Estados da conexão
const socket = ref(null)
const mediaRecorder = ref(null)
const audioStream = ref(null)
const isRecording = ref(false)
const isConnected = ref(false)
const isConnecting = ref(false)

// Transcrições
const transcripts = ref([])
const currentTranscript = ref('')

// Status e controle
const connectionStatus = ref('Desconectado')
const keepAliveActive = ref(false)
const audioDetected = ref(false)

// Configurações do Deepgram
const deepgramConfig = {
  // Substitua pela sua API key do Deepgram
  apiKey: '7bfd2857b37455faf82a84bf1f0e7406afdb1372',
  language: 'pt-BR',
  model: 'nova-2',
  smart_format: true,
  interim_results: false,
  endpointing: 800, // ms de silêncio antes de finalizar
  vad_events: true, // Voice Activity Detection
  keepalive: true,
  diarize: true
}

// Controle de keep alive
const keepAliveInterval = ref(null)
const silenceTimeout = ref(null)
const lastAudioTime = ref(null)

// Computed
const connectionStatusClass = computed(() => ({
  'status-connected': isConnected.value,
  'status-connecting': isConnecting.value,
  'status-disconnected': !isConnected.value && !isConnecting.value
}))

// Methods
const toggleRecording = async () => {
  if (isRecording.value) {
    await stopRecording()
  } else {
    await startRecording()
  }
}

const startRecording = async () => {
  try {
    isConnecting.value = true
    connectionStatus.value = 'Conectando...'
    
    // Solicitar permissão de microfone
    audioStream.value = await navigator.mediaDevices.getUserMedia({ 
      audio: {
        sampleRate: 16000,
        channelCount: 1,
        echoCancellation: true,
        noiseSuppression: true
      } 
    })
    
    // Conectar ao Deepgram WebSocket
    await connectToDeepgram()
    
    // Configurar MediaRecorder
    setupMediaRecorder()
    
    // Iniciar gravação
    mediaRecorder.value.start(4000) // Enviar dados a cada 100ms
    
    isRecording.value = true
    isConnecting.value = false
    connectionStatus.value = 'Conectado'
    lastAudioTime.value = Date.now()
    
    // Iniciar keep alive
    startKeepAlive()
    
  } catch (error) {
    console.error('Erro ao iniciar gravação:', error)
    isConnecting.value = false
    connectionStatus.value = 'Erro na conexão'
    alert('Erro ao acessar o microfone ou conectar ao Deepgram')
  }
}

const stopRecording = async () => {
  isRecording.value = false
  connectionStatus.value = 'Desconectando...'
  
  // Parar MediaRecorder
  if (mediaRecorder.value && mediaRecorder.value.state !== 'inactive') {
    mediaRecorder.value.stop()
  }
  
  // Fechar stream de áudio
  if (audioStream.value) {
    audioStream.value.getTracks().forEach(track => track.stop())
    audioStream.value = null
  }
  
  // Fechar WebSocket
  if (socket.value) {
    socket.value.close()
    socket.value = null
  }
  
  // Limpar intervals
  stopKeepAlive()
  
  isConnected.value = false
  connectionStatus.value = 'Desconectado'
  audioDetected.value = false
  
  // Finalizar transcrição atual se houver
  if (currentTranscript.value) {
    finalizeCurrentTranscript()
  }
}

const connectToDeepgram = async () => {
  return new Promise((resolve, reject) => {
    const params = new URLSearchParams({
      language: deepgramConfig.language,
      model: deepgramConfig.model,
      smart_format: deepgramConfig.smart_format,
      interim_results: deepgramConfig.interim_results,
      endpointing: deepgramConfig.endpointing,
      vad_events: deepgramConfig.vad_events,
      keepalive: deepgramConfig.keepalive,
      didarize: deepgramConfig.diarize
    })
    
    const wsUrl = `wss://api.deepgram.com/v1/listen?${params.toString()}`
    
    socket.value = new WebSocket(wsUrl, ['token', deepgramConfig.apiKey])
    
    socket.value.onopen = () => {
      console.log('Conectado ao Deepgram')
      isConnected.value = true
      resolve()
    }
    
    socket.value.onmessage = (event) => {
      handleDeepgramMessage(JSON.parse(event.data))
    }
    
    socket.value.onerror = (error) => {
      console.error('Erro WebSocket:', error)
      reject(error)
    }
    
    socket.value.onclose = () => {
      console.log('Conexão WebSocket fechada')
      isConnected.value = false
    }
  })
}

const setupMediaRecorder = () => {
  mediaRecorder.value = new MediaRecorder(audioStream.value, {
    mimeType: 'audio/webm;codecs=opus'
  })
  
  mediaRecorder.value.ondataavailable = (event) => {
    // Só enviar se tiver dados substanciais (> 100 bytes para evitar pacotes vazios)
    if (event.data.size > 100 && socket.value && socket.value.readyState === WebSocket.OPEN) {
      socket.value.send(event.data)
      audioDetected.value = true
      lastAudioTime.value = Date.now()
      
      // Reset silence timeout
      resetSilenceTimeout()
    }
  }
}

const handleDeepgramMessage = (data) => {
  if (data.type === 'Results' && data.channel?.alternatives?.length > 0) {
    const alternative = data.channel.alternatives[0]
    const transcript = alternative.transcript
    
    // Só processar se tiver texto substancial (não vazio ou só espaços)
    if (transcript && transcript.trim().length > 0) {
      if (data.is_final) {
        // Transcrição final
        transcripts.value.push({
          text: transcript.trim(),
          confidence: alternative.confidence || 0,
          timestamp: Date.now()
        })
        currentTranscript.value = ''
      } else {
        // Transcrição provisória
        currentTranscript.value = transcript.trim()
      }
    }
  } else if (data.type === 'SpeechStarted') {
    console.log('Fala detectada')
    audioDetected.value = true
  } else if (data.type === 'UtteranceEnd') {
    console.log('Fim da fala detectado')
    finalizeCurrentTranscript()
  } else if (data.type === 'Metadata') {
    console.log('Metadata recebida:', data)
  }
}

const startKeepAlive = () => {
  keepAliveActive.value = true
  
  // Enviar keep alive a cada 8 segundos
  keepAliveInterval.value = setInterval(() => {
    if (socket.value && socket.value.readyState === WebSocket.OPEN) {
      socket.value.send(JSON.stringify({ type: 'KeepAlive' }))
      console.log('Keep alive enviado')
    }
  }, 8000)
  
  // Configurar timeout para detectar silêncio prolongado
  resetSilenceTimeout()
}

const stopKeepAlive = () => {
  keepAliveActive.value = false
  
  if (keepAliveInterval.value) {
    clearInterval(keepAliveInterval.value)
    keepAliveInterval.value = null
  }
  
  if (silenceTimeout.value) {
    clearTimeout(silenceTimeout.value)
    silenceTimeout.value = null
  }
}

const resetSilenceTimeout = () => {
  if (silenceTimeout.value) {
    clearTimeout(silenceTimeout.value)
  }
  
  // Após 5 segundos de silêncio, marcar como sem áudio
  silenceTimeout.value = setTimeout(() => {
    audioDetected.value = false
  }, 5000)
}

const finalizeCurrentTranscript = () => {
  // Só finalizar se tiver conteúdo substancial
  if (currentTranscript.value && currentTranscript.value.trim().length > 0) {
    transcripts.value.push({
      text: currentTranscript.value.trim(),
      confidence: 0.95, // Confidence padrão para transcrições manuais
      timestamp: Date.now()
    })
    currentTranscript.value = ''
  }
}

const formatTime = (timestamp) => {
  return new Date(timestamp).toLocaleTimeString('pt-BR')
}

const getStatusText = () => {
  if (isConnecting.value) return 'Conectando...'
  if (isRecording.value && isConnected.value) return 'Gravando'
  if (isConnected.value) return 'Conectado'
  return 'Desconectado'
}

// Lifecycle
onBeforeUnmount(() => {
  // Limpar recursos ao destruir componente
  stopRecording()
})
</script>

<style scoped>
.container {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.header {
  text-align: center;
  margin-bottom: 30px;
}

.header h1 {
  color: #2c3e50;
  margin-bottom: 10px;
}

.status-indicator {
  display: inline-block;
  padding: 8px 16px;
  border-radius: 20px;
  font-weight: 500;
  font-size: 14px;
  background-color: #e74c3c;
  color: white;
  transition: all 0.3s ease;
}

.status-indicator.connected {
  background-color: #f39c12;
}

.status-indicator.active {
  background-color: #27ae60;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.7; }
  100% { opacity: 1; }
}

.controls {
  text-align: center;
  margin-bottom: 30px;
}

.record-button {
  background-color: #3498db;
  color: white;
  border: none;
  padding: 15px 30px;
  border-radius: 50px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 200px;
}

.record-button:hover {
  background-color: #2980b9;
  transform: translateY(-2px);
}

.record-button.recording {
  background-color: #e74c3c;
}

.record-button.recording:hover {
  background-color: #c0392b;
}

.record-button.connecting {
  background-color: #f39c12;
  cursor: not-allowed;
}

.record-button:disabled {
  cursor: not-allowed;
  opacity: 0.7;
}

.transcription-container {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  margin-bottom: 20px;
}

.transcription-container h2 {
  color: #2c3e50;
  margin-bottom: 20px;
  border-bottom: 2px solid #ecf0f1;
  padding-bottom: 10px;
}

.current-transcript {
  margin-bottom: 20px;
}

.transcript-item {
  display: flex;
  align-items: flex-start;
  gap: 15px;
  padding: 15px;
  margin-bottom: 10px;
  border-radius: 8px;
  border-left: 4px solid #3498db;
  background: #f8f9fa;
}

.transcript-item.interim {
  background: #fff3cd;
  border-left-color: #f39c12;
  animation: fadeIn 0.3s ease;
}

.transcript-item.final {
  background: #d4edda;
  border-left-color: #27ae60;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

.timestamp {
  font-size: 12px;
  color: #7f8c8d;
  min-width: 80px;
  font-weight: 500;
}

.text {
  flex: 1;
  color: #2c3e50;
  line-height: 1.5;
}

.confidence {
  font-size: 12px;
  color: #27ae60;
  font-weight: 600;
  min-width: 50px;
  text-align: right;
}

.status {
  font-size: 12px;
  color: #f39c12;
  font-style: italic;
  min-width: 100px;
  text-align: right;
}

.empty-state {
  text-align: center;
  color: #7f8c8d;
  font-style: italic;
  padding: 40px 20px;
}

.connection-info {
  background: white;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.info-item strong {
  color: #2c3e50;
  font-size: 14px;
}

.info-item span {
  font-size: 16px;
  font-weight: 600;
}

.status-connected {
  color: #27ae60;
}

.status-connecting {
  color: #f39c12;
}

.status-disconnected {
  color: #e74c3c;
}

.active {
  color: #27ae60;
}

/* Responsividade */
@media (max-width: 768px) {
  .container {
    padding: 15px;
  }
  
  .record-button {
    padding: 12px 24px;
    font-size: 14px;
    min-width: 180px;
  }
  
  .transcript-item {
    flex-direction: column;
    gap: 8px;
  }
  
  .timestamp, .confidence, .status {
    min-width: auto;
    text-align: left;
  }
}
</style>