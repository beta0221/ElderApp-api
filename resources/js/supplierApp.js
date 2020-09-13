/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');
import Vue from 'vue';
import 'bootstrap';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faUserSecret } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

library.add(faUserSecret);

Vue.component('font-awesome-icon', FontAwesomeIcon);


Vue.config.productionTip = false;

import CKEditor from '@ckeditor/ckeditor5-vue';
Vue.use(CKEditor)
import ClassicEditor from '@ckeditor/ckeditor5-build-classic'
window.ClassicEditor = ClassicEditor

import User from './SupplierHelper/User'
window.User = User

import Exception from './SupplierHelper/Exception'
window.Exception = Exception

window.EventBus = new Vue();
/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

Vue.component('app-home', require('./supplierComponents/AppHome').default);
// Vue.component('app-login',require('./supplierComponents/SupplierLogin').default);
Vue.component('side-bar', require('./supplierComponents/SupplierSideBar').default);
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
import router from './router/supplierRouter.js'
const app = new Vue({
    el: '#app',
    router,
});
