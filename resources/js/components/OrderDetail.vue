<template>
  <v-dialog v-model="dialog" max-width="480px">
      <v-card id="member-detail-dialog">
        <v-card-title class="headline">訂單編號：{{order_numero}}</v-card-title>

        <div v-if="(orderDelievery)">
            <div class="data-row">
                <span>收件人：{{orderDelievery.receiver_name}}</span>
            </div>

            <div class="data-row">
                <span>聯絡電話：{{orderDelievery.receiver_phone}}</span>
            </div>

            <div class="data-row">
                <span>地址：{{orderDelievery.county}}{{orderDelievery.district}}{{orderDelievery.address}}</span>
            </div>
        </div>

        <div class="data-row">
            <hr>
        </div>
        
        <div>
            <table style="width:100%;text-align:center">
                <tr><th></th><th>商品</th><th>總數</th><th>總金額</th></tr>
                <tr v-for="e in orders" v-bind:key="e.id">
                    <td style="width:80px">
                        <img style="width:100%" :src="productImageDict[e.product_id]">
                    </td>
                    <td>
                        <span>{{e.name}}</span><br>
                        <span>{{(locationDict[e.location_id])?locationDict[e.location_id]:''}}</span>
                    </td>
                    <td>
                        {{e.total_quantity}}
                    </td>
                    <td>
                        {{e.total_cash}}
                    </td>
                </tr>
            </table>
        </div>


        <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="gray darken-1" flat="flat" @click="dialog = false">關閉</v-btn>
        </v-card-actions>

    </v-card>
  </v-dialog>
</template>

<script>
export default {
    destroyed(){
        EventBus.$off('showOrderDetail');
    },
    created(){
        EventBus.$on("showOrderDetail", order_numero => {
            this.order_numero = order_numero;
            this.dialog = true;
            this.getOrderDetail(order_numero);
        });
    },
    data(){
        return{
            dialog: false,
            order_numero:'',
            orders:[],
            orderDelievery:{},
            productImageDict:{},
            locationDict:{},
        };
    },
    methods:{
        getOrderDetail(order_numero){
            axios.get('/api/order/getOrderDetail/'+order_numero)
            .catch(error => {console.log(error);})
            .then(res => {
                this.orders = res.data.orders;
                this.orderDelievery = res.data.orderDelievery;
                this.productImageDict = res.data.productImageDict;
                this.locationDict = res.data.locationDict;
            });
        }
    }
}
</script>

<style>

</style>