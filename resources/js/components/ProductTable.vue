<template>
  <div>

    <product-order-list-panel></product-order-list-panel>

    <div>
    
      <router-link class="white--text" to="/productForm" style="text-decoration:none;">
        <v-btn color="success">
          新增產品
        </v-btn>
      </router-link>
      <div style="display:inline-block;width:160px;margin-left:20px;">
        <v-select v-model="public" :items="publicSelectItem" item-value="value" label="上下架"></v-select>
      </div>
      
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
          <td>
            <v-text-field style="width:72px" label="Solo" placeholder="權重" solo v-model="props.item.order_weight" @keyup.native.enter="updateOrderWeight(props.index)" />
          </td>
          <td class="column-img">
            <img :src="props.item.imgUrl">
          </td>
          <td>
            <span :class="(props.item.public&5)?'green--text':'red--text'">
              {{(props.item.public&5)?'上架':'下架'}}
            </span>
          </td>
          <td>
            <span :class="(props.item.public&6)?'green--text':'red--text'">
              {{(props.item.public&6)?'上架':'下架'}}
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
            public:null,
            totalProduct: 0,
            productAray:[],
            loading: true,
            headers: [
                { text:'#'},
                { text: "排序權重", value: "order_weight" },
                { text: "圖片", value: "img" },
                { text: "兌換區" },
                { text: "商城" },
                { text: "類別", value: "product_category_id" },
                { text: "名稱", value: "name" },
                { text: "價錢", value: "price" },
                { text: "-"},
            ],
            publicSelectItem:[
              {text:'全部',value:null},
              {text:'兌換區',value:5},
              {text:'商城',value:6},
            ]
        }
    },
    watch:{
        pagination:{
          handler(){this.getData();}
        },
        public:{
          handler(){this.getData();}
        }
    },
    created(){
      User.authOnly();
      this.getCategory();
    },
    methods:{
      getData(){
        this.getDataFromApi().then(data => {
          this.productAray = data.items;
          this.totalProduct = data.total;
        });
      },
      showProductOrderListPanel(product){
        EventBus.$emit("showProductOrderListPanel",product);
      },
      updateOrderWeight(index){
        let order_weight = this.productAray[index].order_weight;
        let product_id = this.productAray[index].id;
        axios.post("/api/bcp/product/updateOrderWeight", {
          'product_id':product_id,
          'order_weight':order_weight,
        })
        .then(res =>{
          if(res.status == 200){
            this.getData();
          }
        })
        .catch(function (error) {
          Exception.handle(error);
        })
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
                    sortBy: this.pagination.sortBy,
                    public:this.public,
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