<template>
    <div class="container">
		<div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
        				<div>Register</div>
						<div v-if="loading">wait...</div>
						<div v-if="error" class="badge badge-danger p-1">{{error}}</div>
					</div>

                    <div class="card-body">
						<form @submit.prevent="onSubmit($event)">
							<div class="form-group">
								<label for="user_email">Email</label>
								<input id="user_email" type="email" v-model="user.email" class="w-100" />
							</div>
							<div class="form-group">
								<label for="user_password">Password</label>
								<input id="user_password" type="password" v-model="user.password" class="w-100" />
							</div>
							<div class="form-group">
								<button type="submit" :disabled="loading">Create</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
    </div>
</template>

<script>
	import axios from 'axios';
    export default {
        data() {
            return {
                loading: false,
                error: false,
                user: {
                    email: null,
                    password: null,
                }
            }
        },
        methods: {
            onSubmit($event){
                this.loading = true;
                this.error = false;
				console.log('email:', this.user.email);
				console.log('password:', this.user.password);
				axios.post('api/users', {
					email: this.user.email,
					password: this.user.password,
				})
					.then(res => {
						console.log('res:', res);
						// this.$store.dispatch("retrieveToken", {
						// 	username: this.user.email,
						// 	password: this.user.password,
						// })
						this.$store.commit('storeToken', res.data.access_token)
						this.$store.commit('storeUsername', res.data.user.email);
					})
				// 	.then(res => {
				// 		// this.$router.push({ name: 'home' });
				// 	})
				// 	.catch((err) => {
				// 		console.log('err:', err);
				// 		this.error = err.response.data.message || 'There was an issue creating the user.';
				// 	})
				// 	.then(() => this.loading = false)
			}
		}
	}
</script>

<style lang="scss" scoped>

	$red: lighten(red, 30%);
	$darkRed: darken($red, 50%);

	.form-group {
		margin-bottom: 1em;
		label {
			display: block;
		}
	}
	.alert {
		background: $red;
		color: $darkRed;
		padding: 1rem;
		margin-bottom: 1rem;
		width: 50%;
		border: 1px solid $darkRed;
		border-radius: 5px;
	}
</style>