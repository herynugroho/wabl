export default [
  {
    title: 'Home',
    route: 'home',
    icon: 'HomeIcon',
  },
  {
    title: 'Pesan',
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
      {
        title: 'F.A.Q',
        route: 'faq',
        action: 'read', 
        resource: 'ACL',
      },
      {
        title: 'Chat',
        route: 'chat',
      },
    ]
  }
]
