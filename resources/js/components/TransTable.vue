<template>
  <div>
      <div>
            <v-data-table
            :headers="headers"
            :items="tranList"
            :rows-per-page-items="[15,30]"
            :pagination.sync="pagination"
            :total-items="total"
            :loading="loading"
            class="elevation-1"
        >
            <template v-slot:items="props">
                <td>{{props.index + 1}}</td>
                <td>
                    {{(nameDict[props.item.user_id])?nameDict[props.item.user_id]:''}}
                </td>
                <td>{{props.item.tran_id}}</td>
                <td>
                    <span>{{props.item.event}}</span>
                </td>
                <td :class="(props.item.give_take == 1)?'green--text':'red--text'">
                    <span v-html="(props.item.give_take == 1)?'+':'-'"></span><span >{{props.item.amount}}</span>
                </td>
                <td>
                    {{(nameDict[props.item.target_id])?nameDict[props.item.target_id]:'系統'}}
                </td>
                <td>
                    <span>{{props.item.created_at}}</span>
                </td>
                <td>
                    <v-btn v-if="props.item.target_id == 0" color="error" @click="reverseTran(props.item.id)">回朔</v-btn>
                </td>
            </template>
        </v-data-table>
    </div>
  </div>
</template>

<script>
export default {
    data(){
        return{
            headers: [
                { text:'#'},
                { text: "使用者", value: "user_id" },
                { text: "交易編號", value: "tran_id" },
                { text: "事件", value: "event" },
                { text: "金額", value: "amount" },
                { text: "對象", value: "target_id" },
                { text: "日期", value: "created_at" },
                { text: '-'}
            ],
            pagination: { sortBy: "id", descending: true },
            total:0,
            loading: true,
            tranList:[],
            nameDict:{},
        }
    },
    watch:{
        pagination: {
            handler(){
                this.getTrans();
            }
        }
    },
    created(){
        User.authOnly();
        // this.getTrans();
    },
    methods:{
        getTrans(){
            this.loading = true;
            axios.get('/api/transaction/list', {
                params: {
                    page: this.pagination.page,
                    rowsPerPage: this.pagination.rowsPerPage,
                    descending: this.pagination.descending,
                    sortBy: this.pagination.sortBy,
                }
            })
            .catch(error => {Exception.handle(error);})
            .then(res => {
                this.total = res.data.total;
                this.tranList = res.data.tranList;
                this.nameDict = res.data.nameDict;
                this.loading=false;
            })  
        },
        reverseTran(tran_id){
            if(!confirm('確定回朔？')){
                return;
            }
            axios.post('/api/reserseTransaction', {
                'tran_id':tran_id
            })
            .then(res=>{
                alert(res.data);
                this.getTrans();
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