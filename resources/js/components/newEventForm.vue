<template>
  <!-- dialog -->
  <div>
    <v-card>

        
        <router-link to='/event' class="grey--text " style="text-decoration:none;"><v-btn color="warning">返回</v-btn></router-link>
        <v-card-title class="headline">{{(edit_mode)?'編輯活動':'新增活動'}}</v-card-title>
      

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

        <div v-if="edit_mode==false">
          <v-select :items="eventType" item-text="value" item-value="key" label="活動週期" solo v-model="form.event_type"></v-select>
        </div>

        <v-col class="d-flex" cols="12" sm="6">
          <v-select :items="eventCat" item-text="name" item-value="id" label="活動類別" solo v-model="form.category_id"></v-select>
        </v-col>

          <div>
            <h4 class="grey--text">獎勵等級</h4>
          </div>
          <div>
            <v-select :items="rewardLevel" item-text="reward" item-value="id" label="獎勵等級" solo v-model="form.reward_level_id"></v-select>
          </div>
        

        <v-col class="d-flex" cols="12" sm="6">
          <v-select :items="district" item-text="name" item-value="id" label="地區" solo v-model="form.district_id"></v-select>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <v-text-field label="Solo" placeholder="地點" solo v-model="form.location"></v-text-field>
        </v-col>

        <v-col cols="6" sm="6" md="3">
          <v-btn @click="form.maximum--">-</v-btn>          
            <v-text-field class="d-inline-block" label="人數上限" v-model="form.maximum"></v-text-field>
          <v-btn @click="form.maximum++">+</v-btn>
        </v-col>
        
        <v-col cols="12" sm="6" md="3">
          <div v-show="(form.event_type==1)?true:false">
            <h3 class="grey--text">活動開始時間</h3>
            <v-date-picker v-model="event_date"></v-date-picker>
            <v-time-picker v-model="event_time"></v-time-picker>
          </div>
        </v-col>

        <div v-show="(form.event_type==1)?true:false">
          <h3 class="grey--text">結束時間</h3>
          <v-date-picker v-model="event_date_2"></v-date-picker>
          <v-time-picker v-model="event_time_2"></v-time-picker>
        </div>

        <div v-show="(form.event_type==1)?true:false">
          <hr class="mt-4">
          <v-col cols="12" sm="6" md="3">
            <div class="mt-4 mb-4">
              <h3 class="grey--text">報名截止間</h3>
              <v-date-picker color="red lighten-1" v-model="dead_date"></v-date-picker>
              <v-time-picker ampm-in-title="true" color="red lighten-1" v-model="dead_time"></v-time-picker>
            </div>
          </v-col>
        </div>

        <div v-show="!edit_mode">
          <div v-show="(form.event_type==1)?false:true">
            <v-btn @click="form.days--">-</v-btn>
              <v-text-field class="d-inline-block" label="開課天數" v-model="form.days"></v-text-field>
            <v-btn @click="form.days++">+</v-btn>
          </div>
        </div>

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
  props:['event_slug'],
  data() {
    return {
      edit_mode:false,
      event_image:null,
      file:'',
      eventCat:[],
      eventType:[
        {
          'key':1,
          'value':'一次性'
        },
        {
          'key':2,
          'value':'經常性'
        },
      ],
      eventCatDic:{},
      rewardLevelDic:{},
      rewardLevel:[
        {'id':1,'reward':10},
      ],
      district:[],
      event_date:"",
      event_time:"00:00:00",
      event_date_2:"",
      event_time_2:"00:00:00",
      dead_date: "",
      dead_time: "00:00:00",
      slug:"",
      form: {
        title: "",
        event_type:1,
        category_id: "",
        district_id:"",
        reward_level_id:1,
        location: "",
        maximum:"20",
        dateTime:"",
        dateTime_2:"",
        deadline:"",
        days:"8",
        body: ""
      },


    };
  },
  watch:{
    'form.maximum':function(val){
      if(val < 0){
        this.form.maximum = 0;
      }
    },
    'form.days':function(val){
      if(val < 8){
        this.form.days = 8;
      }
    },
    event_date(val){
      this.form.dateTime = val + ' ' + this.event_time+":00";
    },
    event_time(val){
      this.form.dateTime = this.event_date + ' ' + val+":00";
    },
    event_date_2(val){
      this.form.dateTime_2 = val + ' ' + this.event_time_2+":00";
    },
    event_time_2(val){
      this.form.dateTime_2 = this.event_date_2 + ' ' + val+":00";
    },
    dead_date(val){
      this.form.deadline = val + ' ' + this.dead_time+":00";
    },
    dead_time(val){
      this.form.deadline = this.dead_date + ' ' + val+":00";
    },
    'form.dateTime':function(val){
      if(val){
        let a = val.split(" ");
        this.event_date = a[0];
        this.event_time = a[1].substr(0,5);
      }
    },
    'form.dateTime_2':function(val){
      if(val){
        let a = val.split(" ");
        this.event_date_2 = a[0];
        this.event_time_2 = a[1].substr(0,5);
      }
    },
    'form.deadline':function(val){
      if(val){
        let a = val.split(" ");
        this.dead_date = a[0];
        this.dead_time = a[1].substr(0,5);
      }
    }

  },
  created() {
    this.getRewardLevel();
    this.getCat();
    this.getDistrict();
    if(this.event_slug){
      this.edit_mode = true;
      this.loadEvent();
    }
    
  },
  methods: {
    loadEvent(){
      
      axios.get(`/api/event/${this.event_slug}`)
      .then(res => {
        if(res.data.s==1){
          let event = res.data.event;
          this.form.title = event.title;
          this.form.event_type = event.event_type;
          this.form.category_id = event.category_id;
          this.form.district_id = event.district_id;
          this.form.location = event.location;
          this.form.maximum = event.maximum;
          this.form.body = event.body;
          this.form.dateTime = event.dateTime;
          this.form.dateTime_2 = event.dateTime_2;
          this.form.deadline = event.deadline;
          this.form.days = event.days;
          this.slug = event.slug;
          if(event.image){
            this.event_image = `https://static.happybi.com.tw/events/${event.slug}/${event.image}`;
          }
          
        }
      })
      .catch(err => {
        console.error(err); 
      })

    },
    getRewardLevel(){
      axios.get('/api/getRewardLevel')
      .catch(err => {console.error(err); })
      .then(res => {
        this.rewardLevel = res.data;
      })
    },
    onChangeFileUpload(){
        this.file = this.$refs.file.files[0];
        this.event_image = URL.createObjectURL(this.$refs.file.files[0]);
    },
    getDistrict(){
      axios.get('/api/district')
      .then(res => {
        this.district = res.data
      })
      .catch(err => {
        console.error(err); 
      })
    },
    getCat() {
      axios.get("/api/category")
        .then(res => {
          this.eventCat = res.data
          
          for(let i of res.data){
            this.eventCatDic[i.id] = i.name;
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

      if(this.edit_mode){
        this.updateRequest(formData);
      }else{
        this.storeRequest(formData);
      }
      
    },
    storeRequest(formData){
        axios.post("/api/event", formData,{
          headers:{
            'content-type': 'multipart/form-data',
          }
        })
        .then(res =>{
          if(res.data.s == 1){
            this.$router.push({path:"/event"});
          }else{
            alert(res.data.m);
          }
        })
        .catch(function (error) {
          console.log(error);
          alert('系統錯誤');
        })
    },
    updateRequest(formData){
      formData.append('_method','PUT');
      axios.post("/api/event/"+ this.slug, formData,{
          headers:{
            'content-type': 'multipart/form-data',
          }
        })
        .then(res =>{
          
          if(res.data.s == 1){
            this.$router.push({path:"/event"});
          }else{
            alert(res.data.m);
          }
        })
        .catch(function (error) {
          console.log(error);
          alert('系統錯誤');
        })
    }

  }
};
</script>

<style>
</style>