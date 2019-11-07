<template>
  <v-dialog v-model="dialog" max-width="480px">
    <v-card id="member-detail-dialog" :class="(!isReadMode)?'blue lighten-5':'21'">
      <v-card-title class="headline">姓名：{{user.name}}</v-card-title>

      <div class="data-row">
        <span>姓名</span>
        <input type="text" v-model="user.name" :disabled="isReadMode" />
      </div>

      <div class="data-row">
        <span>身分證</span>
        <input type="text" v-model="user.id_number" :disabled="isReadMode" />
      </div>

      <div class="data-row">
        <span>生日</span>
        <input type="text" v-model="user.birthdate" :disabled="isReadMode" />
      </div>

      <div class="data-row">
        <span>帳號</span>
        <input type="text" v-model="user.email" :disabled="isReadMode" />
      </div>

      <div class="data-row">
        <span>手機</span>
        <input type="text" v-model="user.phone" :disabled="isReadMode" />
      </div>

      <div class="data-row">
        <span>家電</span>
        <input type="text" v-model="user.tel" :disabled="isReadMode" />
      </div>

      <div class="data-row">
        <span>會員編號</span>
        <input type="text" v-model="user.id_code" disabled/>
      </div>

      <div class="data-row">
        <span>樂幣</span>
        <input type="text" v-model="user.wallet" disabled/>
      </div>

      <div class="data-row">
        <span>推薦人</span>
        <input type="text" v-model="user.inviter" :disabled="isReadMode" />
      </div>

      <div class="data-row">
        <span>推薦人電話</span>
        <input type="text" v-model="user.inviter_phone" :disabled="isReadMode" />
      </div>

      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn v-if="!isReadMode" color="gray darken-1" flat="flat" @click="cancelEditMode">取消</v-btn>
        <v-btn v-if="!isReadMode" color="green darken-1" flat="flat" @click="editMemberDetailRequest">更新</v-btn>

        <v-btn v-if="isReadMode" color="blue darken-1" flat="flat" @click="editMode">編輯</v-btn>
        <v-btn v-if="isReadMode" color="gray darken-1" flat="flat" @click="dialog = false">關閉</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  
  created() {
    EventBus.$on("showMemberDetail", user => {
      this.dialog = true;
      this.getMemberDetail(user.id);
      this.user_name = user.name;
    });
  },
  data() {
    return {
      isReadMode: true,
      dialog: false,
      user: {},
      user_temp: {}
    };
  },
  methods: {
    getMemberDetail(id) {
      axios
        .get(`/api/getMemberDetail/${id}`)
        .then(res => {
          if (res.data.length != 0) {
            this.user = res.data;
          }
          // console.log(res);
        })
        .catch(error => {
          console.log(error);
        });
    },
    editMemberDetailRequest() {
      axios
        .post("/api/updateMemberAccount",this.user)
        .then(res => {
          if(res.data.s==1){
            this.isReadMode = true;
          }
          console.log(res);
        })
        .catch(error => {
          console.log(error);
        });
    },
    editMode() {
      this.isReadMode = false;
      this.user_temp = JSON.parse(JSON.stringify(this.user));
    },
    cancelEditMode(){
      this.isReadMode = true;
      this.user = JSON.parse(JSON.stringify(this.user_temp));
    }
  }
};
</script>

<style>
#member-detail-dialog .data-row {
  padding: 0 16px;
  margin-bottom: 4px;
}
#member-detail-dialog input {
  width: 100%;
  border: 1px solid lightgray;
  border-radius: 0.2rem;
  height: 32px;
  padding: 0 12px;
  background-color: #fff;
}
</style>