<template>
  <v-dialog v-model="dialog" max-width="480px">
    <v-card id="member-detail-dialog">
        <v-card-title class="headline">姓名：{{user.name}}</v-card-title>

        <div class="data-row">
            <span>贈送金額</span>
            <input type="number" v-model="amount" />
        </div>

        <div class="data-row">
            <span>留言</span>
            <input type="text" v-model="event"/>
        </div>

        <div class="data-row">
          <v-btn v-show="!(pagination.descending)" @click="setOrder(true)">▲</v-btn>
          <v-btn v-show="(pagination.descending)" @click="setOrder(false)">▼</v-btn>

          <v-text-field style="display:inline-block;width:140px" type="text" placeholder="關鍵字" 
            v-model="queryEvent" @keyup.native.enter="getTransactionHistory"/>
        </div>

        <div class="data-row" style="height:200px;margin-top:12px;">
          <div style="height:100%;">
            <div style="height:100%;overflow-y:scroll;border:.5px solid gray;padding:8px">
              <div v-for="t in transList" v-bind:key="t.id">
                
                <p style="margin-bottom:4px" >
                  <button style="background:red;color:#fff;padding:2px 4px;" v-if="t.target_id==0" @click="reverseTransaction(t.id)">回朔</button>
                  {{t.created_at}} => {{t.event}} ({{((t.give_take)?'+':'-')}}{{t.amount}})
                </p>
              </div>
              
            </div>
          </div>
        </div>

        <div class="data-row">
          <simple-pagination :total="total" :page="pagination.page" :rows="20" v-on:nav_to_page="setPage"></simple-pagination>
        </div>

        <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="green darken-1" flat="flat" @click="sendMoneyRequest">確定發送</v-btn>
            <v-btn color="gray darken-1" flat="flat" @click="dialog = false">關閉</v-btn>
        </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import SimplePagination from "./simplePagination"
export default {
  components:{
    SimplePagination,
  },
  destroyed(){
    EventBus.$off('showSendMoneyPanel');
  },
  mounted() {
    EventBus.$on("showSendMoneyPanel", user => {
      this.dialog = true;
      this.user = user;
      this.amount=null;
      this.event=null;
      this.pagination.page = 1;
      this.getTransactionHistory();
    });
  },
  data() {
    return {
        user:{},
        dialog: false,
        amount:null,
        event:null,
        transList:[],
        pagination:{
          page:1,
          descending:true
        },
        total:0,
        queryEvent:null,
    };
  },
  methods: {
      sendMoneyRequest(){
          axios.post('/api/sendMoneyTo',{
              'id_code':this.user.id_code,
              'event':this.event,
              'amount':this.amount
          })
          .catch(err => {Exception.handle(err); })
          .then(res => {
              alert(res.data.m);
              if(res.data.s==1){
                  this.amount=null;
                  this.event=null;
                  this.pagination.page = 1;
                  this.getTransactionHistory();
              }
          })
      },
      setPage(page){
        this.pagination.page = page;
        this.getTransactionHistory();
      },
      setOrder(descending){
        this.pagination.descending = descending;
        this.getTransactionHistory();
      },
      getTransactionHistory(){
        let params = Object.assign({},this.pagination);
        params['queryEvent'] = this.queryEvent;
        axios.get('/api/transaction/history/'+this.user.id,{
          params,
        })
        .catch(err => {Exception.handle(error); })
        .then(res => {
            this.transList = res.data.transList;
            this.total = res.data.total;
        })
      },
      reverseTransaction(tran_id){
        if(!confirm('確定回朔？')){ return; }
        axios.post('/api/reserseTransaction', {
          'tran_id':tran_id
        })
        .then(res=>{
          this.getTransactionHistory();
        })
        .catch(error => {
          Exception.handle(error);
        })
      }
  }
};
</script>

<style>
</style>