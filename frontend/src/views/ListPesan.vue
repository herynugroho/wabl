<template>
    <b-card>
        <b-card>
            <vue-good-table
                :columns="pesan_columns"
                :rows="pesan_rows"
                :line-numbers="true"
                
                :pagination-options="{
                    enabled: true,
                    perPage:pageLength
                    }"
                :search-options="{ 
                    enabled: true,
                }">
                        <!-- theme="black-rhino" -->
                        <!-- externalQuery: searchTerm  -->

                    <template slot="table-row" slot-scope="props">
                        <span v-if="props.column.field == 'phone'">
                            {{props.row.phone}}
                        </span>

                        
                        <span v-else-if="props.column.field == 'message'">
                            <span v-if="props.row.message==null">-</span>
                            <span v-else>{{props.row.message}}</span>
                        </span>

                        <span v-else-if="props.column.field == 'url'">
                            <span v-if="props.row.url == null">-</span>
                            <span v-else><b-badge variant="primary" v-b-modal.modal_file @click="gambar(props.row.url)"><feather-icon icon="EyeIcon"/> Lihat</b-badge></span>
                        </span>

                        <span v-else-if="props.column.field == 'to_timestamp'">
                            {{props.row.to_timestamp}}
                        </span>

                        <span v-else-if="props.column.field == 'reply'">
                            {{props.row.reply}}
                        </span>

                        <span v-else-if="props.column.field == 'reply_by'">
                            {{props.row.reply_by}}
                        </span>

                        <span v-else-if="props.column.field == 'status'">
                            <span v-if="props.row.status == null"><b-badge variant="danger">Belum Balas</b-badge></span>
                            <span v-else-if="props.row.status == 0"><b-badge variant="info">Terbalas</b-badge></span>
                            <span v-else-if="props.row.status == 1"><b-badge variant="success">Selesai</b-badge></span>
                        </span>
                        <span v-else-if="props.column.field == 'aksi'">
                            <b-dropdown
                                variant="flat-secondary"                            
                                >
                                <template #button-content>
                                    <feather-icon
                                        icon="MenuIcon"
                                    />
                                </template>

                                <b-dropdown-item 
                                    :disabled="props.row.status_perangkaan==1 || props.row.lock==1"
                                    variant="success" type="submit" v-b-modal.lihat_pesan @click="pengirim(props.row.phone, props.row.id_wa)"
                                >
                                    <feather-icon
                                        icon="CheckIcon"
                                        class="mr-1"
                                    /> Balas Pesan
                                </b-dropdown-item>

                                <b-dropdown-item 
                                    :disabled="props.row.status_perangkaan==1 || props.row.lock==1"
                                    variant="danger" type="submit" v-b-modal.modal_konfirmasi_selesai @click="pengirim(props.row.phone)"
                                >
                                    <feather-icon
                                            icon="XIcon"
                                            class="mr-1"
                                        />Selesai
                                </b-dropdown-item>
                            </b-dropdown>
                        </span>

                    </template>

                    <template
                        slot="pagination-bottom"
                        slot-scope="props"
                        >
                        <div class="d-flex justify-content-between flex-wrap">
                        <div class="d-flex align-items-center mb-0 mt-1">
                            <span class="text-nowrap ">
                            Menampilkan 1 sampai
                            </span>
                            <b-form-select
                            v-model="pageLength"
                            :options="['10','20', '50' ]"
                            class="mx-1"
                            @input="(value)=>props.perPageChanged({currentPerPage:value})"
                            />
                            <span class="text-nowrap"> dari {{ props.total }} hasil pencarian </span>
                        </div>
                        <div>
                            <b-pagination
                            :value="1"
                            :total-rows="props.total"
                            :per-page="pageLength"
                            first-number
                            last-number
                            align="right"
                            prev-class="prev-item"
                            next-class="next-item"
                            class="mt-1 mb-0"
                            @input="(value)=>props.pageChanged({currentPage:value})"
                            >
                            <template #prev-text>
                                <feather-icon
                                icon="ChevronLeftIcon"
                                size="18"
                                />
                            </template>
                            <template #next-text>
                                <feather-icon
                                icon="ChevronRightIcon"
                                size="18"
                                />
                            </template>
                            </b-pagination>
                        </div>
                        </div>
                    </template>
                </vue-good-table>
        </b-card>

        <b-modal id="modal_file" centered>
            <b-img fluid v-bind:src="this.filenya"></b-img>
        </b-modal>

        <b-modal id="modal_konfirmasi_selesai" centered hide-footer>
            <h3>Apakah Anda yakin menandai Pesan ini sudah selesai???</h3>
            <b-button @click="pesan_selesai()" variant="success" class="my-1">
                    <feather-icon
                        icon="SendIcon"
                        class="mr-50"/> OK
                </b-button>
        </b-modal>

        <b-modal id="lihat_pesan" centered hide-footer>
            <b-card title="Balas Pesan">
                <b-form-group>
                    <b-form-textarea v-model="pesan" class="mb-1" rows="10"/>
                    <b-form-group label="Kirim Gambar" label-cols-md="4">
                        <b-form-file v-model="gambar" accept=".jpg"></b-form-file>
                    </b-form-group>
                
                    <b-button @click="kirim_pesan()" variant="success" class="my-1">
                        <feather-icon
                            icon="SendIcon"
                            class="mr-50"/>Balas
                    </b-button>
                </b-form-group>
            </b-card>
        </b-modal>
    </b-card>
</template>

<script>

import {BFormFile, BImg, BDropdownDivider, BDropdownItem, BCardText, BDropdownForm, BOverlay, BTable, BButton, BModal, BTab, BTabs, BCard,BFormGroup, BFormInput,BDropdown, BFormSpinbutton, BAlert,BFormSelect, BPagination, BTooltip,BBadge,BFormTextarea,BDropdownGroup} from 'bootstrap-vue'

import {VueGoodTable} from 'vue-good-table'
import {VMoney} from 'v-money'
import BCardCode from '../@core/components/b-card-code/BCardCode.vue'
import axios from 'axios'

import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

import 'vue-good-table/dist/vue-good-table.css'
import FeatherIcon from '../@core/components/feather-icon/FeatherIcon.vue'
import 'vue-search-select/dist/VueSearchSelect.css' 
import { ModelSelect } from 'vue-search-select'
import '../@core/assets/css/pulse.css'

export default {
    components:{
        BFormFile,
        BImg,
        BDropdownDivider,
        BDropdownItem,
        BDropdownGroup,
        BDropdownForm,
        BOverlay,
        BBadge,
        BTable,
        BButton,
        VueGoodTable,
        BCardCode,
        BModal,
        BTab,
        BTabs,
        FeatherIcon,
        BCard,
        BFormGroup,
        BFormInput,
        BFormTextarea,
        BDropdown,
        BFormSpinbutton,
        BAlert,
        BFormSelect,
        BPagination,
        ModelSelect,
        BTooltip,
        BCardText,
    },

    methods:{
        pesan_selesai(){
            axios.post('/api/waselesai', {phone: this.phone})
                .then(response=>{
                    this.$toast({
                            component: ToastificationContent,
                            props: {
                                title: response.data.message,
                                icon: 'AlertIcon',
                                variant: 'info',
                            },
                        });
                        this.$bvModal.hide('modal_konfirmasi_selesai');
                        this.listSekolahNegeri();
                })
        },

        pengirim(phone, id){
            this.phone = phone,
            this.id_wa = id
        },
        kirim_pesan(){
            const instanceAxios = axios.create({
                headers: {'Authorization': 'wxDQ6XHfiFQPrErRP6cUjucsA73dTJUDeg2O6uPDKZBB2qAX1p6sl9ScE9Y8T1IS'}
            })
            
            instanceAxios.post('https://cepogo.wablas.com/api/send-message', {phone: this.phone, message: this.pesan})
                .then(response=>{
                    const respon = response.data.message;
                    if(respon == 'successfully sent text'){
                        axios.post('/api/updatewa', {phone: this.phone, nama: this.userData.username, pesan: this.pesan, id: this.id_wa})
                        this.$toast({
                            component: ToastificationContent,
                            props: {
                                title: response.data.message,
                                icon: 'AlertIcon',
                                variant: 'info',
                            },
                        });
                        this.$bvModal.hide('lihat_pesan');
                        this.listSekolahNegeri();
                    }
                    this.listSekolahNegeri();
                })
        },
        gambar(url){
            this.filenya = url
        },
        
        listSekolahNegeri(){
            axios.post('/api/getwa', {user:this.userData.user_id})
                .then(response=>{
                    this.pesan_rows = response.data.wa_message;
                })
        },
    },

    computed:{
        
    },

    created(){
        this.listSekolahNegeri()
    },

    data(){
        return{
            id_wa:'',
            phone: '',
            filenya: '',
            pesan: '',
            tujuan: '',
            overlay: false,
            pageLength: '10',
            userData: JSON.parse(localStorage.getItem('userData')),
            sumber:null,
            npsn:null,
            status:null,
            jenjang:null,
            subtitle:null,
            nama_kegiatan:null,
            nama_komponen:null,
            id_hapus:null,
            komponen_id: null,
            rekening: null,
            nama_pelatih: null,
            sdm_id: null,
            catatan: null,
            jenis_revisi: null,

            pesan_columns: [
                {
                    label: 'Pengirim',
                    field: 'phone',
                },
                {
                    label: 'Pesan',
                    field: 'message',
                },
                {
                    label: 'Gambar',
                    field: 'url',
                    thClass: 'text-center',
                    tdClass: 'text-center'
                },
                {
                    label: 'status',
                    field: 'status',
                    thClass: 'text-center',
                    tdClass: 'text-center'
                },
                {
                    label: 'Waktu Masuk',
                    field: 'to_timestamp',
                    thClass: 'text-center',
                    tdClass: 'text-center'
                },
                
                {
                    label: 'Penjawab',
                    field: 'reply_by',
                    thClass: 'text-center',
                    tdClass: 'text-center'
                },
                
                {
                    label: 'Jawaban',
                    field: 'reply',
                    thClass: 'text-center',
                    tdClass: 'text-center'
                },
                {
                    label: 'Menu',
                    field: 'aksi',
                },
            ],
            pesan_rows: [],

        }
    }

}
</script>
