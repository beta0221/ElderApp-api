<template>
    <div>
        <div class="side-bar">
            <side-bar></side-bar>
        </div>
        <div class="content">
            <event-table></event-table>
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