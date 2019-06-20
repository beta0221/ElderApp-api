import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)


import MemberTable from '../components/MemberTable'
const routes = [
    {path:'/member', component: MemberTable},
]


const router = new VueRouter({
    routes,
    hashbang:false,
    mode:'history',
})

export default router
