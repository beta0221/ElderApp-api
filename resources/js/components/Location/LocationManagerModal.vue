<template>
  <v-dialog v-model="showDialog" max-width="480px">
    <v-card id="member-detail-dialog" style="padding: 0 12px">
      <v-card-title v-if="name != 'undefinded'" class="headline">
          {{ name }} 管理人員
        </v-card-title>

      <UserSearchbox v-on:clickUser="selectUser"></UserSearchbox>

    <p style="margin-top:20px">管理人員</p>
    <div class="border search-box">
          <div
            class="member-cell"
            v-for="user in managerList"
            v-bind:key="user.id">
            {{ user.name }} {{user.email}} <span :class="(user.valid==1)?'green--text':'red--text'">{{(user.valid==1)?'有效':'無效'}}</span>
            <button style="display:inline-block;float:right" @click="removeUser(user.id)">取消</button>
          </div>
      </div>

    <div style="height: 20px"></div>

      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn @click="showDialog=false">關閉</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import UserSearchbox from '../UserSearchbox'

export default {
  props:['getUrl','postUrl','deleteUrl'],
  components:{
    UserSearchbox,
  },
  mounted() {
    EventBus.$on("showLocationManagers", (data) => {
      this.showDialog = true;
      this.name = data.name;
      this.slug = data.slug;
      this.getLocationManagers();
    });
  },
  destroyed(){
    EventBus.$off('showLocationManagers');
  },
  data() {
    return {
      showDialog: false,
      name:'',
      slug:'',
      managerList: [],
      searchList: [],
      search_text: "",
    };
  },
  methods: {
    searchUser() {
        if(!this.search_text){return;}
      axios
        .get("/api/search-member", {
          params: {
            searchColumn: "name",
            searchText: this.search_text,
          },
        })
        .then((res) => {
          this.searchList = res.data;
        })
        .catch((error) => {
          Exception.handle(error);
        });
    },
    getLocationManagers() {
      axios
        .get(`${this.getUrl}${this.slug}`)
        .then((res) => {
          this.managerList = res.data;
        })
        .catch((error) => {
          Exception.handle(error);
        });
    },
    selectUser(user_id){
        axios.post(`${this.postUrl}${this.slug}`, {
            user_id:user_id   
        })
        .then((res) => {
          this.getLocationManagers();
        })
        .catch((error) => {
          Exception.handle(error);
        });
    },
    removeUser(user_id){
        axios.post(`${this.deleteUrl}${this.slug}`, {
            user_id:user_id   
        })
        .then((res) => {
          this.getLocationManagers();
        })
        .catch((error) => {
          Exception.handle(error);
        });
    }
  },
};
</script>

<style>
.search-box{
    height: 100px;
    overflow-y: scroll;
}
</style>