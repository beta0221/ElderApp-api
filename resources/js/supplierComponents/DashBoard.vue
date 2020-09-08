<template>
    <div>
        <SupplierSideBar></SupplierSideBar>
        <router-view></router-view>
        
    </div>
</template>
<script>
import SupplierSideBar from "./SupplierSideBar";
export default {
    components:{
        SupplierSideBar,
    },
    created() {
        this.$router.push({name:'supplierOrders'});
    },
}
</script>