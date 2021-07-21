<template>
<div>
    <order-detail v-on:refreshTable="getOrders"></order-detail>
    <OrderQueryPanel></OrderQueryPanel>
    <div>
        <v-btn color="info" @click="selectAll">
            全選
        </v-btn>
        <v-btn @click="groupNextStatus">
            下階段
        </v-btn>
        <v-btn @click="groupExportExcel">
            匯出
        </v-btn>
        
        <div style="display:inline-block;width:160px;margin-left:20px;">
            <v-select v-model="searchColumn" :items="columns" item-value="value" label="搜尋欄位"></v-select>
        </div>
        <div v-show="(searchColumn=='ship_status')" style="display:inline-block;width:160px;margin-left:20px;">
            <v-select v-model="searchValue" :items="statusList" item-value="value" label="狀態" @change="searchByColumn"></v-select>
        </div>
        <div v-show="(searchColumn=='created_at')" style="display:inline-block;width:160px;margin-left:20px;">
            <input type="date" v-model="searchValue" @change="searchByColumn">
        </div>
        <div v-show="(searchColumn=='order_numero')" style="display:inline-block;width:160px;margin-left:20px;">
            <v-text-field
            v-model.lazy="searchValue"
            @keyup.native.enter="getOrders"
            append-icon="search"
            label="訂單編號"
            single-line
            hide-details
            ></v-text-field>
        </div>
        <v-btn @click="showOrderQueryPanel">多重條件匯出</v-btn>
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
                <td>
                    <div v-if="userDict[props.item.user_id]">
                        <span>{{userDict[props.item.user_id]}}</span>
                    </div>
                </td>
                <td>
                    <div v-for="product in props.item.list" v-bind:key="product.id">
                        <span>{{product.name}}</span>
                    </div>
                </td>
                <td>{{props.item.order_numero}}</td>
                <td>{{props.item.created_at}}</td>
                <td>
                    <v-btn @click="showOrderDetail(props.item.order_numero)">詳細</v-btn>
                </td>
            </template>
        </v-data-table>
    </div>
</div>
</template>

<script>
import OrderDetail from "./OrderDetail";
import OrderQueryPanel from "./Order/OrderQueryPanel"

export default {
    components:{
        OrderDetail,
        OrderQueryPanel
    },
    data(){
        return{
            columns:[
                {text:'欄位',value:null},
                {text:'狀態',value:'ship_status'},
                {text:'日期',value:'created_at'},
                {text:'訂單編號',value:'order_numero'},
            ],
            colorDict:{
                '0':'error',
                '1':'warning',
                '2':'info',
                '3':'primary',
                '4':'success',
                '5':'gray',
            },
            statusDict:{
                '0':'待出貨',
                '1':'準備中',
                '2':'已出貨',
                '3':'已到貨',
                '4':'結案',
                '5':'作廢',
            },
            statusList:[
                {text:'待出貨',value:0},
                {text:'準備中',value:1},
                {text:'已出貨',value:2},
                {text:'已到貨',value:3},
                {text:'結案',value:4},
                {text:'作廢',value:5},
            ],
            headers: [
                { text:'#'},
                { text:'選取'},
                { text: "狀態", value: "ship_status" },
                { text: "購買人" },
                { text: "商品"},
                { text: "訂單編號", value: "order_numero" },
                { text: "日期", value: "created_at" },
                { text: "",},
            ],
            pagination: { sortBy: "id", descending: true },
            orderList:[],
            userDict:{},
            totalOrders:0,
            loading: true,
            //
            searchColumn:null,
            searchValue:null,
            //
            isSelectAll:false,
        }
    },
    watch:{
        searchColumn(val){
            this.searchValue = null;
            if(val == null){
                this.pagination.page = 1;
                this.getOrders();
            }
        },
        pagination: {
            handler(){
                this.getOrders();
            }
        }
    },
    created(){
        User.authOnly();
    },
    methods:{
        showOrderQueryPanel(){
            EventBus.$emit('showOrderQueryPanel');
        },
        showOrderDetail(order_numero){
            EventBus.$emit('showOrderDetail',order_numero);
        },
        searchByColumn(){
            this.pagination.page = 1;
            this.getOrders();
        },
        getOrders(){
            loading: true,
            axios.get('/api/order/getOrders', {
                params: {
                    page: this.pagination.page,
                    rowsPerPage: this.pagination.rowsPerPage,
                    descending: this.pagination.descending,
                    sortBy: this.pagination.sortBy,
                    column:this.searchColumn,
                    value:this.searchValue,
                }
            })
            .catch(error => {Exception.handle(error);})
            .then(res => {
                this.totalOrders = res.data.total;
                this.orderList = res.data.orderList;
                this.userDict = res.data.userDict;
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
        groupExportExcel(){
            let order_numero_array = this.getCheckedOrderNumero();
            if(order_numero_array.length == 0){
                alert('請勾選');
                return;
            }
            window.open('/order/downloadOrderExcel?token='+localStorage.getItem('token') + '&order_numero_array='+order_numero_array.join(','))
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
                Exception.handle(err);
            })
        },
        selectAll(){
            this.isSelectAll = !this.isSelectAll;
            this.orderList.forEach((order,index) => {
                this.$set(this.orderList[index],'isCheck',this.isSelectAll);
            });
        }
    }
};
</script>

<style>

</style>