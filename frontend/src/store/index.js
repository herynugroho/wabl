import Vue from 'vue'
import Vuex from 'vuex'


// Modules
import app from './app'
import appConfig from './app-config'
import verticalMenu from './vertical-menu'

Vue.use(Vuex)

export default new Vuex.Store({
  modules: {
    app,
    appConfig,
    verticalMenu,
  },
  strict: process.env.DEV,

  state: {
    token: localStorage.getItem('accessToken'),
    kode_kegiatan: '',
    nama_keiatan: ''
  },

  getters: {
    isAuth: state => {
      return state.token != "null" && state.token != null
  }

  },
  mutations: {},
  actions: {}
})
