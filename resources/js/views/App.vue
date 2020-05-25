<template>
	<div>
        <h1>Laravel/Vue(SPA) tech-test</h1>
        <p>
            <router-link :to="{ name: 'home' }">Home</router-link> |
			<router-link :to="{ name: 'users' }">Users</router-link> |

			<span v-if="this.$store.getters.loggedIn">
				<span class="badge badge-success">{{this.$store.state.username}}</span>
				<router-link v-if="this.$store.getters.loggedIn" :to="{ name: 'logout' }">Logout</router-link>
			</span>
			<span v-else>
				<router-link v-if="!this.$store.getters.loggedIn" :to="{ name: 'login' }">Login</router-link> |
				<router-link v-if="!this.$store.getters.loggedIn" :to="{ name: 'register' }">Register</router-link>
			</span>
        </p>

        <router-view></router-view>
    </div>
</template>