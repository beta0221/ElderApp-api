<template>
  <v-dialog v-model="dialog" max-width="480px">
        
        <v-card id="member-detail-dialog">
            <v-card-title class="headline">產品</v-card-title>
            
            <div style="padding:8px">
                <div v-for="product in productList" v-bind:key="product.id">
                    <div class="product-cell" @click="selectProduct(product)">
                        <span>{{product.name}}</span>
                        <div style="float:right">
                            <span v-if="product.quantity != 'undefinded'">兌換：{{product.quantity}}</span>
                            <span v-if="product.quantity_cash != 'undefinded'">商城：{{product.quantity_cash}}</span>
                        </div>
                    </div>
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
export default {
    mounted() {
        EventBus.$on("showProductSelector",url => {
            this.dialog = true;
            this.productList = [];
            this.getProductList(url);
        });
    },
    destroyed(){
        EventBus.$off('showProductSelector');
    },
    data() {
        return {
            dialog: false,
            productList:[],
        };
    },
    methods:{
        getProductList(url){
            axios.get(url)
            .then(res => {
                this.productList = res.data;
            })
            .catch(error => {
                Exception.handle(error);
            })
        },
        selectProduct(product){
            EventBus.$emit('selectProduct',product);
        }
    }
}
</script>

<style>
.product-cell{
    padding: 8px;
    border-radius: .3rem;
    border:1px solid gray;
    margin-bottom: 4px;
    cursor: pointer;
}
.product-cell:hover{
    color:#fff;
    background:gray;
}
</style>