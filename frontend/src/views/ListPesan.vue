<template>
    <b-card>
        <b-card>
            <vue-good-table
                :columns="sekolah_columns"
                :rows="sekolah_negeri_rows"
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
                                    variant="success" type="submit" v-b-modal.lihat_pesan @click="pengirim(props.row.phone)"
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
            <b-card>
                <b-form-textarea v-model="pesan"/>
                <b-button @click="kirim_pesan()" variant="success" class="my-1">
                    <feather-icon
                        icon="SendIcon"
                        class="mr-50"/>Balas
                </b-button>
            </b-card>
        </b-modal>
        <!-- MODAL KEGIATAN -->
        <b-modal id="modal_kegiatan" size="xl">
            <vue-good-table
                :columns="kegiatan_columns"
                :rows="kegiatan_rows"
                :select-options="{ enabled: false }"
                :search-options="{ enabled: false }">

                <template slot="table-row" slot-scope="props">
                    <span v-if="props.column.field == 'nilai'">
                        {{props.row.nilai | currency}}
                    </span>

                    <span v-else-if="props.column.field == 'status_kegiatan'">
                        <span v-if="props.row.status_kegiatan == null"><b-badge>POSISI ENTRY</b-badge></span>
                        <span v-if="props.row.status_kegiatan == 0"><b-badge variant="primary">KIRIM PENYELIA</b-badge></span>
                        <span v-if="props.row.status_kegiatan == 1"><b-badge variant="success">DISETUJUI PENYELIA</b-badge></span>
                        <span v-if="props.row.status_kegiatan == 12"><b-badge variant="danger">REVISI</b-badge></span>
                    </span>
                    <span v-else-if="props.column.field == 'aksi'">
                        <div>
                            <b-button @click="detailKegiatan(props.row.kode_kegiatan,props.row.nama_kegiatan,props.row.status_kegiatan)" variant="success" class="btn-icon rounded">
                                <feather-icon
                                    icon="ClipboardIcon"
                                /> Detail
                            </b-button>
                        </div>
                    </span>
                </template>
            </vue-good-table>
        </b-modal>
        <!-- END MODAL KEGIATAN -->

        <!-- MODAL DETAIL -->
        <b-modal 
            id="detail_kegiatan_honorer" 
            size="xl" 
            hide-footer
            >
            
            <b-alert
                show
                variant="danger"
                >
                <div class="alert-body">
                    <feather-icon
                    class="mr-25"
                    icon="AlertCircleIcon"
                    />
                    <span class="ml-25">Silakan Cek Kembali Kesesuaian Data yang telah Anda Inputkan.</span>
                </div>
            </b-alert>

            <b-card :title="title_detail_kegiatan">
                <!-- :line-numbers="true"
                theme="black-rhino" -->

                <vue-good-table
                theme="black-rhino"
                :columns="detail_kegiatan_honorer_column"
                :rows="detail_kegiatan_honorer_rows"
                :select-options="{ enabled: false }"
                :search-options="{ enabled: true }"
                >

                    <template slot="table-row" slot-scope="props">
                        <span v-if="props.column.field == 'nama_pegawai'">
                                {{props.row.nama_pegawai}}
                        </span>

                        <span v-else-if="props.column.field == 'komponen_name'">
                                {{props.row.komponen_name}}
                        </span>

                        <span v-else-if="props.column.field == 'nominal'">
                                {{props.row.nominal | currency}}
                        </span>


                        <span v-else-if="props.column.field == 'subtitle'">
                                {{props.row.subtitle}}
                        </span>
                    </template>
                </vue-good-table>
                <b-card>
                    <b-form-group>
                        <b-button @click="$bvModal.hide('detail_kegiatan_honorer')" variant="danger">
                            <feather-icon
                                icon="XIcon"
                                class="mr-50"
                            />
                            <span class="align-middle">Tutup</span>
                        </b-button>
                    </b-form-group>
                </b-card>
            </b-card>
        </b-modal>
        <b-modal 
            id="detail_kegiatan" 
            size="xl" 
            hide-footer
            >
            
            <b-alert
                show
                variant="danger"
                >
                <div class="alert-body">
                    <feather-icon
                    class="mr-25"
                    icon="AlertCircleIcon"
                    />
                    <span class="ml-25">Silakan Cek Kembali Kesesuaian Data yang telah Anda Inputkan.</span>
                </div>
            </b-alert>
          
            <b-card :title="title_detail_kegiatan">
                <!-- :line-numbers="true"
                theme="black-rhino" -->
                <vue-good-table
                  :columns="detail_kegiatan_column"
                  :rows="detail_kegiatan_rows"
                  :group-options="{
                    enabled: true,
                    headerPosition: 'top'
                  }"
                >
                  <template slot="table-header-row" slot-scope="props">
                    <span v-if="props.column.field == 'aksi'">
                        <div v-if="$data.status_kegiatan==null">
                            <b-button 
                                :disabled="props.row.status==1"
                                class="ml-1" v-b-modal.v_b_subtitle @click="set_subtitle(props.row.subtitle,props.row.komponen_name,props.row.komponen_id,props.row.no_rekening)" variant="primary" type="submit">
                                <feather-icon
                                    icon="CheckIcon"
                                /> Verifikasi
                            </b-button>
                            <b-button 
                                :disabled="props.row.status==1"
                                 class="ml-1" variant="danger" type="submit" v-b-modal.modal_catatan @click="set_subtitle(props.row.subtitle,props.row.komponen_name,props.row.komponen_id,props.row.no_rekening,'kegiatan')">
                                <feather-icon
                                    icon="XIcon"
                                /> Revisi
                            </b-button>
                        </div>
                    </span>
                    <span v-else-if="props.column.field=='nilai'">
                        {{ props.row.nilai | currency}}                                        
                    </span>
                    <span v-else-if="props.column.field=='status'">
                        <span v-if="props.row.status == null"><b-badge>POSISI ENTRY</b-badge></span>
                        <span v-if="props.row.status == 0"><b-badge variant="primary">KIRIM PENYELIA</b-badge></span>
                        <span v-if="props.row.status == 1"><b-badge variant="success">DISETUJUI PENYELIA</b-badge></span>
                        <span v-if="props.row.status == 12"><b-badge variant="danger">REVISI</b-badge></span>
                    </span>
                    <span v-else>
                        {{ props.formattedRow[props.column.field] }}
                    </span>

                  </template>
                  <template slot="table-row" slot-scope="props">
                    <span v-if="props.column.field == 'aksi'">
                        <div v-if="$data.status_kegiatan==null">
                            <b-button 
                                :disabled="props.row.status==1"
                                class="ml-1" v-b-modal.v_b_komponen @click="set_subtitle(props.row.subtitle,props.row.komponen_name,props.row.komponen_id,props.row.no_rekening)" variant="success" type="submit">
                                <feather-icon
                                    icon="CheckIcon"
                                /> Verifikasi
                            </b-button>
                            <b-button 
                                :disabled="props.row.status==1"
                                class="ml-1" variant="danger" type="submit" v-b-modal.modal_catatan @click="set_subtitle(props.row.subtitle,props.row.komponen_name,props.row.komponen_id,props.row.no_rekening,'kegiatan')">
                                <feather-icon
                                    icon="XIcon"
                                /> Revisi
                            </b-button>
                        </div>
                    </span>
                    <span v-else-if="props.column.field=='nilai'">
                        {{ props.row.nilai | currency}}                                        
                    </span>
                    <span v-else-if="props.column.field=='status'">
                        <span v-if="props.row.status == null"><b-badge>POSISI ENTRY</b-badge></span>
                        <span v-if="props.row.status == 0"><b-badge variant="primary">KIRIM PENYELIA</b-badge></span>
                        <span v-if="props.row.status == 1"><b-badge variant="success">DISETUJUI PENYELIA</b-badge></span>
                        <span v-if="props.row.status == 12"><b-badge variant="danger">REVISI</b-badge></span>
                    </span>
                    <span v-else-if="props.column.field=='komponen_harga_bulat'">
                        {{ props.row.komponen_harga_bulat}}
                    </span>
                  </template>
                </vue-good-table>

                <b-card>
                    <b-form-group>
                        <b-button @click="$bvModal.hide('detail_kegiatan')" variant="danger">
                            <feather-icon
                                icon="XIcon"
                                class="mr-50"
                            />
                            <span class="align-middle">Tutup</span>
                        </b-button>
                    </b-form-group>
                </b-card>
            </b-card>
        </b-modal>
        <!-- END MODAL DETAIL -->

        <!-- MODAL PELATIH -->
        <b-modal id="modal_pelatih" size="xl" hide-footer>
            <vue-good-table
                :columns="pelatih_columns"
                :rows="list_pelatih"
                :line-numbers="true"
                :select-options="{ enabled: false }"
                :search-options="{ enabled: false }">

                <template slot="table-row" slot-scope="props"> 
                    <span v-if="props.column.field == 'nama_pegawai'">
                        {{props.row.nama_pegawai}}
                    </span>
                    <span v-if="props.column.field == 'jenis_pegawai'">
                        {{props.row.jenis_pegawai}}
                    </span>
                    <span v-if="props.column.field == 'keterangan'">
                        {{props.row.keterangan}}
                    </span>

                    <span v-else-if="props.column.field == 'status_guru'">
                            {{props.row.status_guru}}
                    </span>

                    <span v-else-if="props.column.field == 'ket'">
                        <span v-if="props.row.status_guru == 'GTT'">{{props.row.nama_jenis}}</span>
                        <span v-else-if="props.row.status_guru == 'PTT'">{{props.row.jenis_tendik}}</span>
                    </span>
                    <span v-if="props.column.field == 'ssh'">
                        {{props.row.komponen_harga | currency}}
                    </span>
                    <span v-if="props.column.field == 'hari'">
                            {{list_pelatih[props.index].hari}}
                    </span>

                    <span v-else-if="props.column.field == 'jam'">
                            {{list_pelatih[props.index].jam}}
                    </span>

                    <span v-else-if="props.column.field == 'bulan'">
                            {{list_pelatih[props.index].bulan}}
                    </span>

                    <span v-if="props.column.field == 'nilai'">
                        {{list_pelatih[props.index].nominal | currency}}
                    </span>
                    <span v-if="props.column.field == 'status_perangkaan'">
                        <span v-if="props.row.status_perangkaan == null"><b-badge>POSISI ENTRY</b-badge></span>
                        <span v-if="props.row.status_perangkaan == 0"><b-badge variant="primary">KIRIM PENYELIA</b-badge></span>
                        <span v-if="props.row.status_perangkaan == 1"><b-badge variant="success">DISETUJUI PENYELIA</b-badge></span>
                        <span v-if="props.row.status_perangkaan == 12"><b-badge variant="danger">REVISI</b-badge></span>
                    </span>

                    <span v-else-if="props.column.field == 'aksi'">
                        <div>
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
                                    variant="success" type="submit" v-b-modal.v_b_pelatih @click="set_pelatih(props.row.nama_pegawai,props.row.sdm_id)"
                                >
                                    <feather-icon
                                        icon="CheckIcon"
                                        class="mr-1"
                                    /> Verifikasi
                                </b-dropdown-item>

                                <b-dropdown-item 
                                    :disabled="props.row.status_perangkaan==1 || props.row.lock==1"
                                    variant="danger" type="submit" v-b-modal.modal_catatan @click="set_pelatih(props.row.nama_pegawai,props.row.sdm_id,'pelatih')"
                                >
                                    <feather-icon
                                            icon="XIcon"
                                            class="mr-1"
                                        />Revisi
                                </b-dropdown-item>

                                <b-dropdown-divider/>                                

                                <b-dropdown-item  variant="info" type="submit" v-b-modal.modal_skpbm @click="set_pelatih(props.row.nama_pegawai,props.row.sdm_id,'pelatih');cekSkpbmPelatih(props.row.nik_pegawai)">
                                    <feather-icon
                                        icon="InfoIcon"
                                        class="mr-1"
                                    />Info SKPBM
                                </b-dropdown-item>

                                <b-dropdown-item variant="warning" type="submit" v-b-modal.modal_perangkaan @click="set_pelatih(props.row.nama_pegawai,props.row.sdm_id,'pelatih');cekPerangkaanPelatih(props.row.nik_pegawai)">
                                    <feather-icon
                                            icon="InfoIcon"
                                            class="mr-1"
                                        />Info Perangkaan
                                </b-dropdown-item>
                            </b-dropdown>
                            <!-- <b-button 
                                :disabled="props.row.status_perangkaan==1 || props.row.lock==1"
                                size="sm" variant="success" type="submit" v-b-modal.v_b_pelatih @click="set_pelatih(props.row.nama_pegawai,props.row.sdm_id)">
                                <feather-icon
                                    icon="CheckIcon"
                                /> Verifikasi
                            </b-button>
                            <b-button 
                                :disabled="props.row.status_perangkaan==1 || props.row.lock==1"
                                size="sm" variant="danger" type="submit" v-b-modal.modal_catatan @click="set_pelatih(props.row.nama_pegawai,props.row.sdm_id,'pelatih')">
                                <feather-icon
                                    icon="XIcon"
                                /> Revisi
                            </b-button>
                            <b-button 
                                size="sm" variant="info" type="submit" v-b-modal.modal_skpbm @click="set_pelatih(props.row.nama_pegawai,props.row.sdm_id,'pelatih');cekSkpbmPelatih(props.row.nik_pegawai)">
                                <feather-icon
                                    icon="InfoIcon"
                                />
                            </b-button>
                            <b-button 
                                size="sm" variant="warning" type="submit" v-b-modal.modal_perangkaan @click="set_pelatih(props.row.nama_pegawai,props.row.sdm_id,'pelatih');cekPerangkaanPelatih(props.row.nik_pegawai)">
                                <feather-icon
                                    icon="InfoIcon"
                                />
                            </b-button> -->
                        </div>
                    </span>
                </template>
            </vue-good-table>
            <div class="my-1">
                <b-button
                    variant="warning"
                    @click="$bvModal.hide('modal_pelatih')"
                    >
                    <feather-icon
                        icon="XIcon"
                    /> Tutup
                </b-button>
                <b-button
                    variant="primary"
                    :hidden="btn_lock"
                    v-b-modal.v_b_lock_pelatih
                    >
                    <feather-icon
                        icon="LockIcon"
                    /> Lock
                </b-button>
                <b-button v-if="this.list_pelatih.length!=0"
                    variant="danger"
                    :hidden="btn_unlock"
                    v-b-modal.v_b_batal_lock_pelatih
                    >
                    <feather-icon
                        icon="XIcon"
                    /> Batalkan Perangkaan
                </b-button>
            </div>
        </b-modal>
        <!-- END MODAL PELATIH -->

        <!-- MODAL BATAL LOCK PELATIH -->
        <b-modal id="v_b_batal_lock_pelatih" ok-title="Batalkan" cancel-title="Batal" centered @ok="lock_pelatih('unlock')">
            <b-card-text>
                Apakah yakin ingin membatalkan lock?
            </b-card-text>
        </b-modal>
        <!-- END MODAL BATAL LOCK PELATIH -->

        <!-- MODAL VRIFIKASI LOCK PELATIH -->
        <b-modal id="v_b_lock_pelatih" ok-title="Lock" cancel-title="Batal" centered @ok="lock_pelatih('lock')">
            <b-card-text>
                Apakah yakin ingin lock?
            </b-card-text>
        </b-modal>
        <!-- END MODAL VRIFIKASI LOCK PELATIH -->
        
        <!-- MODAL VRIFIKASI BUKA LOCK ALOKASI -->
        <b-modal id="modal_buka_kunci_alokasi" ok-title="Unlock" cancel-title="Batal" centered @ok="unlock_alokasi">
            <b-card-text>
                Apakah yakin ingin unlock alokasi sekolah ini?
            </b-card-text>
        </b-modal>
        <!-- END MODAL VRIFIKASI BUKA LOCK ALOKASI -->

        <!-- MODAL VERIFIKASI SUBTITLE -->
        <b-modal id="v_b_subtitle" ok-title="Verifikasi" cancel-title="Batal" centered @ok="kirim_verifikasi">
            <b-card-text>
                Apakah yakin memverifikasi semua komponen dalam subtitle ({{this.subtitle}})?
            </b-card-text>
        </b-modal>
        <!-- END MODAL VERIFIKASI SUBTITLE -->
        <!-- MODAL VERIFIKASI KOMPONEN -->
        <b-modal id="v_b_komponen" ok-title="Verifikasi" ok-variant="success" cancel-title="Batal" centered @ok="kirim_verifikasi">
            <b-card-text>
                Apakah yakin memverifikasi komponen ({{this.subtitle}})?
            </b-card-text>
        </b-modal>
        <!-- END MODAL VERIFIKASI KOMPONEN -->
        <!-- MODAL VERIFIKASI PELATIH -->
        <b-modal id="v_b_pelatih" ok-title="Verifikasi" ok-variant="success" cancel-title="Batal" centered @ok="kirim_verifikasi_pelatih">
            <b-card-text>
                Apakah yakin memverifikasi pelatih ({{this.nama_pelatih}})?
            </b-card-text>
        </b-modal>
        <!-- END MODAL VERIFIKASI PELATIH -->

        <!-- MODAL CATATAN -->
        <b-modal id="modal_catatan" ok-title="Revisi" ok-variant="danger" cancel-title="Batal" @ok="choose_revisi">
            <label>Catatan</label>
            <b-form-group>
                <b-form-textarea type="text" wrap v-model="catatan"></b-form-textarea>
            </b-form-group>
        </b-modal>
        <!-- END MODAL CATATAN -->

        <!-- MODAL CEK SKPBM -->
        <b-modal id="modal_skpbm" size="lg">
            <b-card title="Jadwal SKPBM">
                <h1>{{this.nama_pelatih}}</h1>
                <b-overlay 
                    :show="this.overlay"
                    spinner-variant="primary"
                    spinner-type="grow"
                    spinner-small
                    rounded="sm"
                >
                    <vue-good-table
                    :columns="jadwal_skpbm_columns"
                    :rows="jadwal_skpbm_rows"
                    :line-numbers="true"
                    :select-options="{ enabled: false }"
                    :search-options="{ enabled: false }">
                        <template slot="table-row" slot-scope="props">
                            <span v-if="props.column.field == 'hari_ke'">
                            <span v-if="props.row.hari_ke == 1">SENIN</span>
                            <span v-if="props.row.hari_ke == 2">SELASA</span>
                            <span v-if="props.row.hari_ke == 3">RABU</span>
                            <span v-if="props.row.hari_ke == 4">KAMIS</span>
                            <span v-if="props.row.hari_ke == 5">JUMAT</span>
                            <span v-if="props.row.hari_ke == 6">SABTU</span>
                            <span v-if="props.row.hari_ke == 7">MINGGU</span>
                        </span>
                        </template>

                    </vue-good-table>
                </b-overlay>
            </b-card>
        </b-modal>
        <!-- END MODAL CEK SKPBM -->

        <!-- MODAL CEK PERANGKAAN -->
        <b-modal id="modal_perangkaan" size="lg">
            <b-card title="Perangkaan Pelatih">
                <h1>{{this.nama_pelatih}}</h1>
                <b-overlay 
                    :show="this.overlay"
                    spinner-variant="primary"
                    spinner-type="grow"
                    spinner-small
                    rounded="sm"
                >
                    <vue-good-table
                    :columns="jadwal_perangkaan_columns"
                    :rows="jadwal_perangkaan_rows"
                    :line-numbers="true"
                    :select-options="{ enabled: false }"
                    :search-options="{ enabled: false }">
                        <template slot="table-row" slot-scope="props">
                            <span v-if="props.column.field == 'status_perangkaan'">
                                <span v-if="props.row.status_perangkaan == null"><b-badge size="sm">POSISI ENTRY</b-badge></span>
                                <span v-if="props.row.status_perangkaan == 0"><b-badge size="sm" variant="primary">KIRIM PENYELIA</b-badge></span>
                                <span v-if="props.row.status_perangkaan == 1"><b-badge size="sm" variant="success">DISETUJUI PENYELIA</b-badge></span>
                                <span v-if="props.row.status_perangkaan == 12"><b-badge size="sm" variant="danger">REVISI</b-badge></span>
                            </span>
                            <span v-if="props.column.field == 'nominal'">
                                {{props.row.nominal | currency}}
                            </span>
                            <span v-if="props.column.field == 'koefisien'">
                                {{props.row.koefisien}}
                            </span>
                            <span v-if="props.column.field == 'unit_name'">
                                {{props.row.unit_name}}
                            </span>
                        </template>

                    </vue-good-table>
                </b-overlay>
            </b-card>
        </b-modal>
        <!-- END MODAL CEK SKPBM -->
    </b-card>
</template>

<script>

import {BImg, BDropdownDivider, BDropdownItem, BCardText, BDropdownForm, BOverlay, BTable, BButton, BModal, BTab, BTabs, BCard,BFormGroup, BFormInput,BDropdown, BFormSpinbutton, BAlert,BFormSelect, BPagination, BTooltip,BBadge,BFormTextarea,BDropdownGroup} from 'bootstrap-vue'

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

        pengirim(phone){
            this.phone = phone
        },
        kirim_pesan(){
            const instanceAxios = axios.create({
                headers: {'Authorization': 'wxDQ6XHfiFQPrErRP6cUjucsA73dTJUDeg2O6uPDKZBB2qAX1p6sl9ScE9Y8T1IS'}
            })
            
            instanceAxios.post('https://cepogo.wablas.com/api/send-message', {phone: this.phone, message: this.pesan})
                .then(response=>{
                    const respon = response.data.message;
                    if(respon == 'successfully sent text'){
                        axios.post('/api/updatewa', {phone: this.phone, nama: this.userData.username, pesan: this.pesan})
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
                    this.sekolah_negeri_rows = response.data.wa_message;
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

            btn_lock:null,
            btn_unlock:null,

            jadwal_perangkaan_rows:[],
            jadwal_perangkaan_columns:[
                {
                label: 'Nama Sekolah',
                field: 'unit_name',
                thClass: 'text-center'
            },
            {
                label: 'Koefisien',
                field: 'koefisien',
                thClass: 'text-center'
            },
            {
                label: 'Nilai (Rp)',
                field: 'nominal',
                thClass: 'text-center',
                type: 'number'
            },
            {
                label: 'Status Perangkaan',
                field: 'status_perangkaan',
                thClass: 'text-center'
            }
            ],

            sekolah_columns: [
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
            sekolah_negeri_rows: [],

            kegiatan_columns: [
                {
                    label: 'Kode Kegiatan',
                    field: 'kode_kegiatan',
                },
                {
                    label: 'Nama Kegiatan',
                    field: 'nama_kegiatan',
                },
                {
                    label: 'Nilai (Rp)',
                    field: 'nilai',
                    type: 'number',
                },
                {
                    label: 'Status',
                    field: 'status_kegiatan',
                },
                {
                    label: 'Catatan',
                    field: 'posisi',
                },
                {
                    label: 'Aksi',
                    field: 'aksi',
                }
            ],

            kegiatan_rows: [],

            detail_kegiatan_honorer_column:[
                {
                    label: 'Nama Pegawai',
                    field: 'nama_pegawai',
                },
                {
                    label: 'Komponen Name',
                    field: 'komponen_name',
                },
                {
                    label: 'Nominal (Rp)',
                    field: 'nominal',
                    type: 'number',
                },
            ],

            detail_kegiatan_honorer_rows: [],

            detail_kegiatan_column:[
                {
                    label: 'Komponen Name',
                    field: 'komponen_name',
                },
                {
                    label: 'Rekening',
                    field: 'no_rekening',
                },
                {
                    label: 'Keofisien',
                    field: 'koefisien',
                },
                {
                    label: 'Harga Satuan (Rp)',
                    field: 'komponen_harga_bulat',
                    type: 'number',
                },
                {
                    label: 'PPN (%)',
                    field: 'pajak',
                },
                {
                    label: 'Nilai (Rp)',
                    field: 'nilai',
                    type: 'number',
                },
                {
                    label: 'Status',
                    field: 'status',
                },
                {
                    label: 'Catatan',
                    field: 'catatan',
                },
                {
                    label: 'Aksi',
                    field: 'aksi',
                },
            ],

            detail_kegiatan_rows: [],

            pelatih_columns: [],

            list_pelatih: [],

            jadwal_skpbm_columns: [
            {
                label: 'Nama Sekolah',
                field: 'nama_sekolah',
                thClass: 'text-center'
            },
            {
                label: 'Keterangan',
                field: 'keterangan',
                thClass: 'text-center'
            },
            {
                label: 'Hari',
                field: 'hari_ke',
                thClass: 'text-center'
            },
            {
                label: 'Jam Awal',
                field: 'jam_awal',
                thClass: 'text-center'
            },
            {
                label: 'Jam Akhir',
                field: 'jam_akhir',
                thClass: 'text-center'
            }
            ],
            jadwal_skpbm_rows:[],

            gtt_columns: [],

            peg_rows: [],
            idKomp:{
                gtt : 196562.5,
                ptt1: 4717500,
                ptt2: 4300480
            },
        }
    }

}
</script>
