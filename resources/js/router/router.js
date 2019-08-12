import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)


import MemberTable from '../components/MemberTable'
import Login from '../components/Login/Login'
const routes = [
    {path:'/member', component: MemberTable},
    {path:'/login',name:'login',component: Login},
]


const router = new VueRouter({
    routes,//short for routes:routes,
    hashbang:false,
    mode:'history',
})

export default router
