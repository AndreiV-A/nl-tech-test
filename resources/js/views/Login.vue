<template>
	<div class="container">
		<div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
  						<div>Login</div>
						<div v-if="loading">wait...</div>
						<div v-if="error" class="badge badge-danger p-1">{{error}}</div>
					</div>

                    <div class="card-body">
						<form autocomplete="off" @submit.prevent="login" method="post">

							<div class="form-group">
								<label for="user_email">Email</label>
								<input id="user_email" type="email" v-model="user.email" class="w-100" />
							</div>

							<div class="form-group">
								<label for="user_password">Password</label>
								<input id="user_password" type="password" v-model="user.password" class="w-100" />
							</div>

							<button type="submit" class="button is-primary">Sign in</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		data(){
			return {
				loading: false,
				user: {
                    email: null,
                    password: null,
                },
				error: null
			};
		},
		
		methods: {
			login(){
				this.loading = true;
				this.$store.dispatch("retrieveToken", {
					username: this.user.email,
					password: this.user.password
				})
				.then(res => {
					this.loading = false;
					this.$router.push({ name: 'home' });
				})
				.catch(err => {
					this.loading = false;
					if (err.response.status){
						console.log('err:', err.response);
						switch (err.response.status){
							case 400:
							case 401:
								this.error = 'Wrong email or password or both.';
								break;
							default:
								this.error = 'Something went wrong.';
						}
					} else {
						this.error = 'Something went wrong.';
					}
					
				});
			}
		}
	};
</script>