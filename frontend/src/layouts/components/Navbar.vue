<template>
  <div class="navbar-container d-flex content align-items-center">

    <!-- Nav Menu Toggler -->
    <ul class="nav navbar-nav d-xl-none">
      <li class="nav-item">
        <b-link
          class="nav-link"
          @click="toggleVerticalMenuActive"
        >
          <feather-icon
            icon="MenuIcon"
            size="21"
          />
        </b-link>
      </li>
    </ul>

    <!-- Left Col -->
    <div class="bookmark-wrapper align-items-center flex-grow-1 d-none d-lg-flex">
      <!-- <dark-Toggler class="d-none d-lg-block" /> -->
    </div>

    <b-navbar-nav class="nav align-items-center ml-auto">
      <b-nav-item-dropdown
        right
        toggle-class="d-flex align-items-center dropdown-user-link"
        class="dropdown-user"
      >
        <template #button-content>
          <div class="d-sm-flex d-none user-nav">
            <p class="user-name font-weight-bolder mb-0">
              {{userData.username}}
            </p>
            <!-- <span class="user-status">{{display.user_id}}</span> -->
            <span v-if="userData.posisi == 1" class="user-status">Sumber Dana: <b-badge variant="primary">{{display.sumber_dana}}</b-badge></span>
          </div>

          <b-avatar
            size="40"
            variant="light-primary"
            badge
            class="badge-minimal"
            badge-variant="success"
          />
        </template>

        <b-dropdown-item to='/setting_profil' link-class="d-flex align-items-center">
          <feather-icon
            size="16"
            icon="UserIcon"
            class="mr-50"
          />
          <span>Profil</span>
        </b-dropdown-item>

        <!-- <b-dropdown-item link-class="d-flex align-items-center">
          <feather-icon
            size="16"
            icon="MailIcon"
            class="mr-50"
          />
          <span>Inbox</span>
        </b-dropdown-item>

        <b-dropdown-item link-class="d-flex align-items-center">
          <feather-icon
            size="16"
            icon="CheckSquareIcon"
            class="mr-50"
          />
          <span>Task</span>
        </b-dropdown-item> -->

        <b-dropdown-item to="/choose" link-class="d-flex align-items-center">
          <feather-icon
            size="16"
            icon="DollarSignIcon"
            class="mr-50"
          />
          <span>Sumber Dana</span>
        </b-dropdown-item>

        <b-dropdown-divider />

        <b-dropdown-item @click="logout" link-class="d-flex align-items-center">
          <feather-icon
            size="16"
            icon="LogOutIcon"
            class="mr-50"
          />
          <span>Logout</span>
        </b-dropdown-item>
      </b-nav-item-dropdown>
    </b-navbar-nav>
  </div>
</template>

<script>
import {
  BLink, BNavbarNav, BNavItemDropdown, BDropdownItem, BDropdownDivider, BAvatar, BBadge
} from 'bootstrap-vue'

export default {
  components: {
    BLink,
    BNavbarNav,
    BNavItemDropdown,
    BDropdownItem,
    BDropdownDivider,
    BAvatar,
    BBadge
  },
  props: {
    toggleVerticalMenuActive: {
      type: Function,
      default: () => {},
    },
  },

  data(){
    return{
      userData: JSON.parse(localStorage.getItem('userData')),
      display:{
          sumber_dana: localStorage.getItem('sumber')
      }
      
    }
  },

  methods:{
    logout(){
      localStorage.removeItem('accessToken')
      // localStorage.removeItem(useJwt.jwtConfig.storageRefreshTokenKeyName)

      // Remove userData from localStorage
      localStorage.removeItem('userData')
      localStorage.removeItem('sumber')
      localStorage.removeItem('kegiatan')

      // Reset ability
      // this.$ability.update(initialAbility)

      // Redirect to login page
      this.$router.push({ name: 'login' })
    }

  }
}
</script>
