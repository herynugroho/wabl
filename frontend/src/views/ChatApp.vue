<template>
  <b-card>
    <div class="wa-app d-flex flex-column flex-md-row">
      <!-- Sidebar kiri: daftar chat -->
      <div
        class="wa-sidebar"
        v-show="showList || !isMobile"
      >
        <!-- Search & Filter -->
        <div class="wa-sidebar-tools p-2 pb-1">
          <b-input-group size="sm" class="mb-2">
            <b-form-input
              v-model="searchText"
              placeholder="Cari nama/nomor/pesan..."
              autocomplete="off"
            />
            <b-input-group-append>
              <b-button @click="searchText = ''" variant="light" v-if="searchText">
                <feather-icon icon="XIcon" size="16"/>
              </b-button>
            </b-input-group-append>
          </b-input-group>
          <b-button-group size="sm" class="mb-1 w-100">
            <b-button :variant="filterType==='terbaru'?'primary':'outline-primary'" @click="filterType='terbaru'">Terbaru</b-button>
            <b-button :variant="filterType==='terlama'?'primary':'outline-primary'" @click="filterType='terlama'">Terlama</b-button>
            <b-button :variant="filterType==='belum'?'primary':'outline-primary'" @click="filterType='belum'">Belum Terbalas</b-button>
          </b-button-group>
        </div>
        <b-list-group>
          <b-list-group-item
            v-for="item in filteredListwaRows"
            :key="item.phone"
            :active="item.phone === phone"
            @click="handleSelectChat(item)"
            class="d-flex justify-content-between align-items-center wa-sidebar-item"
          >
            <div class="flex-grow-1">
              <div class="font-weight-bold">{{ item.name || item.phone }}</div>
              <small class="text-muted text-truncate d-block" style="max-width:180px;">{{ item.message }}</small>
            </div>
            <div class="text-right ml-2 d-flex flex-column align-items-end">
              <span class="wa-sidebar-time">
                {{ getLastTime(item) }}
              </span>
              <b-badge v-if="item.unread > 0" variant="primary" class="mt-1">{{ item.unread }}</b-badge>
            </div>
          </b-list-group-item>
        </b-list-group>
      </div>
      <!-- Panel kanan: isi chat -->
      <div class="wa-main flex-grow-1">
        <!-- Header chat aktif di mobile -->
        <div v-if="isMobile && !showList && phone" class="wa-chat-header-mobile d-flex align-items-center">
          <b-button variant="link" class="p-0 mr-2 wa-back-btn" @click="showList = true">
            <feather-icon icon="ChevronLeftIcon" size="22" />
          </b-button>
          <div class="wa-chat-header-info">
            <span class="wa-chat-header-phone">{{ getActiveNameOrPhone }}</span>
          </div>
        </div>
        <div v-if="phone && (!isMobile || !showList)">
          <div class="wa-chat-panel" ref="chatPanel">
            <div
              v-for="(msg, idx) in chatBubbles"
              :key="idx"
              class="wa-chat-row"
              :class="msg.is_out ? 'wa-chat-out' : 'wa-chat-in'"
            >
              <div class="wa-bubble">
                <div v-if="msg.replyTo" class="wa-reply">
                  {{ msg.replyTo }}
                </div>
                <div v-if="msg.image" class="wa-img-thumb">
                  <b-img
                    :src="msg.image"
                    fluid
                    class="mb-1"
                    @click="showImage(msg.image)"
                    style="cursor:pointer;max-width:180px;max-height:180px;object-fit:cover;"
                  />
                </div>
                <div>{{ msg.text }}</div>
                <div class="wa-time">{{ formatTime(msg.time) }}</div>
                <!-- Tombol copy hanya untuk pesan masuk -->
                <div v-if="!msg.is_out" class="wa-copy-btn-row">
                  <b-button
                    size="xs"
                    variant="link"
                    @click="copyText(msg.text)"
                    class="wa-copy-btn-bubble"
                  >
                    <feather-icon icon="CopyIcon" size="13" class="mr-1" />
                    <span>Copy Pesan</span>
                  </b-button>
                </div>
              </div>
            </div>
          </div>
          <!-- Input sticky di bawah pada mobile -->
          <div :class="['chat-input', 'd-flex', 'mt-2', isMobile && !showList ? 'chat-input-mobile-sticky' : '']">
            <b-form-textarea
              v-model="pesan"
              placeholder="Ketik pesan..."
              rows="1"
              class="mr-1"
              @keydown.enter.exact.prevent="kirim_pesan"
              @keydown.enter.shift="null"
            />
            <b-form-file
              v-model="gambarnya"
              accept=".jpg,.png"
              class="mr-1"
              style="max-width: 180px"
            />
            <b-button variant="primary" @click="kirim_pesan">
              <feather-icon icon="SendIcon" /> Kirim
            </b-button>
          </div>
        </div>
        <!-- Hanya tampilkan pesan ini di desktop -->
        <div v-else-if="(!isMobile && (showList || !phone))" class="text-center text-muted py-5">
          Pilih chat di sebelah kiri untuk mulai percakapan.
        </div>
        <!-- Modal gambar -->
        <b-modal
          v-model="showImgModal"
          size="lg"
          hide-footer
          title="Pratinjau Gambar"
        >
          <div class="text-center">
            <img :src="modalImgUrl" :alt="modalImgUrl" style="max-width:100%;max-height:70vh;" />
          </div>
          <div class="text-center mt-2">
            <b-button variant="primary" @click="openInNewTab(modalImgUrl)">
              Buka di Tab Baru
            </b-button>
          </div>
        </b-modal>
      </div>
    </div>
  </b-card>
</template>

<script>
import { BCard, BListGroup, BListGroupItem, BBadge, BImg, BFormTextarea, BFormFile, BButton, BModal, BInputGroup, BInputGroupAppend, BFormInput, BButtonGroup } from 'bootstrap-vue'
import FeatherIcon from '../@core/components/feather-icon/FeatherIcon.vue'
import axios from 'axios'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BCard,
    BListGroup,
    BListGroupItem,
    BBadge,
    BImg,
    BFormTextarea,
    BFormFile,
    BButton,
    BModal,
    FeatherIcon,
    BInputGroup,
    BInputGroupAppend,
    BFormInput,
    BButtonGroup,
  },
  data() {
    return {
      listwa_rows: [],
      phone: '',
      id_wa: '',
      reply: '',
      pesan: '',
      gambarnya: null,
      urlGambar: null,
      message_rows: [],
      userData: JSON.parse(localStorage.getItem('userData')) || {},
      showImgModal: false,
      modalImgUrl: '',
      refreshInterval: null,
      showList: true,
      isMobile: false,
      isSending: false,
      searchText: '',
      filterType: 'terbaru',
    }
  },
  computed: {
    chatBubbles() {
      const SPLIT = '|||--WABLASSPLIT--|||'
      return this.message_rows.flatMap(row => {
        const arr = []
        // Pesan masuk (user)
        let replyText = null
        let mainText = row.message
        // Deteksi format: "pesan yang di-reply <~ pesan baru"
        if (mainText && mainText.includes('<~')) {
          const parts = mainText.split('<~')
          replyText = parts[0].trim()
          mainText = parts[1].trim()
        }
        arr.push({
          text: mainText,
          time: row.waktu,
          is_out: false,
          image: row.url,
          replyTo: replyText,
        })
        // Jika ada balasan (petugas), split jika ada pemisah
        if (row.reply) {
          const replies = row.reply.split(SPLIT)
          replies.forEach((replyText, idx) => {
            arr.push({
              text: replyText.trim(),
              time: row.reply_time,
              is_out: true,
              image: row.urlfile,
            })
          })
        }
        return arr
      })
    },
    getActiveNameOrPhone() {
      const found = this.listwa_rows.find(item => item.phone === this.phone)
      return found ? (found.name || found.phone) : this.phone
    },
    filteredListwaRows() {
      let rows = this.listwa_rows

      // Filter search
      if (this.searchText) {
        const s = this.searchText.toLowerCase()
        rows = rows.filter(item =>
          (item.name && item.name.toLowerCase().includes(s)) ||
          (item.phone && item.phone.toLowerCase().includes(s)) ||
          (item.message && item.message.toLowerCase().includes(s))
        )
      }

      // Filter status
      if (this.filterType === 'belum') {
        rows = rows.filter(item => !item.reply || item.reply === '' || item.reply === null)
      }

      // Sort
      if (this.filterType === 'terbaru') {
        rows = rows.slice().sort((a, b) => new Date(b.waktu) - new Date(a.waktu))
      } else if (this.filterType === 'terlama') {
        rows = rows.slice().sort((a, b) => new Date(a.waktu) - new Date(b.waktu))
      }

      return rows
    },
  },
  mounted() {
    this.listwa()
    this.setRefreshInterval()
    this.checkMobile()
    window.addEventListener('resize', this.checkMobile)
  },
  beforeDestroy() {
    if (this.refreshInterval) clearInterval(this.refreshInterval)
    window.removeEventListener('resize', this.checkMobile)
  },
  watch: {
    message_rows() {
      this.$nextTick(() => {
        this.scrollToBottom()
      })
    }
  },
  methods: {
    checkMobile() {
      this.isMobile = window.innerWidth <= 900
      if (!this.isMobile) this.showList = true
      // Saat mobile dan sudah pilih chat, sembunyikan list
      if (this.isMobile && this.phone) this.showList = false
    },
    listwa() {
      axios.post('/api/listwa', { user: this.userData.user_id })
        .then(res => {
          this.listwa_rows = res.data.list_wa || []
        })
    },
    handleSelectChat(item) {
      this.selectChat(item)
      if (this.isMobile) this.showList = false
    },
    selectChat(item) {
      this.phone = item.phone
      this.id_wa = item.id_wa || ''
      this.reply = item.reply || ''
      this.listchat(this.phone)
      this.listwa() // refresh sidebar juga setiap klik
    },
    listchat(phone) {
      axios.post('/api/getchat', { phone: phone })
        .then(res => {
          this.message_rows = res.data.chat || []
        })
    },
    formatTime(time) {
      if (!time) return ''
      const d = new Date(time)
      if (isNaN(d)) return time
      return d.toLocaleString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
        hour: '2-digit', minute: '2-digit', second: '2-digit'
      })
    },
    formatShortTime(time) {
      if (!time) return ''
      const d = new Date(time)
      if (isNaN(d)) return ''
      return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
    },
    getLastTime(item) {
      // Ambil waktu terakhir antara pesan masuk (waktu) dan reply (reply_time)
      let last = item.waktu
      if (item.reply_time) {
        const t1 = new Date(item.waktu)
        const t2 = new Date(item.reply_time)
        last = t2 > t1 ? item.reply_time : item.waktu
      }
      return this.formatShortTime(last)
    },
    scrollToBottom() {
      const panel = this.$refs.chatPanel
      if (panel) {
        panel.scrollTop = panel.scrollHeight
      }
    },
    showImage(url) {
      this.modalImgUrl = url
      this.showImgModal = true
    },
    openInNewTab(url) {
      window.open(url, '_blank')
    },
    copyText(text) {
      if (!text) return
      // Fallback: gunakan $copyText jika ada, jika tidak pakai clipboard API
      if (this.$copyText) {
        this.$copyText(text)
      } else if (navigator.clipboard) {
        navigator.clipboard.writeText(text)
      } else {
        // Fallback lama
        const textarea = document.createElement('textarea')
        textarea.value = text
        document.body.appendChild(textarea)
        textarea.select()
        document.execCommand('copy')
        document.body.removeChild(textarea)
      }
      this.$toast && this.$toast({
        component: ToastificationContent,
        props: {
          title: 'Pesan berhasil disalin!',
          icon: 'CopyIcon',
          variant: 'success',
        },
      })
    },
    setRefreshInterval() {
      // Refresh otomatis setiap 5 menit
      this.refreshInterval = setInterval(() => {
        this.refreshAll()
      }, 5 * 60 * 1000)
    },
    refreshAll() {
      this.listwa()
      if (this.phone) this.listchat(this.phone)
    },
    async kirim_pesan() {
      if (this.isSending) return
      this.isSending = true

      const SPLIT = '|||--WABLASSPLIT--|||'

      try {
        // Jika ada gambar, kirim ke backend untuk diteruskan ke Wablas
        if (this.gambarnya) {
          const bodyFormData = new FormData();
          bodyFormData.append('gambar', this.gambarnya);
          bodyFormData.append('phone', this.phone);
          bodyFormData.append('caption', this.pesan || '');

          // Kirim ke backend (uploadimg), backend akan langsung kirim ke Wablas
          const response = await axios.post('/api/uploadimg', bodyFormData);

          // Optional: tampilkan notifikasi dari backend
          this.$toast && this.$toast({
            component: ToastificationContent,
            props: {
              title: response.data.message || 'Gambar dikirim!',
              icon: 'AlertIcon',
              variant: response.data.status ? 'info' : 'danger',
            },
          });

          this.gambarnya = null;
          this.urlGambar = null;
        }

        // Kirim pesan teks (jika ada dan tidak sedang kirim gambar)
        if (this.pesan && !this.gambarnya) {
          const instanceAxios = axios.create({
            headers: { 'Authorization': '699RAeqDRuo6blRVlAPVaPnpyoXWsxytyRPlhSa5tvoQJyRA1aQpbQE.F7lImmyU' }
          })
          const responseMsg = await instanceAxios.post('https://jogja.wablas.com/api/send-message', { phone: this.phone, message: this.pesan })
          if (responseMsg.data.status === true) {
            let reply_new = this.reply
            if (reply_new && reply_new.trim() !== '') {
              reply_new += SPLIT + this.pesan
            } else {
              reply_new = this.pesan
            }
            await axios.post('/api/updatewa', {
              phone: this.phone,
              nama: this.userData.username,
              pesan: this.pesan,
              id: this.id_wa,
              reply: reply_new,
              urlfile: null
            })
            this.$toast && this.$toast({
              component: ToastificationContent,
              props: {
                title: responseMsg.data.message,
                icon: 'AlertIcon',
                variant: 'info',
              },
            });
            this.reply = reply_new
            this.pesan = '';
            this.urlGambar = null;
            this.gambarnya = null;
            await this.refreshAll()
          }
        }
      } catch (e) {
        // Optional: tampilkan error
      }
      this.isSending = false
    }
  }
}
</script>

<style>
.wa-app {
  min-height: 60vh;
  width: 100%;
}
.wa-sidebar {
  width: 300px;
  min-width: 220px;
  max-width: 340px;
  border-right: 1px solid #e3eaf7;
  background: #fafdff;
  min-height: 60vh;
  max-height: 70vh;
  overflow-y: auto;
  flex-shrink: 0;
}
.wa-sidebar-tools {
  background: #fafdff;
  border-bottom: 1px solid #e3eaf7;
  position: sticky;
  top: 0;
  z-index: 10;
}
.wa-sidebar-item {
  border: none !important;
  border-bottom: 1px solid #e3eaf7 !important;
  background: transparent !important;
  transition: background 0.2s;
}
.wa-sidebar-item.active,
.wa-sidebar-item:hover {
  background: #2e6da4 !important;
  color: #fff !important;
}
.wa-sidebar-item.active .font-weight-bold,
.wa-sidebar-item.active small,
.wa-sidebar-item.active .wa-sidebar-time,
.wa-sidebar-item.active .b-badge,
.wa-sidebar-item:hover .font-weight-bold,
.wa-sidebar-item:hover small,
.wa-sidebar-item:hover .wa-sidebar-time,
.wa-sidebar-item:hover .b-badge {
  color: #fff !important;
}
.wa-sidebar-time {
  font-size: 0.85em;
  color: #7a8ca4;
  margin-top: 2px;
  min-width: 48px;
  text-align: right;
  letter-spacing: 0.5px;
}
.wa-main {
  padding: 0 16px;
  min-width: 0;
  width: 100%;
}
.wa-chat-header-mobile {
  background: #fafdff;
  border-bottom: 1px solid #e3eaf7;
  padding: 8px 8px 8px 0;
  min-height: 44px;
  margin-bottom: 4px;
}
.wa-back-btn {
  min-width: 36px;
  min-height: 36px;
  color: #2e6da4 !important;
}
.wa-chat-header-phone {
  font-weight: bold;
  font-size: 1.08em;
  color: #2e6da4;
}
.wa-chat-panel {
  background: #eaf1fb;
  padding: 16px;
  min-height: 350px;
  max-height: 50vh;
  overflow-y: auto;
  width: 100%;
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
  border-radius: 10px;
  padding: 10px 14px;
  max-width: 70%;
  box-shadow: 0 1px 2px rgba(0,0,0,0.04);
  position: relative;
  border: 1px solid #e3eaf7;
  word-break: break-word;
}
.wa-chat-out .wa-bubble {
  background: #d2e6fa;
  border: 1px solid #8bbbe8;
}
.wa-copy-btn-row {
  display: flex;
  justify-content: flex-start;
  margin-top: 4px;
}
.wa-copy-btn-bubble {
  font-size: 0.75em !important;
  padding: 1px 8px !important;
  border-radius: 5px !important;
  box-shadow: none !important;
  color: #2e6da4 !important;
  background: none !important;
  border: none !important;
  text-align: left;
}
.wa-copy-btn-bubble:hover {
  text-decoration: underline;
  background: #eaf1fb !important;
}
.wa-time {
  font-size: 0.75rem;
  color: #7a8ca4;
  text-align: right;
  margin-top: 2px;
}
.chat-input {
  gap: 8px;
  flex-wrap: wrap;
}
.wa-reply {
  border-left: 3px solid #8bbbe8;
  background: #f0f6fb;
  color: #3a4a5d;
  font-size: 0.85em;
  padding: 4px 8px;
  margin-bottom: 4px;
  margin-left: -8px;
  border-radius: 4px;
}
.wa-img-thumb img,
.wa-img-thumb .b-img {
  max-width: 180px !important;
  max-height: 180px !important;
  object-fit: cover;
  cursor: pointer;
  border-radius: 6px;
  border: 1px solid #e0e0e0;
  transition: box-shadow 0.2s;
}
.wa-img-thumb img:hover,
.wa-img-thumb .b-img:hover {
  box-shadow: 0 2px 8px rgba(0,0,0,0.18);
}

/* Responsive */
@media (max-width: 900px) {
  .wa-app {
    flex-direction: column !important;
  }
  .wa-sidebar {
    width: 100%;
    min-width: 0;
    max-width: 100%;
    border-right: none;
    border-bottom: 1px solid #e3eaf7;
    max-height: calc(100vh - 60px);
    min-height: 80px;
    box-shadow: 0 2px 8px rgba(46,109,164,0.07);
    margin-bottom: 8px;
    z-index: 2;
    background: #fafdff;
    overflow-y: auto;
  }
  .wa-main {
    padding: 0 4px;
    padding-bottom: 70px;
  }
  .wa-chat-header-mobile {
    display: flex;
  }
  .wa-chat-panel {
    min-height: 200px;
    max-height: 70vh;
    padding: 8px;
  }
  .wa-bubble {
    max-width: 90vw;
    font-size: 0.97em;
    padding: 8px 8px;
  }
  .wa-img-thumb img,
  .wa-img-thumb .b-img {
    max-width: 120px !important;
    max-height: 120px !important;
  }
  .chat-input {
    flex-direction: column;
    gap: 4px;
  }
  .chat-input-mobile-sticky {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 10;
    background: #fafdff;
    padding: 8px 8px 8px 8px !important;
    box-shadow: 0 -2px 8px rgba(46,109,164,0.07);
    border-top: 1px solid #e3eaf7;
    margin: 0 !important;
    width: 100vw;
    max-width: 100vw;
  }
}
@media (max-width: 600px) {
  .wa-sidebar {
    max-height: calc(100vh - 48px);
    min-height: 60px;
    font-size: 0.95em;
  }
  .wa-chat-header-mobile {
    min-height: 38px;
    padding: 6px 6px 6px 0;
  }
  .wa-chat-panel {
    min-height: 120px;
    max-height: 55vh;
    padding: 4px;
  }
  .wa-bubble {
    max-width: 98vw;
    font-size: 0.95em;
    padding: 6px 6px;
  }
  .wa-img-thumb img,
  .wa-img-thumb .b-img {
    max-width: 80px !important;
    max-height: 80px !important;
  }
}
</style>