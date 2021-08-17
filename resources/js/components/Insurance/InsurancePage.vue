<template>

    <div>
        <h3 style="padding:8px 12px">保險申請表</h3> 
        
        <div>
            <FilterBar :columns="filterColumns" />
        </div>
        <div>
            <v-btn color="info" @click="selectAll">全選</v-btn>
            <v-btn @click="nextStatusRequest">下階段</v-btn>
            <v-btn @click="printPage">列印</v-btn>

            <span style="margin:0 24px">|</span>
            <span>生效日：</span>
            <input type="date" v-model="issueDate">
            <v-btn @click="issueInsurance" color="success">生效</v-btn>
        </div>
        
        <DataTable ref="DataTable" :dict="dict" :headers="headers" :requestUrl="requestUrl" />
        <InsuranceDetailModal />
    </div>
</template>

<script>
import InsuranceDetailModal from './InsuranceDetailModal'
import FilterBar from '../FilterBar'
export default {
    components:{
        InsuranceDetailModal,FilterBar
    },
    data() {
        return {
            requestUrl:"/api/insurance",
            headers:[
                { text: "#"},
                { text: "勾選"},
                { text: "狀態", value: "status" },
                { text: "受保人", value: "name" },
                { text: "身分證", value: "identity_number" },
                { text: "電話", value: "phone" },
                { text: "顯示" },
            ],
            dict:{
                'status':{
                    value:{
                        'pending':'待處理',
                        'processing':'處理中',
                        'verified':'已核帳',
                        'close':'完成',
                        'void':'作廢',
                    },
                    color:{
                        'pending':'grey',
                        'processing':'orange',
                        'verified':'#2196f3',
                        'close':'#7ec365',
                        'void':'red',
                    }
                }
            },
            filterColumns:{
                name:'受保人',
                status:[
                    {value:null,text:'狀態'},
                    {value:'pending',text:'待處理'},
                    {value:'processing',text:'處理中'},
                    {value:'verified',text:'已核帳'},
                    {value:'close',text:'完成'},
                    {value:'void',text:'作廢'},
                ]
            },
            issueDate:null
        };
    },
    methods:{
        selectAll(){
            this.$refs.DataTable.selectAll();
        },
        nextStatusRequest(){
            let id_array = this.$refs.DataTable.getSelectedArray();
            axios.post('/api/insurance/nextStatus',{
                id_array,
            })
            .then(res => {
                this.$refs.DataTable.reloadData();
            })
            .catch(err => {
                Exception.handle(error);
            })
        },
        issueInsurance(){
            if(!this.issueDate){
                alert('請選擇日期');
                return;
            }
            let id_array = this.$refs.DataTable.getSelectedArray();
            let issueDate = this.issueDate;
            axios.post('/api/insurance/issue',{
                id_array,
                issueDate,
            })
            .then(res => {
                this.$refs.DataTable.reloadData();
            })
            .catch(err => {
                Exception.handle(error);
            })
        },
        printPage(){
            let id_array = this.$refs.DataTable.getSelectedArray().join();
            window.open(`/insurance/print?id_array=${id_array}`);
        }
    }
}
</script>

<style>

</style>