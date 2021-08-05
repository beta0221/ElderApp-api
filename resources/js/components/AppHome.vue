<template>
    <div>
        <div class="side-bar">
            <sidebar></sidebar>      
        </div>
        <div class="content">
            <router-view></router-view>
        </div>  
        
    </div>

</template>

<script>
import sidebar from './SideBar'
// import login from "./Login/Login"

export default {
    components:{ 
        sidebar,
        // login
    }
}
</script>

<style>
.side-bar{
    position: absolute;
    left: 0;
    top :0;
    width: 200px;
    height: 100%;
}
.content{
    position: absolute;
    left: 200px;
    top: 0;
    height: 100%;
    width: calc(100% - 200px);
    overflow-y: scroll;
}
</style>
