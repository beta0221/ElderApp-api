<template>
    <div>
        <div class="side-bar">
            <side-bar></side-bar>
        </div>
        <div class="content">
            <member-table></member-table>
        </div>    
    </div>
</template>

<script>
import MemberTable from '../MemberTable'
import sideBar from './associationSidebar'
export default {
    components:{
        MemberTable,
        sideBar
    }
}
</script>

<style>

</style>