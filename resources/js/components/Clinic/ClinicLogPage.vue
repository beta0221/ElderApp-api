<template>
<div>

    <div>
        <h3 style="padding:8px 12px">志工紀錄</h3> 
    </div>

    <div>
        <FilterBar :columns="filterColumns" />
    </div>

    <DataTable ref="DataTable" :dict="dict" :headers="headers" :requestUrl="requestUrl" />
</div>
</template>

<script>
import FilterBar from '../FilterBar'
export default {
    components:{
        FilterBar,
    },
    data(){
        return{
            requestUrl:"/api/clinic/user/log",
            headers:[
                { text: "#"},
                { text: "姓名", value: "user_name" },
                { text: "診所", value: "clinic_name" },
                { text: "日期", value: "created_at" },
                { text: "完成", value: "complete_at" },
            ],
            dict:{

            },
            filterColumns:{
                user_name:'姓名',
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