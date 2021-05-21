import Vue from 'vue'
import { ToastPlugin, ModalPlugin } from 'bootstrap-vue'
import VueCompositionAPI from '@vue/composition-api'

import IdleVue from 'idle-vue'

import router from './router'
import store from './store'
import App from './App.vue'
import axios from 'axios'

import VueCurrencyFilter from 'vue-currency-filter'
// Global Components
import './global-components'

// 3rd party plugins
import '@/libs/portal-vue'
import '@/libs/toastification'
import '@/libs/acl' 

// BSV Plugin Registration
Vue.use(ToastPlugin)
Vue.use(ModalPlugin)

// Composition API
Vue.use(VueCompositionAPI)

Vue.use(VueCurrencyFilter,
  {
    symbol: '',
    thousandsSeparator: '.',
    fractionCount: 0,
    fractionSeparator: ',',
    symbolPosition: 'front',
    symbolSpacing: true,
    avoidEmptyDecimals: undefined,
  })

// Vue.use(IdleVue, {
//   eventEmitter: eventsHub,
//   store,
//   idleTime: 3000, // 3 detik
//   startAtIdle: false
// })

// import core styles
require('@core/scss/core.scss')

// import assets styles
require('@/assets/scss/style.scss')

require('@core/assets/fonts/feather/iconfont.css')

Vue.config.productionTip = false

new Vue({
  router,
  store,
  axios,
  render: h => h(App),
}).$mount('#app')
