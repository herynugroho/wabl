export default [
    {
      title: 'Home',
      route: 'home',
      icon: 'HomeIcon',
    },
    {
      title: 'Booking Antrian',
      route: 'booking',
      icon: 'FileIcon',
    },
    {
      title: 'Komponen SSH',
      icon: 'DollarSignIcon',
      route: 'ssh',
    },
    {
      title: 'Kegiatan',
      icon: 'BriefcaseIcon',
      children: [
            {
              title: 'Kegiatan Awal',
              route: 'kegiatan_awal'
            },
            {
              title: 'Kegiatan Murni',
              route: 'kegiatan',
            }
          ]
    },
    {
      title: 'Berkas BPD',
      icon: 'FileIcon',
      route: 'berkas_bpd',
    },
    // {
    //   title: 'Berkas',
    //   icon: 'FileIcon',
    //   children: [
    //         {
    //             title: 'Berkas BPD',
    //             route: 'berkas_bpd',
    //         },
    //         {
    //             title: 'Pencairan',
    //             route: 'berkas_laporan_pencairan',
    //         },
    //   ]
    // },
    {
        title: 'Entry',
        icon: 'EditIcon',
        children: [
          {
            title: 'Buku Pembantu Bank',
            route: 'entry_pembantu_bank'
          }
        ]
    },
    {
        title: 'Penerimaan',
        route: 'penerimaan_sekolah',
        icon: 'InboxIcon',
    },
    // {
    //   title: 'Setting Profil',
    //   icon: 'FileIcon',
    //   route: 'setting_profil',
    // },
    // {
    //   title: 'Kelengkapan BPD',
    //   icon: 'FileIcon',
    //   children: [
    //         {
    //           title: 'PENGAJUAN BPD AWAL',
    //           route: 'pengajuan_bpd_awal',
    //         },
    //         {
    //           title: 'REKAPITULASI RENCANA PENGGUNAAN',
    //           route: 'rekapitulasi_awal',
    //         },
    //         // {
    //         //   title: 'REKAPITULASI DAFTAR NAMA SISWA',
    //         //   route: 'kegiatan_awal',
    //         // },
    //         // {
    //         //   title: 'SURAT PERNYATAAN KEABSAHAN DATA SISWA',
    //         //   route: 'kegiatan',
    //         // },
    //         // {
    //         //   title: 'SURAT PERNYATAAN TIDAK TERJADI KONFLIK',
    //         //   route: 'kegiatan',
    //         // },
    //         // {
    //         //   title: 'PAKTA INTEGRITAS',
    //         //   route: 'kegiatan',
    //         // },
    //         // {
    //         //   title: 'SURAT PERNYATAAN KESEDIAAN MEMBEBASKAN BIAYA PENDIDIKAN',
    //         //   route: 'kegiatan',
    //         // },
    //         // {
    //         //   title: 'SURAT PERNYATAAN BESARAN PUNGUTAN BIAYA PENDIDIKAN',
    //         //   route: 'kegiatan',
    //         // },
    //         // {
    //         //   title: 'PENGAJUAN BPD ANGGARAN MURNI',
    //         //   route: 'kegiatan',
    //         // },
    //         // {
    //         //   title: 'REKAPITULASI RENCANA PENGGUNAAN MURNI',
    //         //   route: 'kegiatan',
    //         // },
    //         // {
    //         //   title: 'REKAPITULASI DAFTAR NAMA SISWA MURNI',
    //         //   route: 'kegiatan',
    //         // },
    //         // {
    //         //   title: 'SURAT PERNYATAAN KEABSAHAN DATA SISWA MURNI',
    //         //   route: 'kegiatan',
    //         // },
    //         // {
    //         //   title: 'NASKAH PERJANJIAN HIBAH DAERAH',
    //         //   route: 'kegiatan',
    //         // },
    //       ]
    // },
  
    // {
    //   title: 'List Sekolah',
    //   // route: 'list_sekolah',
    //   icon: 'UsersIcon',
    //   action: 'read', 
    //   resource: 'ACL',
    //   children: [
    //     {
    //       title: 'Sekolah Negeri',
    //       route: 'list_sekolah_negeri',
    //       action: 'read', 
    //       resource: 'ACL',
    //     },
    //     {
    //       title: 'Sekolah Swasta',
    //       route: 'list_sekolah_swasta',
    //     }
    //   ]
    // },
  ]
  