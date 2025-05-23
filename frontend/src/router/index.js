import Vue from 'vue'
import VueRouter from 'vue-router'
import { canNavigate } from '@/libs/acl/routeProtection'

Vue.use(VueRouter)

const router = new VueRouter({
  mode: 'history',
  base: process.env.BASE_URL,
  scrollBehavior() {
    return { x: 0, y: 0 }
  },
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('@/views/Home.vue'),
      meta: {
        pageTitle: 'Home',
        breadcrumb: [
          {
            text: 'Home',
            active: true,
          },
        ]
      },
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/Login.vue'),
      meta: {
        layout: 'full',
      },
    },
    {
      path: '/chat-demo',
      name: 'chat-demo',
      component: () => import('@/views/ChatDemo.vue'),
      meta: {
        pageTitle: 'Demo Chat WhatsApp',
        breadcrumb: [
          {
            text: 'Demo Chat WhatsApp',
            active: true,
          },
        ],
      },
    },
    {
      path: '/chat-app',
      name: 'chat-app',
      component: () => import('@/views/ChatApp.vue'),
      meta: {
        pageTitle: 'Wablas SPMB 2025',
        breadcrumb: [
          {
            text: 'Wablas SPMB 2025',
            active: true,
          },
        ],
      },
    },
    {
      path: '/daftar_pesan',
      name: 'daftar_pesan',
      component: () => import('@/views/ListPesan.vue'),
      meta: {
        pageTitle: 'Daftar Pesan',
        breadcrumb: [
          {
            text: 'Daftar Pesan',
            active: true,
          },
        ],
      },
    },
    {
      path: '/listwa',
      name: 'listwa',
      component: () => import('@/views/listwa.vue'),
      meta: {
        pageTitle: 'Incoming Whatsapp',
        breadcrumb: [
          {
            text: 'Incoming Whatsapp',
            active: true,
          },
        ],
      },
    },
    {
      path: '/chat-app',
      name: 'chat-app',
      component: () => import('@/views/ChatApp.vue'),
      meta: {
        pageTitle: 'Pesan Masuk',
        breadcrumb: [
          {
            text: 'Pesan Masuk',
            active: true,
          },
        ],
      },
    },
    {
      path: '/faq',
      name: 'faq',
      component: () => import('@/views/faq.vue'),
      meta: {
        pageTitle: 'F.A.Q',
        breadcrumb: [
          {
            text: 'F.A.Q',
            active: true,
          },
        ],
      },
    },
    {
      path: '/chat',
      name: 'chat',
      component: () => import('@/views/chat/Chat.vue'),
      meta: {
        contentRenderer: 'sidebar-left',
        contentClass: 'chat-application',
        // pageTitle: 'Chat',
        // breadcrumb: [
        //   {
        //     text: 'Chat',
        //     active: true,
        //   },
        // ],
      },
    },
  ],
})

router.beforeEach((to, from, next) => {
  if(!canNavigate(to)){
    if (to.name !== 'login'){
      const auth = localStorage.getItem('accessToken');
      if(auth)
        next()
      else next({ name: 'login' })
    } else next()
  }
})
export default router
