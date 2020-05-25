import Vue from 'vue'
import VueRouter from 'vue-router'
Vue.use(VueRouter)
import store from './store'

import Home from './views/Home';
import Users from './views/Users';
import User from './views/User';
import Login from './views/Login';
import Logout from './views/Logout';
import Register from './views/Register';

const router = new VueRouter({
    mode: 'history',
	
	// routes,

    routes: [
        {
            path: '/',
            name: 'home',
            component: Home
        },
		{
            path: '/users',
            name: 'users',
            component: Users,
        },
		{
            path: '/user/:id',
            name: 'user.view',
            component: User,
        },
		{
            path: '/login',
            name: 'login',
            component: Login,
        },
		{
            path: '/logout',
            name: 'logout',
            component: Logout,
        },
		{
            path: '/register',
            name: 'register',
            component: Register,
        },
    ],
});
import App from './views/App';

new Vue({
    el: '#app',
    router,
	store,
	...App
});
