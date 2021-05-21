<template>
  
</template>

<script>
import { BCard, BCardText, BLink, BButton } from 'bootstrap-vue'

export default {
  components: {
    BCard,
    BCardText,
    BLink,
    BButton
  },

  data(){
    return{
      userData: JSON.parse(localStorage.getItem('userData')),

    }
  }
}
</script>
