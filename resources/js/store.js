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
					username: credentials.username,
					password: credentials.password,
				})				
				.then(response => {
					//console.log(response)
					const token = response.data.access_token
					localStorage.setItem('access_token', token)
					context.commit('storeToken', token)
					localStorage.setItem('username', credentials.username)
					context.commit('storeUsername', credentials.username);
					resolve(response)
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
					.then(response => {
						// console.log(response)
						localStorage.removeItem('access_token')
						context.commit('deleteToken')
						localStorage.removeItem('username')
						context.commit('deleteUsername');
						resolve(response)
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