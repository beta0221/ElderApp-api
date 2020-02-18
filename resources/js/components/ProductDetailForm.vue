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
          <v-switch
            class="mx-2"
            color="green"
            v-model="public"
            :label="(public)?'上架':'下架'"
          ></v-switch>
        </div>

        <v-col cols="12" sm="6" md="3">
          <v-text-field label="Solo" placeholder="產品名稱" solo v-model="form.name"></v-text-field>
        </v-col>

        <v-col>
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
          <span>{{l.name}}：</span>
          <v-text-field class="d-inline-block" label="Solo" placeholder="庫存數量" solo v-model="quantityDic[l.id]"></v-text-field>
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

        <v-col class="d-flex" cols="12" sm="6">
          <v-text-field label="所需樂幣" placeholder="所需樂幣" solo v-model="form.price"></v-text-field>
        </v-col>


        <v-col cols="12" sm="6" md="3">
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
export default {
  props: ["product_slug"],
  watch:{
    public(value){
      if(value){
        this.form.public = 1;
      }else{
        this.form.public = 0;
      }
    }
  },
  data() {
    return {
      edit_mode: false,
      product_image: null,
      product_category: [],
      slug: "",
      public:false,
      form: {
        name: "",
        public:0,
        product_category_id: "",
        select_location:[],
        price: null,
        info:"",
      },
      product_image:null,
      file:'',

      location:[],
      quantityDic:{},
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
        .get(`/api/product/${this.product_slug}`)
        .then(res => {
          if (res.status == 200) {
            this.form.name = res.data.name;
            this.public = res.data.public;
            this.form.public = res.data.public;
            this.form.product_category_id = res.data.product_category_id;
            this.form.price = res.data.price;
            this.form.info = res.data.info;
            if(res.data.img){
              this.product_image = `/images/products/${res.data.slug}/${res.data.img}`;
            }
            if(res.data.location){
              res.data.location.forEach((item)=>{
                this.form.select_location.push(item.location_id);
                this.quantityDic[item.location_id] = item.quantity;
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