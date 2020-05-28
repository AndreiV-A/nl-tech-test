import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'
Vue.use(Vuex)

export default new Vuex.Store({
	state: {
		token: localStorage.getItem('access_token') || null,
		username: localStorage.getItem('username') || null,
	},
	
	getters: {
		loggedIn(state){
			return state.token !== null
		}
	},
	
	mutations: {
		storeToken(state, token){
			state.token = token;
		},
		storeUsername(state, username){
			// console.log('storeUsername:', username);
			state.username = username;
		},
		deleteToken(state){
			state.token = null;
		},
		deleteUsername(state){
			state.username = null;
		},
	},
	
	actions: {
		retrieveToken(context, credentials){
			return new Promise((resolve, reject) => {
				axios.post('/api/login', {
					email: credentials.email,
					password: credentials.password,
				})				
				.then(res => {
					// console.log('login.res:', res);
					const token = res.data.access_token
					localStorage.setItem('access_token', token)
					context.commit('storeToken', token)
					localStorage.setItem('username', credentials.email)
					context.commit('storeUsername', credentials.email);
					resolve(res)
				})
				.catch(error => {
					//console.log(error)
					reject(error)
				})
			})
		},
		destroyToken(context){
			if (context.getters.loggedIn){
				return new Promise((resolve, reject) => {
					axios.post('/api/logout', '', {
						headers: { Authorization: "Bearer " + context.state.token }
					})
					.then(res => {
						// console.log(res)
						localStorage.removeItem('access_token')
						context.commit('deleteToken')
						localStorage.removeItem('username')
						context.commit('deleteUsername');
						resolve(res)
					})
					.catch(error => {
						// console.log(error)
						localStorage.removeItem('access_token')
						context.commit('deleteToken')
						localStorage.removeItem('username')
						context.commit('deleteUsername');
						reject(error)
					})
				})
			}
		}
	}
})