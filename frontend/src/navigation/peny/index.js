export default [
  {
    title: 'Home',
    route: 'home',
    icon: 'HomeIcon',
  },
  // {
  //   title: 'Booking Antrian',
  //   route: 'booking',
  //   icon: 'FileIcon',
  // },
  // {
  //   title: 'Komponen SSH',
  //   icon: 'DollarSignIcon',
  //   route: 'ssh',
  // },
  // {
  //   title: 'Kegiatan',
  //   route: 'kegiatan',
  //   icon: 'BriefcaseIcon',
  // },

  {
    title: 'Pesan',
    // route: 'list_sekolah',
    icon: 'MessageCircleIcon',
    action: 'read', 
    resource: 'ACL',
    children: [
      {
        title: 'WhatsApp Masuk',
        route: 'daftar_pesan',
        action: 'read', 
        resource: 'ACL',
      },
      // {
      //   title: 'Sekolah Swasta',
      //   route: 'list_sekolah_swasta',
      //   action: 'read', 
      //   resource: 'ACL',
      // },
      // {
      //   title: 'Sekolah Swasta',
      //   route: 'list_sekolah_swasta',
      // }
    ]
  }
  // {
  //   title: 'Penerimaan',
  //   icon: 'InboxIcon',
  //   route: 'penerimaan_penyelia',
  // },
  // {
  //   title: 'Verifikasi BPD',
  //   icon: 'FileIcon',
  //   route: 'verifikasi_berkas',
  // },
]
