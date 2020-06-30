<template>
<div>
    <div>
        <v-btn color="info" @click="selectAll">
            全選
        </v-btn>
        <v-btn @click="groupNextStatus">
            下階段
        </v-btn>
        <v-btn>
            匯出
        </v-btn>
    </div>
        <div>
            <v-data-table
            :headers="headers"
            :items="orderList"
            :rows-per-page-items="[15,30]"
            :pagination.sync="pagination"
            :total-items="totalOrders"
            :loading="loading"
            class="elevation-1"
        >
            <template v-slot:items="props">
                <td>{{props.index + 1}}</td>
                <td>
                    <input type="checkbox" v-model="props.item.isCheck">
                </td>
                <td>
                    <v-btn :color="colorDict[props.item.ship_status]" @click="nextStatus(props.item.order_numero)">
                        {{statusDict[props.item.ship_status]}}
                    </v-btn>
                </td>
                <td>{{props.item.order_numero}}</td>
                <td>{{props.item.created_at}}</td>
                <td>
                    <div v-for="product in props.item.list" v-bind:key="product.id">
                        <span>{{product.name}}</span>
                    </div>
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
            colorDict:{
                '0':'error',
                '1':'warning',
                '2':'info',
                '3':'primary',
                '4':'success',
            },
            statusDict:{
                '0':'待出貨',
                '1':'準備中',
                '2':'已出貨',
                '3':'已到貨',
                '4':'結案',
            },
            headers: [
                { text:'#'},
                { text:'選取'},
                { text: "狀態", value: "ship_status" },
                { text: "訂單編號", value: "order_numero" },
                { text: "日期", value: "created_at" },
                { text: "商品"},
            ],
            pagination: { sortBy: "id", descending: true },
            orderList:[],
            totalOrders:0,
            loading: true,
        }
    },
    created(){
        this.getOrders();
    },
    methods:{
        getOrders(){
            loading: true,
            axios.get('/api/order/getOrders', {
                params: {
                    page: this.pagination.page,
                    rowsPerPage: this.pagination.rowsPerPage,
                    descending: this.pagination.descending,
                    sortBy: this.pagination.sortBy
                }
            })
            .catch(error => {Exception.handle(error);})
            .then(res => {
                this.totalOrders = res.data.total;
                this.orderList = res.data.orderList;
                this.loading=false;
            })  
        },
        getCheckedOrderNumero(){
            let numeroArray = [];
            this.orderList.forEach(order => {
                if(order.isCheck){
                    numeroArray.push(order.order_numero);
                }
            });
            return numeroArray;
        },
        groupNextStatus(){
            let order_numero_array = this.getCheckedOrderNumero();
            if(order_numero_array.length == 0){
                alert('請勾選');
                return;
            }
            axios.post('/api/order/groupNextStatus',{
                'order_numero_array':JSON.stringify(order_numero_array)
            })
            .then(res => {
                console.error(res); 
                this.getOrders();
            })
            .catch(err => {
                console.error(err); 
            })
        },
        nextStatus(order_numero){
            axios.post('/api/order/nextStatus',{
                'order_numero':order_numero
            })
            .then(res => {
                if(res.data.s == 1){
                    this.getOrders();
                }else{
                    alert(res.data.m);
                }
            })
            .catch(err => {
                console.error(err); 
            })
        },
        selectAll(){
            this.orderList.forEach((order,index) => {
                this.$set(this.orderList[index],'isCheck',true)
            });
        }
    }
};
</script>

<style>

</style>