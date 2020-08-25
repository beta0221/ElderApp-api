<template>
  <div>

    <product-order-list-panel></product-order-list-panel>

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
          <td class="column-img">
            <img :src="props.item.imgUrl">
          </td>
          <td>
            <span :class="(props.item.public)?'green--text':'red--text'">
              {{props.item.public_text}}
            </span>
          </td>
          <td>{{product_category[props.item.product_category_id]}}</td>
          <td>{{props.item.name}}</td>
          <td>{{props.item.price}}</td>
          <td>
              <v-btn color="info" @click="editProduct(props.item.slug)">編輯</v-btn>
              <v-btn color="" @click="showProductOrderListPanel(props.item)">兌換紀錄</v-btn>
          </td>
          
        </template>
      </v-data-table>
    </div>
  </div>
</template>

<script>
import ProductOrderListPanel from "./ProductOrderListPanel";
export default {
    components:{
      ProductOrderListPanel,
    },
    data(){
        return{
          product_category:{},
            pagination: { sortBy: "id", descending: true },
            totalProduct: 0,
            productAray:[],
            loading: true,
            headers: [
                { text:'#'},
                { text: "圖片", value: "img" },
                { text: "狀態", value: "public" },
                { text: "類別", value: "product_category_id" },
                { text: "名稱", value: "name" },
                { text: "價錢", value: "price" },
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
      User.authOnly();
      this.getCategory();
    },
    methods:{
      showProductOrderListPanel(product){
        EventBus.$emit("showProductOrderListPanel",product);
      },
      getCategory() {
        axios.get(`/api/product-category/`)
        .then(res => {
          if (res.status == 200) {
            res.data.forEach(cat => {
              this.product_category[cat.id] = cat.name;
            });
          }
        })
        .catch(err => {
          console.error(err);
        });
      },
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
.column-img img{
  max-height: 80px;
}
</style>