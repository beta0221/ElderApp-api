import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)

import SupplierOrders from '../supplierComponents/SupplierOrders'
import SupplierLogin from '../supplierComponents/SupplierLogin'


const routes = [
    {path:'/supplier/supplierOrders',name:'supplierOrders',component: SupplierOrders},
    {path:'/supplier/supplierLogin',name:'supplierLogin',component: SupplierLogin},
    {path:'/supplier/admin',name:'supplierLogin',component: SupplierLogin},
]


const router = new VueRouter({
    routes,//short for routes:routes,
    hashbang:false,
    mode:'history',
})

export default router
