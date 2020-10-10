import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)

import Dashboard from '../components/Dashboard/Dashboard'
import MemberTable from '../components/MemberTable'
import EventTable from '../components/EventTable'
import newEventForm from '../components/newEventForm'
import Login from '../components/Login/Login'
import ProductTable from '../components/ProductTable'
import ProductDetailForm from '../components/ProductDetailForm'
import OrderTable from '../components/OrderTable'
import TransTable from '../components/TransTable'
import SendMoneyTable from '../components/SendMoneyTable'
import LocationTable from '../components/Location/LocationTable'

import associationMemberTable from '../components/associationComponents/AssociationMemberTable'

const routes = [
    {path:'/dashboard', component: Dashboard},
    {path:'/member', component: MemberTable},
    {path:'/event', component: EventTable},
    {path:'/login',name:'login',component: Login,props:true},
    {path:'/newEvent',component:newEventForm},
    {path:'/editEvent/:event_slug',component:newEventForm,props:true},
    {path:'/product', component: ProductTable},
    {path:'/productForm', component: ProductDetailForm},
    {path:'/productForm/:product_slug', component: ProductDetailForm,props:true},
    {path:'/order', component: OrderTable},
    {path:'/transaction', component: TransTable},
    {path:'/sendMoney', component: SendMoneyTable},
    {path:'/location', component: LocationTable},
    //
    {path:'/association/member', component: associationMemberTable},
]


const router = new VueRouter({
    routes,//short for routes:routes,
    hashbang:false,
    mode:'history',
})

export default router
