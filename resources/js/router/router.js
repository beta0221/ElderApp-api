import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)


import MemberTable from '../components/MemberTable'
import EventTable from '../components/EventTable'
import newEventForm from '../components/newEventForm'
import Login from '../components/Login/Login'
import ProductTable from '../components/ProductTable'
import ProductDetailForm from '../components/ProductDetailForm'


const routes = [
    {path:'/member', component: MemberTable},
    {path:'/event', component: EventTable},
    {path:'/login',name:'login',component: Login,props:true},
    {path:'/newEvent',component:newEventForm},
    {path:'/editEvent/:event_slug',component:newEventForm,props:true},
    {path:'/product', component: ProductTable},
    {path:'/productForm', component: ProductDetailForm},
    {path:'/productForm/:product_slug', component: ProductDetailForm,props:true},
]


const router = new VueRouter({
    routes,//short for routes:routes,
    hashbang:false,
    mode:'history',
})

export default router
