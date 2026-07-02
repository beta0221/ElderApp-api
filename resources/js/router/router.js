import Vue from 'vue'
import VueRouter from 'vue-router'
import Token from '../Helpers/Token'
import AppStorage from '../Helpers/AppStorage'

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
import ClinicLogPage from '../components/Clinic/ClinicLogPage'

import associationMemberTable from '../components/associationComponents/AssociationMemberTable'
import associationEventPage from '../components/associationComponents/AssociationEventPage'


const routes = [
    {path:'/dashboard', component: Dashboard, meta:{requiresAuth:true}},
    {path:'/member', component: MemberTable, meta:{requiresAuth:true}},
    {path:'/event', component: EventPage, meta:{requiresAuth:true}},
    {path:'/login', name:'login', component: Login, props:true},
    {path:'/newEvent', component:newEventForm, meta:{requiresAuth:true}},
    {path:'/editEvent/:event_slug', component:newEventForm, props:true, meta:{requiresAuth:true}},
    {path:'/product', component: ProductTable, meta:{requiresAuth:true}},
    {path:'/productForm', component: ProductDetailForm, meta:{requiresAuth:true}},
    {path:'/productForm/:product_slug', component: ProductDetailForm, props:true, meta:{requiresAuth:true}},
    {path:'/order', component: OrderTable, meta:{requiresAuth:true}},
    {path:'/transaction', component: TransTable, meta:{requiresAuth:true}},
    {path:'/sendMoney', component: SendMoneyTable, meta:{requiresAuth:true}},
    {path:'/location', component: LocationTable, meta:{requiresAuth:true}},
    {path:'/insurance', component: InsurancePage, meta:{requiresAuth:true}},
    {path:'/clinic', component: ClinicPage, meta:{requiresAuth:true}},
    {path:'/clinicLog', component: ClinicLogPage, meta:{requiresAuth:true}},
    {path:'/association/admin', component: associationMemberTable, meta:{requiresAuth:true}},
    {path:'/association/member', component: associationMemberTable, meta:{requiresAuth:true}},
    {path:'/association/event', component: associationEventPage, meta:{requiresAuth:true}},
    // 所有未匹配路由（含 /app 入口）導向 /member，由守衛接手驗證
    {path:'*', redirect:'/member'},
]


const router = new VueRouter({
    routes,
    hashbang:false,
    mode:'history',
})

function isLoggedIn() {
    const token = AppStorage.getToken()
    return token && Token.isValid(token)
}

router.beforeEach((to, from, next) => {
    if (!to.meta.requiresAuth) {
        // 已登入卻訪問 /login，直接進 /member
        if (to.name === 'login' && isLoggedIn()) {
            return next({ path: '/member' })
        }
        return next()
    }

    if (!isLoggedIn()) {
        return next({ name: 'login', params: { from_url: to.fullPath } })
    }

    next()
})

export default router
