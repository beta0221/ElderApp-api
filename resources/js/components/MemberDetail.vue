<template>

  <v-dialog v-model="dialog" max-width="480px">
    <v-card id="member-detail-dialog" :class="(!isReadMode)?'blue lighten-5':'21'">
      <v-card-title class="headline">姓名：{{user.name}} - (<span :class="(user.valid)?'valid':'un-valid'">{{(user.valid)?'有效會員':'待付款'}}</span>)</v-card-title>
      
      <div class="data-row">
        <span>姓名</span>
        <input type="text" v-model="user.name" :disabled="isReadMode" />
      </div>

      <div class="data-row">
        <span :class="(user.valid)?'valid':'un-valid'">{{(user.valid)?'有效會員':'待付款'}}</span>
        <input type="checkbox" v-model="user.valid" :disabled="isReadMode">
      </div>

      <div class="data-row">
        <span>身分證</span>
        <input type="text" v-model="user.id_number" :disabled="isReadMode" />
      </div>

      <div class="data-row">
        <span>性別</span>
        <select v-model="user.gender" :disabled="isReadMode">
          <option value="0">女</option>
          <option value="1">男</option>
        </select>
      </div>

      <div class="data-row">
        <span>生日</span>
        <input type="text" v-model="user.birthdate" :disabled="isReadMode" />
      </div>

      <div class="data-row">
        <span>帳號</span>
        <input type="text" v-model="user.email" :disabled="isReadMode"/>
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
        <v-btn style="margin-left:0" @click="editInviter">編輯推薦人</v-btn>
      </div>

      <div class="data-row">
        <span>推薦人電話</span>
        <input type="text" v-model="user.inviter_phone" :disabled="isReadMode" />
      </div>

      <div class="data-row">
        <span>地址</span>
        <input type="text" v-model="user.address" :disabled="isReadMode" />
      </div>

      <div class="data-row">
        <span>發票號碼</span>
        <input type="text" v-model="user.invoice" :disabled="isReadMode">
      </div>

      <div class="data-row" v-if="editPasswordMode">
        <hr style="margin:20px 0">
      </div>

      <div class="data-row" v-if="editPasswordMode">
        <span>密碼</span>
        <input type="text" v-model="password"/>
      </div>

      <div class="data-row" v-if="editPasswordMode">
        <span>管理員驗證碼</span>
        <input type="text" v-model="adminCode"/>
      </div>

      <v-card-actions>
        <v-btn color="green darken-1" @click="showSendMoneyPanel">發送樂幣</v-btn>
        <v-spacer></v-spacer>
        <v-btn v-if="!isReadMode || editPasswordMode" color="gray darken-1" flat="flat" @click="cancelEditMode">取消</v-btn>
        <v-btn v-if="!isReadMode" color="green darken-1" flat="flat" @click="editMemberDetailRequest">更新</v-btn>
        <v-btn v-if="editPasswordMode" color="red darken-1" flat="flat" @click="editPasswordRequest">確定修改</v-btn>

        <v-btn v-if="!editPasswordMode && isReadMode" color="red darken-1" flat="flat" @click="editPassword">改密碼</v-btn>
        <v-btn v-if="isReadMode && !editPasswordMode" color="blue darken-1" flat="flat" @click="editMode">編輯</v-btn>
        <v-btn v-if="isReadMode && !editPasswordMode" color="gray darken-1" flat="flat" @click="dialog = false">關閉</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  destroyed(){
    EventBus.$off('showMemberDetail');
  },
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
      user_temp: {},
      editPasswordMode:false,
      password:'',
      adminCode:'',
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
        })
        .catch(error => {
          console.log(error);
        });
    },
    showSendMoneyPanel(){
      EventBus.$emit("showSendMoneyPanel",this.user);
    },
    editMemberDetailRequest() {
      axios
        .post("/api/updateMemberAccount",this.user)
        .then(res => {
          if(res.data.s==1){
            this.isReadMode = true;
            if(this.user.valid){
              this.user.valid = 1;
            }else{
              this.user.valid = 0;
            }
            EventBus.$emit("updateMemberSuccess",this.user);
          }else{
            alert(res.data.m);
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
    editInviter(){
      EventBus.$emit("showInviterPanel",this.user);
    },
    cancelEditMode(){
      this.isReadMode = true;
      this.editPasswordMode=false;
      this.user = JSON.parse(JSON.stringify(this.user_temp));
    },
    editPassword(){
      this.editPasswordMode=true;
      this.user_temp = JSON.parse(JSON.stringify(this.user));
    },
    editPasswordRequest(){
      axios.post('/api/updateMemberPassword/'+this.user.id_code, {
        'password':this.password,
        'adminCode':this.adminCode
      })
      .then(res=>{
        if(res.status==200){
          alert('成功');
          this.password='';
          this.adminCode='';
          this.isReadMode=true;
          this.editPasswordMode=false;
        }
        console.log(res);
      })
      .catch(error=>{
        alert(error);
      });
    }
  }
};
</script>

<style>
.valid{
  color:green
}
.un-valid{
  color:#f44336;
}
#member-detail-dialog .data-row {
  padding: 0 16px;
  margin-bottom: 4px;
}
#member-detail-dialog input,#member-detail-dialog select {
  width: 100%;
  border: 1px solid lightgray;
  border-radius: 0.2rem;
  height: 32px;
  padding: 0 12px;
  background-color: #fff;
}
</style>