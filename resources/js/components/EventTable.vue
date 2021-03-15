<template>
<div class="event-table">
  <div>
      <EventMemberModal></EventMemberModal>
      <LocationManagerModal :getUrl="'/api/event/getEventManagers/'" :postUrl="'/api/event/addManager/'" :deleteUrl="'/api/event/removeManager/'"></LocationManagerModal>
  </div>
  <!-- table -->
  <div>
    <div style="width:140px;padding:0 8px">
      <v-select @change="getTableData" v-model="public_selected" :items="public_select_options" item-value="value" label="上下架"></v-select>
    </div>
    <v-data-table
      :headers="headers"
      :items="eventArray"
      :rows-per-page-items="[15,30]"
      :pagination.sync="pagination"
      :total-items="totalEvent"
      :loading="loading"
      class="elevation-1"
    >
      <template v-slot:items="props">
        <td>{{props.index + 1}}</td>
        <td>
          <v-btn @click="updateEventPublicStatus(props.index)"
          :color="(props.item.public)?'success':'warning'">{{(props.item.public)?'上架':'下架'}}</v-btn>
        </td>
        <td>{{eventCat[props.item.category_id]}}</td>
        <td>{{props.item.owner}}</td>
        <td class="image-td">
          <img :src="staticHost + '/events/'+props.item.slug+'/'+props.item.image">
        </td>
        <td class="title-column" @click="showRewardQrcode(props.item.slug)">{{props.item.title}}</td>
        <td>({{district[props.item.district_id]}}){{props.item.location}}</td>
        <td>{{props.item.dateTime}}</td>
        <td>{{props.item.dateTime_2}}</td>
        <td>{{props.item.deadline}}</td>
        <td>{{props.item.people}}/{{props.item.maximum}}</td>
        <td>
          <v-icon @click="showEventMembers(props.item.slug,props.item.title)">account_circle</v-icon>
        </td>
        <td>
          <v-btn color="success" @click="editManager(props.item)">管理員</v-btn>
          <v-btn color="info" @click="editEvent(props.item.slug)">編輯</v-btn>
        </td>
      </template>
    </v-data-table>
  </div>
</div>


</template>

<script>
import EventMemberModal from "./Event/EventMemberModal";
import LocationManagerModal from "./Location/LocationManagerModal";
export default {
  components:{
    EventMemberModal,
    LocationManagerModal,
  },
  data() {
    return {
      pagination: { sortBy: "id", descending: true },
      totalEvent: 0,
      loading: true,
      eventArray: [],
      eventCat:{},
      district:{},
      headers: [
        { text:'#'},
        { text: "-",width:"1%"},
        { text: "類別", value: "category_id" },
        { text: "老師", value: "owner" },
        { text: "-", value: "image" },
        { text: "活動", value: "title" },
        { text: "地點", value: "location" },
        { text: "活動時間", value: "dateTime" },
        { text: "結束時間", value: "dateTime_2" },
        { text: "截止日期", value: "deadline" },
        { text: "人數上限", value: "maximum" },
        { text: "成員"},
        { text: "-",width:"1%"},
      ],
      staticHost:'',
      public_selected:1,
      public_select_options:[
        {text:'上架',value:1},
        {text:'全部',value:null},
        {text:'下架',value:0},
      ],
    };
  },
  watch: {
    pagination: {
      handler() {
        this.getTableData();
      }
    }
  },
  created(){
    this.getCat();
    this.getDistrict();
    User.authOnly();
  },
  methods:{
    updateEventPublicStatus(index){
      let event = this.eventArray[index];
      axios.post('/api/updateEventPublicStatus', {
        event_id:event.id,
        public:!event.public,
      })
      .catch(error=> { Exception.handle(error); })
      .then(res=>{
        if(res.data.s==1){
          this.eventArray[index].public = res.data.public;
        }
      })
      
    },
    showEventMembers(event_slug,event_name){
      let data = {event_slug,event_name};
      EventBus.$emit('showEventMemberModal',data);
    },
    editManager(event){
      EventBus.$emit('showLocationManagers',{'name':event.title,'slug':event.slug});
    },
    editEvent(id){
      this.$router.push({path:'/editEvent/'+id})
    },
    getDistrict(){
      axios.get('/api/district')
      .then(res => {
        
        for(let data of res.data){
          this.district[data.id] = data.name;
        }
      })
      .catch(err => {
        console.error(err); 
      })
    },
    getCat(){
      axios.get('/api/category')
      .then(res => {
        
        for(let data of res.data){
          this.eventCat[data.id] = data.name;
        }
      })
      .catch(err => {
        console.error(err); 
      })
    },
    showRewardQrcode(slug){
      var win = window.open(`/event_reward_qrcode/${slug}`,'_blank');
      win.focus();
    },
    getTableData() {
      this.loading = true;
      axios.get("/api/event", {
        params: {
          page: this.pagination.page,
          rowsPerPage: this.pagination.rowsPerPage,
          descending: this.pagination.descending,
          sortBy: this.pagination.sortBy,
          public: this.public_selected,
        }   
      })
      .then(res => {
        this.staticHost = res.data.staticHost;
        this.totalEvent = res.data.total;
        this.eventArray = res.data.events;
        this.loading=false;
      })
      .catch(error => {
        Exception.handle(error);
      });
    },

  }


};
</script>

<style>
.event-table td,.event-table th{
  padding: 0 4px!important;
}
.event-table .title-column{
  cursor: pointer;
}
.event-table .title-column:hover{
  background: gray;
  color: #fff;
}
.image-td{
  width: 100px;
}
.image-td img{
  max-width: 100%;
  max-height: 100%;
  height: auto;
  width: auto;
}
</style>