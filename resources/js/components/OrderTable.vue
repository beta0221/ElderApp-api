<template>
<div>
    <div>
        <h2>hello</h2>
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
            headers: [
                { text:'#'},
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
            
        }
    }
};
</script>

<style>

</style>