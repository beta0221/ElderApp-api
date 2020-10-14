<template>
  
  <div>
        <div>
            <router-link class="white--text" to="/newEvent" style="text-decoration:none;">
                <v-btn color="success">新增活動</v-btn>
            </router-link>
        </div>
        <event-table></event-table>
  </div>
  
</template>

<script>
import EventTable from './EventTable'
export default {
    components:{
        EventTable
    }
}
</script>

<style>

</style>