<template>
<div>
    <v-dialog v-model="show" max-width="480px">
        <v-card>
            <v-card-title class="headline">服務記錄：<span v-if="clinic != null">{{clinic.name}}</span></v-card-title>

            <div style="padding:12px 24px">

                <simple-pagination :total="total" :page="pagination.page" :rows="10" v-on:nav_to_page="setPage"></simple-pagination>

                <div v-for="(log,i) in logs" v-bind:key="i">
                    <span>{{log.user_name}}{{(log.is_complete==1)?'（完成）':''}}</span>
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
        EventBus.$on('showClinicLogs',data =>{
            this.clinic_id = data.clinic_id;
            this.getClinicUserLogs();
            this.show = true;
        });
    },
    destroyed(){
        EventBus.$off('showClinicLogs');
    },
    data(){
        return{
            show:false,
            clinic_id:null,
            logs:[],
            clinic:null,
            //
            total:0,
            pagination:{
                page:1,
                descending:true
            },
        }
    },
    methods:{
        getClinicUserLogs(){
            let params = Object.assign({},this.pagination);
            axios.get(`/api/clinic/${this.clinic_id}/log`,{
                params
            })
            .then(res => {
                this.logs = res.data.logs;
                this.total = res.data.total;
                this.clinic = res.data.clinic;
            })
            .catch(err => {
                Exception.handle(err);
            })
        },
        setPage(page){
            this.pagination.page = page;
            this.getClinicUserLogs();
        },
    }
}
</script>

<style>

</style>