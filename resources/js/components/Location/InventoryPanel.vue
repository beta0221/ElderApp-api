<template>
  <v-dialog v-model="dialog" max-width="480px">
      
      <v-card id="member-detail-dialog">

            <v-card-title class="headline">庫存管理</v-card-title>

            
            <div style="padding:8px">
                <h3>{{location.name}}</h3>    
                <div>
                    <img style="max-height:100px;max-width:100px" :src="product.imgUrl"><br>
                    <p>產品:{{product.name}}</p>
                    <p>剩餘總量：{{quantityRow.quantity + quantityRow.quantity_cash}}</p>
                </div>

                <div>
                    <div style="display:inline-block;width:48%;vertical-align:top;text-align: center;">
                        <h4>兌換區</h4>
                        <h4>{{quantityRow.quantity}}</h4>
                        <input type="number" placeholder="數量" v-model="gift_input">
                        <div>
                            <button class="inventory-btn" style="width:30%" @click="updateInventory('add','gift')">+</button>
                            <button class="inventory-btn" style="width:30%" @click="updateInventory('remove','gift')">-</button>
                            <button class="inventory-btn" style="width:30%" @click="updateInventory('move','gift')">=></button>
                        </div>
                    </div>
                    <div style="display:inline-block;width:2px;height:120px;background:lightgray;vertical-align:top">
                    </div>
                    <div style="display:inline-block;width:48%;vertical-align:top;text-align: center;">
                        <h4>商城</h4>
                        <h4>{{quantityRow.quantity_cash}}</h4>
                        <input type="number" placeholder="數量" v-model="cash_input">
                        <div>
                            <button class="inventory-btn" style="width:30%" @click="updateInventory('move','cash')"><=</button>
                            <button class="inventory-btn" style="width:30%" @click="updateInventory('add','cash')">+</button>
                            <button class="inventory-btn" style="width:30%" @click="updateInventory('remove','cash')">-</button>
                        </div>
                        
                    </div>
                </div>
                
                

                <div style="border:1px solid lightgray;height:160px;margin-top:16px;overflow-y:scroll;">
                    <p style="margin-bottom:4px" v-for="inv in inventoryLog" v-bind:key="inv.id" v-html="inv.created_at + ' => ' + inv.comment + ' (' + ((inv.quantity_gift)?'兌換區':'商城') + ((inv.give_take)?'+':'-') + ((inv.quantity_gift)?inv.quantity_gift:'') + ((inv.quantity_cash)?inv.quantity_cash:'') + ')'"></p>
                </div>

                <div style="margin-top:16px">
                    <simple-pagination :total="total" :page="pagination.page" :rows="20" v-on:nav_to_page="setPage"></simple-pagination>
                </div>

            </div>

            <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="gray darken-1" flat="flat" @click="dialog = false">關閉</v-btn>
            </v-card-actions>

      </v-card>

  </v-dialog>
</template>

<script>
import SimplePagination from "../simplePagination"
export default {
    components:{
        SimplePagination,
    },
    destroyed(){
        EventBus.$off('showInventoryPanel');
    },
    mounted() {
        EventBus.$on("showInventoryPanel", data => {
            this.dialog = true;
            this.product_id = data.product_id;
            this.location_id = data.location_id;
            this.getInventory();
        });
    },
    data() {
        return {
            dialog: false,
            product_id:null,
            location_id:null,
            gift_input:null,
            cash_input:null,
            //
            location:{},
            product:{},
            quantityRow:{},
            inventoryLog:[],
            //
            pagination:{
                page:1,
                descending:true,
            },
            total:0,
        };
    },
    methods:{
        setPage(page){
            this.pagination.page = page;
            this.getInventory();
        },
        getInventory(){
            axios.get(`/api/inventory/${this.location_id}/${this.product_id}`, {
                params: this.pagination,
            })
            .catch(error => {Exception.handle(error);})
            .then(res => {
                this.location = res.data.location;
                this.product = res.data.product;
                this.quantityRow = res.data.quantityRow;
                this.total = res.data.pagination.total;
                this.inventoryLog = res.data.inventoryLog;
            })
        },
        updateInventory(action,target){

            let comment = prompt('請輸入事件名稱');
            if(!comment){ return; }
            if(!this.gift_input && !this.cash_input){ alert('請輸入數量');return; }

            let quantity = 0;
            switch (target) {
                case 'gift':
                    quantity = this.gift_input;
                    break;
                case 'cash':
                    quantity = this.cash_input;
                    break;
                default:
                    break;
            }

            axios.post('/api/updateInventory', {
                'location_id':this.location_id,
                'product_id':this.product_id,
                'target':target,
                'action':action,
                'quantity':quantity,
                'comment':comment,
            })
            .catch(error => {Exception.handle(error);})
            .then(res => {
                this.setPage(1);
            })
            
        }
    }
}
</script>

<style>
.inventory-btn{
    border:1px solid gray;
    margin-top: 8px;
    padding: 8px 0;
}
</style>