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

        <div class="data-row" style="height:200px;margin-top:12px;">
          <div style="height:100%;">
            <div style="height:100%;overflow-y:scroll;border:.5px solid gray;padding:8px">
              <p style="margin-bottom:4px" v-for="t in transList" v-bind:key="t.id" v-html="t.created_at + `[${t.tran_id}]`+ ' => ' + t.event + ' (' + ((t.give_take)?'+':'-') + t.amount + ')'"></p>
            </div>
          </div>
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
export default {
  created() {
    EventBus.$on("showSendMoneyPanel", user => {
      this.dialog = true;
      this.user = user;
      this.amount=null;
      this.event=null;
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
    };
  },
  methods: {
      sendMoneyRequest(){
          axios.post('/api/sendMoneyTo',{
              'id_code':this.user.id_code,
              'event':this.event,
              'amount':this.amount
          })
          .catch(err => {console.error(err); })
          .then(res => {
              alert(res.data.m);
              if(res.data.s==1){
                  this.amount=null;
                  this.event=null;
                  this.getTransactionHistory();
              }
          })
      },
      getTransactionHistory(){
        axios.get('/api/trans-history/'+this.user.id)
        .then(res=>{
          this.transList = res.data;
        })
        .catch(error=>{
          console.log(error);
        })
      }
  }
};
</script>

<style>
</style>