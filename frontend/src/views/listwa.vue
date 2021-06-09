<template>
    <b-card>
        <!-- <b-button @click="blast_pesan()"/> -->
        <b-card>
            <vue-good-table
                :columns="listwa_columns"
                :rows="listwa_rows"
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
                            <span>{{props.row.phone}}  </span>
                            <feather-icon icon="MailIcon" class="text-info" v-bind:badge="props.row.unread" badge-classes="badge-info"/>
                        </span>
                        <span v-if="props.column.field == 'waktu'">
                            {{props.row.waktu}}
                        </span>

                        <span v-else-if="props.column.field == 'aksi'">
                            <b-button pill variant="primary" type="submit" v-b-modal.lihat_pesan>
                                <feather-icon
                                        icon="EyeIcon"
                                        class="mr-1"
                                    />Lihat
                            </b-button>
                            <!-- <b-dropdown
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
                            </b-dropdown> -->
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
                            :options="['10','20', '50', '100' ]"
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

        <b-modal id="lihat_pesan" centered hide-footer size="l">
            <b-card title="Balas Pesan">
                <vue-perfect-scrollbar
                    class="user-chats scroll-area"
                >
                    <div class="chats">
                        <div

                        class="chat"
                        
                        >
                        <div class="chat-avatar">
                            <b-avatar
                            size="36"
                            class="avatar-border-2 box-shadow-1"
                            variant="transparent"
                            />
                        </div>
                        <div class="chat-body">
                            <!-- <div
                            v-for="msgData in msgGrp.messages"
                            :key="msgData.time"
                            class="chat-content"
                            >
                            <p>{{ msgData.msg }}</p>
                            </div> -->
                        </div>
                        </div>
                    </div>

                </vue-perfect-scrollbar>
                <!-- @submit.prevent="sendMessage" -->
                <b-form
                    class="chat-app-form"
                    
                    >
                    <!-- v-model="chatInputMessage" -->
                    <b-input-group class="input-group-merge form-send-message mr-1">
                        <b-form-input
                        
                        placeholder="Enter your message"
                        />
                    </b-input-group>
                    <b-button
                        variant="primary"
                        type="submit"
                    >
                        Send
                    </b-button>
                </b-form>
                <!-- <b-form-group>
                    <b-form-textarea v-model="pesan" class="mb-1"/>
                    <b-form-group label="Kirim Gambar" label-cols-md="4">
                        <b-form-file v-model="gambar" accept=".jpg"></b-form-file>
                    </b-form-group>
                
                    <b-button pill @click="kirim_pesan()" variant="primary" class="my-1">
                        <feather-icon
                            icon="SendIcon"/>
                    </b-button>
                </b-form-group> -->
            </b-card>
        </b-modal>
    </b-card>
</template>

<script>

import {BInputGroup, BForm, BAvatar, BFormFile, BImg, BDropdownDivider, BDropdownItem, BCardText, BDropdownForm, BOverlay, BTable, BButton, BModal, BTab, BTabs, BCard,BFormGroup, BFormInput,BDropdown, BFormSpinbutton, BAlert,BFormSelect, BPagination, BTooltip,BBadge,BFormTextarea,BDropdownGroup} from 'bootstrap-vue'

import {VueGoodTable} from 'vue-good-table'
import VuePerfectScrollbar from 'vue-perfect-scrollbar'
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
        BAvatar,
        BInputGroup,
        BForm,
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
        VuePerfectScrollbar
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
                        this.listwa();
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
                        this.listwa();
                    }
                    this.listwa();
                })
        },
        blast_pesan(){
            const iAxios = axios.create({
                headers:{
                    'Authorization': 'yoYE2eLAUtukCQLgPQpQ5JPhay1UUt1PBhLSCjgDEYci2SEI0hsBxO4PZfCOyrb9',
                    'Content-Type': 'application/json'
                }
            })

            iAxios.post('https://cepogo.wablas.com/api/v2/send-bulk/text', {data: this.bulk})
        },
            
        gambar(url){
            this.filenya = url
        },
        
        listwa(){
            axios.post('/api/listwa', {user: this.userData.user_id})
                .then(response=>{
                    this.listwa_rows = response.data.list_wa;
                })
        },
    },

    computed:{
        
    },

    created(){
        this.listwa()
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

            bulk:[
                {
                    phone: '082336056768',
                    message: 'asdfg'
                },
                {
                    phone: '082336056768',
                    message: 'qwerty'
                }
            ],

            listwa_columns: [
                {
                    label: 'Pengirim',
                    field: 'phone',
                },
                {
                    label: 'Waktu Masuk Pesan Terakhir',
                    field: 'waktu',
                    thClass: 'text-center',
                    tdClass: 'text-center'
                },
                {
                    label: 'Aksi',
                    field: 'aksi',
                    thClass: 'text-center',
                    tdClass: 'text-center'
                },
            ],
            listwa_rows: [],

        }
    }

}
</script>

<style lang="scss">
@import "~@core/scss/base/pages/app-chat.scss";
@import "~@core/scss/base/pages/app-chat-list.scss";
</style>
