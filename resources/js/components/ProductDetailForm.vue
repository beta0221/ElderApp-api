<template>
<div>
    <v-card>

        
        <router-link to='/event' class="grey--text " style="text-decoration:none;"><v-btn color="warning">返回</v-btn></router-link>
        <v-card-title class="headline">{{(edit_mode)?'編輯產品':'新增產品'}}</v-card-title>
      

      <div style="padding:0 24px 24px 24px;">

        

        <v-col cols="12" sm="6" md="3">
          <img v-if="(event_image)?true:false" :src="event_image"/><br>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <input style="display:none;" type="file" id="file" ref="file" v-on:change="onChangeFileUpload()"/>
          <v-btn color="success" @click="$refs.file.click()">上傳圖片</v-btn>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-text-field label="Solo" placeholder="活動標題" solo v-model="form.title"></v-text-field>
        </v-col>

        <v-col class="d-flex" cols="12" sm="6">
          <v-select :items="eventCat" item-text="name" item-value="name" label="活動類別" solo v-model="form.category"></v-select>
        </v-col>

        <v-col class="d-flex" cols="12" sm="6">
          <v-select :items="rewardLevel" item-text="reward" item-value="id" label="獎勵等級" solo v-model="form.reward_level_id"></v-select>
        </v-col>

        <v-col class="d-flex" cols="12" sm="6">
          <v-select :items="district" item-text="name" item-value="id" label="地區" solo v-model="form.district_id"></v-select>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-text-field label="Solo" placeholder="地點" solo v-model="form.location"></v-text-field>
        </v-col>

        

        <v-col cols="12" sm="6" md="3">
          <markdown-editor v-model="form.body"></markdown-editor>
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
    props:['product_slug'],
    data(){
        return{
            edit_mode:false,
            product_image:null,
            slug:"",
            form: {
                name: "",
                product_category_id: "",
                price:"",
                quantity:0,
            },
        }
    },
    created(){
        if(this.product_slug){
            this.edit_mode = true;
            this.loadProduct();
        }
    },
    methods:{
        loadProduct(){
            axios.get(`/api/product/${this.product_slug}`)
            .then(res => {
                if(res.status == 200){

                }
            })
            .catch(err => {
                console.error(err); 
            })
        }
    }
}
</script>

<style>

</style>