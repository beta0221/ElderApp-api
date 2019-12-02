<template>
  <div>

    <div>
    
      <router-link class="white--text" to="/productForm" style="text-decoration:none;">
        <v-btn color="success">
          新增產品
        </v-btn>
      </router-link>
      
  </div>



    <div>
      
      <v-data-table
        :headers="headers"
        :items="productAray"
        :rows-per-page-items="[15,30]"
        :pagination.sync="pagination"
        :total-items="totalProduct"
        :loading="loading"
        class="elevation-1"
      >
        <template v-slot:items="props">
          <td>{{props.index + 1}}</td>
          <td>{{props.item.product_category_id}}</td>
          <td>{{props.item.name}}</td>
          <td>{{props.item.price}}</td>
          <td>{{props.item.img}}</td>
          <td>{{props.item.quantity}}</td>
          <td>
              <v-btn color="info" @click="editProduct(props.item.slug)">編輯</v-btn>
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
            pagination: { sortBy: "id", descending: true },
            totalProduct: 0,
            productAray:[],
            loading: true,
            headers: [
                { text:'#'},
                { text: "類別", value: "product_category_id" },
                { text: "名稱", value: "name" },
                { text: "價錢", value: "price" },
                { text: "圖片", value: "img" },
                { text: "庫存", value: "quantity" },
                { text: "-"},
            ],
        }
    },
    watch:{
        pagination:{
            handler(){
                this.getDataFromApi().then(data => {
                    this.productAray = data.items;
                    this.totalProduct = data.total;
                });
            }
        }
    },
    created(){

    },
    methods:{
        editProduct(slug){
            this.$router.push({path:'/productForm/'+slug})
        },
        getDataFromApi() {
            this.loading = true;
            return new Promise((resolve, reject) => {
                const { sortBy, descending, page, rowsPerPage } = this.pagination;
                
                axios
                .get("/api/product", {
                    params: {
                    page: this.pagination.page,
                    rowsPerPage: this.pagination.rowsPerPage,
                    descending: this.pagination.descending,
                    sortBy: this.pagination.sortBy
                    }
                })
                .then(res => {

                    let items = res.data.products;
                    const total = res.data.total;

                    setTimeout(() => {
                    this.loading = false;
                    resolve({
                        items,
                        total
                    });
                    }, 200);
                })
                .catch(error => {
                    Exception.handle(error);
                })
            });
        },
    }
}
</script>

<style>
</style>