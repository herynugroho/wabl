export default [
  {
    title: 'Home',
    route: 'home',
    icon: 'HomeIcon',
  },
  {
    title: 'Wablas SPMB 2025',
    route: 'chat-app',
    icon: 'MessageCircleIcon',
    action: 'read', 
    resource: 'ACL',
    // children: [
    //   {
    //     title: 'Wablas SPMB 2025',
    //     route: 'chat-app',
    //     component: () => import('@/views/ChatApp.vue'),
    //   },
    //   // {
    //   //   title: 'Incoming Whatsapp',
    //   //   route: 'listwa',
    //   // },
    //   // {
    //   //   title: 'WhatsApp Masuk',
    //   //   route: 'daftar_pesan',
    //   //   action: 'read', 
    //   //   resource: 'ACL',
    //   // },
    //   {
    //     title: 'F.A.Q',
    //     route: 'faq',
    //     action: 'read', 
    //     resource: 'ACL',
    //   },
    // ]
  },
  {
    title: 'F.A.Q',
    route: 'faq',
    action: 'read', 
    icon: 'MessageCircleIcon',
    resource: 'ACL'
  }
]
