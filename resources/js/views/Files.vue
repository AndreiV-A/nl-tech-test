<template>
    <div class="container">
		<div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
						<div>Files</div>
						<div v-if="loading">wait...</div>
						<div v-if="error" class="badge badge-danger p-1">{{error}}</div>
					</div>

                    <div class="card-body">
						<input type="file" id="file" ref="file" v-on:change="captureFileUpload()"/>
        				<button @click.prevent="uploadFile()">Upload</button>

						<hr>

                        <ul v-if="files">
							<li v-for="({ id, box_id }, index)  in files" :key="id">
								{{ id }} | BOXId: {{ box_id }}
								<button @click.prevent="deleteFile(id, index)">delete</button>
							</li>
						</ul>
						<div v-else>
							no files here
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
				error: null,
				file_to_upload: null,
				files: null,
			};
		},

		created() {
			this.fetchFiles();
		},

		methods: {

			captureFileUpload(){
				this.file_to_upload = this.$refs.file.files[0];
				console.log('this.file_to_upload:', this.file_to_upload)
			},

			uploadFile(){
				let formData = new FormData();
				formData.append('file', this.file_to_upload);
				axios.post( 'api/boxfiles', formData, {
					headers: {
						Authorization: `Bearer ${this.$store.state.token}`,
						'Content-Type': 'multipart/form-data'
					}
  				}).then( res => {
  					console.log('res:', res);
					this.files.push(res.data);
				}).catch( err => {
  					console.log('err:', err);
				});
			},

			deleteFile( id, index ){
				console.log('deleteFile:', id, index);
				const held = this.files.splice(index, 1)[0];
				axios.delete(`api/boxfiles/${id}`, {
					headers: {
						Accept: 'application/json',
						Authorization: `Bearer ${this.$store.state.token}`,
					},
				})
					.then( res => {
						console.log('res:', res);
						// this.files.splice( this.files.findIndex( item => item.id == id ), 1);
					})
					.catch( err => {
						console.log('err:', err);
						this.files.splice(index, 0, held);
						this.error = err.response.data.message || 'There was an issue deleting the file.';
					})
					.then(() => this.loading = false)
			},

			fetchFiles() {
				this.error = this.users = null;
				this.loading = true;

				axios.get('/api/boxfiles', {
					headers: {
						Accept: 'application/json',
						Authorization: `Bearer ${this.$store.state.token}`,
					},
				})
					.then(res => {
						console.log('res:', res);
            			this.files = res.data.data;
						console.log('arr:', res.data.data);
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