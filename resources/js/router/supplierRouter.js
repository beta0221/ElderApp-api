import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)

import SupplierOrders from '../supplierComponents/SupplierOrders'
import SupplierLogin from '../supplierComponents/SupplierLogin'
import DashBoard from '../supplierComponents/DashBoard'
import SupplierProducts from '../supplierComponents/SupplierProducts'

const routes = [
    {path:'/supplier/supplierLogin',name:'supplierLogin',component: SupplierLogin},
    {path:'/supplier/admin',name:'dashBoard',component: DashBoard,children:[
        {path:'supplierOrders',name:'supplierOrders',component: SupplierOrders},
        {path:'supplierProducts',name:'supplierProducts',component: SupplierProducts},
    ]},
    
    
]


const router = new VueRouter({
    routes,//short for routes:routes,
    hashbang:false,
    mode:'history',
})

export default router
