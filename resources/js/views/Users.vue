<template>
    <div class="container">
		<div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
						<div>Users list</div>
						<div v-if="loading">wait...</div>
						<div v-if="error" class="badge badge-danger p-1">{{error}}</div>
					</div>

                    <div class="card-body">

                        <ul v-if="users">
							<li v-for="{ id, name, email }  in users" :key="id">
								{{ name }}
								<router-link :to="{ name: 'user.view', params: { id } }">...</router-link>
							</li>
						</ul>
						<div v-else>
							no users here
						</div>
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
				users: null,
				error: null,
			};
		},

		created() {
			this.fetchData();
		},

		methods: {
			fetchData() {
				this.error = this.users = null;
				this.loading = true;

				axios.get('/api/users', {
					headers: {
						Accept: 'application/json',
						Authorization: `Bearer ${this.$store.state.token}`,
					},
				})

				// api_users.all(this.$store.state.token)
					.then(res => {
						console.log('res:', res);
            			this.users = res.data.data;
					})
					.catch( err => {
						console.log('err:', err);
            			this.error = err.response.data.message || err.message;
					})
					.then(() => this.loading = false);
			}
		}
	}
</script>