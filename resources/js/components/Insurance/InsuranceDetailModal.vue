<template>
    <v-dialog v-model="show" max-width="480px">
        <v-card>

            <v-card-title class="headline">
                申請表
            </v-card-title>

            <div style="padding:8px 12px">
                
                <div class="data-row">
                    <span>身分證:{{data.identity_number}}</span>
                </div>

                <div class="data-row">
                    <span>聯絡方式:{{data.phone}}</span>
                </div>

                <div class="data-row">
                    <span>狀態:{{data.status}}</span>
                </div>

            </div>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn @click="show = false">關閉</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    destroyed(){
        EventBus.$off('showDetailModal');
    },
    mounted(){
        EventBus.$on('showDetailModal',item => {
            this.id = item.id;
            this.getData();
            this.show = true;
        });
    },
    data() {
        return {
            id:null,
            data:{},
            show:false,  
        };
    },
    methods:{
        getData(){
            axios.get('/api/insurance/'+this.id)
            .then(res => { 
                this.data = res.data;
            })
            .catch(error => {
                Exception.handle(error);
            })
        }
    }
}
</script>

<style>

</style>