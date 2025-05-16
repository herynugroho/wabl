<template>
  <div>
    <b-card>
      <h4>Rekap Petugas WABLAS SPMB 2025</h4>
      <div class="mb-2 d-flex align-items-center">
        <b-badge variant="info" class="mr-2">
          Data diambil: {{ hari }}, {{ tanggal }} {{ jam }}
        </b-badge>
        <b-button size="sm" variant="primary" @click="refreshRekap">
          Refresh
        </b-button>
      </div>
      <b-table
        :items="rekap"
        :fields="fields"
        striped
        hover
        small
        responsive
        class="mt-3"
      ></b-table>
    </b-card>
  </div>
</template>

<script>
import axios from 'axios'
import { BCard, BTable, BBadge, BButton } from 'bootstrap-vue'

export default {
  components: {
    BCard,
    BTable,
    BBadge,
    BButton
  },
  data() {
    return {
      userData: JSON.parse(localStorage.getItem('userData')),
      rekap: [],
      fields: [
        { key: 'user', label: 'Petugas' },
        { key: 'total_nomor', label: 'Total Nomor Masuk' },
        { key: 'total_nomor_dibalas', label: 'Sudah Dibalas' },
        { key: 'total_nomor_blm_dibalas', label: 'Belum Dibalas' }
      ],
      hari: '',
      tanggal: '',
      jam: ''
    }
  },
  mounted() {
    this.ambilTanggal();
    this.refreshRekap();
  },
  methods: {
    ambilTanggal() {
      const hariList = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu']
      const now = new Date()
      this.hari = hariList[now.getDay()]
      this.tanggal = now.toLocaleDateString('id-ID', {
        day: '2-digit', month: 'long', year: 'numeric'
      })
      this.jam = now.toLocaleTimeString('id-ID', {
        hour: '2-digit', minute: '2-digit', second: '2-digit'
      })
    },
    refreshRekap() {
      this.ambilTanggal();
      axios.get('/api/rekap-wa-2025')
        .then(res => {
          this.rekap = res.data.rekap
        })
        .catch(() => {
          this.rekap = []
        })
    }
  }
}
</script>