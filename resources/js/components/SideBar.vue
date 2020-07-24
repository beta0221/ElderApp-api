<template>
  <v-navigation-drawer permanent>
    <v-toolbar flat>
      <v-list>
        <v-list-tile>
          <v-list-tile-title class="title">
            銀髮學院
          </v-list-tile-title>
        </v-list-tile>
      </v-list>
    </v-toolbar>

    <v-divider></v-divider>

    <v-list dense class="pt-0">
      <v-list-tile
        v-for="item in items"
        :key="item.title">
        
        <v-list-tile-action>
          <v-icon>{{ item.icon }}</v-icon>
        </v-list-tile-action>

        <v-list-tile-content>
          
            <v-list-tile-title>
              <router-link :to='item.url' class="grey--text " style="text-decoration:none;">
                {{ item.title }}
              </router-link>
            </v-list-tile-title>
          
        </v-list-tile-content>
        
      </v-list-tile>
    </v-list>
  </v-navigation-drawer>
</template>

<script>
  export default {
    data () {
      return {
        items: [
          { title: 'Home', icon: 'dashboard' ,url:'/'},
          { title: '會員管理', icon: 'account_box',url:'/member' },
          { title: '活動管理', icon: 'room',url:'/event' },
          { title: '產品管理', icon: 'photo_filter',url:'/product' },
          { title: '訂單管理', icon: 'description',url:'/order' },
          { title: '交易紀錄', icon: 'history',url:'/transaction' },
          { title: '發送樂幣', icon: 'monetization_on',url:'/sendMoney' },
        ],
        right: null
      }
    }
  }
</script>

<style>
  
</style>
