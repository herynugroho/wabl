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
    route: 'kegiatan',
    icon: 'BriefcaseIcon',
  },
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
