<template>
<div>

    <div>
        <h3 style="padding:8px 12px">志工紀錄</h3> 
    </div>

    <div>
        <FilterBar :columns="filterColumns" />
    </div>

    <DataTable ref="DataTable" :dict="dict" :headers="headers" :requestUrl="requestUrl" />
    <UserLogModal/>
    <ClinicLogModal/>
</div>
</template>

<script>
import FilterBar from '../FilterBar'
import UserLogModal from './UserLogModal.vue'
import ClinicLogModal from './ClinicLogModal.vue'

export default {
    components:{
        FilterBar,
        UserLogModal,
        ClinicLogModal,
    },
    data(){
        return{
            requestUrl:"/api/clinic/all/log",
            headers:[
                { text: "#"},
                { text: "姓名", value: "user_name" },
                { text: "-" , btnName:'紀錄',eventName:'showUserLogs',eventParam:['user_id']},
                { text: "診所", value: "clinic_name" },
                { text: "-" , btnName:'診所紀錄',eventName:'showClinicLogs',eventParam:['clinic_id']},
                { text: "時數", value: "total_hours" },
                { text: "日期", value: "created_at" },
                { text: "完成", value: "complete_at" },
            ],
            dict:{

            },
            filterColumns:{
                user_name:'姓名',
                clinic_name:'診所',
            },
        }
    },
    methods:{
        showCreateDataModal(){
            EventBus.$emit('showCreateDatalModal');
        }
    }
}
</script>

<style>

</style>