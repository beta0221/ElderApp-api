<template>
    <div>
       <div style="padding:1.5rem;width:90%;margin-left:8rem;" class="container-fluid">
      <div class="card text-center row">
        <div class="card-header">
            
          <ul class="nav nav-tabs card-header-tabs">
              <li class="nav-item mb-2 mr-4">
                  <button class="btn btn-primary">全選</button>
              </li>
              <li class="nav-item mb-2 mr-3">
                  <select class="form-control" name="" id="" v-model="searchColumn">
                      <option value="null">欄位</option>
                      <option value="ship_status">狀態</option>
                      <option value="created_at">日期</option>
                      <option value="order_numero">訂單編號</option>
                  </select>
              </li>
            <li class="nav-item mb-2 mr-3" >
              <select class="form-control" name="" id="" @change="getOrders($event)" 
                      v-model="searchValue" v-show="searchColumn=='ship_status'">
                  <option value="0">待出貨</option>
                  <option value="1">準備中</option>
                  <option value="2">已出貨</option>
                  <option value="3">已到貨</option>
                  <option value="4">結案</option>
              </select>
            </li>
            <li class="nav-item mb-2" v-show="searchColumn=='created_at'">
                <input class="form-control" type="date" v-model="searchValue">
            </li>
            <li class="nav-item mb-2" v-show="searchColumn=='order_numero'">
                <input class="form-control" type="text" v-model="searchValue">
            </li>
           
            <span class="align-right ml-auto">
              <pagination
                v-on:getProducts="getOrders"
                :totalPage="totalPage"
                :pagination="pagination"
              ></pagination>
            </span>
          </ul>
        </div>
        <table class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>選取</th>
              <th>狀態</th>
              <th>名稱</th>
              <th>訂單編號</th>
              <th>日期</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item,key) in orderList" :key="key">
              <td class="align-middle">{{key+1}}</td>
              <td class="align-middle">
                <input type="checkbox" class="form-control" style="height:15px;" v-model="isCheck">
              </td>
              
              <td class="align-middle">
                <button v-if="item.ship_status===0" class="btn btn-secondary">待出貨</button>
                <button v-if="item.ship_status===1" class="btn btn-info">準備中</button>
                <button v-if="item.ship_status===2" class="btn btn-primary">已出貨</button>
                <button v-if="item.ship_status===3" class="btn btn-success">已到貨</button>
                <button v-if="item.ship_status===4" class="btn btn-danger">結案</button>
              </td>
              <td class="align-middle">{{item.name}}</td>
              <td class="align-middle">
                {{item.order_numero}}
              </td>
              <td class="align-middle">
                {{item.created_at}}
              </td>
            </tr>
          </tbody>
        </table>
        <!-- <div class="card-footer d-flex justify-content-between">
      <span>頁數</span>
     
        </div>-->
      </div>
    </div>
    </div>
</template>
<script>
import pagination from './Pagination'
export default {
    components:{
        pagination,
    },
    data(){
        return{
            pagination: { 
                sortBy: "id", 
                descending: true,
                page: 1,
                rowsPerPage: 15,
                },
            orderList:[],
            totalOrders:0,
            totalPage:0,
            searchColumn:null,
            searchValue:null,
            isCheck:false,
        }
    },
    watch:{
        searchColumn(val){
            this.searchValue=null;
            if(val == null){
                this.pagination.page = 1;
                this.getOrders;
            }
        }
    },
    methods:{
        searchByColumn(){
            this.pagination.page = 1;
            this.getOrders();
        },
        getOrders($event){
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
            .then(res => {
                this.totalOrders = res.data.total;
                this.orderList = res.data.orderList;
                this.totalPage = Math.ceil(
                this.totalOrders / this.pagination.rowsPerPage
                );
                console.log(res);
            }).catch(error => {Exception.handle(error);})
        },
    },
    created(){
        this.getOrders();
    }
    
}
</script>
<style scope>
    
</style>