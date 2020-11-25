<template>
  <div>
    <v-card>
      <router-link to="/product" class="grey--text" style="text-decoration:none;">
        <v-btn color="warning">返回</v-btn>
      </router-link>
      <v-card-title class="headline">{{(edit_mode)?'編輯產品':'新增產品'}}</v-card-title>

      <div style="padding:0 24px 24px 24px;">
        <v-col cols="12" sm="6" md="3">
          <img v-if="(product_image)?true:false" :src="product_image" />
          <br />
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <input
            style="display:none;"
            type="file"
            id="file"
            ref="file"
            v-on:change="onChangeFileUpload()"
          />
          <v-btn color="success" @click="$refs.file.click()">上傳圖片</v-btn>
        </v-col>

        <div>
          <label>兌換區</label>
          <v-switch
            class="mx-2"
            color="green"
            v-model="exchange_public"
            :label="(exchange_public)?'上架':'下架'"
          ></v-switch>
          <label>商城</label>
          <v-switch
          class="mx-2"
          color="green"
          v-model="store_public"
          :label="(store_public)?'上架':'下架'">
          </v-switch>
        </div>



        <v-col cols="12" sm="6" md="3">
          <span>產品名稱</span>
          <v-text-field label="Solo" placeholder="產品名稱" solo v-model="form.name"></v-text-field>
        </v-col>

        <v-col>
          <span>經銷據點</span>
          <v-select
            v-model="form.select_location"
            :items="location"
            item-text="name"
            item-value="id"
            chips
            label="經銷據點"
            multiple
            solo
          ></v-select>
        </v-col>

        
        <div v-for="(l,index) in location" v-bind:key="index" v-show="isSelected(l.id)">
          <span>據點庫存（{{l.name}}）：</span>
          <span>兌換庫存</span><v-text-field class="d-inline-block" label="Solo" placeholder="兌換庫存" solo v-model="quantityDic[l.id]"></v-text-field>
          <span>商城庫存</span><v-text-field class="d-inline-block" label="Solo" placeholder="商城庫存" solo v-model="payCashQuantityDict[l.id]"></v-text-field>
        </div>

        <div>
          <span>產品類別</span>
        </div>
        <v-col class="d-flex" cols="12" sm="6">
          <v-select
            :items="product_category"
            item-text="name"
            item-value="id"
            label="產品類別"
            solo
            v-model="form.product_category_id"
          ></v-select>
        </v-col>

        
        <label>每人最多兌換(非必填)</label>
        <v-text-field label="每人最多兌換(非必填)" placeholder="每人最多兌換(非必填)" solo v-model="form.exchange_max"></v-text-field>
        
        <v-layout row wrap>
          <v-flex md3>
            <label>免費兌換（樂幣）</label>
            <v-text-field label="免費兌換（樂幣）" placeholder="免費兌換（樂幣）" solo v-model="form.price"></v-text-field>
          </v-flex>
        </v-layout>
        
        
        <v-layout row wrap>
          <v-flex md3>
              <label>現金支付（各半）</label>
              <v-text-field label="現金支付（各半）" placeholder="現金支付（各半）" solo v-model="form.pay_cash_price"></v-text-field>
          </v-flex>
          <v-flex md3>
              <label>樂幣抵用（各半）</label>
              <v-text-field label="樂幣抵用（各半）" placeholder="樂幣抵用（各半）" solo v-model="form.pay_cash_point"></v-text-field>
          </v-flex>
        </v-layout>

        
        
        <v-layout row wrap>
          <v-flex md3>
            <label>原價(顯示用)</label>
            <v-text-field label="原價" placeholder="原價" solo v-model="form.original_cash"></v-text-field>
          </v-flex>
          <v-flex md3>
            <label>優惠價(實際購買)</label>
            <v-text-field label="優惠價" placeholder="優惠價" solo v-model="form.cash"></v-text-field>
          </v-flex>
        </v-layout>
        

        <v-col cols="12" sm="6" md="3">
          <!-- <ckeditor id="editor" :editor="editor" v-model="form.info" :config="editorConfig"></ckeditor> -->
          <markdown-editor v-model="form.info"></markdown-editor>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-btn v-show="!edit_mode" block color="success" @click="submitForm">新增</v-btn>
          <v-btn v-show="edit_mode" block color="info" @click="submitForm">確定送出</v-btn>
        </v-col>
      </div>
    </v-card>
  </div>
</template>

<script>
import MyUploadAdapter from '../Helpers/MyUploadAdapter'
export default {
  props: ["product_slug"],
  watch:{
    exchange_public(value){
      let is_public = 0;
      if(value && this.store_public){
        is_public = 4;
      }else if(value){
        is_public = 1;
      }else if(this.store_public){
        is_public = 2;
      }
      this.form.public = is_public;
    },
    store_public(value){
      let is_public = 0;
      if(value && this.exchange_public){
        is_public = 4;
      }else if(value){
        is_public = 2;
      }else if(this.exchange_public){
        is_public = 1;
      }
      this.form.public = is_public;
    }
  },
  data() {
    return {
      editor:ClassicEditor,
      editorConfig:{
        placeholder: 'Type some text...',
        extraPlugins:[(editor)=>{
          editor.plugins.get( 'FileRepository' ).createUploadAdapter = ( loader ) => {
            return new MyUploadAdapter( loader , `/api/image/upload/productContent/${this.product_slug}`);
          };
        }]
      },
      edit_mode: false,
      product_image: null,
      product_category: [],
      slug: "",
      exchange_public:false,
      store_public:false,
      form: {
        name: "",
        public:0,
        product_category_id: "",
        select_location:[],
        price: null,
        pay_cash_price:null,
        pay_cash_point:null,
        original_cash:null,
        cash:null,
        exchange_max:null,
        info:"",
      },
      file:'',

      location:[],
      quantityDic:{},
      payCashQuantityDict:{},
    };
  },
  created() {
    if (this.product_slug) {
      this.edit_mode = true;
      this.loadProduct();
    }
    this.getCategory();
    this.getLocation();
  },
  methods: {
    isSelected(id){
      if(this.form.select_location.includes(id)){
        return true;
      }else{
        return false;
      }
    },
    onChangeFileUpload(){
        this.file = this.$refs.file.files[0];
        this.product_image = URL.createObjectURL(this.$refs.file.files[0]);
    },
    getLocation(){
      axios
        .get(`/api/location/`)
        .then(res => {
          if (res.status == 200) {
            this.location = res.data;
          }
        })
        .catch(err => {
          console.error(err);
        });
    },
    getCategory() {
      axios
        .get(`/api/product-category/`)
        .then(res => {
          if (res.status == 200) {
            this.product_category = res.data;
          }
        })
        .catch(err => {
          console.error(err);
        });
    },
    loadProduct() {
      axios
        .get(`/api/productDetail/${this.product_slug}`)
        .then(res => {
          if (res.status == 200) {
            this.form.name = res.data.product.name;
            this.form.public = res.data.product.public;
            if(this.form.public&5){
              this.exchange_public = true;
            }
            if(this.form.public&6){
              this.store_public = true;
            }
            this.form.product_category_id = res.data.product.product_category_id;
            this.form.price = res.data.product.price;
            this.form.pay_cash_price = res.data.product.pay_cash_price;
            this.form.pay_cash_point = res.data.product.pay_cash_point;
            this.form.original_cash = res.data.product.original_cash;
            this.form.cash = res.data.product.cash;
            this.form.exchange_max = res.data.product.exchange_max;
            this.form.info = res.data.product.info;
            if(res.data.product.imgUrl){ this.product_image = res.data.product.imgUrl; }
            if(res.data.location){
              res.data.location.forEach((item)=>{
                this.form.select_location.push(item.location_id);
                this.quantityDic[item.location_id] = item.quantity;
                this.payCashQuantityDict[item.location_id] = item.quantity_cash;
              });
            }
          }
        })
        .catch(err => {
          console.error(err);
        });
    },
    submitForm(){

      let formData = new FormData();
      
      formData.append('file', this.file);
      Object.keys(this.form).forEach(key => formData.append(key, this.form[key]));
      formData.append('quantity',JSON.stringify(this.quantityDic));
      formData.append('quantity_cash',JSON.stringify(this.payCashQuantityDict));
      if(this.edit_mode){
        this.updateRequest(formData);
      }else{
        this.storeRequest(formData);
      }
    },
    storeRequest(formData){
      axios.post("/api/product", formData,{
          headers:{
            'content-type': 'multipart/form-data',
          }
        })
        .then(res =>{
          if(res.status == 200){
            this.$router.push({path:"product"});
          }else{
            alert(res.data);
          }
        })
        .catch(function (error) {
          console.log(error);
          alert('系統錯誤');
        })
    },
    updateRequest(formData){
      formData.append('_method','PUT');
      axios.post("/api/product/"+ this.product_slug, formData,{
          headers:{
            'content-type': 'multipart/form-data',
            'Content-Type': 'application/json'
          }
        })
        .then(res =>{
          if(res.status == 200){
            this.$router.push({path:"/product"});
          }else{
            alert(res.data);
          }
        })
        .catch(function (error) {
          console.log(error);
          alert('系統錯誤');
        })
    },
    

  }
};
</script>

<style>
</style>