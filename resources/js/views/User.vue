<template>
	<div class="container">
		<div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
						<div>User detail</div>
	  					<div v-if="loading">wait...</div>

						<div v-if="error" class="error">
							<div class="badge badge-danger p-1">
								{{ error }}
							</div>
							
							<button @click.prevent="fetchData" class="btn btn-primary btn-sm">
								Try Again
							</button>
						</div>
					</div>

                    <div class="card-body">
						<strong>Name: </strong>{{ user.name }}<br>
						<strong>Email: </strong>{{ user.email }}
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
				error: null,
				user: {
					name: null,
					email: null
				}
			};
		},

		created() {
			this.error = null;
			this.loading = true;

			axios.get(`/api/users/${this.$route.params.id}`, {
					headers: {
						Accept: 'application/json',
						Authorization: `Bearer ${this.$store.state.token}`,
					},
				})
				.then( res => {
					console.log('res:', res);
					this.user = res.data.data;
				})
				.catch( err => {
					console.log('err:', err);
					this.error = err.response.data.message || err.message;
				})
				.then(() => this.loading = false)
		}
	};
</script>