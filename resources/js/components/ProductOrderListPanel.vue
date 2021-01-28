<template>
  <v-dialog v-model="dialog" max-width="480px">
    <v-card style="padding:12px">
        <h3>產品：{{name}}</h3>

        <div class="data-row" style="height:300px;margin-top:12px;">
            <div style="height:100%;">
            <div style="height:100%;overflow-y:scroll;border:.5px solid gray;padding:8px">
                <p
                style="margin-bottom:4px"
                v-for="o in orderList"
                v-bind:key="o.id">
                <button style="background:red;padding:0 4px;color:#fff" v-if="!(o.receive)" @click="deleteOrderDetail(o.id)">取消訂單</button>
                {{o.created_at}} => {{o.name}} （{{o.location}}） <font color="green">{{(o.receive)?'已領取':''}}</font>
                </p>
            </div>
            </div>
        </div>

        <div class="data-row">
            <simple-pagination :total="total" :page="pagination.page" :rows="20" v-on:nav_to_page="setPage"></simple-pagination>
        </div>

        <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="green darken-1" flat="flat" @click="dialog = false">關閉</v-btn>
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
    EventBus.$off('showProductOrderListPanel');
  },
  created() {
    EventBus.$on("showProductOrderListPanel", product => {
        this.dialog = true;
        this.product = product;
        this.pagination.page = 1;
        this.name = product.name;
        this.getProductOrderList();
    });
  },
  data() {
    return {
        name:'',
        product:null,
        user:{},
        dialog: false,
        orderList:[],
        pagination:{
          page:1,
          descending:true
        },
        total:0,
    };
  },
  methods: {
    getProductOrderList(){
        axios.get('/api/order/productOrderList/'+this.product.id,{
            params:this.pagination
        })
        .catch(err => {console.error(err); })
        .then(res => {
            this.orderList = res.data.orderList;
            this.total = res.data.total;
        })
        .catch(err => {
          Exception.handle(err);
        })
    },
    deleteOrderDetail(id){
      if(!confirm('確定刪除？')){
        return;
      }
      axios.post('/api/order/deleteOrderDetail',{
        'order_detail_id':id,
      })
      .then(res => {
        if(res.data.s != 1){
          alert(res.data.m);
          return;
        }
        this.getProductOrderList();
      })
      .catch(err => {
        Exception.handle(err);
      })
    },
    setPage(page){
        this.pagination.page = page;
        this.getProductOrderList();
      },
  }
}
</script>

<style>
</style>