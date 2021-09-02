<template>
<div>

    <CreateDataModal :createUrl="'/api/clinic'" :columns="createColumns" />
    <LocationManagerModal :getUrl="'/api/clinic/getManagers/'" :postUrl="'/api/clinic/addManager/'" :deleteUrl="'/api/clinic/removeManager/'" />

    <div>
        <h3 style="padding:8px 12px">診所管理</h3> 
        <v-btn @click="showCreateDataModal">新增</v-btn>
    </div>
    <DataTable ref="DataTable" :dict="dict" :headers="headers" :requestUrl="requestUrl" />


</div>
</template>

<script>

import CreateDataModal from '../ＣreateDataModal.vue'
import LocationManagerModal from "../Location/LocationManagerModal.vue";

export default {
    components:{
        CreateDataModal,
        LocationManagerModal
    },
    data(){
        return{
            requestUrl:"/api/clinic",
            headers:[
                { text: "#"},
                { text: "名稱", value: "name" },
                // { text: "志工日期", value: "session_date" },
                // { text: "志工時段", value: "session" },
                { text: "連結" , btnName:'時段管理' , url:'/clinic/{slug}/manage', keyParam:'slug'},
                { text: "-" , btnName:'顯示',eventName:'showDetailModal',eventParam:['slug','name']},
                { text: "-" , btnName:'管理員',eventName:'showLocationManagers',eventParam:['slug','name']},
            ],
            dict:{

            },
            createColumns:[
                {type:'text',text:'名稱',name:'name'},
                {type:'text',text:'地址',name:'address'},
            ]
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