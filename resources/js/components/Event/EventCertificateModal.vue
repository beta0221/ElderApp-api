<template>
    <v-dialog v-model="dialog" max-width="480px">
        <v-card style="padding:0 12px">
            <v-card-title class="headline">設定結業證書</v-card-title>

            <div class="mb-2">
                <img v-if="(certificate.image)?true:false" :src="certificate.image"/>
            </div>

            <div class="mb-2">
                <input style="display:none;" type="file" id="file" ref="file" v-on:change="onChangeFileUpload()"/>
                <v-btn color="success" @click="$refs.file.click()">上傳證書圖片</v-btn>
            </div>

            <div>
                <label for="">結業獎勵</label>
                <v-text-field label="Solo" placeholder="畢業獎勵" solo v-model="certificate.reward"></v-text-field>
            </div>

            <v-card-actions>

            <v-spacer></v-spacer>
                
                <v-btn color="gray darken-1" flat="flat" @click="dialog = false">關閉</v-btn>
                <v-btn color="green" class="white--text" @click="submit">提交</v-btn>
            </v-card-actions>

        </v-card>
    </v-dialog>
</template>

<script>
export default {
    mounted() {
        EventBus.$on("showEventCertificateModal", (data) => {
            this.dialog = true;
            this.slug = data.slug;
            this.showCertificate();
        });
    },
    destroyed(){
        EventBus.$off('showEventCertificateModal');
    },
    data(){
        return{
            dialog: false,
            edit_mode: false,
            slug:'',
            certificate:{
                reward:null,
                image:null,
            },
            input_file:null,
        }
    },
    methods:{
        showCertificate(){
            this.certificate.reward = null;
            this.certificate.image = null;
            axios.get(`/api/event/${this.slug}/certificate`)
            .then(res => {
                if(res.data == 'no certificate'){
                    this.edit_mode = false;
                    return;
                }
                this.edit_mode = true;
                this.certificate.reward = res.data.reward;
                this.certificate.image = res.data.image;
            })
            .catch(error => {
                Exception.handle(error);
            })
        },
        onChangeFileUpload(){
            this.input_file = this.$refs.file.files[0];
            this.certificate.image = URL.createObjectURL(this.$refs.file.files[0]);
        },
        submit(){
            let requestUrl = `/api/event/${this.slug}/certificate`;
            if(this.edit_mode == true){
                requestUrl = `/api/event/${this.slug}/certificate/update`;
            }
            let formData = new FormData();
            formData.append('image',this.input_file);
            formData.append('reward',this.certificate.reward);
            axios.post(requestUrl, formData)
            .then(res => {
                this.showCertificate();
            })
            .catch(error => {
                Exception.handle(error);
            })
        },
    }
}
</script>

<style>

</style>