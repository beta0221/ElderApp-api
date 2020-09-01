<template>
    <div>
      <!-- <link rel="stylesheet"
      href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt"
      crossorigin="anonymous"> -->
      
            
            
        <router-view></router-view>
      
    </div>

</template>

<script>

import supplierLogin from './SupplierLogin';

export default {
    components:{
       
    },
    created() {
        this.$router.push({name:'supplierLogin'});
    },
}
</script>

<style>

.side-bar{
    position: absolute;
    left: 0;
    top :0;
    width: 150px;
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
