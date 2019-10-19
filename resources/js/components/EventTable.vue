<template>
<div>
  <!-- title bar -->
  <div>
    
      <router-link class="white--text" to="/newEvent" style="text-decoration:none;">
        <v-btn color="success">
          新增活動
        </v-btn>
      </router-link>
      
  </div>


  <div>
    <v-dialog v-model="dialog" max-width="480px">
        <v-card>
          <v-card-title class="headline">活動：{{dialogName}}</v-card-title>

          <span style="padding:2px 16px" v-if="eventGuests.length == 0">目前無人參加此活動。</span>
          <div style="padding:2px 16px" v-for="guest in eventGuests" v-bind:key="guest.id">
            <span :class="gender[guest.gender]">{{guest.name}}</span>
            <span>手機:{{guest.phone}}</span>
            <span>家電:{{guest.tel}}</span>
          </div>

          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="green darken-1" flat="flat" @click="dialog = false">關閉</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
  </div>
  <!-- table -->
  <div>
    <member-detail :some="123"></member-detail>
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
        <td>{{eventCat[props.item.category_id]}}</td>
        
        <td>{{props.item.title}}</td>
        <td>{{district[props.item.district_id]}}</td>
        <td>{{props.item.location}}</td>
        <td>{{props.item.dateTime}}</td>
        <td>{{props.item.dateTime_2}}</td>
        <td>{{props.item.deadline}}</td>
        <td>{{props.item.people}}/{{props.item.maximum}}</td>
        <td>
          <v-icon @click="showEventMembers(props.item.slug,props.item.title)">account_circle</v-icon>
        </td>
        <td>
          <v-btn color="info" @click="editEvent(props.item.slug)">編輯</v-btn>
        </td>
      </template>
    </v-data-table>
  </div>
</div>


</template>

<script>
import MemberDetail from "./MemberDetail";
export default {
  components:{
    MemberDetail,
  },
  data() {
    return {
      dialog: false,
      dialogName: "",
      eventGuests:[],

      gender: { 1: "blue--text", 0: "red--text" },
      pagination: { sortBy: "id", descending: true },
      totalEvent: 0,
      loading: true,
      eventArray: [],
      eventCat:{},
      district:{},
      headers: [
        { text:'#'},
        { text: "類別", value: "category_id" },
        
        { text: "活動", value: "title" },
        { text: "地區", value: "district_id" },
        { text: "地點", value: "location" },
        { text: "活動時間", value: "dateTime" },
        { text: "結束時間", value: "dateTime_2" },
        { text: "截止日期", value: "deadline" },
        { text: "人數上限", value: "maximum" },
        { text: "成員"},
        { text: "-"},
      ],
      
    };
  },
  watch: {
    pagination: {
      handler() {
        this.getDataFromApi().then(data => {
          this.eventArray = data.items;
          this.totalEvent = data.total;
        });
      }
    }
  },
  created(){
    this.getCat();
    this.getDistrict();
  },
  methods:{
    showEventMembers(event_slug,event_name){
      
      this.dialog = true;
      this.dialogName = event_name;
      axios.get(`/api/eventguests/${event_slug}`)
      .then(res => {
        if(res.data.s == 1){
          this.eventGuests = res.data.guests;
        }
        console.log(res)
      })
      .catch(err => {
        console.error(err); 
      })
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
    getDataFromApi() {
      this.loading = true;
      return new Promise((resolve, reject) => {
        const { sortBy, descending, page, rowsPerPage } = this.pagination;
        // console.log(this.pagination);
         axios
          .get("/api/event", {
            params: {
              page: this.pagination.page,
              rowsPerPage: this.pagination.rowsPerPage,
              descending: this.pagination.descending,
              sortBy: this.pagination.sortBy
            }
          })
          .then(res => {
            // console.log(res.data);
            // return false;
            let items = res.data.events;
            
            const total = res.data.total;

            if (this.pagination.sortBy) {
              items = items.sort((a, b) => {
                const sortA = a[sortBy];
                const sortB = b[sortBy];

                if (descending) {
                  if (sortA < sortB) return 1;
                  if (sortA > sortB) return -1;
                  return 0;
                } else {
                  if (sortA < sortB) return -1;
                  if (sortA > sortB) return 1;
                  return 0;
                }
              });
            }

            setTimeout(() => {
              this.loading = false;
              resolve({
                items,
                total
              });
            }, 300);
          })
          .catch(error => {
            Exception.handle(error);
            // User.logout();
          })
      });
    },

  }


};
</script>

<style>
</style>