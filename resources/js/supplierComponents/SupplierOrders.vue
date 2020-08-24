<template>
  <div>
    <div class="container-fluid">
      <div class="row justify-content-between">
        <div class="col-md-2 mt-3 ml-4">
          <button type="button" class="btn btn-primary" @click="newProduct">新增商品</button>
        </div>
        <div class="col-md-1 mt-2">
          <button type="button" class="btn btn-secondary mt-2 mr-4" @click="logout">登出</button>
        </div>
      </div>
    </div>
    <!-- <loading :active.sync="isLoading"></loading> -->
    <div style="padding:1.5rem">
      <div class="card text-center">
        <div class="card-header">
          <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item">
              <a class="nav-link" href="#">全部</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">上架中</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#">未上架</a>
            </li>
            <span class="align-right ml-auto">
              <pegination
                v-on:getProducts="getProducts"
                :totalPage="totalPage"
                :pagination="pagination"
              ></pegination>
            </span>
          </ul>
        </div>
        <table class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>圖片</th>
              <th>狀態</th>
              <th>名稱</th>
              <th>價格</th>
              <th>編輯訂單</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item,key) in products" :key="key">
              <td class="align-middle">{{key+1}}</td>
              <td class="align-middle">
                <img class="thumbnail" :src="item.imgUrl" />
              </td>
              <td class="align-middle">
                <span v-if="item.public===1" class="text-success">{{item.public_text}}</span>
                <span v-else class="text-danger">{{item.public_text}}</span>
              </td>
              <td class="align-middle">{{item.name}}</td>
              <td class="align-middle">{{item.price}}</td>
              <td class="align-middle">
                <button
                  type="button"
                  class="btn btn-outline-primary btn-sm"
                  @click="getProductDetail(item.slug)"
                >商品詳情</button>
              </td>
            </tr>
          </tbody>
        </table>
        <!-- <div class="card-footer d-flex justify-content-between">
      <span>頁數</span>
     
        </div>-->
      </div>
    </div>
    <div
      class="modal fade"
      id="productModal"
      tabindex="-1"
      role="dialog"
      aria-labelledby="productModal"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content border-0">
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title" id="productModal">
              <span>訂單</span>
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form>
              <div class="row">
                <div class="col-sm-4">
                  <div class="form-group">
                    <label for="image">輸入圖片網址</label>
                    <input
                      type="text"
                      class="form-control"
                      id="image"
                      placeholder="請輸入圖片連結"
                      v-model="tempProduct.imageUrl"
                    />
                  </div>
                  <div class="form-group">
                    <label for="customFile">
                      或 上傳圖片
                      <!-- <i class="fas fa-spinner fa-spin" v-if="status.fileloading"></i> -->
                    </label>
                    <input
                      type="file"
                      id="customFile"
                      class="form-control"
                      ref="files"
                      @change="fileUpload"
                    />
                  </div>
                </div>
                <div class="col-sm-8">
                  <img class="img-fluid" :src="product_imgUrl" alt />
                </div>

                <div class="col-sm-6">
                  <div class="form-group">
                    <label>商品名稱</label>
                    <input type="text" class="form-control" v-model="tempProduct.name" />
                  </div>
                </div>
                <div class="col-sm-6 form-group">
                  <label>商品類別</label>
                  <select class="form-control" name id v-model="tempProduct.product_category_id">
                    <option v-for="op in product_category" :key="op.id" :value="op.id">{{op.name}}</option>
                  </select>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label>現金購買</label>
                    <input type="text" class="form-control" v-model="tempProduct.cash" />
                  </div>
                </div>

                <div class="col-sm-6">
                  <div class="form-group">
                    <label>樂幣兌換</label>
                    <input type="text" class="form-control" v-model="tempProduct.price" />
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label>各半支付（樂幣）</label>
                    <input type="text" class="form-control" v-model="tempProduct.pay_cash_point" />
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label>各半支付（現金）</label>
                    <input type="text" class="form-control" v-model="tempProduct.pay_cash_price" />
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label>每人樂幣兌換上限數量</label>
                    <input type="text" class="form-control" v-model="tempProduct.exchange_max" />
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label>是否上架</label>
                    <input
                      type="checkbox"
                      class="form-control"
                      :true-value="1"
                      :false-value="0"
                      v-model="tempProduct.public"
                    />
                  </div>
                </div>
                <!-- <div class="col-sm-6">
                  <span class="form-group col-sm-6">
                    <label>據點庫存</label>
                    <select class="form-control" v-model="selected" 
                    @change="clickLocation(selected)" required>
                      <option v-for="lct in location"                    
                       :key="lct.id" :value="lct.id">{{lct.name}}</option>
                    </select>
                  </span>

                  <span class="form-group col-sm-6">
                    <input
                      v-for="lct in location"
                      :key="lct.id"
                      v-show="selected===lct.id"
                      class="form-control"
                      type="number"
                      v-model="product_quantity[lct.id]"
                    />
                  </span>
                </div> -->

                    <div>
                        <div v-for="loc in location" v-bind:key="loc.id">
                            <input type="checkbox"  :checked="isSelected(loc.id)" 
                            @change="clickLocationCheckbox($event,loc.id)">
                            <span>{{loc.name}}</span>


                            <div style="display:inline-block" v-if="tempProduct.select_location">

                                <input type="number"
                                    placeholder="庫存數量"
                                    v-if="selected_location.includes(loc.id)"
                                    v-model="product_quantity[loc.id]">
                                
                                
                            </div>
                            
                        </div>

                    </div>
                        
                    

            <!-- <div class="form-group col-sm-6" v-for="(lct,index) in location" :key="index" >
                <span >
                    <label>{{lct.name}}</label>
                    <input type="number" class="form-control"
                    placeholder="庫存數量"
                    v-model="product_quantity[lct.id]">
                </span>   
                </div> -->

                <div class="col-sm-12">
                  <div class="form-group">
                    <label>商品詳細內容</label>
                    <textarea name id cols="100" rows="5" v-model="tempProduct.info"></textarea>
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">取消</button>
            <button type="button" class="btn btn-success" @click="submit">確認更改</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import ProductOrderListPanel from "../components/ProductOrderListPanel";
import Pegination from "./Pegination";
export default {
  components: {
    ProductOrderListPanel,
    Pegination,
  },
  data() {
    return {
      pagination: {
        sortBy: "id",
        descending: true,
        page: 1,
        rowsPerPage: 15,
      },
      totalPage: 0,
      loading: true,
      products: {},
      tempProduct: {},
      isNew: false,
      slug: "",
      product_imgUrl: "",
      product_quantity: {},
      product_category: [],
      location: [],
      selected_location: [],
    };
  },
  methods: {
    logout() {
      User.logout(this.from_url);
    },
    getLocation() {
      axios
        .get(`/api/location/`)
        .then((res) => {
          console.log(res);
          if (res.status == 200) {
            this.location = res.data;
          }
        })
        .catch((err) => {
          console.error(err);
        });
    },
    getCategory() {
      axios
        .get(`/api/product-category/`)
        .then((res) => {
          if (res.status == 200) {
            this.product_category = res.data;
            console.log(res);
          }
        })
        .catch((err) => {
          console.error(err);
        });
    },
    getProducts() {
      axios
        .get("/api/product", {
          params: this.pagination,
        })
        .then((res) => {
          console.log(res);
          this.products = res.data.products;
          this.totalPage = Math.ceil(
            res.data.total / this.pagination.rowsPerPage
          );
        })
        .catch((error) => {
          Exception.handle(error);
        });
    },
    getProductDetail(slug) {
      this.isNew = false;
      this.slug = slug;
      axios
        .get(`/api/productDetail/${slug}`)
        .then((res) => {
          console.log(res);
          $("#productModal").modal("show");
          this.tempProduct = res.data.product;
          
          if (res.data.product.imgUrl) {
            this.product_imgUrl = res.data.product.imgUrl;
          }
          if (res.data.location) {
              let select_location = [];
            res.data.location.forEach((item) => {
              select_location.push(item.location_id);
              //
              this.product_quantity[item.location_id] = item.quantity;
            });
            this.tempProduct.select_location = select_location;
              this.selected_location = select_location;
          }
        })
        .catch((error) => {
          Exception.handle(error);
        });
    },
    submit() {
      let formdata = new FormData();
      let keysArray = Object.keys(this.tempProduct);
      keysArray.forEach((key) => {
          if(key == 'imgUrl'){
             return;
          }
             let value = this.tempProduct[key];
             formdata.append(key, value);
      });
      formdata.append("quantity", JSON.stringify(this.product_quantity));
      if (this.isNew) {
        this.createProduct(formdata);
      } else {
        this.updateProduct(formdata);
      }
    },
    createProduct(formdata) {
      axios
        .post("/api/product", formdata, {
          headers: {
            "content-type": "multipart/form-data",
          },
        })
        .then((res) => {
          console.log(res);
          $("#productModal").modal("hide");
        })
        .catch((err) => {
          console.error(err);
          Exception.handler(error);
        });
    },
    updateProduct(formdata) {
      formdata.append('_method','PUT');
      axios
        .post(`/api/product/${this.slug}`, formdata, {
          headers: {
            "content-type": "multipart/form-data",
          },
        })
        .then((res) => {
          console.log(res);
          $("#productModal").modal("hide");
        })
        .catch((err) => {
          console.error(err);
          Exception.handler(error);
        });
    },
    newProduct() {
      this.isNew = true;
      this.tempProduct = {
          'select_location':[]
      };
      this.product_quantity={};
      this.selected_location=[];
      this.product_imgUrl = "";
      this.openModal();
    },
    fileUpload() {
      this.tempProduct.file = this.$refs.files.files[0];
      this.product_imgUrl = URL.createObjectURL(this.$refs.files.files[0]);
    },
    isSelected(id) {
      if (!this.tempProduct.select_location) {
        return false;
      }
      if (this.tempProduct.select_location.includes(id)) {
        return true;
      }
      return false;
    },
    clickLocationCheckbox($event,id) {     
        let new_select_location = Object.assign([],this.tempProduct.select_location);
        
        if(event.target.checked){
             if (!this.tempProduct.select_location.includes(id)) {
                 new_select_location.push(id);
           }
        }else{
            this.tempProduct.select_location.forEach((value,index)=>{
                if(id==value){
                    new_select_location.splice(index,1);
                }    
             })
        }

        this.$set(this.tempProduct,'select_location',new_select_location);
        this.selected_location = new_select_location;
        
    },
    openModal() {
      $("#productModal").modal("show");
    },
  },
  created() {
    this.getProducts();
    this.getProductDetail();
    this.getLocation();
    this.getCategory();
  },
};
</script>
<style>
.thumbnail {
  max-height: 80px;
}
</style>