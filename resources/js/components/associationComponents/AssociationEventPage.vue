<template>
    <div>
        <div class="side-bar">
            <side-bar></side-bar>
        </div>
        <div class="content">
            <div>
                <router-link class="white--text" to="/newEvent" style="text-decoration:none;">
                    <v-btn color="success">新增活動</v-btn>
                </router-link>
            </div>
            <div>
                <event-table></event-table>
            </div>
            
        </div>    
    </div>
</template>

<script>
import EventTable from '../EventTable'
import sideBar from './associationSidebar'
export default {
    components:{
        EventTable,
        sideBar
    }
}
</script>

<style>

</style>