<template>
  <div class="auth-wrapper auth-v2">
    <b-row class="auth-inner m-0">

      <!-- Brand logo-->
      <b-link class="brand-logo">
        <img src="@/assets/images/logo/logo-1.png" height="80px">
      </b-link>
      <!-- /Brand logo-->

      <!-- Left Text-->
      <b-col
        lg="8"
        class="d-none d-lg-flex align-items-center p-5"
      >
        <div class="w-100 d-lg-flex align-items-center justify-content-center px-5">
          <b-img
            src="@/assets/\images\_\_\_\_\wabl\frontend\src\assets\images\logo/login-v2new.png"
          />
        </div>
      </b-col>
      <!-- /Left Text-->

      <!-- Login-->
      <b-col
        lg="4"
        class="d-flex align-items-center auth-bg px-2 p-lg-5"
      >
        <b-col
          sm="8"
          md="6"
          lg="12"
          class="px-xl-2 mx-auto"
        >
          <b-card-title
            title-tag="h2"
            class="font-weight-bold mb-1"
          >
            Selamat Datang di Sahabat Dispendik
          </b-card-title>
          <b-card-text class="mb-2">
            Silakan Login dengan akun anda 
          </b-card-text>

          <!-- form -->
          <validation-observer ref="loginValidation">
            <b-form
              class="auth-login-form mt-2"
              @submit.prevent
            >
              <!-- email -->
              <b-form-group
                label="User ID"
                label-for="login-email"
              >
                <validation-provider
                  #default="{ errors }"
                  name="userId"
                >
                  <b-form-input
                    id="login-user-id"
                    v-model="loginFormData.user_id"
                    :state="errors.length > 0 ? false:null"
                    name="login-user-id"
                    placeholder="User ID"
                  />
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <!-- forgot password -->
              <b-form-group>
                <div class="d-flex justify-content-between">
                  <label for="login-password">Password</label>
                </div>
                <validation-provider
                  #default="{ errors }"
                  name="Password"
                  rules="required"
                >
                  <b-input-group
                    class="input-group-merge"
                    :class="errors.length > 0 ? 'is-invalid':null"
                  >
                    <b-form-input
                      id="login-password"
                      v-model="loginFormData.password"
                      :state="errors.length > 0 ? false:null"
                      class="form-control-merge"
                      :type="passwordFieldType"
                      name="login-password"
                      placeholder="············"
                    />
                    <b-input-group-append is-text>
                      <feather-icon
                        class="cursor-pointer"
                        :icon="passwordToggleIcon"
                        @click="togglePasswordVisibility"
                      />
                    </b-input-group-append>
                  </b-input-group>
                  <small class="text-danger">{{ errors[0] }}</small>
                </validation-provider>
              </b-form-group>

              <!-- submit buttons -->
              <b-overlay 
                    :show="this.overlay"
                    spinner-variant="primary"
                    spinner-type="grow"
                    spinner-small
                    rounded="sm"
              >
                  <b-button
                    type="submit"
                    variant="primary"
                    block
                    @click="login"
                  >
                    Login
                  </b-button>
              </b-overlay>
            </b-form>
          </validation-observer>

          <!-- divider -->
          <!-- <div class="divider my-2">
            <div class="divider-text">
              atau Login ke Tahun Anggaran Lain
            </div>
          </div> -->

          <div class="d-flex align-items-stretch">
            <!-- <div class="divider-text"> -->
                <!-- <b-dropdown
                id="dropdown"
                v-ripple.400="'rgba(255, 255, 255, 0.15)'"
                center
                text="Pilih Tahun Anggaran"
                variant="warning"
                class="mx-auto"
              >
                  <b-dropdown-item href="https://sipks.dispendik.surabaya.go.id/budget2013/" target="blank">
                    2013
                  </b-dropdown-item>
                  <b-dropdown-item href="https://sipks.dispendik.surabaya.go.id/budget2014/" target="blank">
                    2014
                  </b-dropdown-item>
                  <b-dropdown-item href="https://sipks.dispendik.surabaya.go.id/budget2015" target="blank">
                    2015
                  </b-dropdown-item>
                  <b-dropdown-item href="https://sipks.dispendik.surabaya.go.id/budget2016" target="blank">
                    2016
                  </b-dropdown-item>
                  <b-dropdown-item href="https://sipks.dispendik.surabaya.go.id/budget2017" target="blank">
                    2017
                  </b-dropdown-item>
                  <b-dropdown-item href="https://sipks.dispendik.surabaya.go.id/budget2018" target="blank">
                    2018
                  </b-dropdown-item>
                  <b-dropdown-item href="https://sipks.dispendik.surabaya.go.id/budget2019" target="blank">
                    2019
                  </b-dropdown-item>
                  <b-dropdown-item href="https://sipks.dispendik.surabaya.go.id/budget2020" target="blank">
                    2020
                  </b-dropdown-item>
                  
              </b-dropdown> -->
            <!-- </div> -->
          </div>
        <div class="divider my-2">
            
        </div>
        <footer class="the-footer flex-wrap justify-between" :class="classes">
            <span>COPYRIGHT &copy; {{ new Date().getFullYear() }}
              <a href="https://dispendik.surabaya.go.id/" target="_blank" rel="nofollow">
                Dispendik Kota Surabaya</a>, All rights Reserved
            </span>
        </footer>

          
        </b-col>
      </b-col>
    <!-- /Login-->
    </b-row>
  </div>
</template>

<script>
/* eslint-disable global-require */
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import VuexyLogo from '@core/layouts/components/Logo.vue'
import {
  BOverlay, BRow, BCol, BLink, BFormGroup, BFormInput, BInputGroupAppend, BInputGroup, BFormCheckbox, BCardText, BCardTitle, BImg, BForm, BButton, BDropdown, BDropdownItem
} from 'bootstrap-vue'
import { required } from '@validations'
import { togglePasswordVisibility } from '@core/mixins/ui/forms'
import store from '@/store/index'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import Ripple from 'vue-ripple-directive'
import axios from 'axios'

export default {
  components: {
    BOverlay,
    BRow,
    BCol,
    BLink,
    BFormGroup,
    BFormInput,
    BInputGroupAppend,
    BInputGroup,
    BFormCheckbox,
    BCardText,
    BCardTitle,
    BImg,
    BForm,
    BButton,
    VuexyLogo,
    ValidationProvider,
    ValidationObserver,
    BDropdown,
    BDropdownItem,
    axios,
  },
  mixins: [togglePasswordVisibility],

  created(){
    if (this.$store.getters.isAuth) {
            this.$router.push({ name: 'choose' })
        }
  },

  data() {
    return {
      loginFormData:{
        user_id: '',
        password: '',
      },
      
      sideImg: require('@/assets/images/pages/login-v2.svg'),
      // validation rulesimport store from '@/store/index'
      required,
      overlay: false
    }
  },
  computed: {
    passwordToggleIcon() {
      return this.passwordFieldType === 'password' ? 'EyeIcon' : 'EyeOffIcon'
    },
    imgUrl() {
      if (store.state.appConfig.layout.skin === 'dark') {
        // eslint-disable-next-line vue/no-side-effects-in-computed-properties
        this.sideImg = require('@/assets/images/pages/login-v2.svg')
        return this.sideImg
      }
      return this.sideImg
    },
  },
  methods: {
    validationForm() {
      this.$refs.loginValidation.validate().then(success => {
        if (success) {
          this.$toast({
            component: ToastificationContent,
            props: {
              title: 'Form Submitted',
              icon: 'EditIcon',
              variant: 'success',
            },
            
          })
        }
      })
    },

    login(){
      this.overlay = true
      axios.post('/api/auth', this.loginFormData)
            .then(response => {
              const resMessage = response.data.message;
              const accesToken = response.data.data.token;
              const responseData = response.data.data;

              if(resMessage == 'success'){
                this.$toast({
                  component: ToastificationContent,
                  props: {
                    title: 'Login Berhasil !!',
                    icon: 'CheckIcon',
                    variant: 'success',
                  },
                  
                });
                this.overlay = false;
                localStorage.setItem('userData', JSON.stringify(responseData));
                localStorage.setItem('accessToken', accesToken);
                if(responseData.posisi == 1){
                  this.$router.push({name: 'choose'});
                }else{
                  this.$router.push({name: 'home'});
                }
                
              }if(resMessage == 'Not Found'){
                this.$toast({
                  component: ToastificationContent,
                  props: {
                    title: 'User ID atau Password yang anda masukkan salah',
                    icon: 'XOctagonIcon',
                    variant: 'danger',
                  },
                  
                });
                this.overlay = false;
              }
            })
    }
  },
  directives: {
    Ripple,
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/pages/page-auth.scss';
</style>
