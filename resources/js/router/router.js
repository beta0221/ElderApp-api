import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)

import Dashboard from '../components/Dashboard/Dashboard'
import MemberTable from '../components/MemberTable'
import EventPage from '../components/EventPage'
import newEventForm from '../components/newEventForm'
import Login from '../components/Login/Login'
import ProductTable from '../components/ProductTable'
import ProductDetailForm from '../components/ProductDetailForm'
import OrderTable from '../components/OrderTable'
import TransTable from '../components/TransTable'
import SendMoneyTable from '../components/SendMoneyTable'
import LocationTable from '../components/Location/LocationTable'
import InsurancePage from '../components/Insurance/InsurancePage'
import ClinicPage from '../components/Clinic/ClinicPage'

import associationMemberTable from '../components/associationComponents/AssociationMemberTable'
import associationEventPage from '../components/associationComponents/AssociationEventPage'


const routes = [
    {path:'/dashboard', component: Dashboard},
    {path:'/member', component: MemberTable},
    {path:'/event', component: EventPage},
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
    {path:'/insurance', component: InsurancePage},
    {path:'/clinic', component: ClinicPage},
    //
    {path:'/association/admin', component: associationMemberTable},
    {path:'/association/member', component: associationMemberTable},
    {path:'/association/event', component: associationEventPage}
]


const router = new VueRouter({
    routes,//short for routes:routes,
    hashbang:false,
    mode:'history',
})

export default router
