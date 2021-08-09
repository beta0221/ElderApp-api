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
                    <span>生日:{{data.birthdate}}</span>
                </div>

                <div class="data-row">
                    <span>狀態:{{statusDict[data.status]}}</span>
                </div>

            </div>

            <v-card-actions>
                <v-btn @click="voidInsurance" color="red">作廢</v-btn>
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
            statusDict:{
                'pending':'待處理',
                'processing':'處理中',
                'verified':'已核帳',
                'close':'完成',
                'void':'作廢',
            },
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
        },
        voidInsurance(){
            axios.post(`/api/insurance/${this.id}/void`)
            .then(res => {
                this.getData();
                EventBus.$emit('reloadData');
            })
            .catch(err => {
                Exception.handle(error);
            })
        }
    }
}
</script>

<style>

</style>