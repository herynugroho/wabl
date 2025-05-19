<template>
  <b-card>
    <h4>Demo Tampilan Chat WhatsApp</h4>
    <div class="wa-chat-panel">
      <div
        v-for="(msg, idx) in chat"
        :key="idx"
        class="wa-chat-row"
        :class="msg.is_out ? 'wa-chat-out' : 'wa-chat-in'"
      >
        <div class="wa-bubble">
          <div v-if="msg.image">
            <b-img :src="msg.image" fluid class="mb-1" />
          </div>
          <div>{{ msg.text }}</div>
          <div class="wa-time">{{ msg.time }}</div>
        </div>
      </div>
    </div>
    <div class="chat-input d-flex mt-2">
      <b-form-textarea
        v-model="pesan"
        placeholder="Ketik pesan..."
        rows="1"
        class="mr-1"
      />
      <b-form-file
        v-model="gambar"
        accept=".jpg,.png"
        class="mr-1"
        style="max-width: 180px"
      />
      <b-button variant="primary" @click="kirimPesan">
        <feather-icon icon="SendIcon" /> Kirim
      </b-button>
    </div>
  </b-card>
</template>

<script>
import { BCard, BImg, BFormTextarea, BFormFile, BButton } from 'bootstrap-vue'
import FeatherIcon from '../@core/components/feather-icon/FeatherIcon.vue'

export default {
  components: {
    BCard,
    BImg,
    BFormTextarea,
    BFormFile,
    BButton,
    FeatherIcon
  },
  data() {
    return {
      chat: [
        { text: 'Halo, ada yang bisa dibantu?', time: '09:00', is_out: false },
        { text: 'Saya mau tanya pendaftaran', time: '09:01', is_out: true },
        { text: 'Silakan, kak.', time: '09:02', is_out: false },
        { text: 'Ini contoh gambar', time: '09:03', is_out: true, image: 'https://via.placeholder.com/120x80' }
      ],
      pesan: '',
      gambar: null
    }
  },
  methods: {
    kirimPesan() {
      if (!this.pesan && !this.gambar) return
      let imageUrl = null
      if (this.gambar) {
        // Untuk demo, tampilkan gambar lokal sebagai URL sementara
        imageUrl = URL.createObjectURL(this.gambar)
      }
      this.chat.push({
        text: this.pesan,
        time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
        is_out: true,
        image: imageUrl
      })
      this.pesan = ''
      this.gambar = null
    }
  }
}
</script>

<style>
.wa-chat-panel {
  background: #ece5dd;
  padding: 16px;
  min-height: 350px;
  max-height: 50vh;
  overflow-y: auto;
}
.wa-chat-row {
  display: flex;
  margin-bottom: 8px;
}
.wa-chat-in {
  justify-content: flex-start;
}
.wa-chat-out {
  justify-content: flex-end;
}
.wa-bubble {
  background: #fff;
  border-radius: 8px;
  padding: 8px 12px;
  max-width: 70%;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
  position: relative;
}
.wa-chat-out .wa-bubble {
  background: #dcf8c6;
}
.wa-time {
  font-size: 0.75rem;
  color: #888;
  text-align: right;
  margin-top: 2px;
}
.chat-input {
  gap: 8px;
}
</style>