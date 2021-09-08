<template>
<div>
    <v-dialog v-model="show" max-width="480px">
        <v-card>
            <v-card-title class="headline">服務記錄：<span v-if="user != null">{{user.name}}</span></v-card-title>

            <div style="padding:12px 24px">

                <simple-pagination :total="total" :page="pagination.page" :rows="10" v-on:nav_to_page="setPage"></simple-pagination>

                <div v-for="(log,i) in logs" v-bind:key="i">
                    <span>{{log.clinic_name}}{{(log.is_complete==1)?'（完成）':''}}</span>
                    <span v-if="log.total_hours!=null">時數：{{log.total_hours}}</span><br>
                    <span>{{log.created_at}}~{{log.complete_at}}</span>
                </div>

            </div>


            <v-card-actions>
                <v-spacer></v-spacer>

                <v-btn color="gray" flat="flat" @click="show = false">關閉</v-btn>
            </v-card-actions>

        </v-card>

    </v-dialog>
</div>
</template>

<script>
import SimplePagination from "../simplePagination.vue"
export default {
    components:{
        SimplePagination,
    },
    mounted(){
        EventBus.$on('showUserLogs',data =>{
            this.user_id = data.user_id;
            this.getUserLogs();
            this.show = true;
        });
    },
    destroyed(){
        EventBus.$off('showUserLogs');
    },
    data(){
        return{
            show:false,
            user_id:null,
            logs:[],
            user:null,
            //
            total:0,
            pagination:{
                page:1,
                descending:true
            },
        }
    },
    methods:{
        getUserLogs(){
            let params = Object.assign({},this.pagination);
            axios.get(`/api/clinic/user/${this.user_id}/log`,{
                params
            })
            .then(res => {
                this.logs = res.data.logs;
                this.total = res.data.total;
                this.user = res.data.user;
            })
            .catch(err => {
                Exception.handle(err);
            })
        },
        setPage(page){
            this.pagination.page = page;
            this.getUserLogs();
        },
    }
}
</script>

<style>

</style>